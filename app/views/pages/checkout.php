<?php

// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
  header("Location: /login");
  exit();
}

// Lấy user_id từ phiên
$user_id = $_SESSION['user_id'];

// Lấy thông tin người dùng từ hàm getUserById
$user = $userController->getUserById($user_id);

// Lấy các sản phẩm trong giỏ hàng của người dùng
$cartItems = $cartController->getCartItems($user_id);

if (empty($cartItems)) {
  header("Location: /index.php?page=cart&error=empty");
  exit();
}

// Lấy tổng giá trị giỏ hàng từ `total_cart_price`
$totalAmount = array_sum(array_column($cartItems, 'total_price'));

// Lấy danh sách voucher user đã nhận
$userVouchers = $userController->getUserVouchers($user['id']);
$voucherDiscount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {

  // Check CSRF token
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
  }

  // Lấy thông tin từ form
  $address = trim(strip_tags($_POST['address']));
  $payment_method = $_POST['payment_method'];
  $image = !empty($_POST['image']) ? $_POST['image'] : null;
  $voucher_code = !empty($_POST['voucher_code']) ? $_POST['voucher_code'] : null;
  $shipping_link = trim($_POST['shipping_link']) ?? null;

  if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

    if (in_array(strtolower($file_ext), $allowed)) {
      $image = $_FILES['image']['name'];
      if (move_uploaded_file($_FILES['image']['tmp_name'], "images/banking/$image")) {
        // Image uploaded successfully
      } else {
        $error = "Failed to upload the image. Please try again.";
      }
    } else {
      $error = "Invalid file format. Only JPG, JPEG, PNG, and GIF are allowed.";
    }
  }

  // Gọi OrderController để tạo một đơn hàng mới
  $order_id = $orderController->createOrder($user_id, $cartItems, $payment_method, $address, $shipping_link, $image, $voucher_code);

  // Thông báo đến cửa hàng có đơn hàng mới
  $message = "Have a new order from User: #$user_id with Order: #$order_id. Please check the order detail";
  $userController->addNotification(1, $message);

  // Xóa giỏ hàng sau khi đặt hàng thành công
  $cartController->clearCart($user_id);

  // Sau khi tạo đơn hàng thành công, tự động gửi email thông báo đến khách hàng
  header("Location: /index.php?page=send-email&order_id=$order_id&csrf_token=" . $_SESSION['csrf_token']);
  exit();
}

?>

<!-- Thông tin thanh toán -->
<div class="flex min-h-screen items-center justify-center bg-gradient-to-r from-blue-50 to-blue-100">

  <div class="text-center -mr-20 ml-10">
    <h2 class="text-5xl font-bold p-4 text-blue-700 flex items-center justify-center">
      CHECK-OUT
    </h2>
    <img src="/images/logo.png" alt="System Logo" class="mx-auto w-50 h-50 object-contain">
  </div>

  <form method="POST" action="/checkout" id="checkout-form" enctype="multipart/form-data"
    class="bg-white shadow-sm rounded-lg p-8 w-3/5 mx-auto mb-10 mt-10 border-1 border-yellow-300">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <!-- Bố cục chia thành hai cột -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Cột bên trái: Thông tin đơn hàng & địa chỉ giao hàng -->
      <div class="space-y-6">
        <!-- Thông tin đơn hàng -->
        <div class="bg-gray-100 p-6 rounded-lg shadow-sm">
          <h2 class="text-xl font-bold mb-4 text-gray-800">Order Details</h2>
          <div class="space-y-2">
            <?php foreach ($cartItems as $item): ?>
              <p class="text-gray-800">
                <?= htmlspecialchars($item['name']) ?> (<?= htmlspecialchars($item['quantity']) ?> x <?= htmlspecialchars($item['size']) ?>) -
                $<?= number_format($item['total_price'], 2) ?>
              </p>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Thông tin giao hàng -->
        <div class="bg-gray-100 p-6 rounded-lg shadow-sm">
          <h2 class="text-xl font-bold mb-4 text-gray-800">Shipping Address</h2>
          <textarea name="address" id="address" rows="3" class="w-full p-3 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400" required placeholder="Enter your shipping address..."><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
          <button type="button" onclick="getLocation()" class="mt-3 w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 rounded-lg transition duration-200">
            Use Current Location
          </button>
          <div class="mt-3 flex justify-between text-sm text-gray-700">
            <p><strong>Latitude:</strong> <span id="latitude">N/A</span></p>
            <p><strong>Longitude:</strong> <span id="longitude">N/A</span></p>
          </div>
          <div class="mt-2 text-center">
            <input type="hidden" name="shipping_link" id="map_url">
            <a id="mapLink" href="#" target="_blank" class="text-blue-500 underline hidden">View on Google Maps</a>
          </div>
        </div>

        <!-- Áp dụng Voucher Code -->
        <div class="bg-gray-100 p-6 rounded-lg shadow-sm">
          <h2 class="text-xl font-bold mb-4 text-gray-800">Voucher Code</h2>
          <select name="voucher_code" id="voucher_code" class="w-full p-3 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400" onchange="updateDiscount()">
            <option value="" data-discount="0">-- Select a voucher --</option>
            <?php foreach ($userVouchers as $voucher): ?>
              <?php if ($totalAmount >= $voucher['min_order_value']): ?>
                <option value="<?= htmlspecialchars($voucher['code']) ?>"
                  data-discount="<?= $voucher['discount_amount'] ?>"
                  data-min-order="<?= $voucher['min_order_value'] ?>"
                  title="<?= htmlspecialchars($voucher['description']) ?>">
                  <?= htmlspecialchars($voucher['code']) ?> (<?= $voucher['discount_amount'] < 1 ? '-' . ($voucher['discount_amount'] * 100) . '%' : '-$' . number_format($voucher['discount_amount'], 2) ?>)
                </option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Cột bên phải: Thanh toán & Tóm tắt đơn hàng -->
      <div class="space-y-6">
        <!-- Lưu ý đơn hàng -->
        <div class="p-4 bg-gray-50 rounded-lg border">
          <p class="text-sm text-gray-800"><strong>NOTE:</strong> Orders under $100 will incur a shipping fee of $1.50.</p>
        </div>

        <!-- Phương thức thanh toán -->
        <div class="bg-gray-100 p-6 rounded-lg shadow-sm">
          <h2 class="text-xl font-bold mb-4 text-gray-800">Payment Method</h2>
          <select name="payment_method" id="payment_method" class="w-full p-3 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400" required onchange="toggleBankTransfer()">
            <option value="" selected disabled hidden>-- Select Payment Method --</option>
            <option value="bank_transfer">Bank Transfer</option>
            <option value="cash_on_delivery">Cash on Delivery</option>
          </select>

          <!-- QR Code & Upload hình (chỉ hiển thị khi chọn chuyển khoản) -->
          <div id="bank_transfer_section" class="hidden mt-4 bg-white p-4 rounded-lg shadow">
            <p class="font-semibold text-gray-800">Scan QR Code to Pay:</p>
            <img src="/images/qr-code.png" alt="QR Code" class="w-3/5 h-auto mx-auto my-2">
            <label class="block text-gray-700 font-semibold mt-4">Upload Payment Proof:</label>
            <input type="file" name="image" id="image" class="w-full p-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
          </div>
        </div>

        <!-- Tóm tắt đơn hàng -->
        <?php
        $shippingFee = ($totalAmount >= 100) ? 0 : 1.5;
        $totalWithShipping = $totalAmount + $shippingFee;
        ?>
        <div class="bg-gray-100 p-6 rounded-lg shadow-sm">
          <h2 class="text-xl font-bold mb-4 text-gray-800">Order Summary</h2>
          <div class="space-y-3">
            <div class="flex justify-between">
              <p class="text-gray-700">Subtotal</p>
              <p class="font-semibold text-gray-800">$<?= number_format($totalAmount, 2) ?></p>
            </div>
            <div class="flex justify-between">
              <p class="text-gray-700">Shipping</p>
              <p class="font-semibold text-gray-800">
                <?= ($shippingFee == 0) ? "<span class='text-green-500 font-bold'>FREE</span>" : "$" . number_format($shippingFee, 2) ?>
              </p>
            </div>
            <div class="flex justify-between">
              <p class="text-gray-700">Voucher Discount</p>
              <p id="discount_value" class="font-semibold text-green-500">
                - <?= $voucherDiscount < 1 ? ($totalAmount * $voucherDiscount) . '$' : '$' . number_format($voucherDiscountAmount, 2) ?>
              </p>
            </div>
            <div class="flex justify-between border-t pt-3">
              <p class="text-gray-700 font-bold">Total</p>
              <p id="total_price" class="font-bold text-red-500">$<?= number_format(max($totalWithShipping, 0), 2) ?></p>
            </div>
          </div>
        </div>

        <!-- Nút thao tác -->
        <div class="flex space-x-2">
          <button type="button" onclick="window.location.href='/cart'" class="w-1/2 bg-red-500 hover:bg-red-600 text-white font-semibold py-2 rounded-lg transition duration-200">
            Cancel
          </button>
          <button type="submit" onclick="confirmCheckout(event)" class="w-1/2 bg-green-500 hover:bg-green-600 text-white font-semibold py-2 rounded-lg transition duration-200">
            Place Order
          </button>
        </div>
      </div>
    </div>

    <!-- JavaScript: Lấy vị trí & xử lý lỗi -->
    <script>
      function getLocation() {
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(position => {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            document.getElementById("latitude").textContent = lat;
            document.getElementById("longitude").textContent = lon;

            // Tạo link Google Maps
            const mapUrl = `https://www.google.com/maps?q=${lat},${lon}`;
            const mapLink = document.getElementById("mapLink");
            mapLink.href = mapUrl;
            mapLink.classList.remove("hidden"); // Hiển thị link

            // Lưu link vào input ẩn để gửi lên server
            document.getElementById("map_url").value = mapUrl;

            // Tích hợp OSM (Nominatim) để lấy địa chỉ
            fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`, {
                headers: {
                  "User-Agent": "PizzaDeliveryApp/1.0 (your-email@example.com)" // Thay bằng email của bạn
                }
              })
              .then(response => response.json())
              .then(data => {
                if (data.error) {
                  console.error("OSM Error: ", data.error);
                  document.getElementById("address").value = "Unable to fetch address";
                } else {
                  const address = data.display_name;
                  document.getElementById("address").value = address; // Điền địa chỉ vào textarea
                }
              })
              .catch(error => {
                console.error("Fetch Error: ", error);
                document.getElementById("address").value = "Error fetching address";
              });
          }, showError);
        } else {
          alert("Geolocation is not supported by this browser.");
        }
      }

      function showError(error) {
        switch (error.code) {
          case error.PERMISSION_DENIED:
            alert("User denied the request for Geolocation.");
            break;
          case error.POSITION_UNAVAILABLE:
            alert("Location information is unavailable.");
            break;
          case error.TIMEOUT:
            alert("The request to get user location timed out.");
            break;
          case error.UNKNOWN_ERROR:
            alert("An unknown error occurred.");
            break;
        }
      }
    </script>
    <input type="hidden" name="checkout" value="1">

  </form>
</div>

<script>
  function confirmCheckout(event) {
    event.preventDefault(); // Ngăn form gửi ngay lập tức

    const address = document.getElementById('address').value.trim();
    const paymentMethod = document.querySelector('select[name="payment_method"]').value;

    if (!address) {
      alert("Please enter your shipping address.");
      return;
    }

    if (!paymentMethod) {
      alert("Please select a payment method.");
      return;
    }

    const confirmOrder = confirm("Are you sure want to place an order?");
    if (confirmOrder) {
      document.getElementById('checkout-form').submit();
    }
  }

  function toggleBankTransfer() {
    const paymentMethod = document.getElementById('payment_method').value;
    const bankTransferSection = document.getElementById('bank_transfer_section');

    if (paymentMethod === 'bank_transfer') {
      bankTransferSection.classList.remove('hidden');
    } else {
      bankTransferSection.classList.add('hidden');
    }
  }

  function updateDiscount() {
    const select = document.getElementById("voucher_code");
    const selectedOption = select.options[select.selectedIndex];
    const discountValue = parseFloat(selectedOption.getAttribute("data-discount")) || 0;
    const minOrder = parseFloat(selectedOption.getAttribute("data-min-order")) || 0;
    let subtotal = parseFloat(<?= json_encode($totalAmount) ?>);

    // Kiểm tra subtotal có hợp lệ không
    if (isNaN(subtotal)) {
      console.error("Subtotal is invalid.");
      subtotal = 0; // Đảm bảo không gây lỗi
    }

    if (subtotal < minOrder) {
      alert(`Your order must be at least $${minOrder.toFixed(2)} to use this voucher.`);
      select.value = ""; // Reset lựa chọn
      return;
    }

    // Tính phí vận chuyển
    let shippingFee = subtotal < 100 ? 1.5 : 0;
    subtotal += shippingFee;

    // Tính toán giảm giá
    let discountAmount = discountValue < 1 ? subtotal * discountValue : discountValue;
    let totalAfterDiscount = Math.max(subtotal - discountAmount, 0); // Đảm bảo không bị âm

    // Cập nhật UI
    document.getElementById("discount_value").textContent = discountValue < 1 ?
      `-${(discountValue * 100).toFixed(0)}%` :
      `-$${discountAmount.toFixed(2)}`;

    document.getElementById("total_price").textContent = `$${totalAfterDiscount.toFixed(2)}`;
  }
</script>
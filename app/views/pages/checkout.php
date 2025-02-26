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

// Khởi tạo các controller
$cartController = new CartController($conn);
$orderController = new OrderController($conn);
$userController = new UserController($conn);

// Lấy user_id từ phiên
$user_id = $_SESSION['user_id'];

// Lấy thông tin người dùng từ hàm getUserById
$user = $userController->getUserById($user_id);

// Lấy các sản phẩm trong giỏ hàng của người dùng
$cartItems = $cartController->viewCart($user_id);

if (empty($cartItems)) {
  header("Location: /index.php?page=cart&error=empty"); // Nếu giỏ hàng trống, điều hướng về trang giỏ hàng với thông báo lỗi
  exit();
}

// Lấy tổng giá trị giỏ hàng từ `total_cart_price`
$totalAmount = $cartItems[0]['total_cart_price'] ?? 0; // Sử dụng giá trị tổng giỏ hàng từ sản phẩm đầu tiên

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) { // Kiểm tra xem có yêu cầu đặt hàng hay không
  // Check CSRF token
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
  }

  $address = trim(strip_tags($_POST['address'])); // Làm sạch địa chỉ
  $payment_method = $_POST['payment_method'];

  $image = !empty($_POST['image']) ? $_POST['image'] : null; // Lấy giá trị hình ảnh hoặc có hoặc trả về null

  if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

    if (in_array(strtolower($file_ext), $allowed)) {
      $image = $_FILES['image']['name'];
      if (move_uploaded_file($_FILES['image']['tmp_name'], "banking_images/$image")) {
        // Image uploaded successfully
      } else {
        $error = "Failed to upload the image. Please try again.";
      }
    } else {
      $error = "Invalid file format. Only JPG, JPEG, PNG, and GIF are allowed.";
    }
  }

  // Gọi OrderController để tạo một đơn hàng mới
  $order_id = $orderController->createOrder($user_id, $cartItems, $payment_method, $address, $image);

  // Xóa giỏ hàng sau khi đặt hàng thành công
  $cartController->clearCart($user_id);

  // Điều hướng đến trang thành công đơn hàng
  header("Location: /order-success/order_id=$order_id");
  exit();
}

?>

<!-- Thông tin thanh toán -->
<h1 class="text-center mt-8 text-4xl font-bold text-blue-700 tracking-wide">CHECK OUT</h1>

<div class="container mx-auto px-4 mt-4">
  <form method="POST" action="/checkout" id="checkout-form" enctype="multipart/form-data" class="bg-white shadow-lg border rounded-lg p-8 max-w-4xl mx-auto mb-6">
    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <!-- Bố cục chia thành hai cột -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- Cột bên trái: Thông tin giỏ hàng và địa chỉ giao hàng -->
      <div class="space-y-6">
        <!-- Thông tin giỏ hàng -->
        <div class="bg-gray-100 p-6 rounded-lg shadow-sm">
          <h2 class="text-xl font-bold mb-4 text-gray-800">Your Cart</h2>
          <div class="space-y-4">
            <?php foreach ($cartItems as $item): ?>
              <div class="flex items-center justify-between border-b pb-4">
                <div class="flex items-center space-x-4">
                  <img src="/images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-12 h-12 rounded-lg object-cover">
                  <div>
                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($item['name']) ?></p>
                    <p class="text-sm text-gray-600">Quantity: <?= htmlspecialchars($item['quantity']) ?></p>
                    <p class="text-sm text-gray-600">Size: <?= htmlspecialchars($item['size']) ?></p>
                  </div>
                </div>
                <p class="font-semibold text-gray-800">
                  $<?= number_format($item['total_price'], 2) ?>
                </p>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <!-- Thông tin giao hàng -->
        <div class="bg-gray-100 p-6 rounded-lg shadow-sm">
          <h2 class="text-xl font-bold mb-4 text-gray-800">Shipping Information</h2>
          <textarea name="address" id="address" class="w-full p-3 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400" required placeholder="Enter your shipping address..."><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- Cột bên phải: Tổng số tiền và thanh toán -->
      <div class="space-y-6">
        <!-- Phương thức thanh toán -->
        <div class="bg-gray-100 p-6 rounded-lg shadow-sm">
          <h2 class="text-xl font-bold mb-4 text-gray-800">Payment Method</h2>
          <select name="payment_method" id="payment_method" class="w-full p-3 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400" required onchange="toggleBankTransfer()">
            <option value="" selected disabled hidden>-- Select Payment Method --</option>
            <option value="bank_transfer">Bank Transfer</option>
            <option value="cash_on_delivery">Cash on Delivery</option>
          </select>

          <!-- QR Code + Upload hình -->
          <div id="bank_transfer_section" class="hidden mt-4 bg-white p-4 rounded-lg shadow">
            <p class="font-semibold text-gray-800">Scan QR Code to Pay:</p>
            <img src="/images/qr-code.png" alt="QR Code" class="w-3/5 h-auto mx-auto my-2">

            <label class="block text-gray-700 font-semibold mt-4">Upload Payment Proof:</label>
            <input type="file" name="image" id="image" class="w-full p-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
          </div>
        </div>

        <!-- Tổng số tiền -->
        <div class="bg-gray-100 p-6 rounded-lg shadow-sm">
          <h2 class="text-xl font-bold mb-4 text-gray-800">Order Summary</h2>
          <div class="space-y-3">
            <div class="flex justify-between">
              <p class="text-gray-700">Subtotal</p>
              <p class="font-semibold text-gray-800">$<?= number_format($totalAmount, 2) ?></p>
            </div>
            <div class="flex justify-between">
              <p class="text-gray-700">Shipping</p>
              <p class="font-semibold text-gray-800">$2.99</p>
            </div>
            <div class="flex justify-between border-t pt-3">
              <p class="text-gray-700 font-bold">Total</p>
              <p class="font-bold text-red-500">$<?= number_format($totalAmount + 2.99, 2) ?></p>
            </div>
          </div>
        </div>

        <input type="hidden" name="checkout" value="1">

        <!-- Nút thao tác -->
        <div class="flex flex-col space-y-4">
          <button type="submit" onclick="confirmCheckout(event)" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 w-full">Place Order</button>
          <button type="button" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 w-full" onclick="window.location.href='/cart'">Cancel</button>
        </div>
      </div>
    </div>
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

    const confirmOrder = confirm("Are you sure you want to place an order?");
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
</script>
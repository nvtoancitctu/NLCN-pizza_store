<?php

// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Kiểm tra xem người dùng đã đăng nhập chưa, nếu chưa sẽ điều hướng về trang đăng nhập
if (!isset($_SESSION['user_id'])) {
  header("Location: /login");
  exit();
}

// Lấy user_id từ session để sử dụng trong việc lấy dữ liệu đơn hàng
$user_id = $_SESSION['user_id'];
$orders = $orderController->getOrdersByUserId($user_id);
$user = $userController->getUserById($user_id);

// Lấy danh sách voucher user đã nhận
$userVouchers = $userController->getUserVouchers($user['id']);

// Xử lý điều kiện khi người dùng nhấn vào nút Admin Panel, Logout, hoặc cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if (isset($_POST['admin_panel']) && $user['role'] === 'admin') {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
      die('Invalid CSRF token');
    } else {
      unset($_SESSION['csrf_token']);
    }
    // Điều hướng đến trang quản lý sản phẩm nếu người dùng có quyền admin
    header("Location: /admin");
    exit();
  }

  if (isset($_POST['update_profile'])) {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
      die('Invalid CSRF token');
    } else {
      unset($_SESSION['csrf_token']);
    }
    // Lấy dữ liệu từ form cập nhật
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $image = $_POST['image'] ?? NULL;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
      $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
      $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

      if (in_array(strtolower($file_ext), $allowed)) {
        $image = $_FILES['image']['name'];
        if (move_uploaded_file($_FILES['image']['tmp_name'], "images/avatar/$image")) {
          // Image uploaded successfully
        } else {
          $error = "Failed to upload the image. Please try again.";
        }
      } else {
        $error = "Invalid file format. Only JPG, JPEG, PNG, and GIF are allowed.";
      }
    }
    // Gọi hàm cập nhật thông tin
    $updated = $userController->updateUserProfile($user_id, $name, $phone, $address, $image);

    if ($updated) {
      $_SESSION['success'] = "Profile updated successfully.";
    } else {
      $_SESSION['error'] = "Failed to update profile.";
    }

    header("Location: /account");
    exit();
  }
}
?>

<!-- Profile Section -->
<div class="container mx-auto w-4/5">
  <h2 class="text-4xl font-extrabold my-8 text-center text-blue-700 drop-shadow-lg">PROFILE</h2>

  <!-- Thông tin người dùng hiển thị dưới dạng 2 phần -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-8 p-8 bg-white shadow-md rounded-xl mx-auto mb-8 border-2 border-yellow-400">
    <!-- Phần Avatar bên trái -->
    <div class="flex flex-col items-center md:col-span-1">
      <img src="/images/avatar/<?= $user['avatar'] ?? 'user.png' ?>" alt="User Avatar"
        class="w-32 h-32 rounded-full border-4 border-yellow-400 shadow-md">
      <p class="text-gray-700 font-semibold mt-3"><?= htmlspecialchars($user['name'] ?? 'N/A') ?></p>
    </div>

    <!-- Phần Thông tin bên phải (3 cột) -->
    <div class="md:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Name -->
      <div class="flex items-center space-x-4">
        <i class="fas fa-user text-3xl text-yellow-500"></i>
        <div>
          <p class="font-semibold text-gray-800">ID</p>
          <p class="text-gray-600">#<?= htmlspecialchars($user['id'] ?? 'N/A') ?></p>
        </div>
      </div>

      <!-- Email -->
      <div class="flex items-center space-x-4">
        <i class="fas fa-envelope text-3xl text-yellow-500"></i>
        <div>
          <p class="font-semibold text-gray-800">Email</p>
          <p class="text-gray-600"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></p>
        </div>
      </div>

      <!-- Admin Panel -->
      <div class="flex justify-center items-center space-x-4 col-span-1">
        <?php if ($user['role'] === 'admin'): ?>
          <form method="POST" action="/account">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <button type="submit" name="admin_panel"
              class="bg-yellow-400 hover:bg-yellow-500 text-black font-bold py-2 px-6 rounded-lg transition duration-200">
              Admin Panel
            </button>
          </form>
        <?php else: ?>
          <button class="bg-yellow-400 hover:bg-yellow-500 text-black font-bold p-3 rounded-lg transition duration-200">
            Customer
          </button>
        <?php endif; ?>
      </div>

      <!-- Phone -->
      <div class="flex items-center space-x-4">
        <i class="fas fa-phone text-3xl text-yellow-500"></i>
        <div>
          <p class="font-semibold text-gray-800">Phone</p>
          <p class="text-gray-600"><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></p>
        </div>
      </div>

      <!-- Address -->
      <div class="flex items-center space-x-4">
        <i class="fas fa-map-marker-alt text-3xl text-yellow-500"></i>
        <div>
          <p class="font-semibold text-gray-800">Address</p>
          <p class="text-gray-600"><?= htmlspecialchars($user['address'] ?? 'N/A') ?></p>
        </div>
      </div>

      <!-- Update Profile -->
      <div class="flex justify-center items-center space-x-4 col-span-1">
        <button onclick="toggleForm()"
          class="bg-blue-500 hover:bg-blue-600 text-white font-bold p-3 rounded-lg transition duration-200">
          Update Profile
        </button>
      </div>

    </div>
  </div>

  <!-- Form Cập Nhật Thông Tin Người Dùng, mặc định bị ẩn -->
  <div id="update-profile-form" class="space-y-6 mt-4 mb-8 hidden mx-auto">
    <form action="/account" method="POST" enctype="multipart/form-data" class="space-y-2 bg-white p-6 rounded-lg shadow-md border-2 border-blue-200">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <!-- Name, Phone -->
      <div class="flex flex-col md:flex-row justify-between">
        <div class="w-full md:w-1/2 pr-0 md:pr-3 mb-4 md:mb-0">
          <label for="name" class="block text-gray-700 font-semibold">Name</label>
          <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" class="w-full p-3 border border-gray-300 rounded-md" required>
        </div>

        <div class="w-full md:w-1/2 pl-0 md:pl-3">
          <label for="phone" class="block text-gray-700 font-semibold">Phone</label>
          <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" class="w-full p-3 border border-gray-300 rounded-md">
        </div>
      </div>
      <!--  -->
      <div class="flex flex-col md:flex-row justify-between">
        <div class="w-full md:w-1/2 pr-0 md:pr-3 mb-4 md:mb-0">
          <label for="image" class="block text-red-500 text-sm font-medium mb-2"><i class="fas fa-image mr-2"></i>Product Image</label>
          <div class="flex items-center gap-4">
            <input type="file" name="image" id="image" class="hidden" onchange="updateFileName(this)">
            <label for="image" class="border border-gray-200 rounded-lg px-4 py-2 text-gray-600 cursor-pointer hover:bg-blue-100">
              <i class="fas fa-upload"></i>
            </label>
            <span id="file-name" class="text-gray-500">No file chosen</span>
          </div>
          <div class="mt-2">
            <img id="image-preview" class="hidden w-32 h-32 object-cover rounded-lg" />
          </div>
        </div>

        <div class="w-full md:w-1/2 pl-0 md:pl-3">
          <label for="address" class="block text-gray-700 font-semibold">Address</label>
          <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" class="w-full p-3 border border-gray-300 rounded-md">
        </div>
      </div>

      <div class="flex justify-center">
        <button type="submit" name="update_profile" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-6 rounded-lg shadow-md">Update</button>
      </div>
    </form>

    <script>
      function toggleForm() {
        // Lấy phần tử form
        const form = document.getElementById('update-profile-form');
        // Ẩn/Hiện form
        form.classList.toggle('hidden');
      }

      function updateFileName(input) {
        const file = input.files[0];
        if (file) {
          document.getElementById("file-name").innerText = file.name;
          const reader = new FileReader();
          reader.onload = function(e) {
            const img = document.getElementById("image-preview");
            img.src = e.target.result;
            img.classList.remove("hidden");
          };
          reader.readAsDataURL(file);
        } else {
          document.getElementById("file-name").innerText = "No file chosen";
          document.getElementById("image-preview").classList.add("hidden");
        }
      }
    </script>
  </div>

  <!-- Danh sách voucher đã nhận -->
  <section class="bg-gray-50 p-6 rounded-xl drop-shadow-lg border-2 border-yellow-400">
    <div class="space-y-4">
      <?php if (empty($userVouchers)): ?>
        <p class="text-gray-500 text-center">🚫 You have not claimed any vouchers yet.</p>
      <?php else: ?>
        <?php foreach ($userVouchers as $voucher): ?>
          <div class="flex items-center bg-white p-4 rounded-lg shadow-md border border-gray-300 transition-all duration-300 ease-in-out hover:shadow-lg">
            <!-- Icon voucher -->
            <div class="text-5xl text-red-500 px-4">🎟</div>

            <!-- Thông tin voucher -->
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($voucher['description']) ?></h3>
              <p class="text-gray-700">🔑 Code: <strong class="text-gray-900"><?= htmlspecialchars($voucher['code']) ?></strong></p>
              <p class="text-sm text-gray-500">⏳ Expires: <?= htmlspecialchars($voucher['expiration_date']) ?></p>
            </div>

            <!-- Trạng thái voucher -->
            <div class="text-sm font-medium <?= $voucher['status'] === 'used' ? 'text-red-500' : 'text-green-500' ?>">
              <?= $voucher['status'] === 'used' ? '❌ Used' : '✅ Active' ?>
            </div>

          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <!-- Modal ẩn order details -->
  <h3 class="text-4xl font-extrabold my-8 text-center text-blue-700 drop-shadow-lg">Order History</h3>
  <?php if (empty($orders)): ?>
    <p class="text-center text-gray-600 text-lg">No order history available.</p>
  <?php else: ?>
    <?php foreach ($orders as $order): ?>
      <?php
      $orderdetails = $orderController->getOrderDetailsByOrderId($order['id']);
      $orderStatus = strtolower($order['status'] ?? 'unknown');
      $isCanceled = $orderStatus === 'cancelled';
      ?>
      <div class="relative bg-white p-8 rounded-2xl drop-shadow-lg mb-8 border-2 border-yellow-400">

        <!-- Nội dung Order ID có thể click -->
        <div class="cursor-pointer bg-blue-100 p-4 rounded-lg border border-blue-300 flex items-center justify-between" onclick="toggleOrderDetails('order-<?= $order['id'] ?>')">
          <div class="flex items-center space-x-3">
            <i class="fas fa-receipt text-blue-500 text-xl"></i>
            <p class="font-semibold text-gray-800">
              #<?= htmlspecialchars($order['id']) ?> (<?= htmlspecialchars($order['created_at']) ?>)
              <?= ($order['note'] === "feedbacked") ? "💬" : "" ?>
              <?php if (!empty($order['shipping_link'])): ?>
                <a href="<?= htmlspecialchars($order['shipping_link']) ?>" target="_blank" class="text-red-500">
                  <i class="fas fa-map-marker-alt"></i>
                </a>
              <?php endif; ?>
            </p>
          </div>

          <i class="fas fa-chevron-down text-gray-600" id="icon-order-<?= $order['id'] ?>"></i> <!-- Thông báo nếu đơn hàng bị hủy -->

          <?php if ($isCanceled): ?>
            <div class="absolute inset-0 bg-white bg-opacity-50 backdrop-blur-md flex items-center justify-center z-20 rounded-2xl">
              <div class="bg-yellow-100 text-red-600 p-4 rounded-xl shadow-lg text-center w-3/5">
                <p class="font-bold text-2xl">Cancelled</p>
                <p class="text-gray-800 mt-2">We will contact you shortly for further details.
                  <br>Please checked your email for more information.
                </p>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Nội dung đơn hàng (ẩn mặc định) -->
        <div id="order-<?= $order['id'] ?>" class="space-y-8 mt-4 hidden">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2">

            <div class="bg-white p-3 rounded-lg border-2 border-blue-100 flex items-center space-x-3">
              <i class="fas fa-map-marker-alt text-pink-500 text-xl"></i>
              <div>
                <p class="text-sm text-gray-600">Shipping Address</p>
                <p class="font-semibold text-gray-800"><?= htmlspecialchars($order['address']) ?></p>
              </div>
            </div>

            <div class="bg-white p-3 rounded-lg border-2 border-blue-100 flex items-center space-x-3">
              <i class="fas fa-dollar-sign text-green-500 text-xl"></i>
              <div>
                <p class="text-sm text-gray-600">Total Amount</p>
                <p class="font-semibold text-gray-800">
                  $<?= number_format($order['total'], 2) ?>
                  <?php if (!empty($order['voucher_code'])): ?>
                    <span class="text-red-500" title="<?= htmlspecialchars($order['description']) ?>">
                      (<?= htmlspecialchars($order['voucher_code']) ?>)
                    </span>
                  <?php endif; ?>
                </p>
              </div>
            </div>

            <div class="bg-white p-3 rounded-lg border-2 border-blue-100 flex items-center space-x-3">
              <i class="fas fa-credit-card text-purple-500 text-xl"></i>
              <div>
                <p class="text-sm text-gray-600">Payment Method</p>
                <p class="font-semibold text-gray-800"><?= ucfirst(htmlspecialchars($order['payment_method']) == "bank_transfer" ? "Banking" : "COD") ?></p>
              </div>
            </div>

            <?php
            $statusColors = [
              'pending' => 'text-yellow-500',
              'processing' => 'text-blue-500',
              'completed' => 'text-green-500',
              'cancelled' => 'text-red-500'
            ];
            $statusColor = $statusColors[$orderStatus] ?? 'text-gray-500';
            ?>

            <div class="bg-white p-3 rounded-lg border-2 border-blue-100 flex items-center space-x-3">
              <div class="flex items-center space-x-3">
                <i class="fas fa-truck <?= $statusColor ?> text-xl"></i>
                <div>
                  <p class="text-sm text-gray-600">Status</p>
                  <p class="font-semibold <?= $statusColor ?> capitalize">
                    <?= ucfirst(htmlspecialchars($orderStatus)) ?>
                  </p>
                </div>
              </div>
            </div>

          </div>

          <!-- Bảng chi tiết đơn hàng -->
          <div class="overflow-x-auto bg-white border-2 border-blue-100 rounded-lg shadow-sm p-4">
            <ul class="divide-y divide-gray-300 text-sm">
              <?php foreach ($orderdetails as $item): ?>
                <li class="py-3 px-4 flex flex-col space-y-1 border-l-4 border-yellow-400 bg-yellow-50 rounded-md shadow-sm">
                  <div class="flex justify-between items-center">
                    <span class="font-semibold text-gray-800"><?= htmlspecialchars($item['name']) ?></span>
                    <span class="text-gray-600 text-sm">(Size: <?= htmlspecialchars($item['size']) ?>)</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-gray-600">Quantity: <strong><?= htmlspecialchars($item['quantity']) ?></strong></span>
                    <span class="text-gray-600">
                      Price:
                      <?php if ($item['price_to_display'] < $item['price']): ?>
                        <span class="line-through text-gray-500">$<?= number_format($item['price'], 2) ?></span>
                        <span class="text-red-600 font-semibold">$<?= number_format($item['price_to_display'], 2) ?></span>
                      <?php else: ?>
                        <span class="font-semibold">$<?= number_format($item['price'], 2) ?></span>
                      <?php endif; ?>
                    </span>
                  </div>
                  <div class="text-right font-semibold text-gray-800">
                    Total: <span class="text-lg text-green-600">$<?= number_format($item['total_price'], 2) ?></span>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script>
  function toggleOrderDetails(orderId) {
    var details = document.getElementById(orderId);
    var icon = document.getElementById("icon-" + orderId);

    if (details.classList.contains("hidden")) {
      details.classList.remove("hidden");
      icon.classList.replace("fa-chevron-down", "fa-chevron-up");
    } else {
      details.classList.add("hidden");
      icon.classList.replace("fa-chevron-up", "fa-chevron-down");
    }
  }
</script>

<!-- Logout Button (Trigger Modal) -->
<div class="flex justify-center mb-8 mt-8">
  <button type="button" onclick="openLogoutModal()"
    class="bg-red-500 text-white px-5 py-2 rounded-md hover:bg-red-600 transition duration-200 shadow"
    title="Logout">Logout</button>
</div>
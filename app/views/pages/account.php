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

$orderController = new OrderController($conn);
$orders = $orderController->getOrdersByUserId($user_id);

$userController = new UserController($conn);
$user = $userController->getUserById($user_id);

// Xử lý điều kiện khi người dùng nhấn vào nút Admin Panel, Logout, hoặc cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Check CSRF token
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
  }

  if (isset($_POST['admin_panel']) && $user['role'] === 'admin') {
    // Điều hướng đến trang quản lý sản phẩm nếu người dùng có quyền admin
    header("Location: /admin/list");
    exit();
  }

  if (isset($_POST['logout'])) {
    // Xóa tất cả biến session
    session_unset();
    session_destroy();

    // Chặn trình duyệt lưu cache để tránh vấn đề redirect sai
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Location: /login");
    exit();
  }

  if (isset($_POST['update_profile'])) {
    // Lấy dữ liệu từ form cập nhật
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Gọi hàm cập nhật thông tin
    $updated = $userController->updateUserProfile($user_id, $name, $phone, $address);

    if ($updated) {
      $message = "Profile updated successfully.";
    } else {
      $message = "Failed to update profile.";
    }
    header("Location: /account");
    exit();
  }
}
?>

<!-- Hiển thị alert bằng JavaScript nếu có thông báo -->
<?php if (!empty($message)): ?>
  <script>
    alert("<?= htmlspecialchars($message) ?>");
  </script>
<?php endif; ?>

<!-- Profile Section -->
<div class="container mx-auto w-4/5 mt-10 mb-10">
  <h2 class="text-4xl font-bold text-center mb-8 text-gray-900">Profile</h2>

  <!-- Thông tin người dùng hiển thị dưới dạng lưới -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-8 p-8 bg-white shadow-md rounded-xl mx-auto mb-8 border border-gray-300">
    <!-- Name -->
    <div class="flex items-center space-x-4">
      <i class="fas fa-user text-3xl text-yellow-500"></i>
      <div>
        <p class="font-semibold text-gray-800">Name</p>
        <p class="text-gray-600"><?= htmlspecialchars($user['name'] ?? 'N/A') ?></p>
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

    <!-- Nút Admin Panel -->
    <div class="flex justify-center space-x-4 col-span-1 w-full">
      <?php if ($user['role'] === 'admin'): ?>
        <form method="POST" action="/account">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <button type="submit" name="admin_panel"
            class="bg-yellow-400 hover:bg-yellow-500 text-black font-bold py-2 px-6 rounded-lg transition duration-200">
            Admin Panel
          </button>
        </form>
      <?php else: ?>
        <button class="bg-yellow-400 hover:bg-yellow-500 text-black font-bold py-2 px-6 rounded-lg transition duration-200">Customer</button>
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

    <!-- Nút Update Profile -->
    <div class="flex justify-center space-x-4 col-span-1 bg-white">
      <button onclick="toggleForm()"
        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-3 rounded-lg transition duration-200">
        Update Profile
      </button>
    </div>
  </div>

  <!-- Form Cập Nhật Thông Tin Người Dùng, mặc định bị ẩn -->
  <div id="update-profile-form" class="space-y-6 mt-4 hidden mx-auto">
    <h3 class="text-3xl font-bold text-center mt-8 text-gray-800">Update Profile</h3>
    <form action="/account" method="POST" class="space-y-6 bg-white p-6 rounded-lg shadow-md border border-gray-300">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
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

      <div>
        <label for="address" class="block text-gray-700 font-semibold">Address</label>
        <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" class="w-full p-3 border border-gray-300 rounded-md">
      </div>

      <div class="flex justify-center">
        <button type="submit" name="update_profile" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-6 rounded-lg shadow-md">Update</button>
      </div>
    </form>
  </div>

  <script>
    function toggleForm() {
      // Lấy phần tử form
      const form = document.getElementById('update-profile-form');
      // Ẩn/Hiện form
      form.classList.toggle('hidden');
    }
  </script>

  <!-- Order History Section -->
  <h3 class="text-3xl font-bold text-center mt-8 mb-8 text-gray-800">Order History</h3>
  <?php if (empty($orders)): ?>
    <p class="text-center text-gray-600 text-lg mt-8">No order history available.</p>
  <?php else: ?>
    <?php foreach ($orders as $order): ?>
      <?php
      $orderdetails = $orderController->getOrderDetailsByOrderId($order['id']);
      $orderStatus = strtolower($order['status'] ?? 'unknown');
      $isCanceled = $orderStatus === 'cancelled';
      ?>
      <div class="relative bg-white p-8 rounded-2xl shadow-lg border border-gray-300 mb-8">

        <!-- Thông báo nếu đơn hàng bị hủy -->
        <?php if ($isCanceled): ?>
          <div class="absolute inset-0 bg-white bg-opacity-50 backdrop-blur-md flex items-center justify-center z-20 rounded-2xl">
            <div class="bg-yellow-100 border border-yellow-500 text-red-600 p-8 rounded-xl shadow-lg text-center w-2/5">
              <p class="font-bold text-3xl">Order Canceled</p>
              <p class="text-gray-800 mt-2">We will contact you shortly for further details.</p>
            </div>
          </div>
        <?php endif; ?>

        <!-- Nội dung đơn hàng -->
        <div class="space-y-8 <?= $isCanceled ? 'opacity-50' : '' ?>">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-300 flex items-center space-x-3">
              <i class="fas fa-receipt text-blue-500 text-xl"></i>
              <div>
                <p class="text-sm text-gray-600">Order ID</p>
                <p class="font-semibold text-gray-800">#<?= htmlspecialchars($order['id']) ?></p>
              </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-300 flex items-center space-x-3">
              <i class="fas fa-map-marker-alt text-pink-500 text-xl"></i>
              <div>
                <p class="text-sm text-gray-600">Shipping Address</p>
                <p class="font-semibold text-gray-800"><?= htmlspecialchars($order['address']) ?></p>
              </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-300 flex items-center space-x-3">
              <i class="fas fa-calendar-alt text-indigo-500 text-xl"></i>
              <div>
                <p class="text-sm text-gray-600">Order Date</p>
                <p class="font-semibold text-gray-800"><?= htmlspecialchars($order['created_at']) ?></p>
              </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-300 flex items-center space-x-3">
              <i class="fas fa-dollar-sign text-green-500 text-xl"></i>
              <div>
                <p class="text-sm text-gray-600">Total Amount</p>
                <p class="font-semibold text-gray-800">$<?= number_format($order['total'], 2) ?></p>
              </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-300 flex items-center space-x-3">
              <i class="fas fa-credit-card text-purple-500 text-xl"></i>
              <div>
                <p class="text-sm text-gray-600">Payment Method</p>
                <p class="font-semibold text-gray-800"><?= ucfirst(htmlspecialchars($order['payment_method'])) ?></p>
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

            <div class="relative bg-white p-4 rounded-lg shadow-md border border-gray-300">
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
          <div class="overflow-x-auto">
            <table class="w-full border border-gray-200 rounded-lg overflow-hidden alert alert-info">
              <thead class="bg-gradient-to-r from-yellow-200 to-yellow-300 text-gray-700">
                <tr>
                  <th class="px-6 py-3 text-left font-semibold uppercase">Product</th>
                  <th class="px-6 py-3 text-center font-semibold uppercase">Size</th>
                  <th class="px-6 py-3 text-center font-semibold uppercase">Quantity</th>
                  <th class="px-6 py-3 text-center font-semibold uppercase">Price</th>
                  <th class="px-6 py-3 text-center font-semibold uppercase">Total</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($orderdetails as $item): ?>
                  <tr class="hover:bg-yellow-50 transition-all duration-200 ease-in-out">
                    <td class="px-6 py-4 text-gray-800 font-medium">
                      <?= htmlspecialchars($item['name']) ?>
                    </td>
                    <td class="px-6 py-4 text-center text-gray-600">
                      <?= htmlspecialchars($item['size']) ?>
                    </td>
                    <td class="px-6 py-4 text-center text-gray-600">
                      <?= htmlspecialchars($item['quantity']) ?>
                    </td>
                    <td class="px-6 py-4 text-center text-gray-600">
                      <?php if ($item['price_to_display'] < $item['price']): ?>
                        <span class="line-through text-gray-500 text-sm">$<?= number_format($item['price'], 2) ?></span>
                        <span class="text-red-600 text-base font-semibold">$<?= number_format($item['price_to_display'], 2) ?></span>
                      <?php else: ?>
                        <span>$<?= number_format($item['price'], 2) ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-center font-semibold text-gray-800">
                      $<?= number_format($item['total_price'], 2) ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- Logout Button -->
  <form method="POST" class="flex justify-center mt-8">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <button type="submit" name="logout" onclick="confirmLogout(event)"
      class="bg-red-500 text-white px-5 py-2 rounded-md hover:bg-red-600 transition duration-200 shadow">
      Logout</button>
  </form>
</div>

<script>
  function confirmLogout(event) {
    // Hiển thị hộp thoại xác nhận
    const userConfirmed = confirm("Are you sure you want to logout?");
    if (userConfirmed) {
      // Người dùng xác nhận thì submit form
      document.getElementById('logout-form').submit();
    } else {
      // Ngăn chặn submit nếu người dùng nhấn "Hủy"
      event.preventDefault();
    }
  }
</script>
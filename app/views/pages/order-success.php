<?php

// Điều hướng đến trang login 
if (!isset($_SESSION['user_id'])) {
  header("Location: /login");
  exit(); // Dừng thực thi nếu không có user_id
}

// Khởi tạo orderController
$orderController = new OrderController($conn);

// Lấy user_id từ session
$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) && is_numeric($_GET['order_id']) ? (int) $_GET['order_id'] : null; // Lấy order_id từ URL

// Truy vấn lấy chi tiết đơn hàng
$orderDetails = $orderController->getOrderDetails($order_id, $user_id); // Gọi phương thức để lấy chi tiết đơn hàng
?>

<div class="container w-4/5 mx-auto p-6 bg-white rounded-2xl shadow-xl mb-6 mt-6">
  <h1 class="text-center text-3xl mb-6 font-extrabold text-blue-600">Order Confirmation</h1>

  <?php if ($orderDetails): ?> <!-- Kiểm tra xem có chi tiết đơn hàng hay không -->
    <!-- Bố cục chia thành hai cột -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

      <!-- Cột bên trái: Chi tiết sản phẩm trong đơn hàng -->
      <div class="bg-yellow-100 p-6 rounded-2xl shadow-sm">
        <div class="space-y-4">
          <?php foreach ($orderDetails['items'] as $item): ?>
            <div class="flex items-center justify-between p-4 shadow-sm rounded-2xl bg-white">
              <div class="flex items-center space-x-4">
                <img src="/images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-12 h-12 rounded-lg object-cover">
                <div>
                  <p class="font-semibold text-gray-800"><?= htmlspecialchars($item['name']) ?></p>
                  <p class="text-sm text-gray-600">Price: <?= htmlspecialchars($item['price']) ?></p>
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

      <!-- Cột bên phải: Thông tin đơn hàng -->
      <div class="space-y-6">
        <!-- Card thông tin đơn hàng -->
        <div class="bg-yellow-100 p-6 rounded-2xl shadow-sm">
          <h2 class="text-xl font-semibold mb-4 text-gray-800">Order Summary</h2>
          <ul class="space-y-3">
            <li class="flex justify-between">
              <span class="text-gray-700">Order ID:</span>
              <span class="font-semibold text-blue-600">#<?= htmlspecialchars($orderDetails['id']) ?></span>
            </li>
            <li class="flex justify-between">
              <span class="text-gray-700">Status:</span>
              <span class="font-semibold text-gray-800"><?= htmlspecialchars($orderDetails['status']) ?></span>
            </li>
            <li class="flex justify-between">
              <span class="text-gray-700">Total:</span>
              <span class="font-semibold text-red-500">$<?= number_format($orderDetails['total'], 2) ?></span>
            </li>
            <li class="flex justify-between">
              <span class="text-gray-700">Payment Method:</span>
              <span class="font-semibold text-gray-800"><?= htmlspecialchars($orderDetails['payment_method']) ?></span>
            </li>
            <li class="flex justify-between">
              <span class="text-gray-700">Shipping Address:</span>
              <span class="font-semibold text-gray-800"><?= htmlspecialchars($orderDetails['address']) ?></span>
            </li>
          </ul>
        </div>
        <!-- Nút Back to Home -->
        <div class="text-center">
          <button type="button" class="font-semibold inline-block bg-blue-500 text-white px-8 py-3 rounded-full shadow-md hover:bg-blue-600"
            onclick="window.location.href='/home'">Back to Home</button>
        </div>
      </div>
    </div>

  <?php else: ?>
    <!-- Nếu không có chi tiết đơn hàng -->
    <p class="text-center text-gray-500">Order not found or you are not authorized to view this order.</p>
  <?php endif; ?>
</div>

<!--  -->
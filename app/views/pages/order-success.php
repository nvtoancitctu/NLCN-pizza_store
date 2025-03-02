<?php

// Điều hướng đến trang login nếu chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
  header("Location: /login");
  exit();
}

// Khởi tạo OrderController
$orderController = new OrderController($conn);

// Lấy user_id từ session
$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) && is_numeric($_GET['order_id']) ? (int)$_GET['order_id'] : null;

// Truy vấn lấy chi tiết đơn hàng
$orderDetails = $orderController->getOrderDetails($order_id, $user_id);
?>

<h1 class="text-4xl font-extrabold my-8 text-center text-blue-700 drop-shadow-lg">Order Confirmation</h1>
<div class="container max-w-4xl mx-auto p-6 bg-white rounded-2xl shadow-lg mb-8 alert alert-info">
  <?php if ($orderDetails): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- Cột trái: Danh sách sản phẩm -->
      <div class="bg-gray-100 p-6 rounded-2xl shadow-md">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Your Items</h2>
        <div class="space-y-4">
          <?php foreach ($orderDetails['items'] as $item): ?>
            <div class="flex items-center justify-between p-4 rounded-xl bg-white shadow-sm">
              <div class="flex items-center space-x-4">
                <img src="/images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                  class="w-16 h-16 rounded-lg object-cover">
                <div>
                  <p class="font-semibold text-gray-800"><?= htmlspecialchars($item['name']) ?></p>
                  <p class="text-sm text-gray-600">Price: $<?= number_format($item['price'], 2) ?></p>
                  <p class="text-sm text-gray-600">Qty: <?= htmlspecialchars($item['quantity']) ?></p>
                  <p class="text-sm text-gray-600">Size: <?= htmlspecialchars($item['size']) ?></p>
                </div>
              </div>
              <p class="font-bold text-gray-900">$<?= number_format($item['total_price'], 2) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Cột phải: Thông tin đơn hàng -->
      <div class="space-y-6">

        <!-- Thẻ tóm tắt đơn hàng -->
        <div class="bg-gray-100 p-6 rounded-2xl shadow-md">
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
              <span class="text-gray-700">Subtotal:</span>
              <span class="font-semibold text-gray-900">$<?= number_format($orderDetails['total'], 2) ?></span>
            </li>
            <li class="flex justify-between">
              <span class="text-gray-700">Shipping:</span>
              <span class="font-semibold <?= ($orderDetails['shipping_fee'] == 0) ? 'text-green-500' : 'text-gray-900' ?>">
                <?= ($orderDetails['shipping_fee'] == 0) ? "FREE" : "$" . number_format($orderDetails['shipping_fee'], 2) ?>
              </span>
            </li>
            <li class="flex justify-between border-t pt-3">
              <span class="text-gray-700 font-bold">Total:</span>
              <span class="font-bold text-red-500 text-lg">$<?= number_format($orderDetails['total'] + $orderDetails['shipping_fee'], 2) ?></span>
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

        <!-- Nút điều hướng -->
        <div class="flex justify-center gap-4 mt-6">
          <button type="button"
            class="font-semibold inline-block bg-blue-500 text-white px-8 py-3 rounded-full shadow-md hover:bg-blue-600 transition"
            onclick="window.location.href='/home'">Back to Home</button>
          <button type="button"
            class="font-semibold inline-block bg-green-500 text-white px-8 py-3 rounded-full shadow-md hover:bg-green-600 transition"
            onclick="window.location.href='/account'">Go to Profile</button>
        </div>

      </div>
    </div>

  <?php else: ?>
    <p class="text-center text-gray-500">Order not found or you are not authorized to view this order.</p>
  <?php endif; ?>

</div>
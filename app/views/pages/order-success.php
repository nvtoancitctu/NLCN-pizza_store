<?php

// Điều hướng đến trang login nếu chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
  header("Location: /login");
  exit();
}

// Lấy user_id từ session
$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) && is_numeric($_GET['order_id']) ? (int)$_GET['order_id'] : null;

// Truy vấn lấy chi tiết đơn hàng
$orderDetails = $orderController->getOrderDetails($order_id, $user_id);

?>

<h1 class="text-4xl font-extrabold my-8 text-center text-blue-700 drop-shadow-lg flex items-center justify-center">
  <svg class="w-10 h-10 text-blue-700 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 -3 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-7a9 9 0 11-18 0 9 9 0 0118 0z" />
  </svg>
  Order Confirmation
</h1>
<div class="container max-w-4xl mx-auto p-6 bg-white rounded-2xl shadow-lg mb-8">
  <?php if ($orderDetails): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

      <!-- Danh sách sản phẩm -->
      <div class="bg-gray-100 p-6 rounded-2xl shadow-md">
        <h2 class="text-xl font-semibold mb-4 text-gray-800 flex items-center">
          <svg class="w-6 h-6 text-gray-800 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18M4 7h16M5 11h14M6 15h12M7 19h10" />
          </svg>
          Your Items
        </h2>
        <div class="space-y-4">
          <?php foreach ($orderDetails['items'] as $item): ?>
            <div class="flex items-center gap-4 p-4 bg-white rounded-lg shadow">
              <img src="/images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                class="w-16 h-16 rounded-lg object-cover">
              <div class="text-sm">
                <p class="font-semibold text-gray-800"><?= htmlspecialchars($item['name']) ?></p>
                <p class="text-gray-600">$<?= number_format($item['price'], 2) ?> x <?= htmlspecialchars($item['quantity']) ?> (<?= htmlspecialchars($item['size']) ?>)</p>
              </div>
              <p class="ml-auto font-bold text-gray-900">$<?= number_format($item['total_price'], 2) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Thông tin đơn hàng -->
      <div class="bg-gray-100 p-6 rounded-2xl shadow-md">
        <h2 class="text-xl font-semibold mb-4 text-gray-800 flex items-center">
          <svg class="w-6 h-6 text-gray-800 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 -3 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-7a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Order Summary
        </h2>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <span class="text-gray-700">🆔 Order ID:</span>
          <span class="font-semibold text-blue-600">#<?= htmlspecialchars($orderDetails['id']) ?></span>

          <span class="text-gray-700">📦 Status:</span>
          <span class="font-semibold text-yellow-500"> <?= htmlspecialchars($orderDetails['status']) ?></span>

          <span class="text-gray-700">💰 Subtotal:</span>
          <span class="font-semibold text-gray-900 text-lg">$<?= number_format($orderDetails['order_total'], 2) ?></span>

          <span class="text-gray-700">🚚 Shipping:</span>
          <span class="font-semibold <?= ($orderDetails['shipping_fee'] == 0) ? 'text-green-500' : 'text-blue-600' ?>">
            <?= ($orderDetails['shipping_fee'] == 0) ? "FREE" : "$" . number_format($orderDetails['shipping_fee'], 2) ?>
          </span>

          <span class="text-gray-700">🎟️ Voucher:</span>
          <span class="font-semibold text-green-600" title="<?= htmlspecialchars($orderDetails['description']) ?>">
            <?= htmlspecialchars($orderDetails['code']) ?>
          </span>

          <span class="text-gray-700 font-bold">💳 Total:</span>
          <span class="font-bold text-red-500 text-lg">$<?= number_format($orderDetails['final_total'], 2) ?></span>

          <span class="text-gray-700">💳 Payment:</span>
          <span class="font-semibold text-gray-800"> <?= htmlspecialchars($orderDetails['payment_method']) ?></span>

          <span class="text-gray-700">📍 Address:</span>
          <span class="font-semibold text-gray-800"> <?= htmlspecialchars($orderDetails['address']) ?></span>
        </div>
      </div>
    </div>

    <!-- Nút điều hướng -->
    <div class="flex justify-center gap-4 mt-6">
      <button type="button"
        class="font-semibold bg-blue-500 text-white px-6 py-3 rounded-full shadow-md hover:bg-blue-600 transition flex items-center gap-2"
        onclick="window.location.href='/home'">
        🏠 Back to Home
      </button>
      <button type="button"
        class="font-semibold bg-green-500 text-white px-6 py-3 rounded-full shadow-md hover:bg-green-600 transition flex items-center gap-2"
        onclick="window.location.href='/account'">
        👤 Go to Profile
      </button>
    </div>

  <?php else: ?>
    <p class="text-center text-gray-500">Order not found or you are not authorized to view this order.</p>
  <?php endif; ?>
</div>
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

<div class="container w-4/5 mx-auto p-6 bg-white rounded-2xl shadow-sm m-24 text-center border-2 border-yellow-300">
  <h1 class="text-4xl font-extrabold text-center text-green-500 drop-shadow-lg flex items-center justify-center">
    <svg class="w-10 h-10 text-green-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 -3 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-7a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    Order Confirmation
  </h1>
  <p class="text-gray-600 mt-6">
    Thank you for your order. Please check your email for order details or click the button below.
  </p>

  <!-- Nút See Order -->
  <button type="button" id="seeOrderBtn"
    class="mt-4 font-semibold bg-yellow-500 text-white px-4 py-2 rounded-lg shadow-md hover:bg-yellow-600 transition">
    📄 See Order
  </button>

  <!-- Khung chi tiết đơn hàng (Ẩn mặc định) -->
  <div id="orderDetailsContainer" class="mt-6 hidden">
    <?php if ($orderDetails): ?>
      <div class="flex flex-col gap-6">

        <!-- 1: Thông tin đơn hàng -->
        <div class="bg-gray-100 p-6 rounded-2xl shadow-md text-left">
          <h2 class="text-xl font-semibold mb-4 text-gray-800">Order Summary</h2>
          <div class="grid grid-cols-8 gap-4 text-sm">
            <!-- Hàng 1 -->
            <div class="text-gray-700">🆔 Order ID:</div>
            <div class="font-semibold text-blue-600">#<?= htmlspecialchars($orderDetails['id']) ?></div>

            <div class="text-gray-700">💳 Payment:</div>
            <div class="font-semibold text-gray-800">
              <?= $orderDetails['payment_method'] === 'bank_transfer' ? 'Banking' : 'COD' ?>
            </div>

            <div class="text-gray-700">💰 Subtotal:</div>
            <div class="font-semibold text-gray-900">$<?= number_format($orderDetails['order_total'], 2) ?></div>

            <div class="text-gray-700">🎟️ Voucher:</div>
            <div class="font-semibold text-green-600">
              <?= htmlspecialchars($orderDetails['code'] ?? "None") ?>
            </div>

            <!-- Hàng 2 -->
            <div class="text-gray-700">📦 Status:</div>
            <div class="font-semibold text-yellow-500"><?= htmlspecialchars($orderDetails['status']) ?></div>

            <div class="text-gray-700">📍 Address:</div>
            <div class="font-semibold text-gray-800"><?= htmlspecialchars($orderDetails['address']) ?></div>

            <div class="text-gray-700">🚚 Shipping:</div>
            <div class="font-semibold <?= ($orderDetails['shipping_fee'] == 0) ? 'text-green-500' : 'text-blue-600' ?>">
              <?= ($orderDetails['shipping_fee'] == 0) ? "FREE" : "$" . number_format($orderDetails['shipping_fee'], 2) ?>
            </div>

            <div class="text-gray-700 font-bold">💳 Total:</div>
            <div class="font-bold text-red-500">$<?= number_format($orderDetails['final_total'], 2) ?></div>
          </div>
        </div>

        <!-- 2: Danh sách sản phẩm -->
        <div class="bg-gray-100 p-6 rounded-2xl shadow-md text-left">
          <h2 class="text-xl font-semibold mb-4 text-gray-800">Your Items</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($orderDetails['items'] as $item): ?>
              <div class="flex items-center gap-4 p-4 bg-white rounded-lg shadow">
                <img src="/images/product/<?= htmlspecialchars($item['image']) ?>"
                  alt="<?= htmlspecialchars($item['name']) ?>"
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
      </div>
    <?php else: ?>
      <p class="text-center text-gray-500">Order not found or you are not authorized to view this order.</p>
    <?php endif; ?>
  </div>

  <!-- Nút điều hướng -->
  <div class="flex justify-center gap-4 mt-6">
    <button type="button"
      class="font-semibold bg-blue-500 text-white px-4 py-2 rounded-lg shadow-md hover:bg-blue-600 transition"
      onclick="window.location.href='/home'">
      🏠 Home
    </button>
    <button type="button"
      class="font-semibold bg-green-500 text-white px-4 py-2 rounded-lg shadow-md hover:bg-green-600 transition"
      onclick="window.location.href='/account'">
      👤 Profile
    </button>
  </div>
</div>

<script>
  document.getElementById("seeOrderBtn").addEventListener("click", function() {
    document.getElementById("orderDetailsContainer").classList.toggle("hidden");
  });
</script>
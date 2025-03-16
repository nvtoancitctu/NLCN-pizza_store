<?php

// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Kiểm tra và lấy thông báo thành công từ session
$success = '';
if (isset($_SESSION['success'])) {
  $success = $_SESSION['success'];
  unset($_SESSION['success']); // Xóa thông báo khỏi session
}

// Lấy các sản phẩm khuyến mãi và Lấy 3 sản phẩm ngẫu nhiên
$discountProduct = $productController->getDiscountProduct();
$randomProducts = $productController->getRandomProducts(3);

// Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? $_GET['id'] : null;
$product = $productController->getProductDetails($product_id);

?>

<style>
  /* CSS cho hiệu ứng đập của trái tim */
  .heart-beat {
    animation: heartBeat 1s infinite ease-in-out;
  }

  @keyframes heartBeat {

    0%,
    100% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.3);
    }
  }

  /* CSS cho icon trái tim */
  .heart-icon {
    background-color: #ff5e5e;
    /* Màu đỏ nổi bật */
    color: white;
    padding: 8px;
    border-radius: 50%;
    box-shadow: 0 4px 10px rgba(255, 94, 94, 0.5);
    transition: background-color 0.3s ease, transform 0.3s ease;
  }

  /* Hover cho icon trái tim */
  .heart-icon:hover {
    background-color: #ff3b3b;
    /* Màu đỏ đậm hơn khi hover */
    transform: scale(1.2);
    /* Phóng to nhẹ khi hover */
  }

  @keyframes pulse-effect {

    0%,
    100% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.2);
    }
  }

  .animate-pulse-custom {
    animation: pulse-effect 1s infinite ease-in-out;
  }
</style>

<!-- Hiển thị thông báo thành công nếu có -->
<?php if (!empty($success)): ?>
  <script>
    alert("<?= addslashes($success) ?>");
  </script>
<?php endif; ?>

<div class="container mx-auto px-12">
  <!-- Jumbotron -->
  <div class="relative bg-gradient-to-r from-blue-500 via-blue-600 to-purple-600 shadow-md text-white text-center p-20 rounded-3xl mt-10 overflow-hidden">
    <!-- Nội dung chính -->
    <div class="relative z-10">
      <h1 class="text-7xl font-extrabold tracking-wide drop-shadow-2xl animate-fade-in">
        🍕 Delight in Every Bite! 🍕
      </h1>
      <h3 class="text-2xl mt-4 font-semibold">Welcome to Lover’s Hut – Your Pizza Paradise!</h3>
      <p class="mt-4 text-xl font-light drop-shadow-md animate-slide-in bg-black bg-opacity-25 px-6 py-3 inline-block rounded-xl">
        Fresh, cheesy, and delicious! Your perfect pizza moment starts here.
      </p>

      <a href="/products"
        class="ml-2 mt-8 inline-flex items-center bg-white text-red-600 font-semibold px-6 py-4 rounded-full text-lg border-2 border-red-600 
      transition-all duration-500 transform hover:scale-105 hover:bg-red-600">
        <span class="mr-2">Order Now</span>
        <svg class="w-6 h-6 transition-transform duration-300 group-hover:translate-x-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </a>
    </div>

    <!-- Hiệu ứng lượn sóng -->
    <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none z-0">
      <svg class="relative block w-full" viewBox="0 0 1440 160" xmlns="http://www.w3.org/2000/svg">
        <path fill="#fff" fill-opacity="1"
          d="M0,100 C180,140 360,60 540,100 C720,140 900,40 1080,80 C1260,120 1440,80 1440,80V160H0Z">
        </path>
      </svg>
    </div>
  </div>

  <!-- Discount Products -->
  <h2 class="text-4xl font-extrabold text-center my-10 text-blue-700 drop-shadow-lg">🍕 Special Discount Offer 🍕</h2>
  <?php if (!empty($discountProduct)): ?>
    <?php foreach ($discountProduct as $product): ?>
      <?php if ($product['stock_quantity'] > 0): ?>
        <div class="bg-white shadow-md border-l-4 border-red-500 rounded-2xl mb-6 p-6 transition-transform transform hover:scale-105 duration-300 text-red-900 font-semibold border-2 border-red-500">

          <!-- Ưu đãi giới hạn -->
          <div class="absolute top-4 left-8 text-white text-xl font-bold py-1 px-2 rounded-full animate-pulse-custom"
            style="background: rgb(0, 230, 0);">🔥 Limited Offer</div>

          <!-- Icon trái tim nổi bật với hiệu ứng đập -->
          <div class="absolute top-4 right-4 heart-icon heart-beat">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10 18l-1.45-1.32C4.4 12.36 2 9.28 2 6.5 2 4.42 3.42 3 5.5 3c1.54 0 3.04.99 3.57 2.36h1.87C11.46 3.99 12.96 3 14.5 3 16.58 3 18 4.42 18 6.5c0 2.78-2.4 5.86-6.55 10.18L10 18z" />
            </svg>
          </div>

          <div class="flex justify-center">
            <div class="flex-shrink-0 w-1/3 flex justify-center items-center">

              <!-- Pizza xoay tròn khi hover -->
              <img src="/images/<?php echo htmlspecialchars($product['image']); ?>"
                class="w-4/5 h-auto mx-auto object-cover rounded-lg transition duration-500 ease-in-out transform hover:rotate-12 hover:scale-110"
                alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>

            <div class="flex-grow p-4">
              <h5 class="text-3xl font-extrabold text-gray-800 mb-2"><?php echo htmlspecialchars($product['name']); ?></h5>
              <p class="text-gray-700"><?php echo htmlspecialchars($product['description']); ?></p>

              <!-- Đánh giá sao và nhận xét của khách hàng -->
              <div class="flex items-center mb-2">
                <span class="text-yellow-500 text-xl">&#9733;&#9733;&#9733;&#9733;&#9734;</span> <!-- Hiển thị đánh giá sao -->
                <span class="text-gray-600 ml-2">(120 reviews)</span> <!-- Số lượng đánh giá -->
              </div>

              <p class="mt-2 mb-2">
                <small class="text-gray-600 line-through">Original Price: $<?php echo htmlspecialchars($product['price']); ?></small><br>
                <strong class="text-blue-500 text-2xl font-bold">Discounted Price: </strong>
                <span class="text-red-600 text-3xl font-bold">$<?php echo htmlspecialchars($product['discount']); ?></span>
              </p>
              <p class="text-red-600 font-bold text-lg mt-2 mb-4" id="discount-timer-<?php echo $product['id']; ?>">Special Offer!</p>

              <!-- Thanh tiến trình -->
              <div class="w-full bg-gray-200 rounded-full h-4 mb-4" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                <div id="progress-bar-<?php echo $product['id']; ?>" class="h-4 rounded-full transition-all duration-300" style="width: 0%; background-color: rgb(255, 0, 0);"></div>
              </div>

              <!-- Nút Thêm vào giỏ hàng -->
              <form method="POST" action="/add" class="add-to-cart-form" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']); ?>">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="size" value="S">
                <button type="button" class="font-semibold add-to-cart-button bg-yellow-500 text-white px-5 py-2 rounded-lg transition duration-300 ease-in-out transform hover:bg-purple-600 hover:shadow-lg hover:-translate-y-1 hover:scale-105">Add to Cart</button>
              </form>
            </div>
          </div>
        </div>

        <!-- Countdown Timer and Progress Bar Script -->
        <script>
          // JavaScript countdown timer và cập nhật thanh tiến trình
          function countdownTimer(endTime, elementId, progressBarId, initialTime) {
            var countDownDate = new Date(endTime).getTime();

            var x = setInterval(function() {
              var now = new Date().getTime();
              var distance = countDownDate - now;

              // Tính toán ngày, giờ, phút, giây còn lại
              var days = Math.floor(distance / (1000 * 60 * 60 * 24));
              var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
              var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
              var seconds = Math.floor((distance % (1000 * 60)) / 1000);

              // Đảm bảo có hai chữ số cho giờ, phút, giây
              hours = hours.toString().padStart(2, '0');
              minutes = minutes.toString().padStart(2, '0');
              seconds = seconds.toString().padStart(2, '0');

              // Cập nhật nội dung phần tử với thời gian đếm ngược
              document.getElementById(elementId).innerHTML = `
                <div class="flex justify-center items-center p-2 w-1/3 bg-white alert alert-info rounded-xl shadow-sm">
                  <div class="flex space-x-3 text-center">
                    <div class="flex flex-col items-center">
                      <span class="text-3xl font-bold text-red-600">${days}</span>
                      <span class="text-xs font-medium text-blue-500">Days</span>
                    </div>
                    <div class="flex flex-col items-center">
                      <span class="text-3xl font-bold text-red-600">${hours}</span>
                      <span class="text-xs font-medium text-blue-500">Hours</span>
                    </div>
                    <div class="flex flex-col items-center">
                      <span class="text-3xl font-bold text-red-600">${minutes}</span>
                      <span class="text-xs font-medium text-blue-500">Minutes</span>
                    </div>
                    <div class="flex flex-col items-center">
                      <span class="text-3xl font-bold text-red-600">${seconds}</span>
                      <span class="text-xs font-medium text-blue-500">Seconds</span>
                    </div>
                  </div>
                </div>
              `;

              // Cập nhật thanh tiến trình
              var progressPercentage = (distance / initialTime) * 100;
              var progressBar = document.getElementById(progressBarId);
              progressBar.style.width = progressPercentage + "%";
              progressBar.style.backgroundColor = getColor(progressPercentage);

              // Kiểm tra nếu thời gian hết
              if (distance < 0) {
                clearInterval(x);
                document.getElementById(elementId).innerHTML = "EXPIRED";
                progressBar.style.width = "0%";
                progressBar.style.backgroundColor = "rgb(0, 0, 0)"; // Đen khi hết thời gian
              }
            }, 1000);
          }

          function getColor(progress) {
            if (progress < 50) {
              return 'rgb(255, 0, 0)'; // Đỏ
            } else if (progress < 80) {
              return 'rgb(255, 255, 0)'; // Vàng
            } else {
              return 'rgb(0, 255, 0)'; // Xanh
            }
          }

          // Gọi hàm countdownTimer cho từng sản phẩm
          countdownTimer('<?php echo $product['discount_end_time']; ?>', 'discount-timer-<?php echo $product['id']; ?>', 'progress-bar-<?php echo $product['id']; ?>', <?php echo (strtotime($product['discount_end_time']) - time()) * 1000; ?>);
        </script>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php else: ?>
    <p class="text-center text-gray-700">Currently, no products are on discount.</p>
  <?php endif; ?>

  <!-- Featured Pizzas -->
  <h2 class="text-4xl font-extrabold text-center my-10 text-blue-700 drop-shadow-lg">
    🍕 Featured Pizzas 🍕
  </h2>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
    <?php foreach ($randomProducts as $product): ?>
      <div class="rounded-2xl shadow-md bg-white overflow-hidden transition-transform transform hover:scale-105 hover:shadow-xl border-2 border-blue-500"
        title="<?= htmlspecialchars($product['description']) ?>">
        <img src="/images/<?php echo htmlspecialchars($product['image']); ?>" class="w-3/5 h-auto mx-auto object-cover rounded-lg transition duration-500 ease-in-out transform hover:rotate-12 hover:scale-110"
          alt="<?php echo htmlspecialchars($product['name']); ?>">

        <div class="p-6 text-center">
          <h5 class="text-2xl font-bold mb-2 text-gray-800"><?php echo htmlspecialchars($product['name']); ?></h5>
          <p class="text-xl font-semibold text-blue-500 mt-2">$<?= htmlspecialchars($product['final_price']); ?></p>

          <div class="mt-4 flex justify-center">
            <form method="POST" action="add" class="add-to-cart-form" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
              <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']); ?>">
              <input type="hidden" name="quantity" value="1">
              <input type="hidden" name="size" value="S">
              <button type="button" class="add-to-cart-button px-5 py-2 bg-yellow-500 text-white rounded-lg hover:bg-green-500 transition duration-300 shadow-md">
                🛒 Add to Cart
              </button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php
  $vouchers = $productController->getActiveVouchers();

  $topPizzas = $productController->getTopRatedPizzas();
  // Lấy pizza có điểm rating trung bình cao nhất
  $highestRated = $topPizzas[0];

  // Lấy pizza có số lượt bán cao nhất
  $bestSeller = $topPizzas[0];
  foreach ($topPizzas as $pizza) {
    if ($pizza['total_sales'] > $bestSeller['total_sales']) {
      $bestSeller = $pizza;
    }
  }

  $testimonials = $productController->getCustomerTestimonials();
  ?>

  <!-- Exclusive Vouchers -->
  <section class="bg-red-50 p-6 rounded-xl shadow-lg border border-red-200">
    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">🔥 Coupon Tickets 🔥</h2>

    <?php if (!empty($vouchers)): ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <?php foreach ($vouchers as $voucher): ?>
          <div class="relative bg-white p-5 rounded-lg shadow-md border border-red-300 text-center" title="<?= htmlspecialchars($voucher['description']) ?>">
            <p class="text-gray-700">Expires: <?= htmlspecialchars($voucher['expiration_date']) ?></p>
            <div class="ticket flex flex-col justify-center items-center p-3 bg-red-500 text-white rounded-md mt-3 space-y-2">
              <span class="text-center font-bold">🎟 <?= htmlspecialchars($voucher['code']) ?></span>
            </div>
            <form method="post" action="index.php?page=claim_voucher" class="mt-3">
              <input type="hidden" name="voucher_id" value="<?= htmlspecialchars($voucher['id']) ?>">
              <button type="submit" class="w-full bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition">Claim Now</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-center text-gray-600 text-lg mt-4">No available vouchers right now. Stay tuned for more deals!</p>
    <?php endif; ?>
  </section>

  <!-- Top Rated & Best Seller Pizzas -->
  <section class="mt-12 bg-white p-6 rounded-xl shadow-lg border text-center">
    <h2 class="text-2xl font-bold mb-2">🔥 Best Pizzas This Week 🔥</h2>
    <p class="text-gray-500 text-sm mb-6">Updated on <?= date('d/m/Y') ?></p> <!-- Hiển thị ngày tháng hiện tại -->

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Pizza có điểm rating cao nhất -->
      <div class="p-6 bg-yellow-100 rounded-xl shadow-xl border-4 border-yellow-500">
        <h3 class="text-xl font-bold">🏆 Highest Rated Pizza</h3>
        <h2 class="text-3xl font-extrabold mt-2 text-yellow-700 animate-pulse">
          <?= htmlspecialchars($highestRated['name']) ?>
        </h2>
        <p class="text-gray-700 text-xl mt-2">⭐ <?= number_format($highestRated['avg_rating'], 1) ?> / 5</p>
      </div>

      <!-- Pizza bán chạy nhất -->
      <div class="p-6 bg-green-100 rounded-xl shadow-xl border-4 border-green-500">
        <h3 class="text-xl font-bold">📈 Best Seller</h3>
        <h2 class="text-3xl font-extrabold mt-2 text-green-700 animate-pulse">
          <?= htmlspecialchars($bestSeller['name']) ?>
        </h2>
        <p class="text-gray-700 text-xl mt-2">🔥 Sold: <strong><?= number_format($bestSeller['total_sales']) ?></strong> times</p>
      </div>
    </div>
  </section>

  <!-- Combo Deals -->
  <!-- <h2 class="text-2xl font-bold text-gray-800 mt-8 mb-4">Combo Deals</h2>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <?php foreach ($comboDeals as $deal): ?>
      <div class="bg-green-100 p-6 rounded-xl shadow-lg">
        <h3 class="text-lg font-semibold text-green-700"> <?= htmlspecialchars($deal['name']) ?> </h3>
        <p class="text-gray-600"> <?= htmlspecialchars($deal['description']) ?> </p>
        <p class="text-xl font-bold text-green-500">$<?= number_format($deal['price'], 2) ?></p>
      </div>
    <?php endforeach; ?>
  </div> -->

  <!-- Customer Testimonials -->
  <section class="mt-12 bg-blue-50 p-6 rounded-xl shadow-lg border border-blue-200">
    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">💬 What Our Customers Say 💬</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <?php foreach ($testimonials as $review): ?>
        <div class="bg-white p-4 rounded-lg shadow-md border border-blue-300">
          <p class="text-gray-700 italic">"<?= htmlspecialchars($review['message']) ?>"</p>
          <p class="text-right font-semibold text-blue-700">- <?= htmlspecialchars($review['name']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Special Policies -->
  <section class="bg-gray-50 p-8 rounded-xl shadow-lg border border-gray-200 mt-12 mb-12">
    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">🌟 Our Special Policies 🌟</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div class="flex flex-col items-center bg-white p-6 rounded-xl shadow-md border border-yellow-300 transition-transform hover:scale-105">
        <span class="text-5xl text-yellow-500">🚀</span>
        <h3 class="text-lg font-bold mt-4 text-gray-900">30-Minute Delivery</h3>
        <p class="text-gray-700 text-center mt-2">We guarantee delivery within 30 minutes, or you'll receive a
          <strong class="text-yellow-600">50% refund</strong>.
        </p>
      </div>

      <div class="flex flex-col items-center bg-white p-6 rounded-xl shadow-md border border-green-300 transition-transform hover:scale-105">
        <span class="text-5xl text-green-500">🥬</span>
        <h3 class="text-lg font-bold mt-4 text-gray-900">Fresh Ingredients</h3>
        <p class="text-gray-700 text-center mt-2">We only use
          <strong class="text-green-600">fresh, high-quality</strong> ingredients—never frozen.
        </p>
      </div>

      <div class="flex flex-col items-center bg-white p-6 rounded-xl shadow-md border border-red-300 transition-transform hover:scale-105">
        <span class="text-5xl text-red-500">🍕</span>
        <h3 class="text-lg font-bold mt-4 text-gray-900">100% Handmade Pizza</h3>
        <p class="text-gray-700 text-center mt-2">Our pizzas are
          <strong class="text-red-600">hand-kneaded</strong>, never industrially processed.
        </p>
      </div>
    </div>
  </section>

</div>
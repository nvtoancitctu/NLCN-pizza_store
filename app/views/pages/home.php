<?php

// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Lấy các sản phẩm khuyến mãi và Lấy 3 sản phẩm ngẫu nhiên
$discountProduct = $productController->getDiscountProduct();
$randomProducts = $productController->getRandomProducts(3);

// Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? $_GET['id'] : null;
$product = $productController->getProductDetails($product_id);

// Lấy danh sách combo
$Combos = $productController->listProducts(8);

// Hiển thị thông báo lỗi hoặc thành công nếu có
$message = '';
$messageType = ''; // Để xác định loại thông báo (error hay success)
if (!empty($_SESSION['error'])) {
  $message = $_SESSION['error'];
  $messageType = 'error';
  unset($_SESSION['error']);
} elseif (!empty($_SESSION['success'])) {
  $message = $_SESSION['success'];
  $messageType = 'success';
  unset($_SESSION['success']);
}

?>

<!-- Hiển thị thông báo -->
<?php if (!empty($message)): ?>
  <div class="fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 <?= $messageType === 'error' ? 'bg-red-100 border border-red-400 text-red-700' : 'bg-green-100 border border-green-400 text-green-700' ?>">
    <span><?= htmlspecialchars($message) ?></span>
    <button onclick="this.parentElement.remove()" class="ml-2 text-sm font-semibold">✕</button>
  </div>
<?php endif; ?>

<!-- Script tự động ẩn (đặt ở cuối trang hoặc ngoài vòng lặp) -->
<?php if (!empty($message)): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      setTimeout(() => {
        const elements = document.querySelectorAll('.fixed');
        if (elements.length > 0) {
          elements.forEach(element => element.remove());
        }
      }, 5000);
    });
  </script>
<?php endif; ?>

<div class="container mx-auto px-12">

  <!-- Jumbotron -->
  <div class="bg-gradient-to-r from-blue-400 to-purple-400 shadow-lg text-white text-center p-8 md:p-16 rounded-3xl mt-12 overflow-hidden border-8 border-yellow-100">
    <!-- Nội dung chính -->
    <div class="z-10 flex flex-col md:flex-row items-center justify-between space-y-4 md:space-y-0 md:space-x-8">
      <!-- Nội dung văn bản -->
      <div class="md:w-1/2 text-center md:text-left">
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-wide drop-shadow-xl animate-fade-in text-transparent text-white">
          🍕Best Combo🍕
        </h1>
        <h3 class="text-xl md:text-2xl mt-4 font-semibold text-yellow-100">More Taste, More Savings!</h3>
        <p class="mt-4 text-lg md:text-xl text-black font-bold drop-shadow-md animate-slide-in bg-white bg-opacity-20 px-6 py-3 rounded-xl">
          Indulge in our best-selling pizza combos at unbeatable prices.
        </p>

        <a href="/products&category_id=8"
          class="inline-flex items-center bg-gradient-to-r from-yellow-400 to-orange-400 text-gray-800 font-semibold px-6 py-3 rounded-full text-lg border-2 border-yellow-200 
              transition-all duration-500 transform hover:scale-105 hover:shadow-lg mt-6">
          <span class="mr-2">VIEW COMBOS</span>
          <svg class="w-6 h-6 transition-transform duration-300 group-hover:translate-x-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </a>
      </div>

      <!-- Hình ảnh combo -->
      <div class="md:w-1/2 flex justify-center">
        <?php if (!empty($Combos)): ?>
          <div class="relative w-full max-w-md">
            <div class="relative overflow-hidden rounded-2xl shadow-lg border-4 border-white p-3 bg-white bg-opacity-10">
              <img id="comboImage" src="/images/product/<?= htmlspecialchars($Combos[0]['image']) ?>"
                alt="<?= htmlspecialchars($Combos[0]['name']) ?>"
                class="w-full h-auto object-cover rounded-xl transition-all duration-500 transform hover:scale-110">

              <!-- Nút chuyển ảnh nếu có nhiều combo -->
              <?php if (count($Combos) > 1): ?>
                <button id="prevBtn" class="absolute top-1/2 left-3 transform -translate-y-1/2 bg-black bg-opacity-50 text-white px-3 py-2 rounded-full focus:outline-none hidden">
                  ❮
                </button>
                <button id="nextBtn" class="absolute top-1/2 right-3 transform -translate-y-1/2 bg-black bg-opacity-50 text-white px-3 py-2 rounded-full focus:outline-none">
                  ❯
                </button>

                <script>
                  let comboImages = <?= json_encode(array_column($Combos, 'image')) ?>;
                  let currentIndex = 0;
                  let comboImageElement = document.getElementById('comboImage');
                  let prevBtn = document.getElementById('prevBtn');
                  let nextBtn = document.getElementById('nextBtn');

                  function updateImage(index) {
                    comboImageElement.classList.add('opacity-0');
                    setTimeout(() => {
                      comboImageElement.src = "/images/product/" + comboImages[index];
                      comboImageElement.classList.remove('opacity-0');
                    }, 300);
                  }

                  function nextImage() {
                    currentIndex = (currentIndex + 1) % comboImages.length;
                    updateImage(currentIndex);
                    prevBtn.classList.remove('hidden');
                  }

                  function prevImage() {
                    currentIndex = (currentIndex - 1 + comboImages.length) % comboImages.length;
                    updateImage(currentIndex);
                    if (currentIndex === 0) prevBtn.classList.add('hidden');
                  }

                  nextBtn.addEventListener('click', nextImage);
                  prevBtn.addEventListener('click', prevImage);

                  // Tự động chuyển ảnh sau mỗi 5 giây
                  setInterval(nextImage, 5000);
                </script>
              <?php endif; ?>
            </div>
          </div>
        <?php else: ?>
          <p class="text-lg font-semibold text-yellow-100">No combo available.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- New Pizza -->
  <?php
  $products = $productController->listProducts();

  // Lọc sản phẩm có ghi chú "New"
  $newProducts = array_filter($products, function ($product) {
    return isset($product['note']) && strtolower($product['note']) === 'new';
  });
  ?>

  <!-- New Pizza Section -->
  <div class="container mx-auto">
    <!-- Tiêu đề -->
    <div class="w-full lg:w-8/12 flex items-center justify-center mx-auto my-10">
      <div class="flex-grow border-t-2 border-gray-300"></div>
      <div class="mx-6 md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-green-600 drop-shadow-lg whitespace-nowrap">
        New Arrivals
      </div>
      <div class="flex-grow border-t-2 border-gray-300"></div>
    </div>

    <?php if (!empty($newProducts)): ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($newProducts as $product): ?>
          <div class="flex bg-white border-1 border-yellow-200 rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-transform transform hover:scale-105 p-4">
            <!-- Hình ảnh -->
            <div class="w-1/3 flex items-center justify-center relative">
              <img src="/images/product/<?= htmlspecialchars($product['image']) ?>"
                alt="<?= htmlspecialchars($product['name']) ?>"
                class="h-auto mx-auto object-cover rounded-lg transition duration-500 ease-in-out transform hover:scale-110">
            </div>

            <!-- Nội dung -->
            <div class="w-2/3 px-4 flex flex-col justify-between">
              <div>
                <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($product['name']) ?></h3>
                <p class="text-red-600 text-lg font-bold mt-2">
                  $<?= number_format($product['price'], 2) ?>
                </p>
              </div>

              <!-- Nút Order (giữ nguyên) -->
              <div class="mt-3">
                <form method="POST" action="add" class="add-to-cart-form">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                  <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']); ?>">
                  <input type="hidden" name="quantity" value="1">
                  <input type="hidden" name="size" value="S">
                  <button type="button" class="add-to-cart-button px-3 py-1 bg-yellow-500 text-white rounded-md text-sm hover:bg-green-500 transition duration-300 shadow-sm">
                    🛒 Add
                  </button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-center text-gray-500 text-lg font-semibold">No new products available at the moment.</p>
    <?php endif; ?>
  </div>

  <!-- Discount Products -->
  <div class="container mx-auto">
    <!-- Tiêu đề -->
    <div class="w-full lg:w-8/12 flex items-center justify-center mx-auto my-14">
      <div class="flex-grow border-t-2 border-gray-300"></div>
      <div class="mx-6 md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-green-600 drop-shadow-lg whitespace-nowrap">
        Special Offer
      </div>
      <div class="flex-grow border-t-2 border-gray-300"></div>
    </div>

    <?php if (!empty($discountProduct)): ?>
      <?php foreach ($discountProduct as $product): ?>
        <?php if ($product['stock_quantity'] > 0): ?>
          <div class="relative bg-white shadow-sm border-l-4 border-red-500 rounded-2xl mb-8 p-6 transition-transform transform hover:scale-105 duration-300 border-2 border-red-200">
            <!-- Ưu đãi giới hạn -->
            <div class="absolute top-4 left-4 text-white text-sm font-bold py-1 px-3 rounded-full animate-pulse-custom"
              style="background: linear-gradient(to right, #00e600, #ffd700);">
              🔥 Limited Offer
            </div>

            <!-- Icon trái tim nổi bật với hiệu ứng đập -->
            <div class="absolute top-4 right-4 heart-icon heart-beat text-red-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 18l-1.45-1.32C4.4 12.36 2 9.28 2 6.5 2 4.42 3.42 3 5.5 3c1.54 0 3.04.99 3.57 2.36h1.87C11.46 3.99 12.96 3 14.5 3 16.58 3 18 4.42 18 6.5c0 2.78-2.4 5.86-6.55 10.18L10 18z" />
              </svg>
            </div>

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

            <div class="flex flex-col md:flex-row gap-6">
              <!-- Ảnh sản phẩm -->
              <div class="flex-shrink-0 w-full md:w-1/3 flex justify-center items-center">
                <img src="/images/product/<?php echo htmlspecialchars($product['image']); ?>"
                  class="w-3/4 h-auto mx-auto object-cover rounded-lg transition duration-500 ease-in-out transform hover:scale-110"
                  alt="<?php echo htmlspecialchars($product['name']); ?>">
              </div>

              <!-- Nội dung -->
              <div class="flex-grow p-4">
                <!-- Tên sản phẩm -->
                <h5 class="text-2xl font-extrabold text-gray-800 mb-2"><?php echo htmlspecialchars($product['name']); ?></h5>

                <!-- Mô tả -->
                <p class="text-gray-600 text-sm mb-3"><?php echo htmlspecialchars($product['description']); ?></p>

                <!-- Đánh giá sao và nhận xét -->
                <div class="flex items-center mb-3">
                  <span class="text-yellow-500 text-lg">★★★★☆</span>
                  <span class="text-gray-500 text-sm ml-2">(120 reviews)</span>
                </div>

                <!-- Giá -->
                <div class="mb-3">
                  <p class="text-gray-500 text-sm line-through">Original Price: $<?php echo htmlspecialchars($product['price']); ?></p>
                  <p class="text-red-600 text-2xl font-bold">
                    Discounted Price: $<?php echo htmlspecialchars($product['discount']); ?>
                  </p>
                </div>

                <!-- Timer -->
                <div class="mb-4" id="discount-timer-<?php echo $product['id']; ?>">Special Offer!</div>

                <!-- Thanh tiến trình -->
                <div class="w-full bg-gray-200 rounded-full h-3 mb-4" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                  <div id="progress-bar-<?php echo $product['id']; ?>" class="h-3 rounded-full transition-all duration-300" style="width: 0%; background-color: rgb(255, 0, 0);"></div>
                </div>

                <!-- Nút Thêm vào giỏ hàng -->
                <form method="POST" action="add" class="add-to-cart-form" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                  <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']); ?>">
                  <input type="hidden" name="quantity" value="1">
                  <input type="hidden" name="size" value="S">
                  <button type="button" class="add-to-cart-button px-5 py-2 bg-yellow-500 text-white rounded-lg hover:bg-green-500 transition duration-300 shadow-md">
                    🛒 Add
                  </button>
                </form>
              </div>
            </div>
          </div>

          <!-- Countdown Timer and Progress Bar Script (giữ nguyên) -->
          <script>
            function countdownTimer(endTime, elementId, progressBarId, initialTime) {
              var countDownDate = new Date(endTime).getTime();

              var x = setInterval(function() {
                var now = new Date().getTime();
                var distance = countDownDate - now;

                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                hours = hours.toString().padStart(2, '0');
                minutes = minutes.toString().padStart(2, '0');
                seconds = seconds.toString().padStart(2, '0');

                document.getElementById(elementId).innerHTML = `
                <div class="flex justify-center items-center p-2 w-full md:w-1/2 bg-white alert alert-info rounded-xl shadow-sm">
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

                var progressPercentage = (distance / initialTime) * 100;
                var progressBar = document.getElementById(progressBarId);
                progressBar.style.width = progressPercentage + "%";
                progressBar.style.backgroundColor = getColor(progressPercentage);

                if (distance < 0) {
                  clearInterval(x);
                  document.getElementById(elementId).innerHTML = "EXPIRED";
                  progressBar.style.width = "0%";
                  progressBar.style.backgroundColor = "rgb(0, 0, 0)";
                }
              }, 1000);
            }

            function getColor(progress) {
              if (progress < 50) {
                return 'rgb(255, 0, 0)';
              } else if (progress < 80) {
                return 'rgb(255, 255, 0)';
              } else {
                return 'rgb(0, 255, 0)';
              }
            }

            countdownTimer('<?php echo $product['discount_end_time']; ?>', 'discount-timer-<?php echo $product['id']; ?>', 'progress-bar-<?php echo $product['id']; ?>', <?php echo (strtotime($product['discount_end_time']) - time()) * 1000; ?>);
          </script>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-center text-gray-600 text-lg font-semibold">Currently, no products are on discount.</p>
    <?php endif; ?>
  </div>

  <!-- Featured Pizzas -->
  <div class="w-full lg:w-8/12 flex items-center justify-center mx-auto my-14">
    <div class="flex-grow border-t-2 border-gray-300"></div>
    <div class="mx-6 md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-green-600 drop-shadow-lg whitespace-nowrap">
      You may also like!
    </div>
    <div class="flex-grow border-t-2 border-gray-300"></div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
    <?php foreach ($randomProducts as $product): ?>
      <div class="rounded-2xl shadow-md bg-white overflow-hidden transition-transform transform hover:scale-105 hover:shadow-xl border-2 border-blue-500"
        title="<?= htmlspecialchars($product['description']) ?>">
        <img src="/images/product/<?php echo htmlspecialchars($product['image']); ?>"
          class="w-3/5 h-auto mx-auto object-cover rounded-lg transition duration-500 ease-in-out transform hover:scale-110"
          alt="<?php echo htmlspecialchars($product['name']); ?>">

        <div class="p-6 text-center">
          <h5 class="text-xl font-bold mb-2 text-gray-800"><?php echo htmlspecialchars($product['name']); ?></h5>
          <p class="text-xl font-semibold text-blue-500 mt-2">$<?= htmlspecialchars($product['final_price']); ?></p>

          <div class="mt-2 flex justify-center">
            <form method="POST" action="add" class="add-to-cart-form" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
              <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']); ?>">
              <input type="hidden" name="quantity" value="1">
              <input type="hidden" name="size" value="S">
              <button type="button" class="add-to-cart-button px-5 py-2 bg-yellow-500 text-white rounded-lg hover:bg-green-500 transition duration-300 shadow-md">
                🛒 Add
              </button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="w-full lg:w-10/12 flex items-center justify-center mx-auto my-16">
    <div class="flex-grow border-t-2 border-gray-300"></div>
    <div class="mx-6 md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-green-600 drop-shadow-lg whitespace-nowrap">
      Some Things You Need
    </div>
    <div class="flex-grow border-t-2 border-gray-300"></div>
  </div>

  <!-- XỬ LÝ CÁC MỤC PHỤ -->
  <?php
  $vouchers = $productController->getActiveVouchers();
  $highestRatedPizzas = $productController->getTopRatedPizzas(); // Lấy top 5 pizza theo rating
  $bestSellerPizzas = $productController->getBestSellerPizzas(); // Lấy top 5 pizza bán chạy
  $testimonials = $productController->getCustomerTestimonials();

  // Lấy 5 bình luận ngẫu nhiên
  shuffle($testimonials);
  $randomTestimonials = array_slice($testimonials, 0, 5);

  $totalVouchers = count($vouchers);
  $hasToggle = $totalVouchers > 3;
  ?>

  <!-- Thêm Alpine.js -->
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <!-- Exclusive Vouchers -->
  <section class="bg-gradient-to-r from-purple-50 to-blue-50 p-8 rounded-2xl shadow-sm border-2 border-purple-300">
    <h2 class="text-2xl font-extrabold text-gray-800 mb-8 text-center">🎁 Exclusive Coupons</h2>

    <div x-data="{ showAll: false }">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($vouchers as $index => $voucher): ?>
          <div
            title="<?= htmlspecialchars($voucher['description']) ?>"
            class="voucher-item bg-yellow-50 p-6 rounded-xl shadow-md border-2 border-dashed border-yellow-300 text-center transition transform hover:scale-105 hover:shadow-xl"
            x-show="showAll || <?= $index < 3 ? 'true' : 'false' ?>"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-90"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-90">
            <!-- Tiêu đề mã voucher -->
            <h3 class="text-lg font-bold text-blue-700 mb-3">🎟 <?= htmlspecialchars($voucher['code']) ?></h3>

            <!-- Ngày hết hạn -->
            <p class="text-sm text-red-500 mb-4">
              <span class="font-semibold">🕒 Expires: </span>
              <?= htmlspecialchars($voucher['expiration_date']) ?>
            </p>

            <!-- Nút Claim -->
            <form method="post" action="index.php?page=claim_voucher">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
              <input type="hidden" name="voucher_code" value="<?= htmlspecialchars($voucher['code']) ?>">
              <input type="hidden" name="voucher_id" value="<?= htmlspecialchars($voucher['id']) ?>">
              <button
                type="submit"
                class="w-full bg-gradient-to-r from-blue-500 to-green-500 text-white px-6 py-2 rounded-lg hover:from-blue-600 hover:to-green-600 transition-all duration-300 font-semibold shadow-md">
                Claim Now!
              </button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($hasToggle): ?>
        <!-- Toggle Button -->
        <div class="flex justify-center mt-8">
          <button
            @click="showAll = !showAll"
            class="bg-gradient-to-r from-purple-500 to-indigo-500 text-white px-6 py-3 rounded-full hover:from-purple-600 hover:to-indigo-600 transition-all duration-300 font-semibold shadow-md">
            <span x-text="showAll ? 'Show Less ⬆' : 'See More ⬇'"></span>
          </button>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Combined Section -->
  <section class="bg-gray-50 p-8 mt-12 rounded-2xl shadow-sm text-center border-2 border-purple-300">
    <h2 class="text-2xl font-extrabold text-gray-900 mb-4">🍕 WEEKLY HIGHLIGHTS 🍕</h2>
    <p class="text-gray-500 text-sm mb-8">
      Consistent date: <strong class="text-red-500">
        <?php
        $date = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        echo $date->format('Y-m-d H:i:s');
        ?>
      </strong>
    </p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <!-- Column 1: Top 5 Rated Pizzas -->
      <div class="bg-yellow-50 rounded-2xl shadow-md border-1 border-yellow-400 p-6">
        <h3 class="text-lg font-semibold text-yellow-700 mb-4">🏆 Top 5 Pizzas of the Week</h3>
        <?php if (!empty($highestRatedPizzas)): ?>
          <ul class="space-y-4">
            <?php foreach ($highestRatedPizzas as $index => $pizza): ?>
              <li class="flex items-center space-x-4 transition transform hover:scale-105">
                <span class="text-yellow-600 font-bold text-lg"><?php echo $index + 1; ?></span>
                <img src="/images/product/<?= htmlspecialchars($pizza['image']) ?>"
                  alt="Top Rated Pizza" class="w-16 h-16 object-cover rounded-xl shadow-md">
                <div class="text-left">
                  <h4 class="text-md font-bold text-gray-900"><?= htmlspecialchars($pizza['name']) ?></h4>
                  <p class="text-yellow-600">⭐ <?= number_format($pizza['avg_rating'], 1) ?> / 5</p>
                  <a href="/products&category_id=<?= $pizza['id'] ?>"
                    class="text-yellow-500 hover:underline text-sm">View Details</a>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-gray-600">No top-rated pizzas available this week.</p>
        <?php endif; ?>
      </div>

      <!-- Column 2: Top 5 Best Seller Pizzas -->
      <div class="bg-green-50 rounded-2xl shadow-md border-1 border-green-400 p-6">
        <h3 class="text-lg font-semibold text-green-700 mb-4">📈 Top 5 Best Sellers</h3>
        <?php if (!empty($bestSellerPizzas)): ?>
          <ul class="space-y-4">
            <?php foreach ($bestSellerPizzas as $index => $pizza): ?>
              <li class="flex items-center space-x-4 transition transform hover:scale-105">
                <span class="text-green-600 font-bold text-lg"><?php echo $index + 1; ?></span>
                <img src="/images/product/<?= htmlspecialchars($pizza['image']) ?>"
                  alt="Best Seller Pizza" class="w-16 h-16 object-cover rounded-xl shadow-md">
                <div class="flex-1 flex flex-col justify-center items-start">
                  <h4 class="text-md font-bold text-gray-900"><?= htmlspecialchars($pizza['name']) ?></h4>
                  <div class="flex items-center space-x-1 text-green-600">
                    <span>🔥</span>
                    <span><?= number_format($pizza['total_sales']) ?></span>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-gray-600">No best-selling pizzas available this week.</p>
        <?php endif; ?>
      </div>

      <!-- Column 3: Customer Testimonials -->
      <div class="bg-blue-50 rounded-2xl shadow-md border-1 border-blue-400 p-6">
        <h3 class="text-lg font-semibold text-blue-700 mb-4">💬 Customer Feedback</h3>
        <?php if (!empty($randomTestimonials)): ?>
          <ul class="space-y-4">
            <?php foreach ($randomTestimonials as $review): ?>
              <li class="bg-white p-4 rounded-xl shadow-md border border-blue-300 transition transform hover:scale-105">
                <p class="text-gray-700 italic text-sm">“<?= htmlspecialchars($review['message']) ?>”</p>
                <p class="text-right font-semibold text-blue-700 mt-2 text-sm">- <?= htmlspecialchars($review['name']) ?></p>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-gray-600">No feedback available yet. Be the first to leave a review!</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Special Policies -->
  <section class="bg-gradient-to-r from-blue-50 to-purple-50 p-8 mt-12 rounded-2xl shadow-sm border-2 border-purple-300 mb-12">
    <h2 class="text-2xl font-extrabold text-gray-800 mb-8 text-center">🌟 Our Special Policies 🌟</h2>
    <div class="flex flex-col gap-6">
      <!-- Policy 1: 30-Minute Delivery -->
      <div class="flex items-center bg-white p-6 rounded-xl shadow-md border border-yellow-200 transition-transform hover:scale-105 hover:shadow-xl">
        <div class="flex-shrink-0 w-16 h-16 flex items-center justify-center bg-yellow-100 rounded-full">
          <span class="text-3xl text-yellow-600">🚀</span>
        </div>
        <div class="ml-4 flex-1">
          <h3 class="text-lg font-bold text-gray-900">30-Minute Delivery</h3>
          <p class="text-gray-600 mt-1">
            We guarantee delivery within 30 minutes, or you'll receive a
            <strong class="text-yellow-600">50% refund</strong>.
          </p>
        </div>
      </div>

      <!-- Policy 2: Fresh Ingredients -->
      <div class="flex items-center bg-white p-6 rounded-xl shadow-md border border-green-200 transition-transform hover:scale-105 hover:shadow-xl">
        <div class="flex-shrink-0 w-16 h-16 flex items-center justify-center bg-green-100 rounded-full">
          <span class="text-3xl text-green-600">🥬</span>
        </div>
        <div class="ml-4 flex-1">
          <h3 class="text-lg font-bold text-gray-900">Fresh Ingredients</h3>
          <p class="text-gray-600 mt-1">
            We only use
            <strong class="text-green-600">fresh, high-quality</strong> ingredients—never frozen.
          </p>
        </div>
      </div>

      <!-- Policy 3: 100% Handmade Pizza -->
      <div class="flex items-center bg-white p-6 rounded-xl shadow-md border border-red-200 transition-transform hover:scale-105 hover:shadow-xl">
        <div class="flex-shrink-0 w-16 h-16 flex items-center justify-center bg-red-100 rounded-full">
          <span class="text-3xl text-red-600">🍕</span>
        </div>
        <div class="ml-4 flex-1">
          <h3 class="text-lg font-bold text-gray-900">100% Handmade Pizza</h3>
          <p class="text-gray-600 mt-1">
            Our pizzas are
            <strong class="text-red-600">hand-kneaded</strong>, never industrially processed.
          </p>
        </div>
      </div>
    </div>
  </section>
</div>
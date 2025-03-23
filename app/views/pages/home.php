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

// Lấy danh sách combo
$Combos = $productController->listProducts(8);
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
  <div class="bg-gradient-to-r from-blue-500 via-blue-600 to-purple-600 shadow-md text-white text-center p-14 rounded-3xl mt-10 overflow-hidden border-8 border-yellow-200">
    <!-- Nội dung chính -->
    <div class="z-10 flex flex-col md:flex-row items-center justify-between space-y-8 md:space-y-0">
      <!-- Nội dung văn bản -->
      <div class="md:w-1/2 text-center md:text-left">
        <h1 class="text-4xl md:text-6xl font-extrabold tracking-wide drop-shadow-xl animate-fade-in">
          🍕Best Combo🍕
        </h1>
        <h3 class="text-2xl mt-4 font-semibold">More Taste, More Savings!</h3>
        <p class="mt-4 text-xl font-light drop-shadow-md animate-slide-in bg-black bg-opacity-30 px-6 py-3 inline-block rounded-xl">
          Indulge in our best-selling pizza combos at unbeatable prices.
        </p>

        <a href="/products&category_id=8"
          class="inline-flex items-center bg-white text-red-600 font-semibold px-4 py-3 rounded-full text-lg border-2 border-red-600 
                transition-all duration-500 transform hover:scale-105 mt-6">
          <span class="mr-2">VIEW COMBOS</span>
          <svg class="w-6 h-6 transition-transform duration-300 group-hover:translate-x-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </a>
      </div>

      <!-- Hình ảnh combo -->
      <div class="flex justify-center">
        <?php if (!empty($Combos)): ?>
          <div class="relative w-full max-w-lg">
            <div class="relative overflow-hidden rounded-xl shadow-lg border-8 border-white rounded-3xl p-2">
              <img id="comboImage" src="/images/product/<?= htmlspecialchars($Combos[0]['image']) ?>"
                alt="<?= htmlspecialchars($Combos[0]['name']) ?>"
                class="w-full h-auto object-cover rounded-3xl transition-all duration-500 transform hover:scale-110">

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
          <p class="text-lg font-semibold">No combo available.</p>
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

  // Hàm cắt chuỗi mô tả ngắn gọn (30 ký tự)
  function shortDescription($text, $length = 30)
  {
    return mb_strlen($text) > $length ? mb_substr($text, 0, $length) . '...' : $text;
  }
  ?>

  <!-- New Pizza Section -->
  <div class="container mx-auto px-4">

    <div class="w-full lg:w-6/12 flex items-center justify-center mx-auto my-10">
      <div class="flex-grow border-t-2 border-gray-700"></div>
      <div class="mx-6 md:text-2xl font-extrabold text-gray-700 drop-shadow-lg whitespace-nowrap">
        New Arrivals
      </div>
      <div class="flex-grow border-t-2 border-gray-700"></div>
    </div>

    <?php if (!empty($newProducts)): ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($newProducts as $product): ?>
          <div class="flex bg-white border-1 border-gray-500 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-transform transform hover:scale-105 p-3">
            <!-- Hình ảnh -->
            <div class="w-1/3 flex items-center justify-center relative">
              <img src="/images/product/<?= htmlspecialchars($product['image']) ?>"
                alt="<?= htmlspecialchars($product['name']) ?>"
                class="max-w-[80px] max-h-[80px] object-cover rounded-md">
              <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                NEW
              </span>
            </div>

            <!-- Nội dung -->
            <div class="w-2/3 px-3 flex flex-col justify-between">
              <div>
                <h3 class="text-md font-semibold text-gray-800"><?= htmlspecialchars($product['name']) ?></h3>
                <p class="text-gray-600 text-sm truncate" title="<?= htmlspecialchars($product['description']) ?>">
                  <?= htmlspecialchars(shortDescription($product['description'])) ?>
                </p>
                <p class="text-red-500 text-lg font-bold mt-1">
                  $<?= number_format($product['price'], 2) ?>
                </p>
              </div>

              <!-- Nút Order -->
              <div class="mt-2">
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
      <p class="text-center text-gray-600 text-lg font-semibold">No new products available at the moment.</p>
    <?php endif; ?>
  </div>

  <!-- Discount Products -->
  <div class="w-full lg:w-8/12 flex items-center justify-center mx-auto my-14">
    <div class="flex-grow border-t-2 border-gray-700"></div>
    <div class="mx-6 md:text-4xl font-extrabold text-gray-700 drop-shadow-lg whitespace-nowrap">
      Special Offer
    </div>
    <div class="flex-grow border-t-2 border-gray-700"></div>
  </div>

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
              <img src="/images/product/<?php echo htmlspecialchars($product['image']); ?>"
                class="w-4/5 h-auto mx-auto object-cover rounded-lg transition duration-500 ease-in-out transform hover:scale-110"
                alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>

            <div class="flex-grow p-4">
              <h5 class="text-3xl font-extrabold text-gray-800 mb-2"><?php echo htmlspecialchars($product['name']); ?></h5>
              <p class="text-gray-500"><?php echo htmlspecialchars($product['description']); ?></p>

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
  <div class="w-full lg:w-8/12 flex items-center justify-center mx-auto my-14">
    <div class="flex-grow border-t-2 border-gray-700"></div>
    <div class="mx-6 md:text-4xl font-extrabold text-gray-700 drop-shadow-lg whitespace-nowrap">
      You may also like!
    </div>
    <div class="flex-grow border-t-2 border-gray-700"></div>
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
    <div class="flex-grow border-t-2 border-gray-700"></div>
    <div class="mx-6 md:text-5xl font-extrabold text-gray-700 drop-shadow-lg whitespace-nowrap">
      Some Things You Need
    </div>
    <div class="flex-grow border-t-2 border-gray-700"></div>
  </div>

  <!-- XỬ LÝ CÁC MỤC PHỤ -->
  <?php
  $vouchers = $productController->getActiveVouchers();
  $topPizzas = $productController->getTopRatedPizzas();
  $testimonials = $productController->getCustomerTestimonials();

  // Kiểm tra danh sách pizza có rỗng không
  if (!empty($topPizzas)) {
    $highestRated = $topPizzas[0];
    $bestSeller = $topPizzas[0];

    foreach ($topPizzas as $pizza) {
      if ($pizza['total_sales'] > $bestSeller['total_sales']) {
        $bestSeller = $pizza;
      }
    }
  } else {
    $highestRated = null;
    $bestSeller = null;
  }

  $totalVouchers = count($vouchers);
  $hasToggle = $totalVouchers > 3;
  ?>

  <!-- Thêm Alpine.js -->
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <!-- Exclusive Vouchers -->
  <section class="bg-gray-50 p-8 rounded-2xl shadow-sm border-2 border-purple-300">
    <h2 class="text-2xl font-extrabold text-gray-900 mb-8">Exclusive Coupons</h2>

    <div x-data="{ showAll: false }">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
        <?php foreach ($vouchers as $index => $voucher): ?>
          <div title="<?= htmlspecialchars($voucher['description']) ?>"
            class="voucher-item bg-white p-6 rounded-2xl shadow-md border border-blue-300 text-center transition transform hover:scale-105 hover:shadow-lg"
            x-show="showAll || <?= $index < 3 ? 'true' : 'false' ?>"
            x-transition>

            <p class="text-gray-700 text-sm mb-2">🕒 Expires:
              <span class="font-semibold"><?= htmlspecialchars($voucher['expiration_date']) ?></span>
            </p>

            <form method="post" action="index.php?page=claim_voucher">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
              <input type="hidden" name="voucher_code" value="<?= htmlspecialchars($voucher['code']) ?>">
              <input type="hidden" name="voucher_id" value="<?= htmlspecialchars($voucher['id']) ?>">
              <button type="submit" class="w-full bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition font-semibold">
                🎟 <?= htmlspecialchars($voucher['code']) ?>
              </button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($hasToggle): ?>
        <!-- Toggle Button -->
        <div class="flex justify-center mt-6">
          <button
            @click="showAll = !showAll"
            class="bg-gray-500 text-white px-5 py-2.5 rounded-lg hover:bg-gray-600 transition">
            <span x-text="showAll ? 'Show Less ⬆' : 'Read All ⬇'"></span>
          </button>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Top Rated & Best Seller Pizzas -->
  <section class="bg-gray-50 p-8 mt-12 rounded-2xl shadow-sm text-center border-2 border-purple-300">
    <h2 class="text-3xl font-extrabold text-gray-900 mb-4">🍕 BEST OF THIS WEEK 🍕</h2>
    <p class="text-gray-500 text-sm mb-8">
      Consistent date:<strong class="text-red-500">
        <?php
        $date = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        echo $date->format('Y-m-d H:i:s');
        ?></strong>
    </p>

    <?php if ($highestRated && $bestSeller): ?>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Highest Rated Pizza -->
        <div class="flex bg-yellow-50 rounded-2xl shadow-md border-1 border-yellow-400 transition transform hover:scale-105 p-10 w-full">
          <img src="/images/product/<?= htmlspecialchars($highestRated['image']) ?>"
            alt="Highest Rated Pizza" title="<?php echo htmlspecialchars($highestRated['description']); ?>"
            class="w-2/5 h-auto object-cover rounded-xl shadow-md">

          <div class="flex-1 ml-6 flex flex-col justify-center">
            <h3 class="text-lg font-semibold text-yellow-700">🏆 Highest Rated</h3>
            <h2 class="text-2xl font-extrabold text-gray-900 mt-2"><?= htmlspecialchars($highestRated['name']) ?></h2>
            <p class="text-yellow-600 text-xl font-bold mt-2">⭐ <?= number_format($highestRated['avg_rating'], 1) ?> / 5</p>
            <a href="/products&category_id=<?= $highestRated['id'] ?>"
              class="mt-4 inline-block bg-yellow-500 text-white px-5 py-2 rounded-lg hover:bg-yellow-600 transition">
              View Details
            </a>
          </div>
        </div>

        <!-- Best Seller Pizza -->
        <div class="flex bg-green-50 rounded-2xl shadow-md border-1 border-green-400 transition transform hover:scale-105 p-10 w-full">
          <img src="/images/product/<?= htmlspecialchars($bestSeller['image']) ?>"
            alt="Best Seller Pizza" title="<?php echo htmlspecialchars($bestSeller['description']); ?>"
            class="w-2/5 h-auto object-cover rounded-xl shadow-md">

          <div class="flex-1 ml-6 flex flex-col justify-center">
            <h3 class="text-lg font-semibold text-green-700">📈 Best Seller</h3>
            <h2 class="text-2xl font-extrabold text-gray-900 mt-2"><?= htmlspecialchars($bestSeller['name']) ?></h2>
            <p class="text-green-600 text-xl font-bold mt-2">🔥 Sold: <strong><?= number_format($bestSeller['total_sales']) ?></strong> times</p>
            <a href="/products&category_id=<?= $bestSeller['id'] ?>"
              class="mt-4 inline-block bg-green-500 text-white px-5 py-2 rounded-lg hover:bg-green-600 transition">
              View Details
            </a>
          </div>
        </div>

      </div>
    <?php else: ?>
      <p class="text-center text-gray-600 text-lg mt-4">No top-rated or best-selling pizzas available this week.</p>
    <?php endif; ?>
  </section>

  <!-- Customer Testimonials -->
  <section class="bg-gray-50 p-8 mt-12 rounded-2xl shadow-sm border-2 border-purple-300">
    <h2 class="text-2xl font-extrabold text-gray-900 mb-6">What Our Customers Say 💬</h2>

    <?php if (!empty($testimonials)): ?>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php foreach ($testimonials as $review): ?>
          <div class="bg-white p-6 rounded-xl shadow-md border border-blue-300 flex items-start space-x-4 transition transform hover:scale-105">
            <!-- Avatar (giả lập nếu không có hình) -->
            <img src="/images/avatar/user.png" alt="User Avatar" class="w-14 h-14 rounded-full shadow">

            <div>
              <p class="text-gray-700 italic text-lg leading-relaxed">“<?= htmlspecialchars($review['message']) ?>”</p>
              <p class="text-right font-semibold text-blue-700 mt-2">- <?= htmlspecialchars($review['name']) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-center text-gray-600 text-lg mt-4">No customer feedback available at the moment. Be the first to leave a review!</p>
    <?php endif; ?>
  </section>

  <!-- Special Policies -->
  <section class="bg-gray-50 p-8 mt-12 rounded-2xl shadow-sm border-2 border-purple-300 mb-12">
    <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">🌟 Our Special Policies 🌟</h2>
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
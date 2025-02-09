<?php

// Tạo token CSRF nếu chưa tồn tại
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize the Product Controller
$productController = new ProductController($conn);

// Get the user ID if logged in
$user_id = $_SESSION['user_id'] ?? null;

// Fetch all categories
$categories = $productController->getCategories();

// Fetch products based on the selected category (if any)
$category_id = isset($_GET['category_id']) && is_numeric($_GET['category_id']) ? intval($_GET['category_id']) : null;
$products = $productController->listProducts($category_id);
?>

<!-- Page Title -->
<h1 class="text-center mt-8 text-5xl font-extrabold text-blue-700 tracking-wide">Our Delicious Pizza Menu</h1><br />

<!-- Product Categories -->
<div class="text-center mb-6">
    <a href="/products" class="inline-block px-4 py-2 rounded-lg <?= !$category_id ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-600' ?> m-2 hover:bg-red-700 transition duration-300">All</a>
    <?php foreach ($categories as $category): ?>
        <a href="/products/category_id=<?= $category['id'] ?>"
            class="inline-block px-4 py-2 rounded-lg <?= ($category_id == $category['id']) ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-600' ?> m-2 hover:bg-red-700 transition duration-300">
            <?= htmlspecialchars($category['name']) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Product Listing -->
<div class="container mx-auto px-4 mb-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 ">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="p-2">
                    <div class="rounded-lg shadow-lg transition-transform transform hover:scale-105 bg-white">
                        <!-- Product Image -->
                        <div class="flex justify-center">
                            <img src="/images/<?= htmlspecialchars($product['image']) ?>"
                                class="w-3/5 h-auto mx-auto object-cover rounded-lg transition duration-500 ease-in-out transform hover:rotate-12 hover:scale-110"
                                alt="<?= htmlspecialchars($product['name'] . ' image') ?>">
                        </div>

                        <!-- Product Info -->
                        <div class="p-2">
                            <h5 class="card-title text-2xl font-bold text-gray-800 text-center mb-2"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text text-l text-gray-600 text-center"><?= htmlspecialchars($product['description']) ?></p>

                            <!-- Hiển thị giá: Nếu có giảm giá thì hiện giá giảm -->
                            <div class="text-center">
                                <?php
                                // Lấy thời gian hiện tại
                                $currentDateTime = new DateTime();

                                // Lấy thời gian hết hạn của giảm giá từ cơ sở dữ liệu
                                $discountEndTime = new DateTime($product['discount_end_time']);

                                if ($product['discount'] > 0 && $discountEndTime >= $currentDateTime): ?>
                                    <!-- Hiển thị giá gốc bị gạch ngang -->
                                    <p class="text-l font-semibold text-gray-500 line-through mt-2">
                                        Original Price: $<?= htmlspecialchars($product['price']); ?>
                                    </p>
                                    <!-- Hiển thị giá giảm -->
                                    <p class="text-xl font-semibold text-red-600">
                                        Discounted Price: $<?= htmlspecialchars($product['discount']); ?>
                                    </p>
                                <?php else: ?>
                                    <!-- Nếu không có giảm giá hoặc đã hết hạn, hiển thị giá gốc -->
                                    <h3 class="text-xl font-semibold text-red-600 mt-6 mb-8">
                                        Price: $<?= htmlspecialchars($product['price']); ?>
                                    </h3>
                                <?php endif; ?>
                            </div>

                            <!-- Buttons for Add to Cart -->
                            <div class="mt-4 mb-4 flex justify-center space-x-4">
                                <form method="POST" action="add" class="add-to-cart-form" style="display:inline;">
                                    <!-- CSRF Token -->
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']); ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="size" value="S">
                                    <button type="button" class="add-to-cart-button px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition duration-300">Add to Cart</button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-xl text-gray-700 mt-4">No products found.</p>
        <?php endif; ?>
    </div>
</div>
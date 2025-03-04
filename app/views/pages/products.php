<?php

// Tạo token CSRF nếu chưa tồn tại
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Lấy ID người dùng (nếu có)
$user_id = $_SESSION['user_id'] ?? null;

// Lấy danh mục sản phẩm
$categories = $productController->getCategories();

// Lấy danh sách sản phẩm theo danh mục (nếu có)
$category_id = isset($_GET['category_id']) && is_numeric($_GET['category_id']) ? intval($_GET['category_id']) : null;

// Lấy danh sách sản phẩm
if (isset($_GET['favorite']) && $user_id) {
    // Nếu người dùng nhấn vào nút Favorite
    $products = $productController->getFavoriteProductList($user_id);
} else {
    // Nếu không, lấy sản phẩm theo danh mục (hoặc tất cả)
    $products = $productController->listProducts($category_id);
}

// Lấy danh sách sản phẩm yêu thích của người dùng
$favoriteProductIds = [];
if ($user_id) {
    $favoriteProductIds = $productController->getFavoriteProductIds($user_id);
}

?>

<!-- Tiêu đề -->
<h1 class="text-center mt-8 text-5xl font-extrabold text-blue-700 tracking-wide">Our Delicious Pizza Menu</h1><br />

<!-- Danh mục sản phẩm -->
<div class="text-center mb-6">
    <!-- Nút hiển thị tất cả sản phẩm -->
    <a href="/products" class="inline-block px-4 py-2 rounded-lg <?= (!$category_id && !isset($_GET['favorite'])) ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-600' ?> m-2 hover:bg-red-700 transition duration-300">
        All
    </a>

    <!-- Nút hiển thị theo danh mục -->
    <?php foreach ($categories as $category): ?>
        <a href="/products&category_id=<?= $category['id'] ?>"
            class="inline-block px-4 py-2 rounded-lg <?= ($category_id == $category['id']) ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-600' ?> m-2 hover:bg-red-700 transition duration-300">
            <?= htmlspecialchars($category['name']) ?>
        </a>
    <?php endforeach; ?>

    <!-- Nút yêu thích -->
    <?php if ($user_id): ?>
        <a href="/products&favorite=<?= $user_id ?>" class="inline-block px-4 py-2 rounded-lg <?= isset($_GET['favorite']) ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-600' ?> m-2 hover:bg-red-700 transition duration-300">
            ♥ Favorite
        </a>
    <?php endif; ?>
</div>


<!-- Danh sách sản phẩm -->
<div class="container mx-auto px-4 mb-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="p-2">
                    <div class="rounded-lg shadow-lg bg-white transition-transform transform hover:scale-105 alert alert-info">
                        <!-- Hình ảnh sản phẩm -->
                        <div class="flex justify-center">
                            <img src="/images/<?= htmlspecialchars($product['image']) ?>"
                                class="w-3/5 h-auto mx-auto object-cover rounded-lg transition duration-500 ease-in-out transform hover:rotate-12 hover:scale-110"
                                alt="<?= htmlspecialchars($product['name']) ?>">
                        </div>

                        <!-- Thông tin sản phẩm -->
                        <div class="p-2">
                            <h5 class="text-2xl font-bold text-gray-800 text-center mb-2"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="text-l text-gray-600 text-center"><?= htmlspecialchars($product['description']) ?></p>
                            <p class="text-sm text-gray-600 text-center mt-2 ">Stock Quantity: <?= htmlspecialchars($product['stock_quantity']) ?></p>
                            <!-- Hiển thị giá -->
                            <div class="text-center">
                                <?php
                                $currentDateTime = new DateTime();
                                $discountEndTime = new DateTime($product['discount_end_time']);
                                $isDiscountActive = ($product['discount'] > 0 && $discountEndTime >= $currentDateTime);
                                ?>

                                <?php if ($isDiscountActive): ?>
                                    <p class="text-l font-semibold text-gray-500 line-through mt-2">
                                        Original Price: $<?= htmlspecialchars($product['price']); ?>
                                    </p>
                                    <p class="text-xl font-semibold text-red-600">
                                        Discounted Price: $<?= htmlspecialchars($product['discount']); ?>
                                    </p>
                                <?php else: ?>
                                    <h3 class="text-xl font-semibold text-blue-800 mt-6 mb-8">
                                        Price: $<?= htmlspecialchars($product['price']); ?>
                                    </h3>
                                <?php endif; ?>
                            </div>

                            <!-- Nút Thêm vào giỏ hàng -->
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

                            <!-- Nút Yêu thích -->
                            <div class="absolute top-2 right-2">
                                <?php $isFavorite = in_array($product['id'], $favoriteProductIds); ?>
                                <form method="POST" action="/toggle-favorite">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']); ?>">
                                    <button type="submit" class="px-3 py-2 rounded-full text-xl transition duration-300 
                                        <?= $isFavorite ? 'bg-red-500 text-white hover:bg-red-600' : 'bg-gray-300 text-gray-800 hover:bg-gray-400' ?>">♥</button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-gray-600 text-lg">No products found.</p>
        <?php endif; ?>
    </div>
</div>
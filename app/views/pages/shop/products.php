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
<h1 class="text-center mt-12 text-5xl font-extrabold text-blue-700 tracking-wide">Our Delicious Pizza Menu</h1><br />

<!-- Danh mục sản phẩm -->
<div class="text-center mb-6">
    <!-- Nút hiển thị tất cả sản phẩm -->
    <a href="/products" class="inline-block px-4 py-2 rounded-lg <?= (!$category_id && !isset($_GET['favorite'])) ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-600' ?> m-2 hover:bg-red-700 transition duration-300">
        All
    </a>

    <!-- Nút hiển thị theo danh mục khác (ngoại trừ các loại pizza) -->
    <?php foreach ($categories as $category): ?>
        <a href="/products&category_id=<?= $category['id'] ?>"
            class="inline-block px-4 py-2 rounded-lg <?= ($category_id == $category['id']) ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-600' ?> m-2 hover:bg-red-700 transition duration-300">
            <?= htmlspecialchars($category['name']) ?>
        </a>
    <?php endforeach; ?>

    <!-- Nút yêu thích -->
    <?php if ($user_id): ?>
        <a href="/products&favorite_user=<?= $user_id ?>" class="inline-block px-4 py-2 rounded-lg <?= isset($_GET['favorite']) ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-600' ?> m-2 hover:bg-red-700 transition duration-300">
            ♥ Favorite
        </a>
    <?php endif; ?>
</div>

<style>
    /* Lightbox toàn màn hình */
    .lightbox {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease-in-out;
    }

    /* Ảnh phóng to */
    .lightbox img {
        max-width: 90%;
        max-height: 90%;
        border-radius: 10px;
    }

    /* Khi nhấp vào ảnh nhỏ, ảnh lớn xuất hiện */
    .lightbox:target {
        opacity: 1;
        visibility: visible;
    }
</style>

<!-- Danh sách sản phẩm -->
<div class="container mx-auto px-4 mb-10">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="relative flex bg-white rounded-lg shadow-lg overflow-hidden transition-transform transform hover:scale-105 p-6">

                    <!-- Hiển thị Note nếu có -->
                    <?php if (!empty($product['note'])): ?>
                        <div class="absolute top-2 left-2 bg-yellow-500 text-white text-xs font-bold px-3 py-1 rounded">
                            <?= htmlspecialchars($product['note']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Nếu sản phẩm đã bán hết thì hiển thị "Sold Out" -->
                    <?php if ($product['stock_quantity'] == 0): ?>
                        <div class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center border-4 border-red-600 rounded-lg z-10 pointer-events-none">
                            <span class="text-red-600 text-4xl font-extrabold">SOLD OUT</span>
                        </div>
                    <?php endif; ?>

                    <!-- Hình ảnh sản phẩm bên trái -->
                    <div class="w-1/3 flex items-center justify-center">
                        <a href="#full-img-<?= $product['id'] ?>">
                            <img src="/images/product/<?= htmlspecialchars($product['image']) ?>"
                                class="max-w-full max-h-full object-contain rounded-lg transition duration-500 ease-in-out transform hover:scale-110 cursor-pointer"
                                alt="<?= htmlspecialchars($product['name']) ?>">
                        </a>
                    </div>

                    <!-- Lightbox hiển thị ảnh phóng to -->
                    <a href="#" class="lightbox" id="full-img-<?= $product['id'] ?>">
                        <img src="/images/product/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    </a>

                    <!-- Thông tin sản phẩm bên phải -->
                    <div class="flex-1 p-4">
                        <div class="flex justify-between items-center">
                            <h5 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($product['name']) ?></h5>

                            <!-- Biểu tượng yêu thích, tròn như nút add to cart -->
                            <?php if ($user_id): ?>
                                <?php $isFavorite = in_array($product['id'], $favoriteProductIds); ?>
                                <form method="POST" action="/toggle-favorite">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']); ?>">
                                    <button type="submit" class="text-xl w-10 h-10 flex items-center justify-center rounded-full transition duration-300 
                                    <?= $isFavorite ? 'bg-red-500 text-white hover:bg-red-600' : 'bg-gray-300 text-gray-800 hover:bg-gray-400' ?>">
                                        ♥
                                    </button>
                                </form>
                            <?php else: ?>
                                <button onclick="alert('Please log in to add favorites!'); window.location.href='/login';"
                                    class="text-xl w-10 h-10 flex items-center justify-center rounded-full bg-gray-300 text-gray-800 hover:bg-gray-400 transition duration-300">
                                    ♥
                                </button>
                            <?php endif; ?>
                        </div>

                        <p class="text-sm text-gray-600 mt-2" title="<?php echo htmlspecialchars($product['description']); ?>">
                            <?= strlen($product['description']) > 20 ? htmlspecialchars(substr($product['description'], 0, 30)) . '...' : htmlspecialchars($product['description']); ?>
                        </p>
                        <p class="text-sm text-gray-600 mt-2">Stock: <?= htmlspecialchars($product['stock_quantity']) ?></p>

                        <!-- Khoảng trống trước giá -->
                        <div class="mt-4 flex justify-between items-center">
                            <div>
                                <?php
                                $currentDateTime = new DateTime();
                                $discountEndTime = new DateTime($product['discount_end_time']);
                                $isDiscountActive = ($product['discount'] > 0 && $discountEndTime >= $currentDateTime);
                                ?>

                                <?php if ($isDiscountActive): ?>
                                    <p class="text-sm text-gray-500 line-through">Original: $<?= htmlspecialchars($product['price']); ?></p>
                                    <p class="text-sm text-gray-600">From: <span class="text-xl text-red-600">$<?= htmlspecialchars($product['discount']); ?></span></p>
                                <?php else: ?>
                                    <p class="text-sm text-gray-600">From: <span class="text-xl text-blue-600">$<?= htmlspecialchars($product['price']); ?></span></p>
                                <?php endif; ?>
                            </div>

                            <!-- Nút Thêm vào giỏ hàng -->
                            <form method="POST" action="add" class="add-to-cart-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']); ?>">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="size" value="S">
                                <button type="button" class="add-to-cart-button text-xl bg-yellow-500 text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-yellow-600 transition duration-300" <?= ($product['stock_quantity'] == 0) ? 'disabled' : '' ?>>
                                    +
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-gray-600 text-lg">No products found.</p>
        <?php endif; ?>
    </div>
</div>
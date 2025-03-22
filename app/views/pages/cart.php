<?php
// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['success'] = "Please log in to add items to your cart!";
    header("Location: /login");
    exit();
}

// Lấy user_id từ phiên
$user_id = $_SESSION['user_id'];

// Lấy sản phẩm trong giỏ hàng
$cartItems = $cartController->getCartItems($user_id);

// Xử lý cập nhật số lượng sản phẩm trong giỏ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    } else {
        unset($_SESSION['csrf_token']);
    }

    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];
    $size = $_POST['size'];

    $cartController->updateCartItem($cart_id, $quantity, $size);

    header("Location: /cart");
    exit();
}

// Xử lý xóa sản phẩm khỏi giỏ hàng
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'delete' && isset($_GET['cart_id']) && is_numeric($_GET['cart_id'])) {

        // Check CSRF token
        if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
            die('Invalid CSRF token');
        } else {
            unset($_SESSION['csrf_token']);
        }

        // Xóa sản phẩm
        $cart_id = (int) $_GET['cart_id'];
        $cartController->deleteCartItem($cart_id);

        header("Location: /cart");
        exit();
    } else {
        if ($_GET['action'] === 'delete') {
            echo "Invalid cart ID or action.";
        }
    }
}
?>

<!-- Hiển thị alert bằng JavaScript nếu có thông báo -->
<?php if (!empty($_SESSION['message'])): ?>
    <script>
        alert("<?= htmlspecialchars($_SESSION['message']) ?>");
    </script>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<!-- Tiêu đề chính -->
<h1 class="text-4xl font-extrabold mt-8 text-center text-blue-700 drop-shadow-lg">
    YOUR CART
</h1>

<!-- Ghi chú về giá size và thông tin đặc biệt -->
<div class="mx-auto w-6/12 text-center text-gray-900 mt-4 mb-6 text-sm bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500 shadow-sm">
    <p class="font-semibold text-blue-700">💡 Pricing & Size Guide:</p>
    <ul class="mt-2 text-gray-800 space-y-2">
        <li>✅ <strong>Size M</strong> costs <span class="text-blue-600">+20%</span> and <strong>Size L</strong> costs <span class="text-blue-600">+50%</span> of the base price.</li>
        <li>🍗 <strong>Fried Chicken:</strong>
            <span class="text-gray-700">Size S - 4 pieces, Size M - 6 pieces, Size L - 9 pieces.</span>
        </li>
    </ul>
</div>

<!-- Container chính của giỏ hàng -->
<div class="container mx-auto w-10/12">
    <?php if (!empty($cartItems)): ?>
        <!-- Bảng hiển thị sản phẩm trong giỏ hàng -->
        <div class="bg-white shadow-xl rounded-lg overflow-hidden mb-8 border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <!-- Tiêu đề bảng -->
                    <thead>
                        <tr class="bg-gradient-to-r from-blue-500 to-blue-600 text-white text-sm uppercase">
                            <th class="px-6 py-4 text-center">Product</th>
                            <th class="px-6 py-4 text-center">Price</th>
                            <th class="px-6 py-4 text-center">Stock</th>
                            <th class="px-6 py-4 text-center">Size & Quantity</th>
                            <th class="px-6 py-4 text-center">Total</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr class="border-t border-gray-300 hover:bg-gray-100 transition">
                                <!-- Hình ảnh và tên sản phẩm -->
                                <td class="px-6 py-4 flex items-center space-x-4">
                                    <img src="/images/product/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-16 h-16 rounded-md shadow">
                                    <span class="text-gray-900 font-medium"><?= htmlspecialchars($item['name']) ?></span>
                                </td>

                                <!-- Giá sản phẩm -->
                                <td class="px-6 py-4 text-center text-gray-800">
                                    <?php if ($item['base_price'] < $item['price']): ?>
                                        <span class="text-gray-500 line-through text-sm">
                                            $<?= number_format($item['price'], 2) ?>
                                        </span>
                                        <p class="text-red-600 font-semibold mt-1">
                                            $<?= number_format($item['base_price'], 2) ?>
                                        </p>
                                    <?php else: ?>
                                        <span class="font-semibold">
                                            $<?= number_format($item['price'], 2) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Số lượng tồn kho -->
                                <td class="px-6 py-4 text-center font-semibold text-green-600"><?= htmlspecialchars($item['stock_quantity']) ?></td>

                                <!-- Lựa chọn size & số lượng CHỈ ÁP DỤNG CHO PIZZA VÀ CHICKEN -->
                                <td class="px-6 py-4">
                                    <form method="POST" action="/cart" class="flex justify-center items-center space-x-3">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">

                                        <?php if (in_array($item['category_id'], [1, 2, 3, 6])): ?>
                                            <select name="size" class="border rounded-lg px-2 py-1 bg-white text-center">
                                                <option value="S" <?= $item['size'] === 'S' ? 'selected' : '' ?>>S</option>
                                                <option value="M" <?= $item['size'] === 'M' ? 'selected' : '' ?>>M</option>
                                                <option value="L" <?= $item['size'] === 'L' ? 'selected' : '' ?>>L</option>
                                            </select>
                                        <?php else: ?>
                                            <input type="hidden" name="size" value="<?= $item['size'] ?>">
                                            <span class="text-gray-700"><?= htmlspecialchars($item['size']) ?></span>
                                        <?php endif; ?>

                                        <input type="number" name="quantity" value="<?= htmlspecialchars($item['quantity']) ?>" min="1"
                                            class="w-16 text-center border rounded-lg px-2 py-1 focus:ring focus:ring-blue-300">

                                        <button type="submit" name="update" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-md text-xs font-semibold transition">
                                            Update
                                        </button>
                                    </form>
                                </td>

                                <!-- Tổng giá -->
                                <td class="px-6 py-4 text-center font-semibold text-red-600">
                                    $<?= number_format($item['total_price'], 2) ?>
                                </td>

                                <!-- Xóa sản phẩm -->
                                <td class="px-6 py-4 text-center">
                                    <a href="/index.php?page=cart&action=delete&cart_id=<?= $item['id'] ?>&csrf_token=<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md text-xs font-semibold transition">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tổng giá đơn hàng -->
            <div class="flex justify-between items-center bg-gray-200 px-6 py-4">
                <span class="text-lg font-bold">Total Price:</span>
                <span class="text-2xl font-semibold text-red-600">
                    $<?= number_format(array_sum(array_column($cartItems, 'total_price')), 2) ?>
                </span>
            </div>
        </div>

        <!-- Nút tiến hành thanh toán -->
        <div class="mb-4 text-center">
            <button type="button" onclick="window.location.href='/checkout'"
                class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-red-500 transition duration-300 shadow-lg">
                Checkout
            </button>
        </div>
    <?php else: ?>
        <!-- Hiển thị nếu giỏ hàng trống -->
        <div class="border-2 border-blue-300 text-center p-4 rounded-xl bg-white text-blue-800">
            <p>Your cart is empty. Why not check out our delicious pizzas?</p>
            <button type="button" onclick="window.location.href='/products'"
                class="mt-4 bg-green-600 hover:bg-yellow-600 shadow-lg text-white px-5 py-2 rounded-lg transition duration-300">
                Go to Products
            </button>
        </div>
    <?php endif; ?>
</div>
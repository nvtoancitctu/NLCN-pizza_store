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

<!-- Tiêu đề chính -->
<h1 class="text-4xl font-extrabold mt-8 text-center text-blue-700 drop-shadow-lg">
    YOUR CART
</h1>

<!-- Ghi chú về giá size -->
<p class="text-center text-gray-800 mt-2 mb-4 text-sm">
    <strong>NOTE: </strong>Size M costs +20% and Size L costs +50% of the base price, including discounted prices.
</p>

<!-- Container chính của giỏ hàng -->
<div class="container mx-auto w-10/12">
    <?php if (!empty($cartItems)): ?>
        <!-- Bảng hiển thị sản phẩm trong giỏ hàng -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-8 alert alert-info">
            <div class="overflow-x-auto">
                <table class="table-auto w-full border border-gray-300">
                    <!-- Tiêu đề bảng -->
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-100 to-gray-200 text-blue-600">
                            <th class="px-6 py-3 text-left text-sm font-semibold">Product</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold">Price</th>
                            <th class="px-6 py-3 text-center text-sm font-semibold">Size, Quantity</th>
                            <th class="px-6 py-3 text-center text-sm font-semibold">Total</th>
                            <th class="px-6 py-3 text-center text-sm font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr class="border-t border-gray-300 hover:bg-gray-50 transition duration-150">
                                <!-- Cột hình ảnh và tên sản phẩm -->
                                <td class="px-6 py-4 flex items-center">
                                    <img src="/images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-16 h-16 mr-4 rounded-md shadow-md">
                                    <span class="text-gray-800 font-medium">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </span>
                                </td>

                                <!-- Cột giá sản phẩm -->
                                <td class="px-6 py-4 text-gray-800">
                                    <?php if ($item['base_price'] < $item['price']): ?>
                                        <span class="text-sm text-gray-500 line-through">
                                            $<?= number_format($item['price'], 2) ?>
                                        </span>
                                        <p class="text-red-600 mt-1 font-semibold">
                                            $<?= number_format($item['base_price'], 2) ?>
                                        </p>
                                    <?php else: ?>
                                        <span class="text-gray-800 font-semibold">
                                            $<?= number_format($item['price'], 2) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Cột lựa chọn size và số lượng -->
                                <td class="px-6 py-4 text-center align-middle">
                                    <form method="POST" action="/cart" class="flex justify-center items-center space-x-4">
                                        <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                                        <select name="size" class="border border-gray-300 rounded px-2 py-1 text-center">
                                            <option value="S" <?= $item['size'] === 'S' ? 'selected' : '' ?>>S</option>
                                            <option value="M" <?= $item['size'] === 'M' ? 'selected' : '' ?>>M</option>
                                            <option value="L" <?= $item['size'] === 'L' ? 'selected' : '' ?>>L</option>
                                        </select>

                                        <input type="number" name="quantity" value="<?= htmlspecialchars($item['quantity']) ?>" min="1" class="border border-gray-300 rounded px-2 py-1 w-16 text-center focus:outline-none focus:ring-2 focus:ring-blue-400">
                                        <button type="submit" name="update" class="bg-blue-500 text-white px-3 py-2 rounded hover:bg-blue-600 transition duration-200 text-xs font-semibold">
                                            Update
                                        </button>
                                    </form>
                                </td>

                                <!-- Cột tổng giá của sản phẩm -->
                                <td class="px-4 py-3 text-red-600 font-semibold text-center">
                                    $<?= number_format($item['total_price'], 2) ?>
                                </td>

                                <!-- Cột thao tác xóa sản phẩm khỏi giỏ hàng -->
                                <td class="px-4 py-3 text-center">
                                    <a href="/index.php?page=cart&action=delete&cart_id=<?= $item['id'] ?>&csrf_token=<?= htmlspecialchars($_SESSION['csrf_token']) ?>" class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 transition duration-200 text-xs font-semibold">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Hiển thị tổng giá đơn hàng -->
            <div class="text-right px-6 py-3 bg-gray-100">
                <span class="font-bold text-lg">Total Price: </span>
                <span class="text-red-600 text-xl font-semibold">
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
        <div class="alert alert-info text-center p-4 rounded-xl bg-white text-blue-800">
            <p>Your cart is empty. Why not check out our delicious pizzas?</p>
            <button type="button" onclick="window.location.href='/products'"
                class="mt-4 bg-green-600 hover:bg-yellow-600 shadow-lg text-white px-5 py-2 rounded-lg transition duration-300">
                Go to Products
            </button>
        </div>
    <?php endif; ?>
</div>
<?php
ob_start();
session_start();

// Include file cấu hình (kết nối database)
require_once '../config/config.php';

// Kiểm tra nếu bấm đăng xuất
if (isset($_POST['logout'])) {
    // Destroy session to log out the user
    session_unset();
    session_destroy();
    // Redirect to the home page but with a URL parameter to show the modal
    header("Location: /login");
    exit();
}

// Include các phần như header, navbar
require_once '../app/views/includes/header.php';
require_once '../app/views/includes/navbar.php';

// Include các controllers
require_once '../app/controllers/CartController.php';
require_once '../app/controllers/OrderController.php';
require_once '../app/controllers/ProductController.php';
require_once '../app/controllers/UserController.php';

// Khởi tạo các controllers
$cartController = new CartController($conn);
$orderController = new OrderController($conn);
$productController = new ProductController($conn);
$userController = new UserController($conn);

// Routing đơn giản thông qua tham số "page"
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Điều hướng tới các trang khác nhau
switch ($page) {
    // KHÁCH HÀNG
    case 'home':
        include '../app/views/pages/home.php';
        break;
    case 'claim_voucher':
        include '../app/views/pages/claim_voucher.php';
        break;
    case 'feedback':
        include '../app/views/pages/feedback.php';
        break;
    //---> shoping
    case 'products':
        include '../app/views/pages/shop/products.php';
        break;
    case 'toggle-favorite':
        include '../app/views/pages/shop/toggle_favorite.php';
        break;
    case 'cart':
        include '../app/views/pages/shop/cart.php';
        break;
    case 'checkout':
        include '../app/views/pages/shop/checkout.php';
        break;
    case 'send-email':
        include '../app/views/pages/shop/send-email.php';
        break;
    case 'order-success':
        include '../app/views/pages/shop/order-success.php';
        break;
    //---> authentication
    case 'login':
        include '../app/views/pages/auth/login.php';
        break;
    case 'send-otp':
        include '../app/views/pages/auth/send-otp.php';
        break;
    case 'reset-password':
        include '../app/views/pages/auth/reset-password.php';
        break;
    case 'account':
        include '../app/views/pages/auth/account.php';
        break;
    case 'register':
        include '../app/views/pages/auth/register.php';
        break;
    case 'send-email-welcome':
        include '../app/views/pages/auth/send-email-welcome.php';
        break;
    // QUẢN TRỊ VIÊN
    case 'list':
        include '../app/views/admin/list.php';
        break;
    case 'export-products':
        include '../app/views/admin/export-products.php';
        break;
    case 'import-products':
        include '../app/views/admin/import-products.php';
        break;
    case 'send-email_user':
        include '../app/views/admin/send-email_user.php';
        break;
    //---> quản lí sản phẩm
    case 'add':
        include '../app/views/admin/products/add.php';
        break;
    case 'edit':
        include '../app/views/admin/products/edit.php';
        break;
    case 'delete':
        include '../app/views/admin/products/delete.php';
        break;
    //---> quản lí voucher
    case 'add-voucher':
        include '../app/views/admin/vouchers/add-voucher.php';
        break;
    case 'edit-voucher':
        include '../app/views/admin/vouchers/edit-voucher.php';
        break;
    case 'delete-voucher':
        include '../app/views/admin/vouchers/delete-voucher.php';
        break;
    //---> quản lí đơn hàng
    case 'delete-order':
        include '../app/views/admin/orders/delete-order.php';
        break;
    case 'send-email_order':
        include '../app/views/admin/orders/send-email_order.php';
        break;
    //---> thống kê 
    case 'statistics':
        include '../app/views/admin/reports/statistics.php';
        break;
    case 'exportPDF':
        include '../app/views/admin/reports/exportPDF.php';
        break;
    default:
        include '../app/views/pages/404.php'; // Trang lỗi 404
        break;
}

// Include footer
require_once '../app/views/includes/footer.php';
ob_end_flush();
?>

<!-- Hiển thị thông báo lỗi hoặc thành công nếu có -->
<?php
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
    <script>
        // Tự động ẩn thông báo sau 5 giây
        setTimeout(() => {
            document.querySelector('.fixed').remove();
        }, 5000);
    </script>
<?php endif; ?>
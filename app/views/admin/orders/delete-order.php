<?php

// Kiểm tra quyền admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = "You must login by admin account to access.";
    header("Location: /login");
    exit();
}

// Kiểm tra yêu cầu POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['order_id'])) {

    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    } else {
        unset($_SESSION['csrf_token']);
    }

    $order_id = intval($_POST['order_id']); // Chuyển thành số nguyên để tránh lỗi

    if ($order_id > 0) {
        $orderController->deleteOrder($order_id);
        $_SESSION['success'] = "Order $order_id has been deleted successfully!";
    } else {
        $_SESSION['error'] = "Invalid order ID!";
    }
}

// Quay lại trang danh sách đơn hàng
header("Location: /admin");
exit();

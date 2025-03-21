<?php

// Kiểm tra dữ liệu từ form
if (!isset($_SESSION['user_id'], $_POST['product_id'], $_POST['csrf_token'])) {
    die("Unauthorized request.");
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id']);

// Xác minh CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
} else {
    unset($_SESSION['csrf_token']);
}

// Thay đổi trạng thái yêu thích (thêm hoặc xóa)
$userController->manageFavorite($user_id, $product_id);

// Quay lại trang trước
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;

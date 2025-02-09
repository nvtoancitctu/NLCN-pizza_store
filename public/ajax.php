<?php
session_start();

// Bao gồm các file cần thiết
require_once '../config/config.php';
require_once '../app/controllers/CartController.php';

// Khởi tạo CartController
$cartController = new CartController($conn);

// Lấy ID người dùng nếu đã đăng nhập
$user_id = $_SESSION['user_id'] ?? null;

// Kiểm tra action được thực hiện
$action = $_POST['action'] ?? null;

// Xử lý yêu cầu AJAX
if ($action === 'add_to_cart') {
    if ($user_id) {
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        $size = $_POST['size'];

        // Gọi phương thức addToCart từ CartController
        $success = $cartController->addToCart($user_id, $product_id, $quantity, $size);

        // Trả về kết quả dưới dạng JSON
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
    } else {
        // Trả về thông báo nếu người dùng chưa đăng nhập
        header('Content-Type: application/json');
        echo json_encode(['loggedIn' => false]);
    }
    exit();
}

// Phản hồi mặc định nếu action không hợp lệ
header('Content-Type: application/json');
echo json_encode(['error' => 'Invalid action']);
exit();

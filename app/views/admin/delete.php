<?php

// Kiểm tra quyền truy cập
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: /login");
    exit();
}

$productController = new ProductController($conn);

// Xóa sản phẩm nếu có yêu cầu từ GET
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);

    if ($product_id > 0) {
        $productController->deleteProduct($product_id);
        $_SESSION['success'] = "Product $product_id has been deleted successfully!";
        $_SESSION['limit'] = $productController->countProducts();
        $_SESSION['page'] = 1;
    } else {
        $_SESSION['error'] = "Invalid product ID!";
    }
}

// Quay lại trang danh sách đơn hàng
header("Location: /admin/list");
exit();

<?php
// Kiểm tra quyền truy cập
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: /login");
    exit();
}

$productController = new ProductController($conn);
$products = $productController->listProducts();

// Xóa sản phẩm nếu có yêu cầu từ GET
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $productController->deleteProduct($product_id);
    $_SESSION['success'] = "Product $product_id has been deleted successfully!";
    $_SESSION['limit'] = $productController->countProducts();
    $_SESSION['page'] = 1;
    header("Location: /admin/list");
    exit();
}

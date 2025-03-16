<?php

// Kiểm tra yêu cầu POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']); // Chuyển thành số nguyên để tránh lỗi

    if ($order_id > 0) {
        $orderController->deleteOrder($order_id);
        $_SESSION['success'] = "Order $order_id has been deleted successfully!";
    } else {
        $_SESSION['error'] = "Invalid order ID!";
    }
}

// Quay lại trang danh sách đơn hàng
header("Location: /admin/list");
exit();

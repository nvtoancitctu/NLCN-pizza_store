<?php

// Kiểm tra yêu cầu POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voucher_id'])) {

    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    } else {
        unset($_SESSION['csrf_token']);
    }

    $voucher_id = $_POST['voucher_id'];

    try {
        $stmt = $conn->prepare("DELETE FROM vouchers WHERE id = ?");
        $stmt->execute([$voucher_id]);

        $_SESSION['success'] = "Voucher (ID: $voucher_id) has been deleted successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to delete voucher: " . $e->getMessage();
    }
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;

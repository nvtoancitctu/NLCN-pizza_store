<?php

// Kiểm tra yêu cầu POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voucher_id'])) {
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

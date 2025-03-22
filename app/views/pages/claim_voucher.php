<?php

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You need to log in to claim a voucher.";
    header("Location: /login");
    exit;
}

$userId = $_SESSION['user_id'];
$voucherId = $_POST['voucher_id'];
$voucherCode = $_POST['voucher_code'];

// Check CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
} else {
    unset($_SESSION['csrf_token']);
}

// Kiểm tra user đã claim voucher này chưa
$checkClaim = $conn->prepare("SELECT COUNT(*) FROM user_voucher WHERE user_id = ? AND voucher_id = ? AND status = 'unused'");
$checkClaim->execute([$userId, $voucherId]);
$alreadyClaimed = $checkClaim->fetchColumn();

if ($alreadyClaimed > 0) {
    // Nếu đã claim thì thông báo và quay lại trang trước
    $_SESSION['success'] = "You have already claimed this voucher ($voucherCode)!";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// Tiếp tục xử lý claim
$conn->beginTransaction();
try {
    // Giảm số lượng voucher
    $updateVoucher = $conn->prepare("UPDATE vouchers SET quantity = quantity - 1 WHERE id = ?");
    $updateVoucher->execute([$voucherId]);

    // Thêm voucher vào tài khoản người dùng
    $insertClaim = $conn->prepare("INSERT INTO user_voucher (user_id, voucher_id) VALUES (?, ?)");
    $insertClaim->execute([$userId, $voucherId]);

    $conn->commit();
    $_SESSION['success'] = "You have successfully claimed the voucher ($voucherCode)!";
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['success'] = "An unexpected error occurred.";
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;

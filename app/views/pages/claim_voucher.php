<?php

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You need to log in to claim a voucher.";
    header("Location: /login");
    exit;
}

$userId = $_SESSION['user_id'];
$voucherId = $_POST['voucher_id'];

try {
    // Kiểm tra xem user đã nhận voucher này chưa
    $stmt = $conn->prepare("SELECT COUNT(*) FROM user_voucher WHERE user_id = ? AND voucher_id = ?");
    $stmt->execute([$userId, $voucherId]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        $_SESSION['error'] = "You have already claimed this voucher.";
    } else {
        // Thêm vào bảng user_voucher
        $stmt = $conn->prepare("INSERT INTO user_voucher (user_id, voucher_id) VALUES (?, ?)");
        if ($stmt->execute([$userId, $voucherId])) {
            // Cập nhật trạng thái voucher thành 'used'
            $updateStmt = $conn->prepare("UPDATE vouchers SET status = 'used' WHERE id = ?");
            $updateStmt->execute([$voucherId]);

            $_SESSION['success'] = "You have successfully claimed the voucher!";
        } else {
            $_SESSION['error'] = "Error while claiming the voucher.";
        }
    }
} catch (Exception $e) {
    $_SESSION['error'] = "An unexpected error occurred.";
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;

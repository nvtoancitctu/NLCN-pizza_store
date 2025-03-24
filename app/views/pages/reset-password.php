<?php
// Nếu không có email trong session, điều hướng về trang nhập OTP
if (!isset($_SESSION['reset_email'])) {
    header("Location: /send-otp");
    exit;
}

// Chỉ khởi tạo `otp_verified` nếu chưa có
if (!isset($_SESSION['otp_verified'])) {
    $_SESSION['otp_verified'] = 0;
}

// Đếm số lần nhập sai OTP
if (!isset($_SESSION['otp_attempts'])) {
    $_SESSION['otp_attempts'] = 0;
}

// Xử lý xác thực OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $input_otp = (int)($_POST['otp'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Kiểm tra CSRF token
    if ($csrf_token !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    // Kiểm tra OTP
    if ($input_otp === $_SESSION['reset_otp']) {
        $_SESSION['otp_verified'] = 1; // OTP đúng
        $_SESSION['otp_attempts'] = 0; // Reset số lần nhập sai
    } else {
        $_SESSION['otp_attempts']++;

        if ($_SESSION['otp_attempts'] >= 2) {
            unset($_SESSION['reset_otp'], $_SESSION['otp_attempts']);
            echo "<script>
                    alert('Incorrect OTP! Please request a new OTP.');
                    window.location.href = '/send-otp';
                  </script>";
            exit;
        } else {
            $_SESSION['message'] = "Incorrect OTP! You have one more attempt.";
        }
    }
}

// Xử lý cập nhật mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {

    // Kiểm tra CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if ($csrf_token !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== 1) {
        $_SESSION['message'] = "Please verify OTP first!";
        header("Location: /send-otp");
        exit;
    }

    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $_SESSION['message'] = "Passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $_SESSION['message'] = "Password must be at least 6 characters.";
    } else {
        // Mã hóa mật khẩu
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $email = $_SESSION['reset_email'];

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashed_password, $email]);

        // Xóa session để bảo mật
        unset($_SESSION['reset_email'], $_SESSION['reset_otp'], $_SESSION['otp_verified']);

        $_SESSION['success'] = "Password updated successfully! Please login.";
        header("Location: /login");
        exit();
    }
}
?>

<!-- Giao diện -->
<div class="bg-gradient-to-r from-blue-50 to-blue-100">
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white p-8 rounded-lg shadow-lg w-full max-w-md text-center">
        <h2 class="text-xl font-bold text-gray-700 text-center mb-4">🔑 Reset Password</h2>

        <?php if (!empty($_SESSION['message'])): ?>
            <p class="text-red-500 text-center mb-3"><?= $_SESSION['message'];
                                                        unset($_SESSION['message']); ?></p>
        <?php endif; ?>

        <?php if ($_SESSION['otp_verified'] === 0): ?>
            <form action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="text" name="otp" placeholder="Enter OTP"
                    class="border border-gray-300 rounded-lg w-full py-2 px-4 text-center mb-3" required>
                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold p-2 rounded-lg">Verify OTP</button>
            </form>
        <?php else: ?>
            <form action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="password" name="password" placeholder="New Password"
                    class="border border-gray-300 rounded-lg w-full py-2 px-4 mb-3" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password"
                    class="border border-gray-300 rounded-lg w-full py-2 px-4 mb-3" required>
                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold p-2 rounded-lg">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>
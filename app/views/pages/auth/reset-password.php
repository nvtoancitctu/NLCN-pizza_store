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
        $_SESSION['error'] = "CSRF Token not right, please login again!";
        header("Location: /login");
        exit();
    }

    // Kiểm tra OTP
    if ($input_otp === $_SESSION['reset_otp']) {
        $_SESSION['otp_verified'] = 1; // OTP đúng
        $_SESSION['otp_attempts'] = 0; // Reset số lần nhập sai
        $_SESSION['success'] = "OTP is correct! You can reset your password.";
    } else {
        $_SESSION['otp_attempts']++;

        if ($_SESSION['otp_attempts'] >= 2) {
            unset($_SESSION['reset_otp'], $_SESSION['otp_attempts']);
            $_SESSION['error'] = "Incorrect OTP! Please enter again your email to request a new OTP.";
            header("Location: /send-otp");
            exit;
        } else {
            $_SESSION['error'] = "Incorrect OTP! You have one more attempt.";
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
        $_SESSION['error'] = "Please verify OTP first!";
        header("Location: /send-otp");
        exit;
    }

    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters.";
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

<div class="bg-gradient-to-r from-indigo-50 to-indigo-200 min-h-screen flex items-center justify-center">
    <div class="bg-white p-10 rounded-xl shadow-2xl w-full max-w-md text-center transform transition-all hover:scale-105">
        <h2 class="text-2xl font-semibold text-indigo-700 mb-6">🔑 Reset Password</h2>

        <?php if ($_SESSION['otp_verified'] === 0): ?>
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="text" name="otp" placeholder="Enter OTP"
                    class="border border-indigo-300 rounded-lg w-full py-3 px-5 text-center text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-200" required>
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition duration-300">Verify OTP</button>
            </form>
        <?php else: ?>
            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="password" name="password" placeholder="New Password"
                    class="border border-indigo-300 rounded-lg w-full py-3 px-5 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-200" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password"
                    class="border border-indigo-300 rounded-lg w-full py-3 px-5 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-200" required>
                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition duration-300">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php';

function sendEmail($to, $subject, $message)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'loverhut.pizzastore@gmail.com';
        $mail->Password   = 'bfzc rwzh magz xtdg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('loverhut.pizzastore@gmail.com', 'Lover\'s Hut Pizza Store');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Kiểm tra email từ form gửi OTP
$showOtpForm = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {

    unset($_SESSION['reset_email'], $_SESSION['reset_otp'], $_SESSION['otp_verified']);
    $email = trim($_POST['email']);

    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: /login");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['error'] = "Email not found!";
        } else {
            $otp = rand(100000, 999999);
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['otp_expiry'] = time() + 30;
            $_SESSION['reset_email'] = $email;

            // Send OTP email
            $subject = "🔐 Password Reset OTP";
            $message = "
                        <html>
                        <head>
                            <title>Your OTP Code</title>
                        </head>
                        <body style='font-family: Arial, sans-serif;'>
                            <h2 style='color: #2d89ef;'>🔐 Password Reset Request</h2>
                            <p>Hello,</p>
                            <p>We have received a request to reset the password for your account.</p>
                            <p style='font-size: 18px;'><strong>Your OTP code is: <span style='color: red; font-size: 24px;'>$otp</span></strong></p>
                            <p>This code is valid for 1 minutes.</p>
                            <p>If you did not request a password reset, please ignore this email.</p>
                            <p>Best regards,<br><strong>Lover's Hut Pizza Store</strong></p>
                        </body>
                        </html>
                    ";

            if (sendEmail($email, $subject, $message)) {
                $_SESSION['success'] = "OTP has been sent to your email!";
                $showOtpForm = true;
            } else {
                $_SESSION['error'] = "Failed to send OTP. Please try again!";
            }
        }
    }
}
?>

<div class="bg-gradient-to-r from-indigo-50 to-indigo-200 min-h-screen flex items-center justify-center">
    <div class="bg-white p-10 rounded-xl shadow-2xl w-full max-w-lg text-center transform transition-all hover:scale-105">
        <h2 class="text-2xl font-semibold text-indigo-700 mb-6">🔑 Send OTP</h2>

        <!-- Form gửi OTP -->
        <?php if (!$showOtpForm) : ?>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="email" name="email" placeholder="Enter your email"
                    class="border border-indigo-300 rounded-lg w-full py-3 px-5 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-200" required>
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition duration-300">Send OTP</button>
            </form>
        <?php endif; ?>

        <!-- Form nhập OTP -->
        <?php if ($showOtpForm) : ?>
            <form id="otpForm" method="POST" action="/reset-password" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="number" name="otp" placeholder="Enter OTP"
                    class="border border-indigo-300 rounded-lg w-full py-3 px-5 text-center text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-200" required>
                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition duration-300">Verify OTP</button>
            </form>
            <p id="countdown" class="text-center text-red-600 mt-5 font-medium">
                OTP expires in <span id="timer" class="font-bold">30</span> seconds.
            </p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div id="otpExpiredModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-120 text-center">
        <h3 class="text-lg font-semibold text-red-600 mb-4">OTP Expired</h3>
        <p class="text-gray-700 mb-6">Your OTP has expired! A new OTP is being sent...</p>
        <button id="closeModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300">OK</button>
    </div>
</div>

<!-- JavaScript để xử lý modal và đếm ngược -->
<script>
    let timeLeft = 30; // Thời gian đếm ngược (giây)
    const timerDisplay = document.getElementById('timer');

    const interval = setInterval(() => {
        if (timeLeft > 0) {
            timeLeft--;
            timerDisplay.textContent = timeLeft;
        } else {
            clearInterval(interval);
            document.getElementById('otpExpiredModal').classList.remove('hidden');
        }
    }, 1000);

    // Đóng modal và reload trang khi nhấn nút OK
    document.getElementById('closeModal').addEventListener('click', () => {
        document.getElementById('otpExpiredModal').classList.add('hidden');
        location.reload(); // Reload trang để gửi lại OTP
    });
</script>
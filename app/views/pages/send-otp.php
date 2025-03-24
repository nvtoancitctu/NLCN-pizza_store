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
        $error = "Invalid email!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "Email not found!";
        } else {
            $otp = rand(100000, 999999);
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['otp_expiry'] = time() + 10;
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
                $success = "OTP has been sent to your email!";
                $showOtpForm = true;
            } else {
                $error = "Failed to send OTP. Please try again!";
            }
        }
    }
}
?>

<div class="bg-gradient-to-r from-blue-50 to-blue-100">
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white p-8 rounded-lg shadow-lg w-full max-w-md text-center">
        <h2 class="text-xl font-bold text-gray-700 mb-4">🔑 Send OTP</h2>

        <?php if (isset($error)) : ?>
            <p class="text-red-500 mb-3"><?= $error ?></p>
        <?php endif; ?>

        <?php if (isset($success)) : ?>
            <p class="text-green-500 mb-3"><?= $success ?></p>
        <?php endif; ?>

        <!-- Form gửi OTP -->
        <?php if (!$showOtpForm) : ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="email" name="email" placeholder="Enter your email"
                    class="border border-gray-300 rounded-lg w-full py-2 px-4 mb-3" required>
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold p-2 rounded-lg">Send OTP</button>
            </form>
        <?php endif; ?>

        <!-- Form nhập OTP -->
        <?php if ($showOtpForm) : ?>
            <form id="otpForm" method="POST" action="/reset-password">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="number" name="otp" placeholder="Enter OTP"
                    class="border border-gray-300 rounded-lg w-full py-2 px-4 text-center mb-3" required>
                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold p-2 rounded-lg">Verify OTP</button>
            </form>
            <p id="countdown" class="text-center text-red-500 mt-4">
                OTP expires in <span id="timer">60</span> seconds.
            </p>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let countdown = 10;
        let timerSpan = document.getElementById("timer");

        if (timerSpan) {
            let interval = setInterval(() => {
                if (countdown > 0) {
                    countdown--;
                    timerSpan.textContent = countdown;
                } else {
                    clearInterval(interval);
                    alert("OTP has expired! A new OTP is being sent...");
                    location.reload(); // Reload trang để gửi lại OTP
                }
            }, 1000);
        }
    });
</script>
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php';

function sendEmail($to, $subject, $message)
{
    $mail = new PHPMailer(true);

    try {
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'loverhut.pizzastore@gmail.com'; // Email của bạn
        $mail->Password   = 'bfzc rwzh magz xtdg'; // Mật khẩu ứng dụng
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8'; // Đảm bảo hiển thị tiếng Việt đúng

        // Người gửi
        $mail->setFrom('loverhut.pizzastore@gmail.com', 'Lover\'s Hut Pizza Store');
        $mail->addAddress($to); // Người nhận

        // Nội dung email (HTML)
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Gửi email thất bại. Lỗi: {$mail->ErrorInfo}";
    }
}

//------------------------------------------------------------------------//

// Kiểm tra quyền admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = "You must login by admin account to access.";
    header("Location: /login");
    exit();
}

// Kiểm tra CSRF Token hợp lệ
if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    exit("<h1 class='text-center mt-5'>Forbidden: Invalid CSRF token</h1>");
}

// Reset CSRF token ngay trước khi điều hướng hoặc xử lý
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Kiểm tra các tham số bắt buộc
if (!isset($_GET['user_email']) || !isset($_GET['type'])) {
    echo "Invalid request.";
    exit();
}

$userEmail = urldecode($_GET['user_email']);
$type = $_GET['type'];
$days = isset($_GET['days']) ? intval($_GET['days']) : 0;

if (!$userEmail) {
    echo "User email not found.";
    exit();
}

$subject = "";
$message = "";

// Tạo nội dung email dựa theo loại thông báo
switch ($type) {
    case "block":
        $subject = "Your account has been blocked";
        $start_date = date("Y-m-d H:i:s"); // Ngày bắt đầu bị block
        $end_date = date("Y-m-d H:i:s", strtotime("+{$days} days")); // Ngày hết hạn block

        $message = "
        <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2 style='color: red;'>🚨 Account Blocked Notification</h2>
            <p>Your account has been blocked for <strong>{$days} day(s)</strong> due to policy violations.</p>
            <p><strong>🔒 Blocked from:</strong> {$start_date}</p>
            <p><strong>🔓 Unblock date:</strong> {$end_date}</p>
            <p>If you believe this is a mistake, please contact our support team.</p>
            <hr>
            <p style='color: red;'><strong>⚠️ Important:</strong> You will not be able to log in or access our services during the block period.</p>
            <p>For further assistance, please reach out to us at <a href='loverhut.pizzastore@gmail.com'>email</a>.</p>
        </div>";
        break;

    case "unblock":
        $subject = "Your account has been unblocked";
        $message = "
        <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2 style='color: green;'>✅ Account Unblocked Notification</h2>
            <p>Your account has been unblocked successfully and you can now access our services.</p>
            <p>Thank you for your patience.</p>
            <hr>
            <p><strong>🎉 Welcome back!</strong> You can now log in using your existing credentials.</p>
            <p>If you experience any login issues, reset your password or contact <a href='loverhut.pizzastore@gmail.com'>email</a>.</p>
        </div>";
        break;

    case "delete":
        $subject = "Your account has been deleted";
        $message = "
        <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2 style='color: red;'>❌ Account Deletion Notification</h2>
            <p>Your account has been permanently deleted from our system.</p>
            <p>If you have any questions, please contact our support team.</p>
            <hr>
            <p><strong>🔎 Need help?</strong> If this action was not intended, contact us immediately at <a href='loverhut.pizzastore@gmail.com'>email</a>.</p>
            <p>We appreciate your time with us and hope to see you again.</p>
        </div>";
        break;

    default:
        echo "Invalid email type.";
        exit();
}

// Gửi email sử dụng hàm sendEmail()
sendEmail($userEmail, $subject, $message);
header("Location: /admin");
exit();

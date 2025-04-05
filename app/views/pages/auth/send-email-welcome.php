<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php'; // Nạp thư viện PHPMailer

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

if (!isset($_GET['user_email']) || !isset($_GET['csrf_token'])) {
    echo "Invalid request.";
    echo $_GET['user_email'];
    exit();
}

$userEmail = urldecode($_GET['user_email']);
$csrfToken = $_GET['csrf_token'];

// Kiểm tra CSRF token hợp lệ
if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $csrfToken) {
    echo "Invalid CSRF token.";
    exit();
}

// Tiêu đề & nội dung email
$subject = "🎉 Welcome to Lover's Hut Pizza Store!";
$message = "
<div style='font-family: Arial, sans-serif; line-height: 1.6;'>
    <h2 style='color: green;'>Welcome, $userEmail! 🎊</h2>
    <p>Thank you for joining <strong>Lover's Hut Pizza Store</strong>. We're excited to have you on board! 🍕</p>
    <p>Start exploring our delicious pizzas and enjoy exclusive deals made just for you.</p>
    <p><strong>🔥 Special Offer:</strong> Get 10% off on your first order! Use code <strong>WELCOME10</strong> at checkout.</p>
    <p>You can see your full information at Profile page.</p>
    <hr>
    <p style='font-size: 12px; color: #555;'>If you have any questions, feel free to contact us <a href='loverhut.pizzastore@gmail.com'>here</a>.</p>
</div>";

// Lấy user_id từ user_name
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$userEmail]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit();
}

// Thêm voucher vào tài khoản người dùng (nếu chưa có)
$insertClaim = $conn->prepare("INSERT INTO user_voucher (user_id, voucher_id) VALUES (?, ?)");
$insertClaim->execute([$user['id'], 4]);

// Gửi email
sendEmail($userEmail, $subject, $message);

// Reset CSRF token ngay trước khi điều hướng hoặc xử lý
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

header("Location: /login");
exit();

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



// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) && $_SESSION['user_role'] !== 'admin') {
    header("Location: /login");
    exit();
}

if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    header("Location: /admin");
    exit();
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Kiểm tra và lọc dữ liệu đầu vào
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$order_status = isset($_GET['status']) ? htmlspecialchars($_GET['status'], ENT_QUOTES, 'UTF-8') : '';
var_dump($order_status); // Xem giá trị thực tế

// Lấy thông tin đơn hàng và chi tiết đơn 
$orderDetails = $orderController->getOrderDetails($order_id, $user_id);

if (!$orderDetails) {
    header("Location: /admin");
    exit();
}

// Lấy email khách hàng
$to = filter_var($orderDetails['email'], FILTER_VALIDATE_EMAIL);
if (!$to) {
    header("Location: /admin");
    exit();
}

// Tạo nội dung email dựa trên trạng thái đơn hàng
$message = "
<div style='font-family: Arial, sans-serif; line-height: 1.6;'>
    <h2 style='color: blue;'>UPDATE ORDER STATUS</h2>
        <p><strong>📌 Order ID      :</strong> #$order_id</p>
        <p><strong>📦 Order Total   :</strong> $" . $orderDetails['final_total'] . "</p>
        <p><strong>🕒 Order Datetime:</strong> " . $orderDetails['created_at'] . "</p>
        <p><strong>🚚 Shipping time :</strong> " . $orderDetails['status_at'] . "</p>
    <hr>";

if ($order_status === 'completed') {
    $subject = "🎉 Your order #$order_id is COMPLETED!";
    $message .= "
        <p>🎉 Your order has been successfully <strong style='color: green;'>Completed</strong>! Thank you for choosing Lover's Hut Pizza Store.</p>
        <p>Your delicious pizza is on the way! 🚚</p>
        <p>If you have any questions, feel free to contact us.</p>
    ";
} elseif ($order_status === 'cancelled') {
    $subject = "⚠️ Order #$order_id is CANCELLED";
    $message .= "
        <p>❌ Your order has been <strong style='color: red;'>Cancelled</strong>. We apologize for any inconvenience.</p>
        <p>If you did not request this cancellation, please contact our support team immediately.</p>
    ";
}

$message .= "
    <hr>
    <p style='font-size: 12px; color: #555;'>Thank you for shopping with us!</p>
    <p style='font-size: 12px; color: #555;'>
        Our Location: 
        <a href='https://www.google.com/maps?q=10.2250824,105.5637198' target='_blank'>
            View on Google Maps
        </a>
    </p>
</div>";

// Gửi email
sendEmail($to, $subject, $message);
header("Location: /admin");
exit();

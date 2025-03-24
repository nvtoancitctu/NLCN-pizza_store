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

// Đảm bảo có order_id
if (!isset($_GET['order_id'])) {
    header("Location: /login");
    exit();
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Lấy thông tin đơn hàng từ OrderController
$orderDetails = $orderController->getOrderDetails($order_id, $user_id);

// Nếu không tìm thấy đơn hàng
if (!$orderDetails) {
    $_SESSION['success'] = "Order not found. Please try again or login to view your order.";
    header("Location: /login");
    exit();
}

// Create email content in English
$message = "
    <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
        <h2 style='color: green;'>Thank you for ordering at Lover's Hut Pizza Store!</h2>
        <p><strong>📌 Order details:</strong></p>
        <table style='width: 100%; border-collapse: collapse;'>
            <thead>
                <tr style='background: #f8f8f8;'>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Product</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: center;'>Quantity</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: center;'>Size</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: right;'>Price ($)</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: right;'>Total ($)</th>
                </tr>
            </thead>
            <tbody>";

foreach ($orderDetails['items'] as $item) {
    $message .= "
                <tr>
                    <td style='border: 1px solid #ddd; padding: 8px;'>{$item['name']}</td>
                    <td style='border: 1px solid #ddd; padding: 8px; text-align: center;'>{$item['quantity']}</td>
                    <td style='border: 1px solid #ddd; padding: 8px; text-align: center;'>{$item['size']}</td>
                    <td style='border: 1px solid #ddd; padding: 8px; text-align: right;'>{$item['price']}</td>
                    <td style='border: 1px solid #ddd; padding: 8px; text-align: right;'>{$item['total_price']}</td>
                </tr>";
}

$message .= "
            </tbody>
        </table>
        <hr>
        <p><strong>🚚 Shipping Fee ($):</strong> " . ($orderDetails['shipping_fee'] > 0 ? "{$orderDetails['shipping_fee']}" : "Free") . "</p>
        <p><strong>💰 Total Amount ($):</strong> {$orderDetails['final_total']}</p>
        <p><strong>🎟️ Voucher:</strong> " . (!empty($orderDetails['code']) ? "{$orderDetails['code']}" : "None") . "</p>
        <p><strong>📍 Shipping Address:</strong> {$orderDetails['address']} ({$orderDetails['shipping_link']})</p>
        <p><strong>💳 Payment Method:</strong> " . ($orderDetails['payment_method'] === 'bank_transfer' ? 'Banking' : 'COD') . "</p>
        <hr>
        <p style='color: green;'><strong>🚀 Your order is being processed and will be delivered soon!</strong></p>
        <p style='font-size: 12px; color: #555;'>If you have any questions, feel free to contact us via this email.</p>
    </div>
";

// Gửi email
$to = $orderDetails['email'];
$mailSent = sendEmail($to, "Order Confirmation #$order_id", $message);

if ($mailSent) {
    // Chuyển hướng đến order-success
    header("Location: /order-success/order_id=$order_id");
    exit();
} else {
    echo "Gửi email thất bại!";
}

<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../../vendor/tecnickcom/tcpdf/tcpdf.php';
require_once '../vendor/autoload.php'; // Nạp thư viện PHPMailer và TCPDF

function generateInvoicePDF($orderDetails, $order_id)
{
    $pdf = new TCPDF();
    $pdf->SetAutoPageBreak(true, 5);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor("Lover's Hut Pizza Store");
    $pdf->SetTitle("Invoice #$order_id");
    $pdf->SetMargins(10, 10, 10);
    $pdf->AddPage();

    // Đường dẫn đến logo
    $logoPath = 'D:\NLCN_Project_PizzaStore\public\images\logo.png';

    // Thêm logo căn giữa
    $pdf->Image($logoPath, 90, 10, 30, 30, 'PNG');

    // Tiêu đề cửa hàng
    $pdf->Ln(30);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, "Lover's Hut Pizza Store", 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 10, '123 Pizza Street, City, Country', 0, 1, 'C');
    $pdf->Ln(2);

    // Hiển thị thông tin đơn hàng
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, "Invoice #$order_id", 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 10, 'Order Time: ' . $orderDetails['created_at'], 0, 1, 'C');
    $pdf->Ln(5);

    // Bảng sản phẩm
    $pdf->SetFont('helvetica', '', 10);
    $html = '
<table border="1" cellspacing="3" cellpadding="5" style="width: 100%;">
    <tr style="font-weight: bold; background-color: #f2f2f2; text-align: center;">
        <th style="width: 40%;">Product</th>
        <th style="width: 10%;">Qty</th>
        <th style="width: 10%;">Size</th>
        <th style="width: 20%;">Price ($)</th>
        <th style="width: 20%;">Total ($)</th>
    </tr>';

    foreach ($orderDetails['items'] as $item) {
        $html .= "<tr>
        <td style='text-align: left;'>{$item['name']}</td>
        <td style='text-align: center;'>{$item['quantity']}</td>
        <td style='text-align: center;'>{$item['size']}</td>
        <td style='text-align: right;'>$" . number_format($item['price'], 2) . "</td>
        <td style='text-align: right;'>$" . number_format($item['total_price'], 2) . "</td>
    </tr>";
    }

    $html .= '</table>';
    $pdf->writeHTML($html, true, false, true, false, '');

    // Thêm tổng tiền
    $pdf->Cell(140, 10, "Shipping Fee:", 0, 0, 'R');
    $pdf->Cell(40, 10, ($orderDetails['shipping_fee'] > 0 ? "$" . number_format($orderDetails['shipping_fee'], 2) : "Free"), 0, 1, 'R');
    $pdf->Cell(140, 10, "Total Amount:", 0, 0, 'R');
    $pdf->Cell(40, 10, "$" . number_format($orderDetails['final_total'], 2), 0, 1, 'R');
    $pdf->Cell(140, 10, "Payment Method:", 0, 0, 'R');
    $pdf->Cell(40, 10, ($orderDetails['payment_method'] === 'bank_transfer' ? 'Banking' : 'COD'), 0, 1, 'R');

    // Lời cảm ơn
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->Cell(0, 10, "Thank you for your order!", 0, 1, 'C');

    // Lưu file PDF
    $pdfFilePath = "D:\NLCN_Project_PizzaStore\public\images\invoices\invoice_#$order_id.pdf";
    $pdf->Output($pdfFilePath, 'F'); // 'F' để lưu file thay vì hiển thị
    return $pdfFilePath;
}

function sendEmailWithInvoice($to, $subject, $message, $pdfFilePath)
{
    $mail = new PHPMailer(true);

    try {
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'loverhut.pizzastore@gmail.com'; // Email
        $mail->Password   = 'bfzc rwzh magz xtdg'; // Mật khẩu ứng dụng
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Người gửi
        $mail->setFrom('loverhut.pizzastore@gmail.com', 'Lover\'s Hut Pizza Store');
        $mail->addAddress($to);

        // Đính kèm hóa đơn PDF
        $mail->addAttachment($pdfFilePath);

        // Nội dung email
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

// Kiểm tra CSRF Token hợp lệ
if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    exit("<h1 class='text-center mt-5'>Forbidden: Invalid CSRF token</h1>");
}

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

// Tạo hóa đơn PDF
$pdfFilePath = generateInvoicePDF($orderDetails, $order_id);

// Nội dung email
$message = "
    <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
        <h2 style='color: green;'>Thank you for ordering at Lover's Hut Pizza Store!</h2>
        <p><strong>📌 Order details:</strong> (See attached invoice)</p>
        <p><strong>🚚 Shipping Fee ($):</strong> " . ($orderDetails['shipping_fee'] > 0 ? "{$orderDetails['shipping_fee']}" : "Free") . "</p>
        <p><strong>💰 Total Amount ($):</strong> {$orderDetails['final_total']}</p>
        <p><strong>📍 Shipping Address:</strong> {$orderDetails['address']} <a href='{$orderDetails['shipping_link']}'>view-map</a></p>
        <p><strong>💳 Payment Method:</strong> " . ($orderDetails['payment_method'] === 'bank_transfer' ? 'Banking' : 'COD') . "</p>
        <hr>
        <p style='color: green;'><strong>🚀 Your order is being processed and will be delivered soon!</strong></p>
        <p style='font-size: 12px; color: #555;'>If you have any questions, feel free to contact us via this email.</p>
    </div>
";

// Gửi email kèm hóa đơn PDF
$to = $orderDetails['email'];
$mailSent = sendEmailWithInvoice($to, "Order Confirmation #$order_id", $message, $pdfFilePath);

// Xóa file PDF sau khi gửi để tránh đầy bộ nhớ
if ($mailSent) {
    header("Location: /order-success/order_id=$order_id");
    exit();
} else {
    echo "Gửi email thất bại!";
}

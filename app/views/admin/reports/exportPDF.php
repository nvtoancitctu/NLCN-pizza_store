<?php
ob_start(); // Bắt đầu buffer để ngăn output trước khi tạo PDF

require_once __DIR__ . '/../../../vendor/tecnickcom/tcpdf/tcpdf.php';
require_once '../vendor/autoload.php'; // Nạp thư viện PHPMailer và TCPDF

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Unauthorized access.");
}

$timePeriod = $_GET['time_period'] ?? 'daily';
$statisticsController = new OrderController($conn);
$salesData = $statisticsController->getSalesStatistics($timePeriod);

// Khởi tạo PDF
$pdf = new TCPDF();
$pdf->SetCreator('Pizza Store');
$pdf->SetAuthor('Admin');
$pdf->SetTitle('Sales Report');
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

// Thêm logo
$logoPath = __DIR__ . "/../../../public/images/logo.png"; // Đường dẫn logo
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 15, 10, 30, 0, 'PNG');
}

// Thiết lập múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Hiển thị tên cửa hàng và ngày giờ xuất
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'LOVER\'S HUT PIZZA STORE', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'C');
$pdf->Ln(5);

// Tiêu đề
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Sales Report - ' . ucfirst($timePeriod), 0, 1, 'C');
$pdf->Ln(5);

// Bảng dữ liệu
$pdf->SetFont('helvetica', '', 12);
$html = '<table border="1" cellspacing="3" cellpadding="5">
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <th>Category</th>
                <th>Total Quantity</th>
                <th>Total Sales ($)</th>
            </tr>';

foreach ($salesData as $sales) {
    $category = match ($timePeriod) {
        'payment_method' => $sales['method'],
        'product' => $sales['product_name'],
        'status' => $sales['status'],
        default => $sales['date'],
    };

    $html .= "<tr>
                <td>{$category}</td>
                <td>{$sales['total_quantity']}</td>
                <td>" . number_format($sales['revenue'], 2) . "</td>
              </tr>";
}

$html .= '</table>';
$pdf->writeHTML($html, true, false, true, false, '');

// Tính tổng số lượng và tổng doanh thu
$totalQuantity = array_sum(array_column($salesData, 'total_quantity'));
$totalRevenue = array_sum(array_column($salesData, 'revenue'));

// Lấy tháng và năm hiện tại
$currentMonth = date('m');
$currentYear = date('Y');

// Lấy tháng và năm của tháng trước
$lastMonth = $currentMonth - 1;
if ($lastMonth == 0) {
    $lastMonth = 12;
    $currentYear -= 1;
}

// Câu lệnh SQL để lấy tổng số lượng và tổng doanh thu tháng trước
$sql = "
    SELECT SUM(oi.quantity) AS total_quantity, 
        (SELECT SUM(o.total) FROM orders o WHERE MONTH(o.created_at) = 2 AND YEAR(o.created_at) = 2025) AS total_revenue
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    WHERE MONTH(o.created_at) = :lastMonth AND YEAR(o.created_at) = :lastYear
";

// Chuẩn bị và thực thi câu lệnh SQL
$stmt = $conn->prepare($sql);
$stmt->bindParam(':lastMonth', $lastMonth, PDO::PARAM_INT);
$stmt->bindParam(':lastYear', $currentYear, PDO::PARAM_INT);
$stmt->execute();

// Lấy kết quả
$lastMonthData = $stmt->fetch(PDO::FETCH_ASSOC);
$lastMonthQuantity = $lastMonthData['total_quantity'] ?? 0;
$lastMonthRevenue = $lastMonthData['total_revenue'] ?? 0;

// Tính tỷ lệ tăng hoặc giảm
$quantityGrowth = (($totalQuantity - $lastMonthQuantity * 2) / $lastMonthQuantity) * 100;
$revenueGrowth = (($totalRevenue - $lastMonthRevenue * 2) / $lastMonthRevenue) * 100;

// Thêm phần tổng kết vào PDF
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Summary', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12);

// Thêm phần so sánh trong bảng
$pdf->Ln(5);
$pdf->SetFont('helvetica', '', 12);
$html = '<table border="1" cellspacing="3" cellpadding="5">
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <th>Category</th>
                <th>Current Period</th>
                <th>Last Period</th>
                <th>Growth/Decline</th>
            </tr>
            <tr>
                <td>Total Quantity Sold</td>
                <td>' . $totalQuantity . '</td>
                <td>' . $lastMonthQuantity . '</td>
                <td>' . number_format($quantityGrowth, 2) . '%</td>
            </tr>
            <tr>
                <td>Total Revenue ($)</td>
                <td>' . number_format($totalRevenue, 2) . '</td>
                <td>' . number_format($lastMonthRevenue, 2) . '</td>
                <td>' . number_format($revenueGrowth, 2) . '%</td>
            </tr>
          </table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Đưa ra nhận xét về doanh thu
$pdf->SetFont('helvetica', 'B', 12);
if ($revenueGrowth > 10) {
    $comment = "Great performance! The sales are significantly better this month. Keep up the good work!";
} elseif ($revenueGrowth > 0) {
    $comment = "Good performance! There's a positive growth, but there's still room for more improvements.";
} elseif ($revenueGrowth < 0) {
    $comment = "Sales performance is down. Consider reviewing marketing strategies or running promotions to boost sales.";
} else {
    $comment = "Sales are stable with no noticeable growth or decline.";
}
$pdf->MultiCell(0, 10, "COMMENT: $comment", 0, 'L');

// Định dạng tên file PDF theo kiểu Sales_Report_{time_period}.pdf
$pdfFileName = "sales_report_{$timePeriod}.pdf";
$pdfPath = "D:/NLCN_Project_PizzaStore/public/images/sales-report/{$pdfFileName}";

// Xuất file PDF
$pdf->Output($pdfPath, 'F');

// Xóa buffer trước khi gửi header
ob_end_clean();

$_SESSION['success'] = "PDF report has been successfully exported at D:/NLCN_Project_PizzaStore/...!";
header("Location: /admin/statistics");
exit();

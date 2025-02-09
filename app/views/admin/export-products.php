<?php

// Kiểm tra quyền admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit("Forbidden: You do not have permission to access this resource.");
}

// ** Xóa mọi buffer đã xuất trước đó **
ob_clean();
ob_start();

// Thiết lập header HTTP để xuất file CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="database_export.csv"');

// Mở output để ghi dữ liệu vào file CSV
$output = fopen('php://output', 'w');

// Lấy danh sách tất cả các bảng trong cơ sở dữ liệu
$query = $conn->query("SHOW TABLES");
$tables = $query->fetchAll(PDO::FETCH_COLUMN);

// Xuất dữ liệu của từng bảng
foreach ($tables as $table) {
    // Ghi tiêu đề bảng
    fputcsv($output, ["Table: $table"]);

    // Lấy dữ liệu từ bảng hiện tại
    $query = $conn->query("SELECT * FROM `$table`");
    $rows = $query->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($rows)) {
        // Ghi tiêu đề cột
        fputcsv($output, array_keys($rows[0]));

        // Ghi dữ liệu từng dòng
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
    } else {
        // Nếu bảng trống, ghi chú thích
        fputcsv($output, ['No data available']);
    }

    // Dòng trống giữa các bảng
    fputcsv($output, []);
}

// Đóng output
fclose($output);

// Dừng và xóa mọi dữ liệu còn lại trong buffer
ob_end_flush();
exit();

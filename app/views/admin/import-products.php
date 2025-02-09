<?php

// Kiểm tra quyền admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit("Forbidden: You do not have permission to access this resource.");
}

$productController = new ProductController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['product_file'])) {
    $file = $_FILES['product_file'];

    // Kiểm tra lỗi file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo "File upload error. Please try again.";
        exit();
    }

    // Kiểm tra định dạng file CSV
    $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
    if ($fileType !== 'csv') {
        echo "Invalid file format. Please upload a CSV file.";
        exit();
    }

    // Mở file CSV
    $fileHandle = fopen($file['tmp_name'], 'r');
    if (!$fileHandle) {
        echo "Unable to read the uploaded file.";
        exit();
    }

    // Đọc tiêu đề cột
    $header = fgetcsv($fileHandle);

    // Đọc dữ liệu từng dòng và thêm/cập nhật vào cơ sở dữ liệu
    while (($row = fgetcsv($fileHandle)) !== false) {
        // Gán dữ liệu từ file CSV vào các cột
        $data = array_combine($header, $row);

        // Kiểm tra và thêm/cập nhật sản phẩm
        $productController->importOrUpdateProduct($data);
    }

    fclose($fileHandle);
    $_SESSION['success'] = "Products have been successfully imported file.";
    header("Location: /admin/list");
    exit();
}

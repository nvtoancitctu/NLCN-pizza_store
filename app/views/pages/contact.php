<?php

// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Lấy thông tin người dùng từ session, nếu có
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';

// Khởi tạo UserController với kết nối CSDL
$userController = new UserController($conn);
$message = '';

// Kiểm tra nếu form được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    // Làm sạch và kiểm tra dữ liệu từ form
    $name = htmlspecialchars(trim($_POST['name']));             // Xử lý dữ liệu nhập vào để tránh XSS
    $email = htmlspecialchars(trim($_POST['email']));           // Làm sạch email nhập vào
    $user_message = htmlspecialchars(trim($_POST['message']));  // Làm sạch thông điệp

    // Kiểm tra nếu tất cả các trường đều có dữ liệu
    if (!empty($name) && !empty($email) && !empty($user_message)) {
        // Gọi hàm xử lý thêm contact và truyền tham số vào
        $result = $userController->handleAddContact($user_id, $name, $email, $user_message);

        if ($result === true) {
            echo "<script>alert('Your message has been submitted!');</script>";             // Hiển thị thông báo thành công
        } else {
            echo "<script>alert('Failed to save your message. Error: $result');</script>";  // Hiển thị thông báo lỗi
        }
    } else {
        $message = "All fields are required!";                                              // Thông báo nếu có trường trống
    }
}
?>

<!-- Form liên hệ được căn giữa trong container, với thiết kế responsive và hộp thoại -->
<div class="container mx-auto p-6">
    <h1 class="text-4xl font-extrabold mb-6 text-center text-blue-700 drop-shadow-lg">Contact Us</h1>
    <form action="/contact" method="POST" class="bg-white p-10 rounded-xl shadow-lg max-w-lg mx-auto alert alert-info">
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <!-- Trường nhập tên, mặc định là tên người dùng từ session -->
        <div class="mb-6">
            <label for="name" class="block text-blue-700 text-sm font-bold mb-2">Your Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user_name) ?>"
                class="w-full p-3 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400" required> <!-- Mặc định điền tên người dùng nếu đã đăng nhập -->
        </div>
        <!-- Trường nhập email, mặc định là email người dùng từ session -->
        <div class="mb-6">
            <label for="email" class="block text-blue-700 text-sm font-bold mb-2">Your Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user_email) ?>"
                class="w-full p-3 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400" required> <!-- Mặc định điền email người dùng nếu đã đăng nhập -->
        </div>
        <!-- Trường nhập tin nhắn -->
        <div class="mb-6">
            <label for="message" class="block text-blue-700 text-sm font-bold mb-2">Message:</label>
            <textarea id="message" name="message" rows="2"
                class="w-full p-3 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-400" required></textarea>
        </div>
        <!-- Nút gửi tin nhắn -->
        <div class="text-center">
            <button type="submit" class="font-bold px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-transform transform hover:scale-105">
                Send Message
            </button>
        </div>
    </form>
</div>
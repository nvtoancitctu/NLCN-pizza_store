<?php

// Tạo token CSRF nếu chưa tồn tại
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';    // Khởi tạo biến để lưu thông báo lỗi

// Kiểm tra nếu form đã được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kiểm tra token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo "<h1 class='text-center mt-5'>Forbidden: Invalid CSRF token</h1>";
        exit();
    }

    // Lấy dữ liệu từ form và loại bỏ khoảng trắng
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Kiểm tra mật khẩu xác nhận
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Thực hiện đăng ký người dùng
        $result = $userController->register($name, $email, $password);

        // Kiểm tra kết quả đăng ký
        if (strpos($result, 'successful') !== false) {
            $_SESSION['success'] = "Registration successful! You can now log in.";
            header("Location: /login");
            exit();
        } else {
            $error = $result;
        }
    }
}
?>

<!-- Hiển thị lỗi nếu có -->
<?php if (!empty($error)): ?>
    <script>
        alert("<?= addslashes($error) ?>");
    </script>
<?php endif; ?>

<!-- Giao diện Form đăng ký -->
<h1 class="text-center text-4xl font-bold mt-6 text-gray-900">Register</h1>

<div class="container mx-auto max-w-md p-8 bg-white shadow-lg rounded-xl mt-8 mb-8 alert alert-info">
    <form method="POST" action="/register">
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <div class="mb-6">
            <label for="name" class="block text-gray-700 font-bold mb-2">Name:</label>
            <input type="text" name="name" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
        </div>

        <div class="mb-6">
            <label for="email" class="block text-gray-700 font-bold mb-2">Email:</label>
            <input type="email" name="email" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
        </div>

        <div class="mb-6">
            <label for="password" class="block text-gray-700 font-bold mb-2">Password:</label>
            <div class="relative">
                <input type="password" id="password" name="password" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-3 flex items-center text-gray-500">
                    👁️
                </button>
            </div>
        </div>

        <div class="mb-6">
            <label for="confirm_password" class="block text-gray-700 font-bold mb-2">Confirm Password:</label>
            <div class="relative">
                <input type="password" id="confirm_password" name="confirm_password" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                <button type="button" onclick="togglePassword('confirm_password')" class="absolute inset-y-0 right-3 flex items-center text-gray-500">
                    👁️
                </button>
            </div>
        </div>

        <div class="text-center">
            <button type="submit" class="w-2/5 text-center p-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-md transition duration-200">Register</button>
        </div>
    </form>

    <p class="text-center mt-6 text-gray-600">Already have an account? <a href="/login" class="text-blue-600 hover:underline">Login here</a></p>
</div>

<script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        input.type = input.type === "password" ? "text" : "password";
    }
</script>
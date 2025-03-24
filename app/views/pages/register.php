<?php

// Tạo token CSRF nếu chưa tồn tại
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';    // Khởi tạo biến để lưu thông báo lỗi

// Kiểm tra nếu form đã được gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
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
            unset($_SESSION['csrf_token']); // Xóa token sau khi đăng ký thành công
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

<!-- Giao diện Form Đăng Ký -->
<div class="flex min-h-screen items-center justify-center bg-gradient-to-r from-blue-50 to-blue-100">
    <div class="w-full max-w-md bg-white shadow-xl rounded-xl p-8 mt-16 mb-16">

        <h1 class="text-center text-3xl font-extrabold text-blue-700 mb-6">📝 Register</h1>

        <form method="POST" action="/register" class="space-y-5">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']) ?>">

            <!-- Name Input -->
            <div>
                <label for="name" class="block text-gray-700 font-semibold mb-2">Name:</label>
                <div class="relative">
                    <input type="text" name="name" class="border border-gray-300 rounded-lg w-full py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    <span class="absolute right-4 top-3 text-gray-400">🧑</span>
                </div>
            </div>

            <!-- Email Input -->
            <div>
                <label for="email" class="block text-gray-700 font-semibold mb-2">Email:</label>
                <div class="relative">
                    <input type="email" name="email" class="border border-gray-300 rounded-lg w-full py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    <span class="absolute right-4 top-3 text-gray-400">📧</span>
                </div>
            </div>

            <!-- Password Input -->
            <div>
                <label for="password" class="block text-gray-700 font-semibold mb-2">Password:</label>
                <div class="relative">
                    <input type="password" id="password" name="password" class="border border-gray-300 rounded-lg w-full py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-4 flex items-center text-gray-500 hover:text-gray-700 transition-all">
                        <span id="eyeIcon1">👁️</span>
                    </button>
                </div>
            </div>

            <!-- Confirm Password Input -->
            <div>
                <label for="confirm_password" class="block text-gray-700 font-semibold mb-2">Confirm Password:</label>
                <div class="relative">
                    <input type="password" id="confirm_password" name="confirm_password" class="border border-gray-300 rounded-lg w-full py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    <button type="button" onclick="togglePassword('confirm_password')" class="absolute inset-y-0 right-4 flex items-center text-gray-500 hover:text-gray-700 transition-all">
                        <span id="eyeIcon2">👁️</span>
                    </button>
                </div>
            </div>

            <!-- Register Button -->
            <div class="text-center">
                <button type="submit" class="w-full p-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-md">
                    Register
                </button>
            </div>
        </form>

        <!-- Login Link -->
        <p class="text-center mt-6 text-gray-600">
            Already have an account?
            <a href="/login" class="text-blue-600 font-semibold hover:underline">Login here</a>
        </p>
    </div>
</div>

<script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        const eyeIcon = id === "password" ? document.getElementById("eyeIcon1") : document.getElementById("eyeIcon2");

        if (input.type === "password") {
            input.type = "text";
            eyeIcon.textContent = "🙈";
        } else {
            input.type = "password";
            eyeIcon.textContent = "👁️";
        }
    }
</script>
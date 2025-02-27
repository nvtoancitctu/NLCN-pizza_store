<?php

// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Khởi tạo UserController
$userController = new UserController($conn);

$error = '';

// Kiểm tra và lấy thông báo thành công từ session
$success = '';
if (isset($_SESSION['success'])) {
  $success = $_SESSION['success'];
  unset($_SESSION['success']); // Xóa thông báo khỏi session
}

// Kiểm tra nếu người dùng đã đăng nhập
if (isset($_SESSION['user_id'])) {
  // Người dùng đã đăng nhập, điều hướng về trang chủ
  header("Location: /home");
  exit();
}

// Xử lý khi người dùng gửi biểu mẫu đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // Check CSRF token
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
  }

  $email = $_POST['email'];
  $password = $_POST['password'];

  $result = $userController->login($email, $password);

  if (is_array($result) && isset($result['error'])) {
    // Hiển thị lỗi nếu tài khoản bị khóa
    $error = $result['error'];
  } elseif (is_array($result) && isset($result['success'])) {
    // Đăng nhập thành công
    $_SESSION['success'] = "Login successful! Welcome to Lover's Hub Pizza Store.";
    header("Location: /home");
    exit();
  } else {
    // Sai email hoặc mật khẩu
    $error = "Invalid email or password.";
  }
}
?>

<!-- Hiển thị thông báo lỗi nếu có -->
<?php if (!empty($error)): ?>
  <script>
    alert("<?= addslashes($error) ?>");
  </script>
<?php endif; ?>

<!-- Hiển thị thông báo thành công nếu có -->
<?php if (!empty($success)): ?>
  <script>
    alert("<?= addslashes($success) ?>");
  </script>
<?php endif; ?>

<!-- Giao diện người dùng -->
<h1 class="text-center text-4xl font-bold mt-10 text-gray-900">Login</h1>
<div class="container mx-auto max-w-md p-8 bg-white shadow-lg rounded-xl mt-8 mb-8">
  <form method="POST" action="/login">
    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <div class="mb-6">
      <label for="email" class="block text-gray-700 font-bold mb-2">Email:</label>
      <input type="email" name="email" class="shadow-sm appearance-none border border-gray-300 rounded-md w-full p-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400 transition duration-150" required>
    </div>
    <!--  -->
    <div class="mb-6 relative">
      <label for="password" class="block text-gray-700 font-bold mb-2">Password:</label>
      <div class="relative">
        <input type="password" name="password" id="passwordInput" class="shadow-sm appearance-none border border-gray-300 rounded-md w-full p-3 pr-10 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400 transition duration-150" required>
        <!-- Nút hiển thị / ẩn mật khẩu -->
        <button type="button" id="togglePassword" class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700">
          <span id="eyeIcon">👁️</span>
        </button>
      </div>
    </div>

    <div class="mb-6 flex justify-between text-sm">
      <!-- Liên kết quên mật khẩu -->
      <a href="/forgot-password" class="text-blue-600 hover:underline">Forgot password?</a>
    </div>

    <div class="text-center">
      <button type="submit" class="w-3/5 text-center p-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-md transition duration-200">Login</button>
    </div>
  </form>
  <p class="text-center mt-6 text-gray-600">Don't have an account? <a href="/register" class="text-blue-600 hover:underline">Register here</a></p>
</div>

<script>
  document.getElementById("togglePassword").addEventListener("click", function() {
    var passwordInput = document.getElementById("passwordInput");
    var eyeIcon = document.getElementById("eyeIcon");

    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      eyeIcon.textContent = "🙈"; // Icon mắt đóng
    } else {
      passwordInput.type = "password";
      eyeIcon.textContent = "👁️"; // Icon mắt mở
    }
  });
</script>
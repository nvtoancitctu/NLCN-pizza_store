<?php

// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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

  $email = trim($_POST['email']);
  $password = $_POST['password'];

  $result = $userController->login($email, $password);

  if (is_array($result) && isset($result['error'])) {
    $error = $result['error'];
  } elseif (is_array($result) && isset($result['success'])) {

    // Kiểm tra role của user và điều hướng tương ứng
    if ($_SESSION['user_role'] == 'admin') {
      $_SESSION['success'] = 'Welcome back my admin! Go to admin page.';
      unset($_SESSION['csrf_token']);
      header("Location: /admin");
      exit();
    } else {
      $_SESSION['success'] = "Login successful! Welcome to Lover's Hub Pizza Store.";
      unset($_SESSION['csrf_token']);
      header("Location: /home");
      exit();
    }
  } else {
    // Sai email hoặc mật khẩu
    $error = "Invalid email or password.";
  }
}
?>

<!-- Hiển thị thông báo lỗi hoặc thành công nếu có -->
<?php
$message = '';
$messageType = ''; // Để xác định loại thông báo (error hay success)
if (!empty($_SESSION['error'])) {
  $message = $_SESSION['error'];
  $messageType = 'error';
  unset($_SESSION['error']);
} elseif (!empty($_SESSION['success'])) {
  $message = $_SESSION['success'];
  $messageType = 'success';
  unset($_SESSION['success']);
}
?>

<?php if (!empty($message)): ?>
  <div class="fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 <?= $messageType === 'error' ? 'bg-red-100 border border-red-400 text-red-700' : 'bg-green-100 border border-green-400 text-green-700' ?>">
    <span><?= htmlspecialchars($message) ?></span>
    <button onclick="this.parentElement.remove()" class="ml-2 text-sm font-semibold">✕</button>
  </div>
  <script>
    // Tự động ẩn thông báo sau 5 giây
    setTimeout(() => {
      document.querySelector('.fixed').remove();
    }, 5000);
  </script>
<?php endif; ?>

<!-- Giao diện đăng nhập -->
<div class="flex min-h-screen items-center justify-center bg-gradient-to-r from-blue-50 to-blue-100">
  <div class="w-full max-w-md bg-white shadow-lg rounded-xl p-8">

    <h1 class="text-center text-3xl font-extrabold text-blue-700 mb-6">🔐 Login</h1>

    <form method="POST" action="/login" class="space-y-4">
      <!-- CSRF Token -->
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

      <!-- Email Input -->
      <div>
        <label for="email" class="block text-gray-700 font-semibold mb-2">Email:</label>
        <div class="relative">
          <input type="email" name="email" class="border border-gray-300 rounded-lg w-full py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
          <span class="absolute right-4 top-3 text-gray-400">
            📧
          </span>
        </div>
      </div>

      <!-- Password Input -->
      <div class="mb-2">
        <label for="password" class="block text-gray-700 font-semibold mb-2">Password:</label>
        <div class="relative">
          <input type="password" name="password" id="passwordInput" class="border border-gray-300 rounded-lg w-full py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
          <button type="button" id="togglePassword" class="absolute inset-y-0 right-4 flex items-center text-gray-500 hover:text-gray-700">
            <span id="eyeIcon">👁️</span>
          </button>
        </div>
      </div>

      <!-- Nút Quên mật khẩu -->
      <a href="/send-otp" class="text-blue-600 text-sm font-semibold hover:underline">Forgot password?</a>

      <!-- Login Button -->
      <div class="text-center">
        <button type="submit" class="w-full p-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-md transition-all duration-200">
          Login
        </button>
      </div>
    </form>

    <!-- Register Link -->
    <p class="text-center mt-6 text-gray-600">
      Don't have an account?
      <a href="/register" class="text-blue-600 font-semibold hover:underline">Register here</a>
    </p>
  </div>
</div>

<script>
  document.getElementById("togglePassword").addEventListener("click", function() {
    var passwordInput = document.getElementById("passwordInput");
    var eyeIcon = document.getElementById("eyeIcon");

    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      eyeIcon.textContent = "🙈"; // Mắt đóng
    } else {
      passwordInput.type = "password";
      eyeIcon.textContent = "👁️"; // Mắt mở
    }
  });
</script>
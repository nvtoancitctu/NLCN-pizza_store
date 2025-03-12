<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Sử dụng đường dẫn tuyệt đối để đảm bảo tìm thấy file
require_once '../app/models/Cart.php';

$user_id = $_SESSION['user_id'] ?? null;
$cartItemCount = getCartItemCount($conn, $user_id);

// Hàm lấy tổng số lượng sản phẩm trong giỏ hàng
function getCartItemCount($conn, $user_id)
{
  if (!$user_id) return 0;

  $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = :user_id");
  $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
  $stmt->execute();
  return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
}
?>

<!-- Thanh điều hướng -->
<nav class="bg-gray-800 text-white shadow-lg">
  <div class="container mx-auto px-6 lg:px-20 py-3 flex justify-between items-center">

    <!-- Cột 1: Logo & Tên thương hiệu -->
    <div class="flex items-center space-x-3">
      <img src="/images/logo.png" alt="Lover's Hut" class="w-14 h-14 rounded-full">
      <a href="/" class="text-4xl font-bold italic font-serif text-yellow-400 shadow-2xl">
        Lover's Hut
      </a>

    </div>

    <!-- Cột 2: Menu Điều hướng -->
    <div class="hidden lg:flex space-x-8 items-center">

      <!-- HOME -->
      <a href="/home" class="hover:text-yellow-400 transition flex items-center space-x-2">
        <i class="fas fa-home"></i>
        <span>Home</span>
      </a>

      <!-- PRODUCTS -->
      <a href="/products" class="hover:text-yellow-400 transition flex items-center space-x-2">
        <i class="fas fa-pizza-slice"></i>
        <span>Products</span>
      </a>

      <!-- FEEDBACK -->
      <a href="/feedback" class="hover:text-yellow-400 transition flex items-center space-x-2">
        <i class="fas fa-comment"></i>
        <span>Feedback</span>
      </a>

      <!-- Cart Button Giống Ảnh -->
      <a href="/cart" class="inline-flex items-center space-x-2 border border-gray-300 px-3 py-1 rounded-full hover:text-yellow-400 transition">
        <!-- Số sản phẩm trong giỏ -->
        <span class="font-semibold text-sm">
          <?= $cartItemCount ?>
        </span>

        <!-- Icon giỏ hàng -->
        <i class="fas fa-shopping-cart text-lg"></i>
      </a>

    </div>

    <!-- Cột 3: User -->
    <div class="hidden lg:flex items-center space-x-2">
      <?php if (isset($_SESSION['user_name'])): ?>
        <div class="flex items-center space-x-2">
          <!-- Tên User -->
          <i class="fas fa-user"></i><span class="font-semibold"><?= htmlspecialchars($_SESSION['user_name']) ?></span>

          <!-- Link Profile -->
          <a href="/account" class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm px-3 py-1 rounded-lg">
            Profile
          </a>

          <!-- Nút Logout -->
          <form method="POST" id="logout-form">
            <button type="submit" name="logout" onclick="confirmLogout(event)"
              class="bg-red-500 hover:bg-red-600 text-white text-sm px-3 py-1 rounded-lg transition">
              Logout
            </button>
          </form>
        </div>
      <?php else: ?>
        <a href="/login" class="hover:text-yellow-400 transition flex items-center space-x-2">
          <i class="fas fa-sign-in-alt"></i>
          <span>Login</span>
        </a>
      <?php endif; ?>
    </div>

    <script>
      function confirmLogout(event) {
        if (!confirm("Are you sure you want to logout?")) {
          event.preventDefault();
        }
      }
    </script>

    <!-- Mobile Menu Button -->
    <div class="lg:hidden">
      <button id="navbar-toggler" class="text-white focus:outline-none">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>
    </div>

  </div>

  <!-- Mobile Menu -->
  <div class="lg:hidden hidden" id="mobile-menu">
    <ul class="flex flex-col items-center bg-gray-800 py-4 space-y-2">
      <?php foreach ($nav_links as $page => $data): ?>
        <li>
          <a href="/<?= $page ?>" class="block px-3 py-2 text-white hover:bg-yellow-400 flex items-center space-x-2">
            <i class="<?= $data['icon'] ?>"></i>
            <span><?= $data['label'] ?></span>
          </a>
        </li>
      <?php endforeach; ?>

      <?php if (isset($_SESSION['user_name'])): ?>
        <button class="block px-3 py-2 text-white hover:bg-yellow-400 flex items-center space-x-2">
          <i class="fas fa-user"></i>
          <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
        </button>
        <div id="mobile-user-dropdown" class="hidden">
          <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <a href="/admin/list" class="block px-3 py-2 text-white hover:bg-yellow-400">Admin Panel</a>
          <?php endif; ?>
          <a href="/account" class="block px-3 py-2 text-white hover:bg-yellow-400">Profile</a>
          <form method="POST" id="mobile-logout-form">
            <button type="submit" name="logout" class="block w-full text-left px-3 py-2 text-white hover:bg-yellow-400">Logout</button>
          </form>
        </div>
      <?php else: ?>
        <li>
          <a href="/login" class="block px-3 py-2 text-white hover:bg-yellow-400 flex items-center space-x-2">
            <i class="fas fa-sign-in-alt"></i>
            <span>Login</span>
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</nav>
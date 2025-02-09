<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Sử dụng đường dẫn tuyệt đối để đảm bảo tìm thấy file
require_once '../app/models/Cart.php';
require_once '../config/config.php';

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

<nav class="bg-gradient-to-r from-red-600 to-blue-600 text-white shadow-lg navbar">
  <div class="container mx-auto px-4 py-3 flex justify-between items-center">
    <div class="flex items-center space-x-3">
      <img src="/images/logo.png" alt="Pizza Store" class="h-14 w-14">
      <a href="/" class="text-3xl font-bold">Lover's Hub</a>
    </div>

    <!-- Mobile Menu Button -->
    <div class="lg:hidden">
      <button id="navbar-toggler" class="text-white focus:outline-none">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>
    </div>

    <!-- Navbar Links for Desktop -->
    <div class="hidden lg:flex space-x-8 items-center" id="navbar-menu">
      <?php
      $nav_links = [
        'home' => ['label' => 'Home', 'icon' => 'fas fa-home'],
        'products' => ['label' => 'Products', 'icon' => 'fas fa-pizza-slice'],
        'cart' => ['label' => 'Cart', 'icon' => 'fas fa-shopping-cart'],
        'contact' => ['label' => 'Contact', 'icon' => 'fas fa-envelope']
      ];
      foreach ($nav_links as $page => $data): ?>
        <a href="/<?= $page ?>" class="hover:text-yellow-300 transition duration-300 flex items-center space-x-1 relative">
          <i class="<?= $data['icon'] ?>"></i>
          <span><?= $data['label'] ?></span>
          <?php if ($page == 'cart'): ?>
            <!-- Số lượng sản phẩm trong giỏ hàng hiển thị ở góc trên -->
            <span class="absolute  transform -top-1 -right-1.5 -translate-y-1/2 translate-x-1/2 bg-yellow-300 text-blue-600 font-bold rounded-full text-xs px-2 py-1">
              <?= $cartItemCount ?>
            </span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>

      <?php if (isset($_SESSION['user_name'])): ?>
        <div class="relative">
          <button class="flex items-center space-x-2 hover:text-yellow-300" id="user-dropdown-toggle">
            <i class="fas fa-user"></i>
            <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
          </button>
          <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-36 bg-white text-black rounded-lg shadow-lg">
            <a href="/account" class="text-center block px-4 py-2 hover:bg-gray-200">Profile</a>
            <form method="POST" id="logout-form">
              <button type="submit" name="logout" onclick="confirmLogout(event)" class="text-center block w-full text-left px-4 py-2 hover:bg-gray-200">Logout</button>
            </form>
          </div>
        </div>

        <script>
          function confirmLogout(event) {
            // Hiển thị hộp thoại xác nhận
            const userConfirmed = confirm("Are you sure you want to logout?");
            if (userConfirmed) {
              // Người dùng xác nhận thì submit form
              document.getElementById('logout-form').submit();
            } else {
              // Ngăn chặn submit nếu người dùng nhấn "Hủy"
              event.preventDefault();
            }
          }

          // Đóng dropdown khi nhấp ra ngoài
          document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('user-dropdown');
            const toggle = document.getElementById('user-dropdown-toggle');
            // Kiểm tra nếu nhấp bên ngoài toggle và dropdown
            if (!toggle.contains(event.target) && !dropdown.contains(event.target)) {
              dropdown.classList.add('hidden');
            }
          });
        </script>

      <?php else: ?>
        <a href="/login" class="hover:text-yellow-300 transition duration-300 flex items-center space-x-1">
          <i class="fas fa-sign-in-alt"></i>
          <span>Login</span>
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Mobile Menu -->
  <div class="lg:hidden hidden" id="mobile-menu">
    <ul class="flex flex-col items-center bg-red-500 py-4 space-y-2">
      <?php foreach ($nav_links as $page => $data): ?>
        <li>
          <a href="/<?= $page ?>" class="block px-3 py-2 text-white hover:bg-yellow-400 flex items-center space-x-1">
            <i class="<?= $data['icon'] ?>"></i>
            <span><?= $data['label'] ?></span>
          </a>
        </li>
      <?php endforeach; ?>

      <?php if (isset($_SESSION['user_name'])): ?>
        <button class="block px-3 py-2 text-white hover:bg-yellow-400 flex items-center space-x-1" id="mobile-user-dropdown-toggle">
          <i class="fas fa-user"></i>
          <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
        </button>
        <div id="mobile-user-dropdown" class="hidden">
          <a href="/account" class="block px-3 py-2 text-white hover:bg-yellow-400">Profile</a>
          <form method="POST" id="mobile-logout-form">
            <button type="submit" name="logout" class="block w-full text-left px-3 py-2 text-white hover:bg-yellow-400">Logout</button>
          </form>
        </div>
      <?php else: ?>
        <li>
          <a href="/login" class="block px-3 py-2 text-white hover:bg-yellow-400 flex items-center space-x-1">
            <i class="fas fa-sign-in-alt"></i>
            <span>Login</span>
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</nav>
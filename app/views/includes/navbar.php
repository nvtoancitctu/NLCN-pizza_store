<?php

require_once '../app/models/Cart.php';

$user_id = $_SESSION['user_id'] ?? null;
$cartItemCount = getCartItemCount($conn, $user_id);
$notificationCount = getUnreadNotifications($conn, $user_id);

// Hàm lấy tổng số lượng sản phẩm trong giỏ hàng
function getCartItemCount($conn, $user_id)
{
  if (!$user_id) return 0;
  $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = :user_id");
  $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
  $stmt->execute();
  return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
}

function getUnreadNotifications($conn, $user_id)
{
  if (!$user_id) return 0;
  $stmt = $conn->prepare("SELECT COUNT(*) as unread FROM notifications WHERE user_id = :user_id AND is_read = 0");
  $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
  $stmt->execute();
  return $stmt->fetch(PDO::FETCH_ASSOC)['unread'] ?? 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
  $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id");
  $stmt->bindParam(':id', $_POST['notification_id'], PDO::PARAM_INT);
  $stmt->execute();

  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;
}

$nav_links = [
  '' => ['label' => 'Home', 'icon' => 'fas fa-home'],
  'products' => ['label' => 'Menu', 'icon' => 'fas fa-pizza-slice'],
  'about' => ['label' => 'About', 'icon' => 'fas fa-info-circle'],
  'feedback' => ['label' => 'Contact', 'icon' => 'fas fa-phone'],
];
?>

<nav class="bg-gray-800 text-white shadow-lg">
  <div class="container mx-auto px-6 lg:px-10 py-3 flex justify-between items-center">

    <!-- Cột 1: Logo & Tên thương hiệu -->
    <div class="flex items-center space-x-2">
      <img src="/images/logo.png" alt="Lover's Hut" class="w-14 h-14 rounded-full">
      <a href="/" class="text-3xl font-bold font-serif text-yellow-400">Lover's Hut</a>
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
        <span>Menus</span>
      </a>

      <!-- FEEDBACK -->
      <a href="/feedback" class="hover:text-yellow-400 transition flex items-center space-x-2">
        <i class="fas fa-comment"></i>
        <span>Feedback</span>
      </a>

      <!-- Cart Button Giống Ảnh -->
      <a href="/cart" class="inline-flex items-center space-x-2 border border-gray-300 px-3 py-1 rounded-full hover:text-yellow-400 transition">
        <!-- Số sản phẩm trong giỏ -->
        <span class="font-semibold text-sm text-yellow-400">
          <?= $cartItemCount ?>
        </span>

        <!-- Icon giỏ hàng -->
        <i class="fas fa-shopping-cart text-lg"></i>
      </a>

      <div class="relative">
        <button id="notification-button" class="relative flex items-center space-x-2 border px-3 py-1 rounded-full hover:text-yellow-400">
          <i class="fas fa-bell text-lg"></i>
          <?php if ($notificationCount > 0): ?>
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1">
              <?= $notificationCount ?>
            </span>
          <?php endif; ?>
        </button>

        <!-- Dropdown Notification -->
        <div id="notification-dropdown" class="position-absolute top-100 end-0 bg-white shadow-lg rounded-xl hidden"
          style="z-index: 1050; min-width: 550px;">
          <div class="p-3 text-gray-800 text-sm">
            <?php
            $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($notifications)):
            ?>
              <p class="text-center text-gray-500">No new notifications</p>
            <?php else: ?>
              <ul class="max-h-80 overflow-y-auto">
                <?php foreach ($notifications as $notification): ?>
                  <li class="px-3 py-2 border-b hover:bg-gray-100 cursor-pointer">
                    <form method="POST" style="display:inline;">
                      <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                      <button type="submit" class="w-full text-left <?= $notification['is_read'] == 1 ? 'text-gray-600' : 'text-gray-800 font-semibold' ?>">
                        <span class="block"><?= htmlspecialchars($notification['message']) ?></span>
                        <span class="text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?></span>
                      </button>
                    </form>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        </div>
        <!-- Script để hiển thị dropdown khi click vào icon chuông -->
        <script>
          document.addEventListener("DOMContentLoaded", function() {
            const bellButton = document.getElementById("notification-button");
            const dropdown = document.getElementById("notification-dropdown");
            bellButton.addEventListener("click", function(event) {
              event.stopPropagation();
              dropdown.classList.toggle("hidden");
            });
            document.addEventListener("click", function() {
              dropdown.classList.add("hidden");
            });
          });
        </script>
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
            <button type="button" onclick="openLogoutModal()"
              class="bg-red-500 hover:bg-red-600 text-white text-sm px-3 py-1 rounded-lg transition">
              Logout
            </button>
          </div>
        <?php else: ?>
          <a href="/login" class="hover:text-yellow-400 transition flex items-center space-x-2">
            <i class="fas fa-sign-in-alt"></i>
            <span>Login</span>
          </a>
        <?php endif; ?>
      </div>

      <!-- Mobile Menu Button -->
      <div class="lg:hidden">
        <button id="navbar-toggler" class="text-white focus:outline-none">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
      </div>

    </div>

    <!-- Button mở menu mobile -->
    <button id="mobile-menu-button" class="lg:hidden text-white p-2">
      <i class="fas fa-bars text-2xl"></i>
    </button>

    <!-- Mobile Menu -->
    <div class="lg:hidden hidden absolute top-16 left-0 w-full bg-gray-800 shadow-lg" id="mobile-menu">
      <ul class="flex flex-col items-center py-4 space-y-2">
        <?php foreach ($nav_links as $page => $data): ?>
          <li>
            <a href="/<?= $page ?>" class="block px-3 py-2 text-white hover:bg-yellow-400 flex items-center space-x-2">
              <i class="<?= $data['icon'] ?>"></i>
              <span><?= $data['label'] ?></span>
            </a>
          </li>
        <?php endforeach; ?>

        <?php if (isset($_SESSION['user_name'])): ?>
          <li class="relative">
            <button id="mobile-user-button" class="block px-3 py-2 text-white hover:bg-yellow-400 flex items-center space-x-2">
              <i class="fas fa-user"></i>
              <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
            </button>

            <div id="mobile-user-dropdown" class="hidden absolute left-0 w-full bg-gray-700 mt-2 rounded shadow-md">
              <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="/admin" class="block px-3 py-2 text-white hover:bg-yellow-400">Admin Panel</a>
              <?php endif; ?>

              <a href="/account" class="block px-3 py-2 text-white hover:bg-yellow-400">Profile</a>

              <button type="button" onclick="openLogoutModal()" class="block w-full text-left px-3 py-2 text-white hover:bg-yellow-400">
                Logout
              </button>
            </div>
          </li>
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

    <style>
      #mobile-menu {
        transition: all 0.3s ease-in-out;
      }
    </style>

    <!-- Modal xác nhận Logout -->
    <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
      <div class="bg-white rounded-lg shadow-lg w-120 p-6">
        <h2 class="text-base font-semibold text-center mb-4 text-gray-800">Are you sure you want to logout?</h2>
        <form method="POST" class="flex justify-center space-x-4">
          <button type="button" onclick="closeLogoutModal()"
            class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition">Cancel</button>
          <button type="submit" name="logout"
            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">Logout</button>
        </form>
      </div>
    </div>

</nav>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const menuButton = document.getElementById("mobile-menu-button");
    const mobileMenu = document.getElementById("mobile-menu");
    const userButton = document.getElementById("mobile-user-button");
    const userDropdown = document.getElementById("mobile-user-dropdown");

    // Toggle menu khi bấm nút
    menuButton.addEventListener("click", function() {
      mobileMenu.classList.toggle("hidden");
    });

    // Toggle dropdown user khi bấm vào tên người dùng
    if (userButton) {
      userButton.addEventListener("click", function() {
        userDropdown.classList.toggle("hidden");
      });
    }
  });

  function openLogoutModal() {
    const modal = document.getElementById("logoutModal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");
  }

  function closeLogoutModal() {
    const modal = document.getElementById("logoutModal");
    modal.classList.remove("flex");
    modal.classList.add("hidden");
  }

  // Đóng modal khi click ngoài modal
  window.addEventListener("click", function(e) {
    const modal = document.getElementById("logoutModal");
    if (e.target === modal) {
      closeLogoutModal();
    }
  });
</script>
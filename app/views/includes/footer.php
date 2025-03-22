<footer class="bg-gray-900 text-white py-4 mt-auto">
  <div class="container mx-auto px-4 lg:px-10">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center md:text-left items-center">

      <!-- Cột 1: Logo & Giới thiệu -->
      <div class="flex items-center justify-center md:justify-start space-x-3">
        <img src="/images/logo.png" alt="Pizza Store" class="w-12 h-12">
        <a href="/home" class="text-2xl font-bold">Lover's Hut</a>
      </div>

      <!-- Cột 2: Điều hướng (Quick Links) -->
      <div class="flex flex-col items-center">
        <div class="flex space-x-4">
          <?php
          $footerLinks = [
            'home' => ['label' => 'Home', 'icon' => 'fas fa-home'],
            'products' => ['label' => 'Menus', 'icon' => 'fas fa-pizza-slice'],
            'feedback' => ['label' => 'Feedback', 'icon' => 'fas fa-comment'],
            'account' => ['label' => 'Profile', 'icon' => 'fas fa-user']
          ];
          foreach ($footerLinks as $page => $data): ?>
            <a href="/<?= $page ?>" class="flex items-center space-x-1 text-gray-300 hover:text-yellow-400 transition">
              <i class="<?= $data['icon'] ?> text-base"></i>
              <span class="text-sm"><?= $data['label'] ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Cột 3: Follow Us -->
      <div class="flex flex-col items-center">
        <div class="flex space-x-4">
          <a href="#" class="text-gray-400 hover:text-blue-400 transition">
            <i class="fab fa-facebook text-lg"></i>
          </a>
          <a href="#" class="text-gray-400 hover:text-blue-300 transition">
            <i class="fab fa-twitter text-lg"></i>
          </a>
          <a href="#" class="text-gray-400 hover:text-red-400 transition">
            <i class="fab fa-instagram text-lg"></i>
          </a>
        </div>
      </div>

      <!-- Cột 4: Contact Us -->
      <div class="flex items-center justify-center md:justify-start space-x-2">
        <span class="text-gray-300 text-sm">Contact Us:</span>
        <a href="tel:0932822075" class="text-yellow-400 font-semibold hover:underline">
          (+84) 932 822 075
        </a>
      </div>

    </div>

    <!-- Bản quyền -->
    <div class="text-center text-gray-500 text-xs mt-2 border-t border-gray-700 pt-2">
      © 2025 - Pizza Store by Nguyen Van Toan B2111824
    </div>
  </div>
</footer>

<!-- Optional Scripts -->
<script src="js/script.js?v=2.0"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
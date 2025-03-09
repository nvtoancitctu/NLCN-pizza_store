<!-- Footer -->
<footer class="bg-gradient-to-r from-red-600 to-blue-600 text-white py-4 mt-auto">
  <div class="container mx-auto text-center">
    <p class="mb-2">© 2025 - Pizza Store by Nguyen Van Toan B2111824</p>
    <div class="flex justify-center space-x-4">
      <?php
      $footerLinks = [
        'home' => ['label' => 'Home', 'icon' => 'fas fa-home'],
        'products' => ['label' => 'Products', 'icon' => 'fas fa-pizza-slice'],
        'feedback' => ['label' => 'Feedback', 'icon' => 'fas fa-comment']
      ];
      foreach ($footerLinks as $page => $data): ?>
        <a href="/<?= $page ?>" class="hover:text-yellow-400 transition duration-300 flex items-center space-x-2">
          <i class="<?= $data['icon'] ?>"></i>
          <span><?= $data['label'] ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</footer>

<!-- Optional Scripts -->
<script src="js/script.js?v=2.0"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
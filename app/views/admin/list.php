<?php

// Kiểm tra quyền admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: /login");
    exit();
}

// Tạo token CSRF nếu chưa tồn tại
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Kiểm tra và lấy thông báo thành công từ session
$success = '';
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

$productController = new ProductController($conn);

$searchTerm = '';
$limit = isset($_POST['limit']) ? max(1, (int)$_POST['limit']) : $productController->countProducts();   // Số lượng sản phẩm hiển thị mặc định là ALL
$page = isset($_POST['page']) ? max(1, (int)$_POST['page']) : 1;      // Trang hiện tại, mặc định là trang 1
$offset = ($page - 1) * $limit;                                       // Tính toán offset

// Lấy danh sách sản phẩm hoặc tìm kiếm sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    // Kiểm tra token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo "<h1 class='text-center mt-5'>Forbidden: Invalid CSRF token</h1>";
        exit();
    }

    $searchTerm = isset($_POST['search_term']) ? trim($_POST['search_term']) : '';
    $products = $productController->searchProducts($searchTerm);
    $totalProducts = count($products); // Cập nhật tổng số sản phẩm tìm thấy
} else {
    // Lấy danh sách sản phẩm với phân trang
    $products = $productController->getProductsByCategoryWithPagination(null, $limit, $offset);
    $totalProducts = $productController->countProducts(); // Tổng số sản phẩm
}

$totalPages = ceil($totalProducts / $limit); // Tổng số trang

// Kiểm tra hành động 'export-products'
if (isset($_GET['action']) && $_GET['action'] === 'export-products') {
    $productController->exportProducts();
}

?>

<!-- Hiển thị thông báo thành công nếu có -->
<?php if (!empty($success)): ?>
    <script>
        alert("<?= addslashes($success) ?>");
    </script>
<?php endif; ?>

<h1 class="text-4xl font-extrabold text-center my-10 text-blue-700 drop-shadow-lg">Product Management</h1>

<div class="container-fluid mx-auto p-6 bg-white shadow-xl rounded-lg mb-4 w-full lg:w-11/12">
    <div class="row mb-4">
        <!-- Nút chức năng -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <!-- Nút thêm sản phẩm và thống kê -->
            <div class="d-flex align-items-center mb-3">
                <button class="btn btn-success me-3" onclick="window.location.href='/admin/add'">+ New Product</button>
                <button class="btn btn-primary me-3" onclick="window.location.href='/admin/statistics'">Statistics</button>
            </div>

            <!-- Nút xuất/nhập dữ liệu -->
            <div class="d-flex align-items-center mb-3 flex-wrap">
                <!-- Xuất dữ liệu -->
                <button class="btn btn-outline-success me-4 mb-2" onclick="window.location.href='/admin/export-products'">
                    Export to CSV
                </button>

                <!-- Nhập dữ liệu -->
                <form method="POST" action="/admin/import-products" enctype="multipart/form-data" class="d-flex align-items-center flex-wrap">
                    <label for="product_file" class="form-label mb-0 me-3 align-self-center">Upload CSV:</label>
                    <input type="file" name="product_file" id="product_file" class="form-control w-auto me-3 mb-2" accept=".csv" required>
                    <button type="submit" class="btn btn-primary mb-2">Import</button>
                </form>
            </div>
        </div>

        <!-- Thanh tìm kiếm -->
        <div class="col-md-12">
            <form method="POST" class="d-flex align-items-center flex-wrap">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div class="input-group w-100 mb-2">
                    <input type="text" name="search_term" class="form-control" placeholder="Search products..."
                        value="<?= htmlspecialchars($searchTerm ?? '') ?>" aria-label="Search products"
                        aria-describedby="button-search">
                    <button class="btn btn-primary" type="submit" name="search" id="button-search">Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Form chọn số lượng sản phẩm hiển thị -->
    <form method="POST" class="text-center mb-6">
        <input type="hidden" name="page" value="1">

        <label for="limit" class="mr-2 text-lg">Select Number of Products:</label>
        <select name="limit" id="limit" onchange="this.form.submit()" class="p-2 border rounded">
            <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
            <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20</option>
        </select>
    </form>

    <!-- Danh mục sản phẩm -->
    <div class="table-responsive">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md">
            <thead>
                <tr class="bg-gray-100 text-gray-800 text-center">
                    <th class="px-3 py-2 border-b">ID</th>
                    <th class="px-3 py-2 border-b">Image</th>
                    <th class="px-3 py-2 border-b">Name</th>
                    <th class="px-3 py-2 border-b">Price</th>
                    <th class="px-3 py-2 border-b">Description</th>
                    <th class="px-3 py-2 border-b">Discount</th>
                    <th class="px-3 py-2 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 border-b text-center"><?= htmlspecialchars($product['id']) ?></td>
                            <td class="px-3 py-2 border-b text-center">
                                <img src="/images/<?= htmlspecialchars($product['image']); ?>" class="w-16 h-16 object-cover mx-auto rounded-lg" alt="<?= htmlspecialchars($product['name']); ?>">
                            </td>
                            <td class="px-3 py-2 border-b font-semibold text-gray-800 text-center"><?= htmlspecialchars($product['name']) ?></td>
                            <td class="px-3 py-2 border-b text-green-600 font-bold text-center">$<?= number_format($product['price'], 2) ?></td>
                            <td class="px-3 py-2 border-b text-gray-600"><?= htmlspecialchars(substr($product['description'], 0, 50)) ?>...</td>
                            <td class="px-3 py-2 border-b text-red-500 font-bold text-center">
                                <?php if (!empty($product['discount']) && $product['discount'] > 0): ?>
                                    $<?= number_format($product['discount'], 2) ?>
                                <?php else: ?>
                                    <span class="text-gray-500">No Discount</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-2 border-b text-center">
                                <div class="d-flex justify-content-center flex-wrap">
                                    <a href="/admin/edit/id=<?= $product['id'] ?>" class="btn btn-warning me-2 mb-2">Edit</a>
                                    <a href="javascript:void(0);" onclick="confirmDelete(<?= $product['id'] ?>)" class="btn btn-danger mb-2">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-gray-500 py-4">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <form method="POST" class="text-center mt-6 flex justify-center items-center space-x-4">
            <!-- Trường ẩn để giữ giá trị limit -->
            <input type="hidden" name="limit" value="<?= htmlspecialchars($limit) ?>">

            <!-- Dropdown chọn số trang -->
            <div class="flex items-center">
                <label for="page" class="text-lg mr-2">Page:</label>
                <select name="page" id="page" onchange="this.form.submit()" class="p-2 border rounded">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <option value="<?= $i ?>" <?= $page == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Nút Previous -->
            <button type="submit" name="page" value="<?= max(1, $page - 1) ?>"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-300 <?= $page <= 1 ? 'cursor-not-allowed opacity-50' : '' ?>"
                <?= $page <= 1 ? 'disabled' : '' ?>>
                Previous
            </button>

            <!-- Nút Next -->
            <button type="submit" name="page" value="<?= min($totalPages, $page + 1) ?>"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-300 <?= $page >= $totalPages ? 'cursor-not-allowed opacity-50' : '' ?>"
                <?= $page >= $totalPages ? 'disabled' : '' ?>>
                Next
            </button>
        </form>
    </div>
</div>

<script>
    function confirmDelete(productId) {
        const confirmDelete = confirm("Are you sure you want to delete this product?");
        if (confirmDelete) {
            window.location.href = `/admin/delete&id=${productId}`;
        }
    }
</script>
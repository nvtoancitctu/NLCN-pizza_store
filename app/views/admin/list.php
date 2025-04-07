<?php

// Kiểm tra quyền admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = "You must login by admin account to access.";
    header("Location: /login");
    exit();
}

// Tạo token CSRF nếu chưa tồn tại
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Lấy danh mục sản phẩm
$categories = $productController->getAllCategoryNamesExceptPizza();

$searchTerm = '';

$category = isset($_POST['category']) && $_POST['category'] !== '' ? $_POST['category'] : null;

$limit = isset($_POST['limit']) ? max(1, (int)$_POST['limit']) : 5;     // Mặc định 5 sản phẩm
$page = isset($_POST['page']) ? max(1, (int)$_POST['page']) : 1;        // Trang hiện tại, mặc định là trang 1
$offset = ($page - 1) * $limit;                                         // Tính offset

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
    $totalProducts = count($products); // Số sản phẩm tìm thấy
} else {
    // Lấy danh sách sản phẩm với phân trang
    $products = $productController->getProductsByCategoryWithPagination($category, $limit, $offset);
    $totalProducts = $category ? $productController->countProductsByCategory($category) : $productController->countProducts();
}

$totalPages = max(1, ceil($totalProducts / $limit)); // Tổng số trang
?>

<!--------------------------------------- Quản lý sản phẩm, thống kê, xuất file csv --------------------------------------->
<h1 class="text-4xl font-extrabold text-center my-10 text-blue-700 drop-shadow-lg">Products Management</h1>
<div class="container-fluid mx-auto p-6 bg-white shadow-sm rounded-lg w-full lg:w-11/12 border-2 border-blue-600">
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
                <button class="btn btn-outline-success me-4 mb-2" onclick="window.location.href='/admin/export-products'">Export to CSV</button>
                <form method="POST" action="/admin/import-products" enctype="multipart/form-data" class="d-flex align-items-center flex-wrap">
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

    <!-- Form chọn số lượng sản phẩm hiển thị và loại sản phẩm -->
    <form method="POST" class="text-center mb-6">
        <input type="hidden" name="page" value="1">

        <!-- Chọn số lượng sản phẩm hiển thị -->
        <label for="limit" class="mr-2 text-lg">Number of Products:</label>
        <select name="limit" id="limit" onchange="this.form.submit()" class="p-2 border rounded">
            <option value="" selected disabled hidden>All</option>
            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
            <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20</option>
            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
        </select>

        <!-- Chọn loại sản phẩm -->
        <label for="category" class="ml-4 mr-2 text-lg">Category:</label>
        <select name="category" id="category" onchange="this.form.submit()" class="p-2 border rounded">
            <option value="" selected disabled hidden>All</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat['id']) ?>" <?= ($category == $cat['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
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
                    <th class="px-3 py-2 border-b">Description</th>
                    <th class="px-3 py-2 border-b">Stock</th>
                    <th class="px-3 py-2 border-b">Price</th>
                    <th class="px-3 py-2 border-b">Discount</th>
                    <th class="px-3 py-2 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                        <tr class="hover:bg-gray-50" title="<?php echo htmlspecialchars($product['description']); ?>">
                            <td class="px-3 py-2 border-b text-center"><?= htmlspecialchars($product['id']) ?></td>
                            <td class="px-3 py-2 border-b text-center">
                                <img src="/images/product/<?= htmlspecialchars($product['image'] ?? 'logo.png') ?>"
                                    class="w-16 h-16 object-cover mx-auto rounded-lg"
                                    alt="<?= htmlspecialchars($product['name']) ?>">
                            </td>
                            <td class="px-3 py-2 border-b font-semibold text-gray-800"><?= htmlspecialchars($product['name']) ?></td>
                            <td class="px-3 py-2 border-b text-gray-600"><?= htmlspecialchars(substr($product['description'], 0, 20)) ?>...</td>
                            <td class="px-3 py-2 border-b text-green-600 font-bold text-center"><?= htmlspecialchars($product['stock_quantity']) ?></td>
                            <td class="px-3 py-2 border-b text-green-600 font-bold text-center">$<?= number_format($product['price'], 2) ?></td>
                            <td class="px-3 py-2 border-b text-red-500 font-bold text-center">
                                <?php if (!empty($product['discount']) && $product['discount'] > 0): ?>
                                    $<?= number_format($product['discount'], 2) ?>
                                <?php else: ?>
                                    <span class="text-gray-500">---</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-2 border-b text-center">
                                <div class="d-flex justify-content-center flex-nowrap gap-2">
                                    <!-- Nút Edit -->
                                    <a href="/admin/edit-product/id=<?= $product['id'] ?>" class="btn btn-warning me-2" title="Edit Product">
                                        <i class="fas fa-edit"></i> <!-- Icon Edit -->
                                    </a>

                                    <!-- Nút Delete -->
                                    <form id="deleteForm-<?= $product['id'] ?>" action="/admin/delete" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="button" onclick="openDeleteModal('<?= $product['id'] ?>')" class="btn btn-danger" title="Delete Product">
                                            <i class="fas fa-trash-alt"></i> <!-- Icon Delete -->
                                        </button>
                                    </form>

                                    <!-- Modal Xác nhận Xóa Sản phẩm -->
                                    <div id="deleteModal-<?= $product['id'] ?>" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center z-50 hidden">
                                        <div class="bg-white p-6 rounded-lg shadow-lg w-120">
                                            <h3 class="text-lg font-semibold text-gray-800">Are you sure you want to delete this product?</h3>
                                            <p class="mt-2 text-sm text-gray-600">Product ID: <?= $product['id'] ?></p>
                                            <div class="mt-4 flex justify-center space-x-8">
                                                <button type="button" onclick="closeDeleteModal('<?= $product['id'] ?>')" class="px-3 py-1 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">Cancel</button>
                                                <button type="button" onclick="confirmDelete('<?= $product['id'] ?>')" class="px-3 py-1 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">Delete</button>
                                            </div>
                                        </div>
                                    </div>
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
            <!-- Trường ẩn để giữ giá trị limit và category -->
            <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
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
    function openDeleteModal(productId) {
        document.getElementById('deleteModal-' + productId).classList.remove('hidden');
    }

    function closeDeleteModal(productId) {
        document.getElementById('deleteModal-' + productId).classList.add('hidden');
    }

    function confirmDelete(productId) {
        document.getElementById('deleteForm-' + productId).submit();
    }
</script>

<!--------------------------------------- Quản lý đơn hàng, cập nhật trạng thái --------------------------------------->
<?php

// Lấy danh sách khách hàng
$customers = $userController->getAllUsers();

// Lấy dữ liệu lọc từ form
$customerName = isset($_POST['customer_name']) ? ($_POST['customer_name']) : null;

$limit_order = isset($_POST['limit_order']) ? max(1, $_POST['limit_order']) : 5;
$page_order = isset($_POST['page_order']) ? max(1, $_POST['page_order']) : 1;
$offset_order = ($page_order - 1) * $limit_order;

// Xử lý lấy danh sách đơn hàng theo bộ lọc
$orders = $orderController->getOrdersWithFilters($customerName, $limit_order, $offset_order);
$totalOrders = $orderController->countFilteredOrders($customerName);
$totalPages = ceil($totalOrders / $limit_order);

// Xử lý trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_order'])) {

    // Kiểm tra token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo "<h1 class='text-center mt-5'>Forbidden: Invalid CSRF token</h1>";
        exit();
    }

    // Lấy dữ liệu từ form, kiểm tra sự tồn tại và làm sạch dữ liệu đầu vào
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : null;
    $customer_name = isset($_POST['customer_name']) ? trim(htmlspecialchars($_POST['customer_name'])) : '';
    $total_price = isset($_POST['total']) ? floatval($_POST['total']) : 0;
    $order_status = isset($_POST['status']) ? trim(htmlspecialchars($_POST['status'])) : '';

    // Kiểm tra xem order_id có hợp lệ không
    if ($order_id && !empty($customer_name) && $total_price >= 0 && !empty($order_status)) {

        // Lấy user_id từ bảng orders để kiểm tra status hiện tại của đơn hàng
        $stmt = $conn->prepare("SELECT user_id, status FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['status'] !== $order_status) {
            // Gọi phương thức updateOrder trong controller và lưu session thông báo thành công
            $updated = $orderController->updateOrder($order_id, $customer_name, $total_price, $order_status);
            $_SESSION['success'] = "Order (ID: $order_id) is updated successfully.";

            // Gọi hàm thêm notification để thông báo trạng thái đơn hàng cho khách hàng
            $user_id = $user['user_id'];
            $message = "Your order (ID: $order_id) has been updated to status: $order_status.";
            $userController->addNotification($user_id, $message);

            // Gửi email nếu đơn hàng thành công (status = 'completed') hoặc bị hủy (status = 'canceled')
            if ($order_status === 'completed' || $order_status === 'cancelled') {
                header("Location: /index.php?page=send-email_order&order_id=$order_id&status=$order_status&user_id=$user_id&csrf_token=" . $_SESSION['csrf_token']);
                exit();
            }
        } else {
            $_SESSION['error'] = "Failed to update order (ID: $order_id).";
        }

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header("Location: /admin");
        exit;
    }
}
?>

<!-- Breadcrumb -->
<div class="w-full lg:w-8/12 flex items-center justify-center mx-auto my-10">
    <div class="flex-grow border-t-2 border-gray-700"></div>
    <h1 class="mx-6 text-3xl md:text-4xl font-extrabold text-gray-700 drop-shadow-lg whitespace-nowrap">
        <span class="text-gray-700 text-4xl font-bold">[</span>
        Orders Management
        <span class="text-gray-700 text-4xl font-bold">]</span>
    </h1>
    <div class="flex-grow border-t-2 border-gray-700"></div>
</div>

<!-- Form chỉnh sửa đơn hàng (mặc định ẩn) -->
<div id="edit-order-form" class="space-y-6 mb-8 hidden mx-auto w-full lg:w-10/12">
    <form action="/admin" method="POST" class="space-y-6 bg-white p-6 rounded-lg shadow-sm border-2 border-yellow-300">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <!-- Bố cục 2 cột -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Cột 1: Thông tin đơn hàng -->
            <div class="space-y-5">
                <div>
                    <label for="editOrderId" class="block text-gray-700 font-semibold">Order ID</label>
                    <input type="text" id="editOrderId" name="order_id" class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 text-gray-600" readonly>
                </div>
                <div>
                    <label for="editPaymentMethod" class="block text-gray-700 font-semibold">Payment Method</label>
                    <input type="text" id="editPaymentMethod" class="w-full p-3 border border-gray-200 rounded-md bg-gray-50 text-gray-600" readonly>
                    <input type="hidden" name="payment_method" id="hiddenPaymentMethod">
                </div>
                <div>
                    <label for="editCustomerName" class="block text-gray-700 font-semibold">Customer Name <span class="text-red-500">*</span></label>
                    <input type="text" id="editCustomerName" name="customer_name" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-400 focus:border-blue-400" required>
                </div>
                <div>
                    <label for="editTotalPrice" class="block text-gray-700 font-semibold">Total Price <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" id="editTotalPrice" name="total" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-400 focus:border-blue-400" required>
                </div>
                <div>
                    <label for="editStatus" class="block text-gray-700 font-semibold">Status <span class="text-red-500">*</span></label>
                    <select id="editStatus" name="status" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                        <option value="pending" class="text-yellow-600">Pending</option>
                        <option value="processing" class="text-blue-600">Processing</option>
                        <option value="completed" class="text-green-600">Completed</option>
                        <option value="cancelled" class="text-red-600">Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- Cột 2: Bank Transfer Image -->
            <div class="space-y-5">
                <div>
                    <label class="block text-gray-700 font-semibold">Bank Transfer Image</label>
                    <div id="bankTransferImageContainer" class="w-full flex items-center justify-center hidden border border-gray-200 rounded-md bg-gray-50" style="min-height: 200px;">
                        <img id="bankTransferImage" class="max-h-96 w-auto rounded-md shadow-sm">
                    </div>
                </div>
            </div>
        </div>

        <!-- Nút Thao Tác -->
        <div class="flex justify-center space-x-4 mt-6">
            <button type="submit" name="edit_order" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-8 rounded-lg shadow-md transition duration-200">Save</button>
            <button type="button" id="cancelEdit" class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-8 rounded-lg shadow-md transition duration-200">Cancel</button>
        </div>
    </form>
</div>

<!-- Bảng đơn hàng -->
<div class="container-fluid mx-auto p-6 bg-white shadow-sm rounded-lg w-full lg:w-11/12 border-2 border-blue-600">
    <div class="table-responsive">
        <form method="POST" class="flex flex-col sm:flex-row justify-center items-center mb-6 space-y-4 sm:space-y-0 sm:space-x-6">
            <input type="hidden" name="page_order" value="1">

            <!-- Chọn số lượng đơn hàng hiển thị -->
            <div class="flex items-center space-x-2">
                <label for="limit_order" class="text-gray-700 font-medium">Orders per page:</label>
                <select name="limit_order" id="limit_order" onchange="this.form.submit()" class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-400">
                    <option value="" selected disabled hidden>All</option>
                    <option value="10" <?= $limit_order == 10 ? 'selected' : '' ?>>10</option>
                    <option value="20" <?= $limit_order == 20 ? 'selected' : '' ?>>20</option>
                    <option value="50" <?= $limit_order == 50 ? 'selected' : '' ?>>50</option>
                </select>
            </div>

            <!-- Lọc theo tên khách hàng -->
            <div class="flex items-center space-x-2">
                <label for="customer_name" class="text-gray-700 font-medium">Customer:</label>
                <select name="customer_name" id="customer_name" onchange="this.form.submit()" class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-400">
                    <option value="" <?= (empty($customerName) || $customerName === 'all') ? 'selected' : '' ?>>All</option>
                    <?php foreach ($customers as $cus): ?>
                        <option value="<?= htmlspecialchars($cus['id']) ?>" <?= ($customerName == $cus['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cus['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <!-- DANH SÁCH ĐƠN HÀNG -->
        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
            <thead>
                <tr class="bg-blue-100 text-gray-700 text-center font-semibold">
                    <th class="px-4 py-3 border-b">Order ID</th>
                    <th class="px-4 py-3 border-b">Customer</th>
                    <th class="px-4 py-3 border-b">Total Price</th>
                    <th class="px-4 py-3 border-b">Status</th>
                    <th class="px-4 py-3 border-b">Created At</th>
                    <th class="px-4 py-3 border-b">Payment Method</th>
                    <th class="px-4 py-3 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr class="hover:bg-gray-100 text-center text-gray-600">
                            <td class="px-4 py-3 border-b"><?= htmlspecialchars($order['id']) ?></td>
                            <td class="px-4 py-3 border-b"><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td class="px-4 py-3 border-b text-green-600 font-semibold">$<?= number_format($order['total'], 2) ?></td>
                            <td class="px-4 py-3 border-b">
                                <span class="<?= $order['status'] === 'completed' ? 'text-green-600' : ($order['status'] === 'pending' ? 'text-yellow-600' : ($order['status'] === 'processing' ? 'text-blue-600' : 'text-red-600')) ?> font-semibold">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 border-b"><?= htmlspecialchars($order['created_at']) ?></td>
                            <td class="px-4 py-3 border-b"><?= htmlspecialchars($order['payment_method']) ?></td>
                            <td class="px-4 py-3 border-b">
                                <div class="flex justify-center gap-2">
                                    <button type="button" class="btn bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded-lg shadow-sm edit-btn transition duration-200"
                                        data-id="<?= $order['id'] ?>"
                                        data-customer="<?= ($order['customer_name']) ?>"
                                        data-total="<?= $order['total'] ?>"
                                        data-status="<?= $order['status'] ?>"
                                        data-payment="<?= ($order['payment_method']) ?>"
                                        data-image="<?= isset($order['images']) ? ($order['images']) : '' ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <form id="deleteOrderForm-<?= $order['id'] ?>" action="/admin/delete-order" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <button type="button" onclick="openDeleteOrderModal('<?= $order['id'] ?>')" class="btn bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg shadow-sm transition duration-200" title="Delete Order">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>

                                    <!-- Modal Xác nhận Xóa Đơn Hàng -->
                                    <div id="deleteOrderModal-<?= $order['id'] ?>" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center z-50 hidden">
                                        <div class="bg-white p-6 rounded-lg shadow-lg w-120">
                                            <h3 class="text-lg font-semibold text-gray-800">Are you sure you want to delete this order?</h3>
                                            <p class="mt-2 text-sm text-gray-600">Order ID: <span class="font-semibold"><?= $order['id'] ?></span></p>
                                            <div class="mt-4 flex justify-center space-x-4">
                                                <button type="button" onclick="closeDeleteOrderModal('<?= $order['id'] ?>')" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition duration-200">Cancel</button>
                                                <button type="button" onclick="confirmDeleteOrder('<?= $order['id'] ?>')" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition duration-200">Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-gray-500 py-4">No orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Phân trang -->
    <form method="POST" class="text-center mt-6 flex justify-center items-center space-x-4">
        <input type="hidden" name="limit_order" value="<?= htmlspecialchars($limit_order) ?>">
        <input type="hidden" name="customer_name" value="<?= htmlspecialchars($customerName) ?>">

        <div class="flex items-center space-x-2">
            <label for="page" class="text-gray-700 font-medium">Page:</label>
            <select name="page_order" id="page_order" onchange="this.form.submit()" class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-400">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <option value="<?= $i ?>" <?= $page_order == $i ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <button type="submit" name="page_order" value="<?= max(1, $page_order - 1) ?>"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 <?= $page_order <= 1 ? 'cursor-not-allowed opacity-50' : '' ?>"
            <?= $page_order <= 1 ? 'disabled' : '' ?>>
            Previous
        </button>

        <button type="submit" name="page_order" value="<?= min($totalPages, $page_order + 1) ?>"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 <?= $page_order >= $totalPages ? 'cursor-not-allowed opacity-50' : '' ?>"
            <?= $page_order >= $totalPages ? 'disabled' : '' ?>>
            Next
        </button>
    </form>
</div>

<script>
    function openDeleteOrderModal(orderId) {
        document.getElementById(`deleteOrderModal-${orderId}`).classList.remove('hidden');
    }

    function closeDeleteOrderModal(orderId) {
        document.getElementById(`deleteOrderModal-${orderId}`).classList.add('hidden');
    }

    function confirmDeleteOrder(orderId) {
        document.getElementById(`deleteOrderForm-${orderId}`).submit();
    }

    function toggleEditForm(orderId, customer, total, status, payment, image) {
        const form = document.getElementById('edit-order-form');
        document.getElementById('editOrderId').value = orderId;
        document.getElementById('editCustomerName').value = customer;
        document.getElementById('editTotalPrice').value = total;
        document.getElementById('editStatus').value = status;
        document.getElementById('editPaymentMethod').value = payment;
        document.getElementById('hiddenPaymentMethod').value = payment;

        const bankTransferImageContainer = document.getElementById('bankTransferImageContainer');
        const bankTransferImage = document.getElementById('bankTransferImage');

        if (payment.toLowerCase() === "bank_transfer" && image) {
            bankTransferImage.src = "/images/banking/" + image;
            bankTransferImageContainer.classList.remove("hidden");
        } else {
            bankTransferImageContainer.classList.add("hidden");
            bankTransferImage.src = "";
        }

        form.classList.remove('hidden');
        window.scrollTo({
            top: form.offsetTop,
            behavior: 'smooth'
        });
    }

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            toggleEditForm(
                this.dataset.id,
                this.dataset.customer,
                this.dataset.total,
                this.dataset.status,
                this.dataset.payment,
                this.dataset.image
            );
        });
    });

    document.getElementById('cancelEdit').addEventListener('click', function() {
        document.getElementById('edit-order-form').classList.add('hidden');
    });
</script>

<!--------------------------------------- Quản lý phản hồi --------------------------------------->
<?php
// Lấy danh sách phản hồi
$stmt = $conn->query("SELECT * FROM feedback ORDER BY id ASC");
$feedbacks = $stmt->fetchAll();

// Xử lý phản hồi của admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['response'], $_POST['id'])) {
    $id = intval($_POST['id']);
    $response = trim($_POST['response']);

    if ($id > 0 && !empty($response)) {
        $stmt = $conn->prepare("UPDATE feedback SET response = ?, responsed_at = NOW() WHERE id = ?");
        $stmt->execute([$response, $id]);

        // Lấy user_id từ bảng feedback để gửi thông báo
        $stmt = $conn->prepare("SELECT user_id FROM feedback WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $user_id = $user['user_id'];
            $message = "Your feedback #$id has been responded to by the admin.";

            // Gọi hàm thêm notification
            $userController->addNotification($user_id, $message);
        }

        $_SESSION['success'] = "Feedback #$id has been responded to successfully.";
    } else {
        $_SESSION['error'] = "Invalid feedback ID or response cannot be empty.";
    }
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>

<div class="w-full lg:w-8/12 flex items-center justify-center mx-auto my-10">
    <div class="flex-grow border-t-2 border-gray-700"></div>
    <h1 class="mx-6 text-3xl md:text-4xl font-extrabold text-gray-700 drop-shadow-lg whitespace-nowrap">
        <span class="text-gray-700 text-4xl font-bold">[</span>
        Feedbacks Management
        <span class="text-gray-700 text-4xl font-bold">]</span>
    </h1>
    <div class="flex-grow border-t-2 border-gray-700"></div>
</div>

<div class="container mx-auto p-6 bg-white shadow-sm rounded-lg w-full lg:w-11/12 border-2 border-blue-600">
    <div class="overflow-x-auto">
        <table class="w-full bg-white border border-gray-200 rounded-lg shadow-md">
            <thead>
                <tr class="bg-blue-100 text-gray-800 text-center uppercase text-sm">
                    <th class="px-4 py-3 border-b">ID</th>
                    <th class="px-4 py-3 border-b">Sender</th>
                    <th class="px-4 py-3 border-b">Order</th>
                    <th class="px-4 py-3 border-b">Message</th>
                    <th class="px-4 py-3 border-b">Rating</th>
                    <th class="px-4 py-3 border-b">Created</th>
                    <th class="px-4 py-3 border-b">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($feedbacks)): ?>
                    <?php foreach ($feedbacks as $fb): ?>
                        <tr class="hover:bg-gray-50 border-b text-center">
                            <td class="px-4 py-3"><?= htmlspecialchars($fb['id']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($fb['name']) ?></td>
                            <td class="px-4 py-3 font-semibold">#<?= htmlspecialchars($fb['order_id']) ?></td>
                            <td class="px-4 py-3 text-left text-sm text-gray-700 max-w-xs break-words">
                                <?= nl2br(htmlspecialchars($fb['message'])) ?>
                            </td>
                            <td class="px-4 py-3 text-yellow-500 font-semibold"><?= htmlspecialchars($fb['rating']) ?><i class="fas fa-star"></i></td>
                            <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($fb['created_at']) ?></td>
                            <td class="px-4 py-3">
                                <button onclick="openReplyModal(<?= $fb['id'] ?>, '<?= htmlspecialchars($fb['message'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($fb['response'] ?? '', ENT_QUOTES, 'UTF-8') ?>')"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg shadow-md transition-all">
                                    ✏️
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-gray-500 py-4"></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Reponse-->
<div id="replyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-11/12 md:w-2/3 lg:w-1/3 p-6">
        <h2 class="text-xl font-semibold mb-4">
            Reply to Feedback <span id="feedbackIdLabel" class="text-blue-600"></span>
        </h2>

        <form method="post">
            <input type="hidden" id="replyFeedbackId" name="id">
            <label class="block mb-2 font-semibold">Customer Feedback:</label>
            <textarea id="replyMessage" class="w-full p-2 border rounded-lg bg-gray-100" readonly></textarea>

            <label class="block mt-4 mb-2 font-semibold">Your Response:</label>
            <textarea name="response" id="replyResponse" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-400"></textarea>

            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" onclick="closeReplyModal()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">Close</button>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">Reply</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReplyModal(feedbackId, message, response) {
        document.getElementById('replyFeedbackId').value = feedbackId;
        document.getElementById('replyMessage').value = message;
        document.getElementById('replyResponse').value = response;
        document.getElementById('feedbackIdLabel').textContent = `#${feedbackId}`;
        document.getElementById('replyModal').classList.remove('hidden');
    }

    function closeReplyModal() {
        document.getElementById('replyModal').classList.add('hidden');
    }
</script>

<!--------------------------------------- Quản lý tài khoản --------------------------------------->
<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {

    // Kiểm tra CSRF Token hợp lệ
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        exit("<h1 class='text-center mt-5'>Forbidden: Invalid CSRF token</h1>");
    }

    $user_id = intval($_POST['user_id']); // Chuyển đổi thành số nguyên để tránh injection

    // Lấy email trước khi xóa
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $userEmail = $stmt->fetchColumn(); // Lấy email của user

    // Xử lý trường hợp cập nhật và khóa tài khoản
    if (isset($_POST['block_user'])) {
        $days = max(1, intval($_POST['days'])); // đảm bảo days >= 1

        // Lấy các thông tin từ form (các trường đã có name trong form)
        $data = [
            'name'          => trim($_POST['name'] ?? ''),
            'email'         => trim($_POST['email'] ?? ''),
            'phone'         => trim($_POST['phone'] ?? ''),
            'address'       => trim($_POST['address'] ?? ''),
            'blocked_until' => trim($_POST['blocked_until'] ?? ''), // Ngày theo định dạng YYYY-MM-DD
        ];

        // Cập nhật thông tin người dùng
        $updateSuccess = $userController->updateUser($user_id, $data);

        // Khóa tài khoản trong $days ngày (giả sử hàm blockUser đã được định nghĩa)
        $blockSuccess = $userController->blockUser($user_id, $days);

        if ($updateSuccess && $blockSuccess) {
            $_SESSION['success'] = "User (ID: $user_id) info updated and blocked for $days days successfully.";
            // Redirect đến trang gửi email thông báo (đảm bảo đường dẫn và các tham số phù hợp)
            header("Location: /index.php?page=send-email_user&user_email=" . urlencode($userEmail) . "&type=block&days=$days&csrf_token=" . $_SESSION['csrf_token']);
            exit();
        } else {
            $_SESSION['error'] = "Failed to update and block user (ID: $user_id).";
        }
    }

    // Trường hợp: Mở khóa tài khoản
    if (isset($_POST['unblock'])) {
        if ($userController->unblockUser($user_id)) {
            $_SESSION['success'] = "User (ID: $user_id) has been unblocked successfully.";

            // Gửi email cho người dùng
            header("Location: /index.php?page=send-email_user&user_email=" . urlencode($userEmail) . "&type=unblock&csrf_token=" . $_SESSION['csrf_token']);
            exit();
        } else {
            $_SESSION['error'] = "Failed to unblock user (ID: $user_id).";
        }
    }

    // Trường hợp: Xóa tài khoản
    if (isset($_POST['delete_user'])) {

        if ($userController->deleteUser($user_id)) {
            $_SESSION['success'] = "User (ID: $user_id) has been deleted successfully.";

            // Gửi email cho người dùng
            header("Location: /index.php?page=send-email_user&user_email=" . urlencode($userEmail) . "&type=delete&csrf_token=" . $_SESSION['csrf_token']);
            exit();
        } else {
            $_SESSION['error'] = "Failed to delete user (ID: $user_id).";
        }
    }

    // Reset CSRF token ngay trước khi điều hướng hoặc xử lý
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    // Các trường hợp sai sẽ điều hướng về admin:
    header("Location: /admin");
    exit();
}

?>

<div class="w-full lg:w-8/12 flex items-center justify-center mx-auto my-10">
    <div class="flex-grow border-t-2 border-gray-700"></div>
    <h1 class="mx-6 text-3xl md:text-4xl font-extrabold text-gray-700 drop-shadow-lg whitespace-nowrap">
        <span class="text-gray-700 text-4xl font-bold">[</span>
        Accounts Management
        <span class="text-gray-700 text-4xl font-bold">]</span>
    </h1>
    <div class="flex-grow border-t-2 border-gray-700"></div>
</div>

<!-- Form Block User (mặc định ẩn) -->
<div id="block-user-form" class="space-y-8 mb-8 hidden mx-auto w-full lg:w-10/12">
    <form action="" method="POST" class="space-y-8 bg-white p-8 rounded-xl shadow-lg border-2 border-yellow-400">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <!-- Input ẩn chứa user_id -->
        <input type="hidden" name="user_id" id="blockUserId">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- User ID (hiển thị, không gửi) -->
            <div>
                <label class="block text-blue-700 font-semibold">User ID</label>
                <input type="text" id="blockUserDisplayId" class="w-full h-12 p-3 border border-gray-300 rounded-lg bg-gray-100" disabled>
            </div>

            <!-- Role (hiển thị, không chỉnh sửa) -->
            <div>
                <label class="block text-blue-700 font-semibold">Role</label>
                <input type="text" id="blockUserRole" class="w-full h-12 p-3 border border-gray-300 rounded-lg bg-gray-100" disabled>
            </div>

            <!-- Blocked Until (hiển thị, không chỉnh sửa) -->
            <div>
                <label class="block text-blue-700 font-semibold">Unblock Date</label>
                <input type="text" id="blockUserBlockedUntil" class="w-full h-12 p-3 border border-gray-300 rounded-lg bg-gray-100" disabled>
            </div>

            <!-- Name -->
            <div>
                <label class="block text-blue-700 font-semibold">Name</label>
                <input type="text" name="name" id="blockUserName" class="w-full h-12 p-3 border border-gray-300 rounded-lg" required>
            </div>

            <!-- Email -->
            <div>
                <label class="block text-blue-700 font-semibold">Email</label>
                <input type="email" name="email" id="blockUserEmail" class="w-full h-12 p-3 border border-gray-300 rounded-lg" required>
            </div>

            <!-- Block Days và Blocked Until trên cùng một dòng -->
            <div class="flex items-center space-x-4">
                <!-- Block Days -->
                <div class="flex flex-col">
                    <label class="text-red-500 font-semibold">Block Days</label>
                    <input type="number" name="days" id="blockDays" min="1" value="1"
                        class="w-24 h-12 p-3 border border-gray-300 rounded-lg">
                </div>

                <!-- Blocked Until -->
                <div class="flex flex-col">
                    <label class="text-blue-700 font-semibold text-center">Blocked Until</label>
                    <div id="blockUntilDate"
                        class="flex justify-center items-center text-gray-800 bg-gray-100 w-48 h-12 p-3 rounded-md border border-gray-300 text-sm">
                        Chưa có dữ liệu
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div>
                <label class="block text-blue-700 font-semibold">Address</label>
                <input type="text" name="address" id="blockUserAddress" class="w-full h-12 p-3 border border-gray-300 rounded-lg">
            </div>

            <!-- Phone -->
            <div>
                <label class="block text-blue-700 font-semibold">Phone</label>
                <input type="text" name="phone" id="blockUserPhone" class="w-full h-12 p-3 border border-gray-300 rounded-lg">
            </div>
        </div>

        <div class="flex justify-center space-x-6 mt-6">
            <button type="submit" name="block_user" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg shadow-lg">Confirm</button>
            <button type="button" id="cancelBlock" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg shadow-lg">Cancel</button>
        </div>
    </form>
</div>

<div class="container-fluid mx-auto p-6 bg-white shadow-sm rounded-lg w-full lg:w-11/12 border-2 border-blue-600">
    <!-- Bảng danh sách người dùng -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md">
            <thead>
                <tr class="bg-blue-100 text-blue-900 text-center font-semibold">
                    <th class="px-4 py-3 border-b">ID</th>
                    <th class="px-4 py-3 border-b">Name</th>
                    <th class="px-4 py-3 border-b">Email</th>
                    <th class="px-4 py-3 border-b">Created At</th>
                    <th class="px-4 py-3 border-b">Blocked Until</th>
                    <th class="px-4 py-3 border-b">Action</th>

                </tr>
            </thead>
            <tbody>
                <?php
                $userController = new UserController($conn);
                $users = $userController->getAllUsers();

                if (count($users) > 0):
                    foreach ($users as $user) : ?>
                        <tr class="hover:bg-gray-50 text-center border-b">
                            <td class="px-4 py-3"><?= htmlspecialchars($user['id']) ?></td>
                            <td class="px-4 py-3 text-left"><?= htmlspecialchars($user['name']) ?></td>
                            <td class="px-4 py-3 text-left"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($user['created_at']) ?></td>
                            <td class="px-4 py-3 <?= $user['blocked_until'] ? 'text-red-500 font-bold' : 'text-gray-500' ?>">
                                <?= htmlspecialchars($user['blocked_until'] ?? 'Not Blocked') ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center items-center gap-2">
                                    <!-- Nút khóa (Block) -->
                                    <button class="block-btn bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md"
                                        data-id="<?= htmlspecialchars($user['id']) ?>"
                                        data-name="<?= htmlspecialchars($user['name']) ?>"
                                        data-email="<?= htmlspecialchars($user['email']) ?>"
                                        data-role="<?= htmlspecialchars($user['role']) ?>"
                                        data-phone="<?= htmlspecialchars($user['phone'] ?? 'N/A') ?>"
                                        data-address="<?= htmlspecialchars($user['address'] ?? 'N/A') ?>"
                                        data-block="<?= htmlspecialchars($user['blocked_until'] ?? 'N/A') ?>">
                                        <i class="fas fa-lock"></i>
                                    </button>

                                    <!-- Nút mở khóa (Unblock) & Xóa -->
                                    <form id="userActionForm_<?= $user['id'] ?>" action="" method="POST" class="inline-flex items-center gap-2">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

                                        <?php $isLocked = isset($user['blocked_until']) && strtotime($user['blocked_until']) > time(); ?>
                                        <!-- Hiển thị nút mở khóa nếu thời hạn khóa còn hiệu lực -->
                                        <button type="button" name="unblock" title="Unblock"
                                            class="<?= $isLocked ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-400 cursor-not-allowed' ?> text-white px-3 py-2 rounded-md"
                                            <?= $isLocked ? "onclick=\"confirmAction('unblock', '{$user['id']}')\"" : 'disabled' ?>>
                                            <i class="fas fa-unlock"></i>
                                        </button>

                                        <button type="button" name="delete_user" title="Delete User"
                                            class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-md"
                                            onclick="confirmAction('delete_user', '<?= $user['id'] ?>')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Modal Xác nhận Hành Động (ID duy nhất cho từng user) -->
                                <div id="confirmationModal_<?= $user['id'] ?>" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center z-50 hidden">
                                    <div class="bg-white p-6 rounded-lg shadow-lg w-120">
                                        <h3 id="confirmationMessage_<?= $user['id'] ?>" class="text-lg font-semibold text-gray-800">
                                            <!-- Thông điệp sẽ được cập nhật động -->
                                        </h3>
                                        <p class="mt-2 text-sm text-gray-600">User ID: <span class="font-semibold"><?= $user['id'] ?></span></p>
                                        <div class="mt-4 flex justify-center space-x-4">
                                            <button type="button" onclick="closeConfirmationModal('<?= $user['id'] ?>')"
                                                class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                                                Cancel
                                            </button>
                                            <button type="button" onclick="submitAction('<?= $user['id'] ?>')"
                                                class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                                                Yes
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-gray-500 py-4">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Hàm mở modal với ID người dùng
    function confirmAction(action, userId) {
        const modal = document.getElementById('confirmationModal_' + userId);
        const messageEl = document.getElementById('confirmationMessage_' + userId);

        if (!modal || !messageEl) {
            console.error("Modal hoặc message không tồn tại!", userId);
            return;
        }

        if (action === 'unblock') {
            messageEl.textContent = "Are you sure you want to unblock this user?";
        } else if (action === 'delete_user') {
            messageEl.textContent = "Are you sure you want to delete this user?";
        } else {
            messageEl.textContent = "Are you sure you want to continue this action?";
        }

        modal.setAttribute('data-action', action);
        modal.classList.remove('hidden');
    }

    // Hàm đóng modal
    function closeConfirmationModal(userId) {
        const modal = document.getElementById('confirmationModal_' + userId);
        modal.classList.add('hidden');
    }

    function submitAction(userId) {
        const modal = document.getElementById('confirmationModal_' + userId);
        const action = modal.getAttribute('data-action');
        const form = document.getElementById('userActionForm_' + userId);

        // Xoá input ẩn cũ nếu có
        let existing = document.getElementById('pendingAction_' + userId);
        if (existing) {
            existing.remove();
        }

        // Tạo input mới
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = action;
        input.value = '1';
        input.id = 'pendingAction_' + userId;
        form.appendChild(input);

        // Reset modal (ẩn modal và xoá data-action nếu cần)
        modal.classList.add('hidden');
        modal.removeAttribute('data-action');

        // Gửi form
        form.submit();
    }

    document.addEventListener("DOMContentLoaded", function() {
        const blockButtons = document.querySelectorAll(".block-btn");
        const blockForm = document.getElementById("block-user-form");
        const cancelBlock = document.getElementById("cancelBlock");

        // Các input cần cập nhật giá trị
        const blockUserId = document.getElementById("blockUserId");
        const blockUserDisplayId = document.getElementById("blockUserDisplayId");
        const blockUserName = document.getElementById("blockUserName");
        const blockUserEmail = document.getElementById("blockUserEmail");
        const blockUserRole = document.getElementById("blockUserRole");
        const blockUserPhone = document.getElementById("blockUserPhone");
        const blockUserAddress = document.getElementById("blockUserAddress");
        const blockUserBlockedUntil = document.getElementById("blockUserBlockedUntil");
        const blockDays = document.getElementById("blockDays");

        blockButtons.forEach(button => {
            button.addEventListener("click", function() {
                const userId = this.getAttribute("data-id");
                const userName = this.getAttribute("data-name");
                const userEmail = this.getAttribute("data-email");
                const userRole = this.getAttribute("data-role");
                const userPhone = this.getAttribute("data-phone");
                const userAddress = this.getAttribute("data-address");
                const blockedUntil = this.getAttribute("data-block");

                // Gán giá trị vào form
                blockUserId.value = userId;
                blockUserDisplayId.value = userId;
                blockUserName.value = userName;
                blockUserEmail.value = userEmail;
                blockUserRole.value = userRole;
                blockUserPhone.value = userPhone;
                blockUserAddress.value = userAddress;
                // Nếu không có ngày block, để rỗng để admin tự nhập
                blockUserBlockedUntil.value = (blockedUntil && blockedUntil !== "NULL") ? blockedUntil : "";
                blockDays.value = 1; // Reset số ngày block

                // Hiển thị form
                blockForm.classList.remove("hidden");
            });
        });

        cancelBlock.addEventListener("click", function() {
            blockForm.classList.add("hidden");
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        const blockDaysInput = document.getElementById("blockDays");
        const blockUntilDateSpan = document.getElementById("blockUntilDate");

        function updateBlockedUntil() {
            let days = parseInt(blockDaysInput.value) || 1;
            let now = new Date();
            now.setDate(now.getDate() + days); // Cộng số ngày block vào thời gian hiện tại

            // Lấy thông tin ngày và giờ
            let year = now.getFullYear();
            let month = String(now.getMonth() + 1).padStart(2, "0"); // Định dạng MM
            let day = String(now.getDate()).padStart(2, "0"); // Định dạng DD
            let hours = String(now.getHours()).padStart(2, "0"); // Giờ HH
            let minutes = String(now.getMinutes()).padStart(2, "0"); // Phút MM
            let seconds = String(now.getSeconds()).padStart(2, "0"); // Giây SS

            // Hiển thị ngày và giờ theo định dạng YYYY-MM-DD HH:MM:SS
            let formattedDateTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
            blockUntilDateSpan.textContent = formattedDateTime;
        }

        // Gọi hàm khi trang tải lần đầu
        updateBlockedUntil();

        // Gọi hàm mỗi khi người dùng thay đổi số ngày block
        blockDaysInput.addEventListener("input", updateBlockedUntil);
    });
</script>

<!--------------------------------------- Quản lý Voucherss --------------------------------------->
<?php

// Lấy danh sách các voucher
$stmt = $conn->query("SELECT * FROM vouchers ORDER BY id ASC");
$vouchers = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset-voucher'])) {

    // Lấy danh sách voucher còn hạn
    $stmt = $conn->query("SELECT id, code, expiration_date FROM vouchers WHERE expiration_date IS NOT NULL");
    $vouchers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($vouchers as $voucher) {

        $voucherDate = new DateTime($voucher['expiration_date'], new DateTimeZone('Asia/Ho_Chi_Minh'));
        $now = new DateTime("now", new DateTimeZone('Asia/Ho_Chi_Minh'));

        if ($voucherDate < $now) {

            // Cập nhật trạng thái voucher (hết hạn)
            $updateStmt = $conn->prepare("UPDATE vouchers SET expiration_date = NULL WHERE id = ?");
            $updateStmt->execute([$voucher['id']]);

            // Nếu voucher đã hết hạn, tìm user_id đã nhận voucher này
            $userStmt = $conn->prepare("SELECT user_id FROM user_voucher WHERE voucher_id = ? AND status = 'unused'");
            $userStmt->execute([$voucher['id']]);
            $users = $userStmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($users)) {
                // Gửi thông báo đến từng user có voucher hết hạn
                foreach ($users as $user_id) {
                    $message = "Your voucher " . htmlspecialchars($voucher['code']) . " has expired.";
                    $userController->addNotification($user_id, $message);
                }
            }
        } else {
            // Tính số ngày còn lại
            $interval = $now->diff($voucherDate);
            $daysLeft = $interval->days;
            $hoursLeft = $interval->h;
            $minutesLeft = $interval->i;

            // Lấy danh sách user đã nhận voucher
            $userStmt = $conn->prepare("SELECT user_id FROM user_voucher WHERE voucher_id = ? AND status = 'unused'");
            $userStmt->execute([$voucher['id']]);
            $users = $userStmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($users)) {
                foreach ($users as $user_id) {
                    $message = "Your voucher " . htmlspecialchars($voucher['code']) . " is valid for $daysLeft days, $hoursLeft hours, and $minutesLeft minutes.";
                    $userController->addNotification($user_id, $message);
                }
            }
        }
    }

    $_SESSION['success'] = "Voucher expiration dates have been checked and updated.";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>

<h1 class="text-4xl font-extrabold text-center my-8 text-blue-600 drop-shadow-lg">Vouchers Management</h1>
<div class="container-fluid mx-auto p-6 mb-8 bg-white shadow-sm rounded-lg w-full lg:w-11/12 border-2 border-blue-600">
    <!-- Thêm Voucher -->
    <a href="/admin/add-voucher" class="mb-4 bg-green-500 text-white px-4 py-2 rounded-lg inline-block">+ Add Voucher</a>

    <!-- Reset hạn dùng -->
    <form action="/admin" method="POST" class="inline-block ml-2">
        <button type="submit" name="reset-voucher" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">🔄 Reset Expiration</button>
    </form>

    <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md">
        <thead>
            <tr class="bg-green-100 text-gray-800 text-center uppercase text-sm">
                <th class="px-4 py-3 border-b">ID</th>
                <th class="px-4 py-3 border-b">Code</th>
                <th class="px-4 py-3 border-b">Description</th>
                <th class="px-4 py-3 border-b">Discount</th>
                <th class="px-4 py-3 border-b">Min Order</th>
                <th class="px-4 py-3 border-b">Quantity</th>
                <th class="px-4 py-3 border-b">Expiration</th>
                <th class="px-4 py-3 border-b">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($vouchers)): ?>
                <?php foreach ($vouchers as $v): ?>
                    <tr class="hover:bg-gray-50 border-b text-center" title="<?= htmlspecialchars($v['description']) ?>">
                        <td class="px-4 py-3"><?= htmlspecialchars($v['id']) ?></td>
                        <td class="px-4 py-3 text-red-600 font-semibold"><?= htmlspecialchars($v['code']) ?></td>
                        <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars(substr($v['description'], 0, 15)) ?>...</td>
                        <td class="px-4 py-3 text-green-600 font-semibold">$<?= htmlspecialchars($v['discount_amount']) ?></td>
                        <td class="px-4 py-3 text-blue-600 font-semibold">$<?= htmlspecialchars($v['min_order_value']) ?></td>
                        <td class="px-4 py-3 text-purple-600 font-semibold"><?= htmlspecialchars($v['quantity']) ?></td>
                        <td class="px-4 py-3 text-red-600 font-semibold"><?= htmlspecialchars($v['expiration_date'] ?? '---') ?></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <!-- Nút edit -->
                                <a href="/admin/edit-voucher/id=<?= $v['id'] ?>"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-lg">✏️</a>

                                <!-- Nút xóa voucher -->
                                <button type="button" onclick="openModal(<?= $v['id'] ?>)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg" title="Delete Voucher">❌</button>

                                <!-- Modal xác nhận xóa -->
                                <div id="deleteModal<?= $v['id'] ?>" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center z-50 hidden">
                                    <div class="bg-white p-6 rounded-lg shadow-lg w-120">
                                        <h2 class="text-lg font-semibold">Are you sure you want to delete this voucher?</h2>
                                        <p class="mt-2 text-sm text-gray-600">Voucher ID: <span class="font-semibold"><?= $v['id'] ?></span></p>
                                        <form action="/admin/delete-voucher" method="POST" id="deleteForm<?= $v['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                            <input type="hidden" name="voucher_id" id="voucher_id<?= $v['id'] ?>" value="<?= $v['id'] ?>">
                                            <div class="mt-4 flex justify-center space-x-4">
                                                <button type="button" onclick="closeModal(<?= $v['id'] ?>)" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg">Cancel</button>
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">Delete</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-gray-500 py-4"></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    // Mở modal
    function openModal(voucherId) {
        const modal = document.getElementById('deleteModal' + voucherId);
        const voucherInput = document.getElementById('voucher_id' + voucherId);

        // Đảm bảo rằng modal và input voucher_id có giá trị đúng
        modal.classList.remove('hidden');
        voucherInput.value = voucherId;
    }

    // Đóng modal
    function closeModal(voucherId) {
        const modal = document.getElementById('deleteModal' + voucherId);
        modal.classList.add('hidden');
    }
</script>

<!-- Logout Button (Trigger Modal) -->
<div class="flex justify-center mb-8">
    <button type="button" onclick="openLogoutModal()"
        class="bg-red-500 text-white px-5 py-2 rounded-md hover:bg-red-600 transition duration-200 shadow"
        title="Logout">Logout</button>
</div>
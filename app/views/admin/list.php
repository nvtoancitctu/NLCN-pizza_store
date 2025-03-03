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

// Kiểm tra xem form đã được submit chưa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_order'])) {
    // Lấy dữ liệu từ form, kiểm tra sự tồn tại và làm sạch dữ liệu đầu vào
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : null;
    $customer_name = isset($_POST['customer_name']) ? trim(htmlspecialchars($_POST['customer_name'])) : '';
    $total_price = isset($_POST['total']) ? floatval($_POST['total']) : 0;
    $order_status = isset($_POST['status']) ? trim(htmlspecialchars($_POST['status'])) : '';

    // Kiểm tra xem order_id có hợp lệ không
    if ($order_id && !empty($customer_name) && $total_price >= 0 && !empty($order_status)) {
        // Gọi phương thức updateOrder trong controller
        $updated = $orderController->updateOrder($order_id, $customer_name, $total_price, $order_status);
        $_SESSION['success'] = "Order $order_id is updated successfully.";
        header("Location: /admin/list");
        exit;
    }
}

// Cập nhật số ngày khóa tài khoản
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {

    $user_id = $_POST['user_id'];

    // Cập nhật số ngày block user 
    if (isset($_POST['block_user'])) {
        $_POST['days'] = intval($_POST['days']);
        $days = $_POST['days'];

        if ($days > 0) {
            $userController->blockUser($user_id, $days);
            $_SESSION['success'] = "User $user_id is blocked for $days days successfully.";
        } else {
            $_SESSION['success'] = "Please enter a valid block duration (minimum 1 day).";
        }
    } elseif (isset($_POST['check_block'])) {
        // Kiểm tra block days
        $message = $userController->unblockUser($user_id);
        $_SESSION['success'] = $message;
    }

    header('Location: /admin/list');
    exit();
}

?>

<!-- Hiển thị thông báo thành công nếu có -->
<?php if (!empty($success)): ?>
    <script>
        alert("<?= addslashes($success) ?>");
    </script>
<?php endif; ?>

<!-- Quản lý sản phẩm, thống kê, xuất file csv -->
<h1 class="text-4xl font-extrabold text-center my-10 text-blue-700 drop-shadow-lg">Product Management</h1>

<div class="container-fluid mx-auto p-6 bg-white shadow-xl rounded-lg w-full lg:w-11/12 border-2 border-blue-600">
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
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <button class="btn btn-outline-success me-4 mb-2" onclick="window.location.href='/admin/export-products'">Export to CSV</button>

                <form method="POST" action="/admin/import-products" enctype="multipart/form-data" class="d-flex align-items-center flex-wrap">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
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
                                    <a href="/admin/edit-product/id=<?= $product['id'] ?>" class="btn btn-warning me-2">Edit</a>
                                    <form action="/admin/delete" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
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

<!-- Shipment Management -->
<h1 class="text-4xl font-extrabold text-center my-8 text-blue-700 drop-shadow-lg">Order Management</h1>

<!-- Form Chỉnh Sửa Đơn Hàng (Mặc Định Ẩn) -->
<div id="edit-order-form" class="space-y-6 mb-8 hidden mx-auto w-full lg:w-11/12">
    <form action="/admin/list" method="POST" class="space-y-6 bg-white p-6 rounded-lg shadow-md alert alert-info">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <!-- Bố cục 2 cột -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Cột 1: Customer Name, Total Price, Status -->
            <div class="space-y-4">
                <div>
                    <label for="editOrderId" class="block text-blue-700 font-semibold">Order ID</label>
                    <input type="text" id="editOrderId" name="order_id" class="w-full p-3 border border-gray-300 rounded-md bg-gray-100" readonly>
                </div>
                <div>
                    <label for="editPaymentMethod" class="block text-blue-700 font-semibold">Payment Method</label>
                    <input type="text" id="editPaymentMethod" class="w-full p-3 border border-gray-300 rounded-md bg-gray-100" readonly>
                    <input type="hidden" name="payment_method" id="hiddenPaymentMethod">
                </div>
                <div>
                    <label for="editCustomerName" class="block text-red-500 font-semibold">Customer Name</label>
                    <input type="text" id="editCustomerName" name="customer_name" class="w-full p-3 border border-gray-300 rounded-md" required>
                </div>
                <div>
                    <label for="editTotalPrice" class="block text-red-500 font-semibold">Total Price</label>
                    <input type="number" step="0.01" id="editTotalPrice" name="total" class="w-full p-3 border border-gray-300 rounded-md" required>
                </div>
                <div>
                    <label for="editStatus" class="block text-red-500 font-semibold">Status</label>
                    <select id="editStatus" name="status" class="w-full p-3 border border-gray-300 rounded-md">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

            </div>

            <!-- Cột 2: Bank Transfer Image -->
            <div class="space-y-4">
                <div>
                    <label class="block text-blue-700 font-semibold">Bank Transfer Image</label>
                    <div id="bankTransferImageContainer" class="w-full flex items-center justify-center hidden" style="min-height:200px;">
                        <img id="bankTransferImage" class="border rounded-md shadow-md h-auto w-4/12 mt-8">
                    </div>
                </div>
            </div>
        </div>

        <!-- Nút Thao Tác -->
        <div class="flex justify-center space-x-4 mt-6">
            <button type="submit" name="edit_order" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-6 rounded-lg shadow-md">Save Changes</button>
            <button type="button" id="cancelEdit" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-6 rounded-lg shadow-md">Cancel</button>
        </div>
    </form>
</div>

<!-- Bảng chi tiết đơn hàng -->
<div class="container-fluid mx-auto p-6 bg-white shadow-xl rounded-lg w-full lg:w-11/12 border-2 border-blue-600">
    <div class="table-responsive">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md">
            <thead>
                <tr class="bg-gray-100 text-gray-800 text-center">
                    <th class="px-3 py-2 border-b">Order ID</th>
                    <th class="px-3 py-2 border-b">Customer</th>
                    <th class="px-3 py-2 border-b">Total Price</th>
                    <th class="px-3 py-2 border-b">Status</th>
                    <th class="px-3 py-2 border-b">Created At</th>
                    <th class="px-3 py-2 border-b">Payment Method</th>
                    <th class="px-3 py-2 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $orderController = new OrderController($conn);
                $orders = $orderController->getAllOrders();

                if (count($orders) > 0):
                    foreach ($orders as $order): ?>
                        <tr class="hover:bg-gray-50 text-center">
                            <td class="px-3 py-2 border-b"><?= htmlspecialchars($order['id']) ?></td>
                            <td class="px-3 py-2 border-b"><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td class="px-3 py-2 border-b text-green-600 font-bold">$<?= number_format($order['total'], 2) ?></td>
                            <td class="px-3 py-2 border-b">
                                <?php if ($order['status'] === 'completed'): ?>
                                    <span class="text-green-500 font-bold">Completed</span>
                                <?php elseif ($order['status'] === 'pending'): ?>
                                    <span class="text-yellow-500 font-bold">Pending</span>
                                <?php elseif ($order['status'] === 'processing'): ?>
                                    <span class="text-blue-500 font-bold">Processing</span>
                                <?php else: ?>
                                    <span class="text-red-500 font-bold">Cancelled</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-2 border-b"><?= htmlspecialchars($order['created_at']) ?></td>
                            <td class="px-3 py-2 border-b"><?= htmlspecialchars($order['payment_method']) ?></td>
                            <td class="px-3 py-2 border-b">
                                <div class="d-flex justify-content-center flex-wrap">
                                    <button type="button" class="btn btn-warning me-2 edit-btn"
                                        data-id="<?= $order['id'] ?>"
                                        data-customer="<?= htmlspecialchars($order['customer_name']) ?>"
                                        data-total="<?= $order['total'] ?>"
                                        data-status="<?= $order['status'] ?>"
                                        data-payment="<?= htmlspecialchars($order['payment_method']) ?>"
                                        data-image="<?= isset($order['images']) ? htmlspecialchars($order['images']) : '' ?>">
                                        Edit
                                    </button>
                                    <form action="/admin/delete-order" method="POST" onsubmit="return confirm('Are you sure you want to delete this order?');">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-gray-500 py-4">No orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
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
            bankTransferImage.src = "/banking_images/" + image;
            bankTransferImageContainer.classList.remove("hidden");
        } else {
            bankTransferImageContainer.classList.add("hidden");
            bankTransferImage.src = "";
        }

        // Hiển thị form
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

<!-- Quản lý tài khoản -->
<h1 class="text-4xl font-extrabold text-center my-8 text-blue-700 drop-shadow-lg">Account Management</h1>

<!-- Form Block User (Ẩn Mặc Định) -->
<div id="block-user-form" class="space-y-6 mb-8 hidden mx-auto w-full lg:w-11/12">
    <form action="/admin/list" method="POST" class="space-y-6 bg-white p-6 rounded-lg shadow-md alert alert-info">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="user_id" id="blockUserId">

        <!-- Grid 2 cột -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Cột 1: User ID, Name, Role -->
            <div class="space-y-4">
                <div>
                    <label class="block text-blue-700 font-semibold">User ID</label>
                    <input type="text" id="blockUserDisplayId" class="w-full p-3 border border-gray-300 rounded-md bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-blue-700 font-semibold">Role</label>
                    <input type="text" id="blockUserRole" class="w-full p-3 border border-gray-300 rounded-md bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-blue-700 font-semibold">Blocked Until</label>
                    <input type="text" id="blockUserBlockedUntil" class="w-full p-3 border border-gray-300 rounded-md bg-gray-100" readonly>
                </div>
            </div>

            <!-- Cột 2: Email, Blocked Until, Block Days -->
            <div class="space-y-4">
                <div>
                    <label class="block text-blue-700 font-semibold">Name</label>
                    <input type="text" id="blockUserName" class="w-full p-3 border border-gray-300 rounded-md bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-blue-700 font-semibold">Email</label>
                    <input type="text" id="blockUserEmail" class="w-full p-3 border border-gray-300 rounded-md bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-red-500 font-semibold">Block Days</label>
                    <input type="number" name="days" id="blockDays" min="1" class="w-full p-3 border border-gray-300 rounded-md">
                </div>
            </div>
        </div>
        <!-- Nút Confirm và Cancel -->
        <div class="flex justify-center space-x-4 mt-6">
            <button type="submit" name="block_user" class="bg-red-500 hover:bg-red-600 text-white py-2 px-6 rounded-lg shadow-md">Confirm Block</button>
            <button type="button" id="cancelBlock" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-6 rounded-lg shadow-md">Cancel</button>
            <button type="submit" name="check_block" class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-6 rounded-lg shadow-md">Check</button>
        </div>
    </form>
</div>

<div class="container-fluid mx-auto p-6 mb-8 bg-white shadow-xl rounded-lg w-full lg:w-11/12 border-2 border-blue-600">
    <!-- Bảng danh sách người dùng -->
    <div class="table-responsive">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md">
            <thead>
                <tr class="bg-gray-100 text-gray-800 text-center">
                    <th class="px-3 py-2 border-b">ID</th>
                    <th class="px-3 py-2 border-b">Name</th>
                    <th class="px-3 py-2 border-b">Email</th>
                    <th class="px-3 py-2 border-b">Phone</th>
                    <th class="px-3 py-2 border-b">Address</th>
                    <th class="px-3 py-2 border-b">Created At</th>
                    <th class="px-3 py-2 border-b">Blocked Until</th>
                    <th class="px-3 py-2 border-b">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $userController = new UserController($conn);
                $users = $userController->getAllUsers();

                if (count($users) > 0):
                    foreach ($users as $user) : ?>
                        <tr class="hover:bg-gray-50 text-center">
                            <td class="px-3 py-2 border-b"><?= htmlspecialchars($user['id']) ?></td>
                            <td class="px-3 py-2 border-b"><?= htmlspecialchars($user['name']) ?></td>
                            <td class="px-3 py-2 border-b"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-3 py-2 border-b"><?= htmlspecialchars($user['phone'] ?? 'NULL') ?></td>
                            <td class="px-3 py-2 border-b"><?= htmlspecialchars($user['address'] ?? 'NULL') ?></td>
                            <td class="px-3 py-2 border-b"><?= htmlspecialchars($user['created_at']) ?></td>
                            <td class="px-3 py-2 border-b text-red-500 font-bold"><?= htmlspecialchars($user['blocked_until'] ?? 'NULL') ?></td>
                            <td class="px-3 py-2 border-b">
                                <button class="block-btn bg-red-500 text-white px-4 py-2 rounded-md"
                                    data-id="<?= htmlspecialchars($user['id']) ?>"
                                    data-name="<?= htmlspecialchars($user['name']) ?>"
                                    data-email="<?= htmlspecialchars($user['email']) ?>"
                                    data-role="<?= htmlspecialchars($user['role']) ?>"
                                    data-block="<?= htmlspecialchars($user['blocked_until'] ?? 'NULL') ?>">
                                    Block
                                </button>
                            </td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-gray-500 py-4">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
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
        const blockUserBlockedUntil = document.getElementById("blockUserBlockedUntil");
        const blockDays = document.getElementById("blockDays");

        blockButtons.forEach(button => {
            button.addEventListener("click", function() {
                const userId = this.getAttribute("data-id");
                const userName = this.getAttribute("data-name");
                const userEmail = this.getAttribute("data-email");
                const userRole = this.getAttribute("data-role");
                const blockedUntil = this.getAttribute("data-block");

                // Gán giá trị vào form
                blockUserId.value = userId;
                blockUserDisplayId.value = userId;
                blockUserName.value = userName;
                blockUserEmail.value = userEmail;
                blockUserRole.value = userRole;
                blockUserBlockedUntil.value = (blockedUntil !== "NULL" && blockedUntil) ? blockedUntil : "Not Blocked";
                blockDays.value = ""; // Reset ngày block

                // Hiển thị form block
                blockForm.classList.remove("hidden");
            });
        });

        cancelBlock.addEventListener("click", function() {
            blockForm.classList.add("hidden"); // Ẩn form khi bấm cancel
        });
    });
</script>

<!-- Quản lý phản hồi -->
<?php

// Lấy danh sách phản hồi
$stmt = $conn->query("SELECT * FROM feedback ORDER BY created_at ASC");
$feedbacks = $stmt->fetchAll();

// Xử lý phản hồi của admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['response'], $_POST['id'])) {

    $id = $_POST['id'];
    $response = $_POST['response'];

    $stmt = $conn->prepare("UPDATE feedback SET response = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$response, $id]);

    $_SESSION['success'] = "Feedback $id is responsed successfully.";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
<h1 class="text-4xl font-extrabold text-center my-8 text-blue-700 drop-shadow-lg">Feedback Management</h1>
<div class="container-fluid mx-auto p-6 mb-8 bg-white shadow-xl rounded-lg w-full lg:w-11/12 border-2 border-blue-600">
    <div class="table-responsive">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md">
            <thead>
                <tr class="bg-gray-100 text-gray-800 text-center">
                    <th class="px-3 py-2 border-b">ID</th>
                    <th class="px-3 py-2 border-b">Sender</th>
                    <th class="px-3 py-2 border-b">Email</th>
                    <th class="px-3 py-2 border-b">Order</th>
                    <th class="px-3 py-2 border-b">Message</th>
                    <th class="px-3 py-2 border-b">Created</th>
                    <th class="px-3 py-2 border-b">Response</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($feedbacks)): ?>
                    <?php foreach ($feedbacks as $fb): ?>
                        <tr class="hover:bg-gray-50 text-center">
                            <td class="px-3 py-2 border-b"><?= htmlspecialchars($fb['id']) ?></td>
                            <td class="px-3 py-2 border-b"><?= htmlspecialchars($fb['name']) ?></td>
                            <td class="px-3 py-2 border-b"><?= htmlspecialchars($fb['email']) ?></td>
                            <td class="px-3 py-2 border-b">#<?= htmlspecialchars($fb['order_id']) ?></td>
                            <td class="px-3 py-2 border-b text-left text-sm">
                                <?= nl2br(htmlspecialchars($fb['message'])) ?>
                            </td>
                            <td class="px-3 py-2 border-b"> <?= htmlspecialchars($fb['created_at']) ?> </td>
                            <td class="px-3 py-2 border-b">
                                <form method="post" class="flex items-center space-x-2">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($fb['id']) ?>">
                                    <textarea name="response" required class="w-full p-2 border rounded-lg"><?= htmlspecialchars($fb['response'] ?? '') ?></textarea>
                                    <button type="submit" class="bg-blue-500 text-white px-3 py-2 rounded-md">Reply</button>
                                </form>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-gray-500 py-4">No feedback found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Logout Button -->
<form method="POST" class="flex justify-center mb-8">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <button type="submit" name="logout" onclick="confirmLogout(event)"
        class="bg-red-500 text-white px-5 py-2 rounded-md hover:bg-red-600 transition duration-200 shadow">
        Logout</button>
</form>

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
</script>
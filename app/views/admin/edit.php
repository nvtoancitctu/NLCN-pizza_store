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

$categories = $productController->getAllCategoryNamesExceptPizza();
$product_id = $_GET['id'];
$product = $productController->getProductDetails($product_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Kiểm tra token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo "<h1 class='text-center mt-5'>Forbidden: Invalid CSRF token</h1>";
        exit();
    } else {
        unset($_SESSION['csrf_token']);
    }

    // Lấy dữ liệu từ form
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $stock_quantity = $_POST['stock_quantity'];

    $note = trim($_POST['note']) ?? null;
    $discount = !empty($_POST['discount']) ? $_POST['discount'] : null;
    $discount_end_time = !empty($_POST['discount_end_time']) ? $_POST['discount_end_time'] : null;

    $currentProduct = $productController->getProductDetails($product_id);
    $image = $currentProduct['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        if (in_array(strtolower($file_ext), $allowed)) {
            // Đặt tên mới cho ảnh để tránh trùng tên
            $newImageName = basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], "images/product/$newImageName")) {
                $image = $newImageName; // Cập nhật đường dẫn ảnh mới nếu tải lên thành công
            } else {
                $error = "Failed to upload the image. Please try again.";
            }
        } else {
            $error = "Invalid file format. Only JPG, JPEG, PNG, GIF and WEBP files are allowed.";
        }
    }

    // Chạy cập nhật sản phẩm trong mọi trường hợp (dù có hoặc không có ảnh mới)
    $productController->updateProduct($product_id, $name, $description, $price, $image, $category_id, $stock_quantity, $note, $discount, $discount_end_time);

    $_SESSION['success'] = "Product (ID: $product_id) has been updated successfully!";
    $_SESSION['limit'] = $productController->countProducts();
    $_SESSION['page'] = 1;

    header("Location: /admin/list");
    exit();
}

?>

<h1 class="text-4xl font-extrabold text-center my-6 text-blue-700">Edit Product</h1>
<div class="flex justify-center mb-8">
    <div class="w-full max-w-4xl rounded-lg border-2 border-blue-700 bg-gray-50 shadow-lg p-6">
        <form action="/admin/edit-product/id=<?= htmlspecialchars($product['id']) ?>" method="POST" enctype="multipart/form-data"
            class="bg-gray-50 border border-gray-400 rounded-lg px-8 pt-6 pb-8">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Cột 1 -->
                <div>
                    <div class="mb-4">
                        <label for="name" class="block text-blue-700 text-sm font-medium mb-2"><i class="fas fa-tag mr-2"></i>Product Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>"
                            class="border border-gray-400 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="price" class="block text-blue-700 text-sm font-medium mb-2"><i class="fas fa-dollar-sign mr-2"></i>Price</label>
                        <input type="number" name="price" value="<?= htmlspecialchars($product['price']) ?>"
                            class="border border-gray-400 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500" min="0" step="0.01" placeholder="Enter price (e.g., 15.50)" required>
                    </div>
                    <div class="mb-4">
                        <label for="category_id" class="block text-blue-700 text-sm font-medium mb-2"><i class="fas fa-list mr-2"></i>Category</label>
                        <select name="category_id" class="border border-gray-400 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category['id']) ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="stock_quantity" class="block text-blue-700 text-sm font-medium mb-2"><i class="fas fa-boxes mr-2"></i>Stock Quantity</label>
                        <input type="number" name="stock_quantity" value="<?= htmlspecialchars($product['stock_quantity']) ?>"
                            class="border border-gray-400 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500" min="0" required>
                    </div>
                </div>
                <!-- Cột 2 -->
                <div>
                    <div class="mb-4">
                        <label for="note" class="block text-blue-700 text-sm font-medium mb-2"><i class="fas fa-sticky-note mr-2"></i>Note</label>
                        <input type="text" name="note" value="<?= htmlspecialchars($product['note']) ?>"
                            class="border border-gray-400 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="discount" class="block text-blue-700 text-sm font-medium mb-2"><i class="fas fa-percentage mr-2"></i>Discount Price</label>
                        <input type="number" name="discount" value="<?= htmlspecialchars($product['discount']) ?>"
                            class="border border-gray-400 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500" min="0" step="0.01" placeholder="Enter discount (e.g., 15.50)">
                    </div>
                    <div class="mb-4">
                        <label for="discount_end_time" class="block text-blue-700 text-sm font-medium mb-2"><i class="fas fa-clock mr-2"></i>Discount End Time (UTC)</label>
                        <input type="datetime-local" id="discount_end_time" name="discount_end_time" value="<?= htmlspecialchars($product['discount_end_time']) ?>"
                            class="border border-gray-400 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-blue-700 text-sm font-medium mb-2">
                            <i class="fas fa-image mr-2"></i>Product Image
                        </label>
                        <div class="flex items-center gap-6">
                            <!-- Ảnh cũ -->
                            <?php if (!empty($product['image'])): ?>
                                <img src="/images/product/<?= htmlspecialchars($product['image']) ?>" alt="Product Image"
                                    class="w-32 h-32 object-cover border rounded-lg shadow">
                            <?php else: ?>
                                <p class="text-sm text-gray-600">No image uploaded.</p>
                            <?php endif; ?>

                            <!-- Mũi tên -->
                            <i class="fas fa-arrow-right text-gray-500 text-2xl"></i>

                            <!-- Preview ảnh mới -->
                            <img id="image-preview" class="hidden w-32 h-32 object-cover rounded-lg border">
                        </div>
                        <!-- Upload ảnh mới -->
                        <div class="flex items-center mt-2">
                            <input type="file" name="image" id="image" class="hidden" onchange="updateFileName(this)">
                            <label for="image"
                                class="mr-2 border border-gray-200 rounded-lg px-4 py-2 text-gray-600 cursor-pointer hover:bg-blue-100 flex items-center space-x-2">
                                <i class="fas fa-upload"></i>
                                <span>Upload</span>
                            </label>
                            <span id="file-name" class="text-gray-500">No file chosen</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Textarea Description -->
            <div class="mb-4">
                <label for="description" class="block text-blue-700 text-sm font-medium mb-2"><i class="fas fa-align-left mr-2"></i>Description</label>
                <textarea name="description" class="border border-gray-400 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>
            <!-- Button Actions -->
            <div class="flex justify-center space-x-4">
                <button type="button" class="flex items-center bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-all duration-200"
                    onclick="window.location.href='/admin/list'">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Admin
                </button>
                <button type="submit" class="flex items-center bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-all duration-200">
                    <i class="fas fa-save mr-2"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function updateFileName(input) {
        const file = input.files[0];
        if (file) {
            document.getElementById("file-name").innerText = file.name;
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById("image-preview");
                img.src = e.target.result;
                img.classList.remove("hidden");
            };
            reader.readAsDataURL(file);
        } else {
            document.getElementById("file-name").innerText = "No file chosen";
            document.getElementById("image-preview").classList.add("hidden");
        }
    }
</script>
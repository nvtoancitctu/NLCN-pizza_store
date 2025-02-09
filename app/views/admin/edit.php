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

$productController = new ProductController($conn);
$categories = $productController->getDistinctCategories();
$product_id = $_GET['id'];
$product = $productController->getProductDetails($product_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo "<h1 class='text-center mt-5'>Forbidden: Invalid CSRF token</h1>";
        exit();
    }

    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];

    $discount = !empty($_POST['discount']) ? $_POST['discount'] : null;
    $discount_end_time = !empty($_POST['discount_end_time']) ? $_POST['discount_end_time'] : null;

    $currentProduct = $productController->getProductDetails($product_id);
    $image = $currentProduct['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        if (in_array(strtolower($file_ext), $allowed)) {
            // Đặt tên mới cho ảnh để tránh trùng tên
            $newImageName = basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], "images/$newImageName")) {
                $image = $newImageName; // Cập nhật đường dẫn ảnh mới nếu tải lên thành công
            } else {
                $error = "Failed to upload the image. Please try again.";
            }
        } else {
            $error = "Invalid file format. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    }

    // Chạy cập nhật sản phẩm trong mọi trường hợp (dù có hoặc không có ảnh mới)
    $productController->updateProduct($product_id, $name, $description, $price, $image, $category_id, $discount, $discount_end_time);
    $_SESSION['success'] = "Product $product_id has been updated successfully!";
    $_SESSION['limit'] = $productController->countProducts();
    $_SESSION['page'] = 1;
    header("Location: /admin/list");
    exit();
}

?>

<h1 class="text-4xl font-extrabold text-center my-6 text-blue-700">Edit Product</h1>

<div class="text-center mb-4">
    <button type="button" class="inline-block bg-green-500 text-white px-5 py-2 rounded-full hover:bg-purple-600 transition-all duration-200"
        onclick="window.location.href='/admin/list'">Back to Admin</button>
</div>

<div class="flex justify-center mb-8">
    <div class="w-full max-w-4xl">
        <form action="/admin/edit/id=<?= htmlspecialchars($product['id']) ?>" method="POST" enctype="multipart/form-data" class="bg-gray-50 border border-gray-200 rounded-lg px-8 pt-6 pb-8">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Cột 1 -->
                <div>
                    <div class="mb-4">
                        <label for="name" class="block text-gray-800 text-sm font-medium mb-2">Product Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label for="price" class="block text-gray-800 text-sm font-medium mb-2">Price</label>
                        <input type="number" name="price" value="<?= htmlspecialchars($product['price']) ?>" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:border-blue-500" min="0" step="0.01" placeholder="Enter price (e.g., 15.50)" required>
                    </div>
                    <div class="mb-4">
                        <label for="category_id" class="block text-gray-800 text-sm font-medium mb-2">Category</label>
                        <select name="category_id" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:border-blue-500" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category['id']) ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <!-- Cột 2 -->
                <div>
                    <div class="mb-4">
                        <label for="discount" class="block text-gray-800 text-sm font-medium mb-2">Discount Price</label>
                        <input type="number" name="discount" value="<?= htmlspecialchars($product['discount']) ?>" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:border-blue-500" min="0" step="0.01" placeholder="Enter discount (e.g., 15.50)">
                    </div>
                    <div class="mb-4">
                        <label for="discount_end_time" class="block text-gray-800 text-sm font-medium mb-2">Discount End Time (UTC)</label>
                        <input type="datetime-local" id="discount_end_time" name="discount_end_time" value="<?= htmlspecialchars($product['discount_end_time']) ?>" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="image" class="block text-gray-800 text-sm font-medium mb-2">Product Image</label>

                        <!-- Hiển thị ảnh cũ -->
                        <?php if (!empty($product['image'])): ?>
                            <div class="mb-3">
                                <img src="/images/<?= htmlspecialchars($product['image']) ?>" alt="Product Image" class="w-32 h-32 object-cover border rounded-lg shadow">
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 mb-3">No image uploaded for this product.</p>
                        <?php endif; ?>

                        <div class="flex items-center">
                            <input type="file" name="image" id="image" class="hidden" onchange="updateFileName(this)">
                            <label for="image" class="border border-gray-300 rounded-lg w-full py-2 px-3 text-gray-600 text-center cursor-pointer hover:bg-blue-50">
                                <span id="file-name">Choose an image...</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Textarea Description -->
            <div class="mb-4">
                <label for="description" class="block text-gray-800 text-sm font-medium mb-2">Description</label>
                <textarea name="description" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:border-blue-500"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>
            <!-- Button Submit -->
            <div class="flex justify-center">
                <button type="submit" class="bg-blue-500 text-white py-2 px-6 rounded-lg hover:bg-blue-600 transition-all duration-200">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function updateFileName(input) {
        const fileName = input.files.length > 0 ? input.files[0].name : "Choose an image...";
        document.getElementById("file-name").innerText = fileName;
    }
</script>
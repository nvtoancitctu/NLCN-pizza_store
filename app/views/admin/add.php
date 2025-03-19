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

$categories = $productController->getDistinctCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo "<h1 class='text-center mt-5'>Forbidden: Invalid CSRF token</h1>";
        exit();
    }

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $stock_quantity = $_POST['stock_quantity'];

    $note = trim($_POST['note']);
    $discount = $_POST['discount'];
    $discount_end_time = $_POST['discount_end_time'];
    $image = $_POST['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        if (in_array(strtolower($file_ext), $allowed)) {
            $image = $_FILES['image']['name'];
            if (move_uploaded_file($_FILES['image']['tmp_name'], "images/$image")) {
                // Image uploaded successfully
            } else {
                $error = "Failed to upload the image. Please try again.";
            }
        } else {
            $error = "Invalid file format. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    }

    $productController->createProduct($name, $description, $price, $image, $category_id, $stock_quantity, $note, $discount, $discount_end_time);
    $_SESSION['success'] = "Product $name has been added successfully!";
    $_SESSION['limit'] = $productController->countProducts();
    $_SESSION['page'] = 1;
    header("Location: /admin/list");
    exit();
}
?>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<h1 class="text-4xl font-extrabold text-center my-6 text-blue-700">Add New Product</h1>
<div class="flex justify-center mb-8">
    <div class="w-full max-w-4xl rounded-lg border-2 border-blue-400 p-6 bg-white">
        <form action="/admin/add" method="POST" enctype="multipart/form-data" class="space-y-6">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Cột 1 -->
                <div>
                    <div class="mb-4 relative">
                        <label for="name" class="block text-red-500 text-sm font-medium mb-2"><i class="fas fa-pizza-slice mr-2"></i>Product Name</label>
                        <input type="text" name="name" class="border border-gray-200 rounded-lg w-full py-2 px-3 " required>
                    </div>
                    <div class="mb-4">
                        <label for="price" class="block text-red-500 text-sm font-medium mb-2"><i class="fas fa-dollar-sign mr-2"></i>Price</label>
                        <input type="number" name="price" class="border border-gray-200 rounded-lg w-full py-2 px-3 " min="0.01" step="0.01" placeholder="e.g., 15.50" required>
                    </div>
                    <div class="mb-4">
                        <label for="category_id" class="block text-red-500 text-sm font-medium mb-2"><i class="fas fa-list-alt mr-2"></i>Category</label>
                        <select name="category_id" class="border border-gray-200 rounded-lg w-full py-2 px-3 " required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category['id']) ?>"> <?= htmlspecialchars($category['name']) ?> </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="">
                        <label for="stock_quantity" class="block text-red-500 text-sm font-medium mb-2"><i class="fas fa-boxes mr-2"></i>Stock Quantity</label>
                        <input type="number" name="stock_quantity" value="1"
                            class="border border-gray-400 rounded-lg w-full py-2 px-3 focus:outline-none" min="1" required>
                    </div>
                </div>
                <!-- Cột 2 -->
                <div>
                    <div class="mb-4">
                        <label for="note" class="block text-green-500 text-sm font-medium mb-2"><i class="fas fa-sticky-note mr-2"></i>Note</label>
                        <input type="text" name="note" class="border border-gray-200 rounded-lg w-full py-2 px-3">
                    </div>
                    <div class="mb-4">
                        <label for="discount" class="block text-green-500 text-sm font-medium mb-2"><i class="fas fa-tags mr-2"></i>Discount Price</label>
                        <input type="number" name="discount" class="border border-gray-200 rounded-lg w-full py-2 px-3 " min="0" step="0.01" placeholder="e.g., 10.00">
                    </div>
                    <div class="mb-4">
                        <label for="discount_end_time" class="block text-green-500 text-sm font-medium mb-2"><i class="fas fa-clock mr-2"></i>Discount End Time</label>
                        <input type="datetime-local" name="discount_end_time" class="border border-gray-200 rounded-lg w-full py-2 px-3 ">
                    </div>
                    <div class="">
                        <label for="image" class="block text-red-500 text-sm font-medium mb-2"><i class="fas fa-image mr-2"></i>Product Image</label>
                        <div class="flex items-center gap-4">
                            <input type="file" name="image" id="image" class="hidden" onchange="updateFileName(this)" required>
                            <label for="image" class="border border-gray-200 rounded-lg px-4 py-2 text-gray-600 cursor-pointer hover:bg-blue-100">
                                <i class="fas fa-upload"></i>
                            </label>
                            <span id="file-name" class="text-gray-500">No file chosen</span>
                        </div>
                        <div class="mt-2">
                            <img id="image-preview" class="hidden w-32 h-32 object-cover rounded-lg" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label for="description" class="block text-red-500 text-sm font-medium mb-2"><i class="fas fa-align-left mr-2"></i>Description</label>
                <textarea name="description" class="border border-gray-200 rounded-lg w-full py-2 px-3 " placeholder="Optional" required></textarea>
            </div>
            <!-- Button Actions -->
            <div class="flex justify-center space-x-4">
                <button type="button" class="bg-green-400 text-white px-4 py-2 rounded-lg hover:bg-green-500 flex items-center transition-all duration-200" onclick="window.location.href='/admin/list'">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Admin
                </button>
                <button type="submit" class="bg-blue-400 text-white px-4 py-2 rounded-lg hover:bg-blue-500 flex items-center transition-all duration-200">
                    <i class="fas fa-plus mr-2"></i> Add new product
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
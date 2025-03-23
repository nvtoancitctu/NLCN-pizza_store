<?php

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: vouchers.php?error=invalid_id");
    exit();
}

// Lấy dữ liệu voucher hiện tại
$stmt = $conn->prepare("SELECT * FROM vouchers WHERE id = :id");
$stmt->execute([':id' => $id]);
$voucher = $stmt->fetch();

if (!$voucher) {
    header("Location: vouchers.php?error=not_found");
    exit();
}

// Cập nhật voucher khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];

    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    } else {
        unset($_SESSION['csrf_token']);
    }

    $description = trim($_POST['description']);
    $discount = $_POST['discount'];
    $min_order_value = $_POST['min_order_value'];
    $quantity = $_POST['quantity'];
    $expiration = $_POST['expiration'];

    $sql = "UPDATE vouchers SET code = :code, description = :description, discount_amount = :discount,
            min_order_value = :min_order_value, quantity = :quantity, expiration_date = :expiration WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':id' => $id,
        ':code' => $code,
        ':description' => $description,
        ':discount' => $discount,
        ':min_order_value' => $min_order_value,
        ':quantity' => $quantity,
        ':expiration' => $expiration
    ]);
    $_SESSION['success'] = "Voucher (ID: $id) has been updated successfully!";
    header("Location: /admin");
    exit;
}
?>

<div class="flex justify-center mb-8 mt-8">
    <div class="w-full max-w-3xl rounded-lg border-2 border-blue-400 p-6 bg-white">
        <h2 class="text-xl font-bold mb-6 text-center text-blue-500">Edit Voucher</h2>
        <form method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Cột 1 -->
                <div>
                    <div class="mb-4">
                        <label class="block text-blue-500 text-sm font-medium mb-2">🔢 Voucher Code:</label>
                        <input type="text" name="code" value="<?= htmlspecialchars($voucher['code']) ?>" required
                            class="w-full border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-300">
                    </div>
                    <div class="mb-4">
                        <label class="block text-blue-500 text-sm font-medium mb-2">💰 Discount Amount:</label>
                        <input type="number" name="discount" value="<?= $voucher['discount_amount'] ?>" min="0" step="0.01" required
                            class="w-full border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-300">
                    </div>
                    <div class="">
                        <label class="block text-blue-500 text-sm font-medium mb-2">📉 Min Order Value:</label>
                        <input type="number" name="min_order_value" value="<?= $voucher['min_order_value'] ?>" min="0" step="0.01"
                            class="w-full border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-300">
                    </div>
                </div>
                <!-- Cột 2 -->
                <div>
                    <div class="mb-4">
                        <label class="block text-blue-500 text-sm font-medium mb-2">🔢 Quantity:</label>
                        <input type="number" name="quantity" value="<?= $voucher['quantity'] ?>" min="1" required
                            class="w-full border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-300">
                    </div>
                    <div class="mb-4">
                        <label class="block text-blue-500 text-sm font-medium mb-2">⏳ Expiration Date & Time:</label>
                        <input type="datetime-local" name="expiration" value="<?= $voucher['expiration_date'] ?>" required
                            class="w-full border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-300">
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-blue-500 text-sm font-medium mb-2">📝 Description:</label>
                <textarea name="description" class="w-full border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-300"><?= htmlspecialchars($voucher['description']) ?></textarea>
            </div>
            <div class="flex justify-center space-x-4">
                <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-all duration-200" onclick="window.location.href='/admin'">
                    ❌ Cancel
                </button>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-all duration-200">
                    🔄 Update Voucher
                </button>
            </div>
        </form>
    </div>
</div>
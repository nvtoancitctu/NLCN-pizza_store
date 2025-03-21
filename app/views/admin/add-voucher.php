<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    } else {
        unset($_SESSION['csrf_token']);
    }

    // Lấy dữ liệu từ form
    $code = trim($_POST['code']);
    $description = trim($_POST['description']);
    $discount = floatval($_POST['discount']);
    $min_order_value = floatval($_POST['min_order_value']);
    $quantity = intval($_POST['quantity']);
    $expiration = $_POST['expiration'];

    try {
        // Lấy ID lớn nhất hiện có
        $stmt = $conn->query("SELECT MAX(id) AS max_id FROM vouchers");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $new_voucher_id = $result['max_id'] + 1; // ID mới = ID lớn nhất + 1

        // Chèn dữ liệu vào bảng vouchers với ID mới
        $sql = "INSERT INTO vouchers (id, code, description, discount_amount, min_order_value, quantity, expiration_date) 
                VALUES (:id, :code, :description, :discount, :min_order_value, :quantity, :expiration)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $new_voucher_id,
            ':code' => $code,
            ':description' => $description,
            ':discount' => $discount,
            ':min_order_value' => $min_order_value,
            ':quantity' => $quantity,
            ':expiration' => $expiration
        ]);

        // Thông báo thành công
        $_SESSION['success'] = "New Voucher (ID: $new_voucher_id) has been added successfully!";
        header("Location: /admin");
        exit;
    } catch (PDOException $e) {
        // Xử lý lỗi nếu có
        $_SESSION['error'] = "Error adding voucher: " . $e->getMessage();
        header("Location: /admin/add-voucher");
        exit;
    }
}

?>

<div class="flex justify-center mb-8 mt-8">
    <div class="w-full max-w-3xl rounded-lg border-2 border-blue-400 p-6 bg-white">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">🎟️ Add New Voucher</h2>
        <form method="POST" action="/admin/add-voucher" class="space-y-6">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Cột 1 -->
                <div>
                    <div class="mb-4">
                        <label for="code" class="block text-blue-500 text-sm font-medium mb-2">🔢 Voucher Code</label>
                        <input type="text" name="code" class="border border-gray-200 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-300" required>
                    </div>
                    <div class="mb-4">
                        <label for="discount" class="block text-blue-500 text-sm font-medium mb-2">💰 Discount Amount</label>
                        <input type="number" name="discount" class="border border-gray-200 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-300" min="0" step="0.01" required>
                    </div>
                    <div class="">
                        <label for="min_order_value" class="block text-blue-500 text-sm font-medium mb-2">📉 Min Order Value</label>
                        <input type="number" name="min_order_value" class="border border-gray-200 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-300" min="0" step="0.01">
                    </div>
                </div>
                <!-- Cột 2 -->
                <div>
                    <div class="mb-4">
                        <label for="quantity" class="block text-blue-500 text-sm font-medium mb-2">🔢 Quantity</label>
                        <input type="number" name="quantity" class="border border-gray-200 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-300" min="1" required>
                    </div>
                    <div class="mb-4">
                        <label for="expiration" class="block text-blue-500 text-sm font-medium mb-2">⏳ Expiration Date & Time</label>
                        <input type="datetime-local" name="expiration" class="border border-gray-200 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-300" required>
                    </div>
                </div>
            </div>
            <div class="">
                <label for="description" class="block text-blue-500 text-sm font-medium mb-2">📝 Description</label>
                <textarea name="description" class="border border-gray-200 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-300"></textarea>
            </div>
            <!-- Button Actions -->
            <div class="flex justify-center space-x-4">
                <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 flex items-center transition-all duration-200" onclick="window.location.href='/admin'">
                    ❌ Cancel
                </button>
                <button type="submit" class="bg-blue-400 text-white px-4 py-2 rounded-lg hover:bg-blue-500 flex items-center transition-all duration-200">
                    💾 Save Voucher
                </button>
            </div>
        </form>
    </div>
</div>
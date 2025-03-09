<?php
// Khởi tạo CSRF token nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';

// Lấy danh sách đơn hàng của người dùng
$orders = $orderController->getOrdersByUserId($user_id);

// Lấy danh sách phản hồi
$feedbacks = $userController->getUserFeedback($user_id);

// Xử lý các yêu cầu POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {

    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $order_id = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);
        $user_message = htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8');
        $rating = (isset($_POST['rating'])) ? $_POST['rating'] : NULL;

        if (!empty($name) && !empty($email) && $order_id > 0) {
            if ($userController->handleAddFeedback($user_id, $name, $email, $order_id, $user_message, $rating)) {
                $_SESSION['success'] = "Your feedback has been submitted!";
            } else {
                $_SESSION['success'] = "Error submitting feedback.";
            }
        } else {
            $_SESSION['success'] = "All fields are required!";
        }
    } elseif ($action === 'edit') {
        $feedback_id = filter_var($_POST['feedback_id'], FILTER_VALIDATE_INT);
        $new_message = htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8');

        if ($feedback_id > 0 && !empty($new_message)) {
            if ($userController->updateFeedback($feedback_id, $user_id, $new_message)) {
                $_SESSION['success'] = "Feedback updated successfully.";
            } else {
                $_SESSION['success'] = "Failed to update feedback.";
            }
        }
    } elseif ($action === 'delete') {
        $feedback_id = filter_var($_POST['feedback_id'], FILTER_VALIDATE_INT);

        if ($feedback_id > 0) {
            if ($userController->deleteFeedback($feedback_id, $user_id)) {
                $_SESSION['success'] = "Feedback deleted successfully.";
            } else {
                $_SESSION['success'] = "Failed to delete feedback.";
            }
        }
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// Kiểm tra và lấy thông báo thành công từ session
$success = '';
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']); // Xóa thông báo khỏi session
}

?>

<!-- Hiển thị thông báo thành công nếu có -->
<?php if (!empty($success)): ?>
    <script>
        alert("<?= addslashes($success) ?>");
    </script>
<?php endif; ?>

<div class="container mx-auto p-6 w-8/12">
    <h2 class="text-3xl font-bold mb-6 text-blue-700 text-center flex items-center justify-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M2 10a8 8 0 1116 0A8 8 0 012 10zm9-3a1 1 0 10-2 0v3a1 1 0 00.293.707l2 2a1 1 0 101.414-1.414L11 10.586V7z" clip-rule="evenodd" />
        </svg>
        Submit Your Feedback
    </h2>
    <div class="bg-white p-10 rounded-xl shadow-lg mb-6">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="action" value="add">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <!-- Name -->
                <div>
                    <label class="block text-gray-700 mb-2 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 2a4 4 0 110 8 4 4 0 010-8zM2 18a8 8 0 0116 0H2z" clip-rule="evenodd" />
                        </svg>
                        Your Name:
                    </label>
                    <input type="text" name="name" value="<?= $user_name ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400" required>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-gray-700 mb-2 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2.003 5.884L10 10.618l7.997-4.734A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.382l-8 4.737-8-4.737V14a2 2 0 002 2h12a2 2 0 002-2V8.382z" />
                        </svg>
                        Your Email:
                    </label>
                    <input type="email" name="email" value="<?= $user_email ?>" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400" required>
                </div>
            </div>

            <!-- Order Selection -->
            <div class="mb-4">
                <label class="block text-gray-700 mb-2 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-1 1H4a1 1 0 01-1-1V3zm0 5a1 1 0 011-1h12a1 1 0 011 1v8a1 1 0 01-1 1H4a1 1 0 01-1-1V8z" />
                    </svg>
                    Order ID:
                </label>
                <select name="order_id" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400" required>
                    <option value="">Select an order</option>
                    <?php foreach ($orders as $order): ?>
                        <?php
                        $orderdetails = $orderController->getOrderDetailsByOrderId($order['id']);
                        $productNames = array_map(fn($item) => $item['name'], $orderdetails);
                        $productList = implode(", ", $productNames);
                        ?>
                        <option value="<?= $order['id'] ?>">
                            Order #<?= $order['id'] ?> - <?= $order['created_at'] ?> (<?= $productList ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Star Rating Selection -->
            <div class="mb-4">
                <label class="block text-gray-700 mb-2 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 17.3l-5.2 3.3 1.4-6.1L3 9.3l6.3-.5L12 3l2.7 5.8 6.3.5-4.2 4.2 1.4 6.1z" />
                    </svg>
                    Rating:
                </label>
                <select name="rating" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400" required>
                    <option value="">Select a rating</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>">⭐ <?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Message -->
            <div class="mb-4">
                <label class="block text-gray-700 mb-2 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-500" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v8a2 2 0 01-2 2h-4l-4 3v-3H4a2 2 0 01-2-2V5z" />
                    </svg>
                    Message:
                </label>
                <textarea name="message" rows="2" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400"></textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-center">
                <button type="submit" class="bg-blue-700 hover:bg-gray-700 text-white py-2 px-6 rounded-lg shadow-md flex items-center gap-2 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M2 10a8 8 0 1116 0A8 8 0 012 10zm9-3a1 1 0 10-2 0v3a1 1 0 00.293.707l2 2a1 1 0 101.414-1.414L11 10.586V7z" clip-rule="evenodd" />
                    </svg>
                    Send
                </button>
            </div>
        </form>
    </div>
</div>

<div class="bg-white p-10 rounded-xl shadow-lg w-10/12 mx-auto mb-6">
    <div class="space-y-6">
        <?php foreach ($feedbacks as $feedback): ?>
            <div class="border-1 border-blue-300 p-5 rounded-lg shadow-md bg-white">
                <div class="flex justify-between items-center border-b pb-3">
                    <h3 class="text-lg font-semibold text-gray-800">
                        🛒 Feedback for Order ID: <span class="text-blue-600 font-bold">#<?= $feedback['order_id'] ?></span>
                        <?php if (!empty($feedback['rating'])): ?>
                            <span class="text-yellow-500 font-bold">
                                <?= str_repeat('⭐', $feedback['rating']) ?> (<?= $feedback['rating'] ?>/5)
                            </span>
                        <?php else: ?>
                            <span class="text-sm text-gray-500">No rating</span>
                        <?php endif; ?>
                    </h3>

                    <p class="text-sm text-gray-500">
                        🕒 Created: <?= date('d/m/Y H:i:s', strtotime($feedback['created_at'])) ?>
                    </p>
                </div>

                <div class="mt-3">
                    <p class="text-gray-800">
                        <strong class="text-blue-500">💬 Message:</strong>
                        <?= htmlspecialchars(!empty($feedback['message']) ? $feedback['message'] : 'No feedback') ?>
                    </p>

                    <p class="text-gray-800 mt-2">
                        <strong class="text-blue-500">✅ Response:</strong>
                        <?= $feedback['response']
                            ? "<span class='text-green-600 font-medium'>$feedback[response]</span>"
                            : "<span class='text-gray-500'>No response yet</span>" ?>
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-3 mt-4 text-sm">
                    <p class="text-gray-600">
                        <strong class="text-blue-500">📅 Updated:</strong>
                        <?= date('d/m/Y H:i:s', strtotime($feedback['updated_at'])) ?>
                    </p>
                    <p class="text-gray-600">
                        <strong class="text-blue-500">🛠 Admin Response:</strong>
                        <?= $feedback['responsed_at']
                            ? date('d/m/Y H:i:s', strtotime($feedback['responsed_at']))
                            : '<span class="text-gray-500">Not responded yet</span>' ?>
                    </p>
                </div>

                <!-- Thêm ghi chú -->
                <div class="mt-3 p-3 bg-blue-50 border-l-4 border-blue-400 text-blue-700 rounded-md">
                    <strong>📌 Note:</strong> This feedback is important for improving customer experience.
                    Please review and respond as soon as possible.
                </div>

                <div class="flex justify-end space-x-3 mt-4">
                    <button onclick="openEditModal(<?= $feedback['id'] ?>, '<?= htmlspecialchars($feedback['message'], ENT_QUOTES, 'UTF-8') ?>')"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg shadow-md transition-all">
                        ✏️ Edit
                    </button>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this feedback?');">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="feedback_id" value="<?= htmlspecialchars($feedback['id']) ?>">
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg shadow-md transition-all">
                            🗑️ Delete
                        </button>
                    </form>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

    <!-- Modal chỉnh sửa feedback -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-xl font-bold mb-4">Edit Feedback</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="feedback_id" id="editFeedbackId">
                <div class="mb-4">
                    <label class="block text-gray-700">Message:</label>
                    <textarea name="message" id="editMessage" rows="4" class="w-full p-3 border rounded-lg" required></textarea>
                </div>
                <div class="flex justify-between">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg">Cancel</button>
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal(feedbackId, message) {
        document.getElementById('editFeedbackId').value = feedbackId;
        document.getElementById('editMessage').value = message;
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>
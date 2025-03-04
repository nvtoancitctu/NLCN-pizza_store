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

        if (!empty($name) && !empty($email) && $order_id > 0) {
            if ($userController->handleAddFeedback($user_id, $name, $email, $order_id, $user_message)) {
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
    <div class="bg-white p-10 rounded-xl shadow-lg mb-6">
        <h2 class="text-3xl font-bold mb-4 text-blue-700 text-center">Submit Your Feedback</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="action" value="add">
            <div class="mb-4">
                <label class="block text-blue-700 mb-2">Your Name:</label>
                <input type="text" name="name" value="<?= $user_name ?>" class="w-full p-3 border rounded-lg" required>
            </div>
            <div class="mb-4">
                <label class="block text-blue-700 mb-2">Your Email:</label>
                <input type="email" name="email" value="<?= $user_email ?>" class="w-full p-3 border rounded-lg" required>
            </div>
            <div class="mb-4">
                <label class="block text-blue-700 mb-2">Order ID:</label>
                <select name="order_id" class="w-full p-3 border rounded-lg" required>
                    <option value="">Select an order</option>
                    <?php foreach ($orders as $order): ?>
                        <option value="<?= $order['id'] ?>">Order #<?= $order['id'] ?> - <?= $order['created_at'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-blue-700 mb-2">Message:</label>
                <textarea name="message" rows="2" class="w-full p-3 border rounded-lg"></textarea>
            </div>
            <div class="flex justify-center">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-6 rounded-lg shadow-md">Send</button>
            </div>
        </form>
    </div>

    <div class="bg-white p-10 rounded-xl shadow-lg">
        <h2 class="text-3xl font-bold mb-4 text-blue-700 text-center">Your Feedback</h2>
        <div class="space-y-4">
            <?php foreach ($feedbacks as $feedback): ?>
                <div class="border-2 p-4 rounded-lg">
                    <p><strong>Order ID:</strong> <?= $feedback['order_id'] ?></p>
                    <p><strong>Message:</strong> <?= htmlspecialchars($feedback['message']) ?></p>
                    <p><strong>Response:</strong> <?= $feedback['response'] ?: '<span class="text-gray-500">No response yet</span>' ?></p>
                    <div class="flex space-x-2 mt-3">
                        <button onclick="openEditModal(<?= $feedback['id'] ?>, '<?= htmlspecialchars($feedback['message'], ENT_QUOTES, 'UTF-8') ?>')"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">Edit</button>
                        <form method="POST" onsubmit="return confirm('Are you sure want to delete this feedback?');">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="feedback_id" value="<?= htmlspecialchars($feedback['id']) ?>">
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
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
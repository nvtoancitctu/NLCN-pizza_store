<?php

// Khởi tạo CSRF token nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';

$message = '';

// Lấy danh sách đơn hàng của người dùng
$orders = $orderController->getOrdersByUserId($user_id);

// Xử lý các yêu cầu POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $order_id = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);
        $user_message = htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8');

        if (!empty($name) && !empty($email) && !empty($user_message) && $order_id > 0) {
            if ($userController->handleAddFeedback($user_id, $name, $email, $order_id, $user_message)) {
                $message = "Your feedback has been submitted!";
            } else {
                $message = "Error submitting feedback.";
            }
        } else {
            $message = "All fields are required!";
        }
    } elseif ($action === 'edit') {
        $feedback_id = filter_var($_POST['feedback_id'], FILTER_VALIDATE_INT);
        $new_message = htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8');

        if ($feedback_id > 0 && !empty($new_message)) {
            if ($userController->updateFeedback($feedback_id, $user_id, $new_message)) {
                $message = "Feedback updated successfully.";
            } else {
                $message = "Failed to update feedback.";
            }
        }
    } elseif ($action === 'delete') {
        $feedback_id = filter_var($_POST['feedback_id'], FILTER_VALIDATE_INT);

        if ($feedback_id > 0) {
            if ($userController->deleteFeedback($feedback_id, $user_id)) {
                $message = "Feedback deleted successfully.";
            } else {
                $message = "Failed to delete feedback.";
            }
        }
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// Lấy danh sách phản hồi
$feedbacks = $userController->getUserFeedback($user_id);
?>

<div class="container mx-auto p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white p-10 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold mb-4 text-blue-700">Submit Your Feedback</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="add">
                <div class="mb-4">
                    <label class="block text-blue-700">Your Name:</label>
                    <input type="text" name="name" value="<?= $user_name ?>" class="w-full p-3 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block text-blue-700">Your Email:</label>
                    <input type="email" name="email" value="<?= $user_email ?>" class="w-full p-3 border rounded-lg" required>
                </div>
                <div class="mb-4">
                    <label class="block text-blue-700">Order ID:</label>
                    <select name="order_id" class="w-full p-3 border rounded-lg" required>
                        <option value="">Select an order</option>
                        <?php foreach ($orders as $order): ?>
                            <option value="<?= $order['id'] ?>">Order #<?= $order['id'] ?> - <?= $order['created_at'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-blue-700">Message:</label>
                    <textarea name="message" rows="4" class="w-full p-3 border rounded-lg" required></textarea>
                </div>
                <div class="flex justify-center">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-6 rounded-lg shadow-md">Send</button>
                </div>
            </form>
        </div>
        <div class="bg-white p-10 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold mb-4 text-blue-700">Your Feedback</h2>
            <?php foreach ($feedbacks as $feedback): ?>
                <div class="border-2 p-4 rounded-lg mb-3">
                    <p><strong>Order ID:</strong> <?= $feedback['order_id'] ?></p>
                    <p><strong>Message:</strong> <?= htmlspecialchars($feedback['message']) ?></p>
                    <p><strong>Response:</strong> <?= $feedback['response'] ?: '<span class="text-gray-500">No response yet</span>' ?></p>
                    <div class="flex items-center space-x-2 mt-3">
                        <form method="POST" class="flex w-full space-x-2">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="feedback_id" value="<?= htmlspecialchars($feedback['id']) ?>">

                            <input type="text" name="message" value="<?= htmlspecialchars($feedback['message'], ENT_QUOTES, 'UTF-8') ?>"
                                class="flex-grow p-2 border rounded-lg" required>

                            <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">Edit</button>
                        </form>
                        <form method="POST" onsubmit="return confirm('Are you sure?');">
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
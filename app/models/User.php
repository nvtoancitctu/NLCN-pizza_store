<?php

class User
{
    private $conn;
    private $table = 'users';

    public $id;
    public $name;
    public $email;
    public $password;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Đăng ký người dùng mới
     * @param string $name - Tên người dùng
     * @param string $email - Địa chỉ email
     * @param string $password - Mật khẩu
     * @return string - Thông báo trạng thái
     */
    public function register($name, $email, $password)
    {
        // Kiểm tra email đã tồn tại
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ($stmt->rowCount() > 0) {
            return "Email already exists.";
        }

        // Kiểm tra tính hợp lệ của dữ liệu
        if (empty($name) || empty($email) || empty($password)) {
            return "All fields are required.";
        }

        if (strlen($password) < 6) {
            return "Password must be at least 6 characters long.";
        }

        // Mã hóa mật khẩu và thực hiện đăng ký
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Kiểm tra nếu bảng users trống, thì reset AUTO_INCREMENT về 1
        $query = "SELECT COUNT(*) FROM users";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $rowCount = $stmt->fetchColumn();

        // Nếu bảng trống, reset AUTO_INCREMENT về 1
        if ($rowCount == 0) {
            $resetQuery = "ALTER TABLE users AUTO_INCREMENT = 1";
        } else {
            // Nếu bảng có dữ liệu, lấy giá trị MAX(id) và set AUTO_INCREMENT tiếp theo
            $maxIdQuery = "SELECT MAX(id) FROM users";
            $stmt = $this->conn->prepare($maxIdQuery);
            $stmt->execute();
            $maxId = $stmt->fetchColumn();

            // Đặt AUTO_INCREMENT tiếp theo là MAX(id) + 1
            $resetQuery = "ALTER TABLE users AUTO_INCREMENT = " . ($maxId + 1);
        }

        // Thực thi câu lệnh ALTER TABLE để thiết lập AUTO_INCREMENT
        $this->conn->prepare($resetQuery)->execute();

        try {
            $stmt = $this->conn->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
            $stmt->execute(['name' => $name, 'email' => $email, 'password' => $hashedPassword]);
            return "Registration successful!";
        } catch (PDOException $e) {
            return "Error during registration: " . $e->getMessage();
        }
    }

    /**
     * Kiểm tra thông tin đăng nhập, kiểm tra tài khoản có bị khóa không?
     * @param string $email - Địa chỉ email
     * @param string $password - Mật khẩu
     * @return mixed - Thông tin người dùng nếu thành công, false nếu thất bại
     */
    public function login($email, $password)
    {
        $query = "SELECT id, name, email, role, blocked_until, password FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false; // Không tìm thấy user
        }

        // Kiểm tra tài khoản có bị khóa không
        if (isset($user['blocked_until']) && strtotime($user['blocked_until']) > time()) {
            return ["error" => "Your account is blocked until " . $user['blocked_until']];
        }

        // Kiểm tra mật khẩu
        if (password_verify($password, $user['password'])) {
            unset($user['password']); // Không lưu password vào session

            // Lưu thông tin vào session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            return ["success" => true];
        }

        return false; // Sai mật khẩu
    }

    // ------------------------------------------
    // Quản lý thông tin người dùng
    // ------------------------------------------
    public function getAllUsers()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE role != 'admin'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Trả về danh sách người dùng
    }

    /**
     * Lấy thông tin người dùng theo ID
     * @param int $id - ID người dùng
     * @return array|null - Thông tin người dùng hoặc null
     */
    public function getUserById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // Trả về thông tin người dùng
    }

    /**
     * Cập nhật thông tin người dùng (name, phone, address) theo ID người dùng
     *
     * @param int $user_id - ID của người dùng
     * @param string $name - Tên người dùng mới
     * @param string $phone - Số điện thoại mới của người dùng
     * @param string $address - Địa chỉ mới của người dùng
     * @return bool - Trả về true nếu cập nhật thành công, ngược lại trả về false
     */
    public function updateUserProfile($user_id, $name, $phone, $address, $avatar)
    {
        // Cập nhật thông tin người dùng trong database
        $query = "UPDATE " . $this->table . " SET name = :name, phone = :phone, address = :address, avatar = :avatar WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Bind giá trị vào các tham số
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':avatar', $avatar);
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

        // Nếu có hình ảnh, đổi tên ảnh theo định dạng: order_id + "_" + YYYY-MM-DD + extension
        if (!empty($avatar)) {
            // Truy vấn để lấy tên file ảnh từ CSDL
            $stmt = $this->conn->prepare("SELECT avatar FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && !empty($user['avatar'])) {
                $imagePath = "images/avatar/" . $user['avatar'];

                // Kiểm tra xem file có tồn tại không, nếu có thì xóa
                if (file_exists($imagePath)) {
                    unlink($imagePath); // Xóa file ảnh
                }
            }

            $ext = pathinfo($avatar, PATHINFO_EXTENSION);
            $newImageName = "USER" . $user_id . "-" . date("Ymd") . "." . $ext;
            $oldPath = "images/avatar/" . $avatar;
            $newPath = "images/avatar/" . $newImageName;
            if (file_exists($oldPath)) {
                // Đổi tên file trên server
                if (rename($oldPath, $newPath)) {
                    // Cập nhật trường images trong bảng orders với tên ảnh mới
                    $updateImageQuery = "UPDATE users SET avatar = :avatar WHERE id = :user_id";
                    $updateStmt = $this->conn->prepare($updateImageQuery);
                    $updateStmt->bindParam(':avatar', $newImageName, PDO::PARAM_STR);
                    $updateStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $updateStmt->execute();
                    // Cập nhật biến $image nếu cần sử dụng sau này
                    $avatar = $newImageName;
                }
            }
        }

        // Thực thi câu lệnh và kiểm tra kết quả
        if ($stmt->execute()) {
            // Cập nhật thông tin trong session nếu cập nhật thành công
            $_SESSION['user_name'] = $name;
            $_SESSION['user_phone'] = $phone;
            $_SESSION['user_address'] = $address;
            return true;
        } else {
            return false;
        }
    }

    // ------------------------------------------
    // Quản lý tài khoản (block and unblock)
    // ------------------------------------------
    public function blockUser($userId, $days)
    {
        if (!is_numeric($days) || $days < 1) {
            return false; // Ngăn chặn giá trị không hợp lệ
        }

        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $blockedUntil = date('Y-m-d H:i:s', strtotime("+$days days"));

        $query = "UPDATE users SET blocked_until = :blocked_until WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['blocked_until' => $blockedUntil, 'id' => $userId]);

        return $stmt->rowCount();
    }

    // Mở khóa tài khoản người dùng bằng cách đặt lại trường blocked_until thành NULL
    public function unblockUser($user_id)
    {
        try {
            $sql = "UPDATE users SET blocked_until = NULL WHERE id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return "Error unblocking user: " . $e->getMessage();
        }
    }

    // Kiểm tra ngày giờ hết hạn của tài khoản
    public function checkblockUser($userId)
    {
        $query = "SELECT blocked_until FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['blocked_until']) {

            $blockedUntil = new DateTime($user['blocked_until'], new DateTimeZone('Asia/Ho_Chi_Minh'));
            $now = new DateTime("now", new DateTimeZone('Asia/Ho_Chi_Minh'));

            if ($blockedUntil > $now) {
                $interval = $now->diff($blockedUntil);
                $daysLeft = $interval->days;
                $hoursLeft = $interval->h;
                $minutesLeft = $interval->i;

                return "This account is still blocked. Remaining time: $daysLeft days, $hoursLeft hours, $minutesLeft minutes.";
            } else {

                $updateQuery = "UPDATE users SET blocked_until = NULL WHERE id = ?";
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->execute([$userId]);
                return "This account has been unlocked.";
            }
        }
        return "This account is not blocked.";
    }

    /** Xóa tài khoản */
    public function deleteUser($id)
    {
        // Truy vấn để lấy tên file ảnh từ CSDL
        $stmt = $this->conn->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && !empty($user['avatar'])) {
            $imagePath = "images/avatar/" . $user['avatar'];

            // Kiểm tra xem file có tồn tại không, nếu có thì xóa
            if (file_exists($imagePath)) {
                unlink($imagePath); // Xóa file ảnh
            }
        }
        // Truy vấn để xóa tài khoản
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // ------------------------------------------
    // Kiểm tra sản phẩm yêu thích
    // ------------------------------------------
    public function isFavorite($user_id, $product_id)
    {
        $sql = "SELECT COUNT(*) FROM favorites WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
        return $stmt->fetchColumn() > 0;
    }

    // Thêm sản phẩm yêu thích
    public function addFavorite($user_id, $product_id)
    {
        // Kiểm tra nếu bảng products trống, thì reset AUTO_INCREMENT về 1
        $query = "SELECT COUNT(*) FROM favorites";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $rowCount = $stmt->fetchColumn();

        // Nếu bảng trống, reset AUTO_INCREMENT về 1
        if ($rowCount == 0) {
            $resetQuery = "ALTER TABLE favorites AUTO_INCREMENT = 1";
        } else {
            // Nếu bảng có dữ liệu, lấy giá trị MAX(id) và set AUTO_INCREMENT tiếp theo
            $maxIdQuery = "SELECT MAX(id) FROM favorites";
            $stmt = $this->conn->prepare($maxIdQuery);
            $stmt->execute();
            $maxId = $stmt->fetchColumn();

            // Đặt AUTO_INCREMENT tiếp theo là MAX(id) + 1
            $resetQuery = "ALTER TABLE favorites AUTO_INCREMENT = " . ($maxId + 1);
        }

        // Thực thi câu lệnh ALTER TABLE để thiết lập AUTO_INCREMENT
        $this->conn->prepare($resetQuery)->execute();

        $sql = "INSERT INTO favorites (user_id, product_id) VALUES (:user_id, :product_id)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
    }

    // Bỏ sản phẩm yêu thích
    public function removeFavorite($user_id, $product_id)
    {
        $sql = "DELETE FROM favorites WHERE user_id = :user_id AND product_id = :product_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
    }

    // ------------------------------------------
    // Xử lý feedback
    // ------------------------------------------
    public function handleAddFeedback($user_id, $name, $email, $order_id, $user_message, $rating)
    {
        // Kiểm tra nếu bảng products trống, thì reset AUTO_INCREMENT về 1
        $query = "SELECT COUNT(*) FROM feedback";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $rowCount = $stmt->fetchColumn();

        // Nếu bảng trống, reset AUTO_INCREMENT về 1
        if ($rowCount == 0) {
            $resetQuery = "ALTER TABLE feedback AUTO_INCREMENT = 1";
        } else {
            // Nếu bảng có dữ liệu, lấy giá trị MAX(id) và set AUTO_INCREMENT tiếp theo
            $maxIdQuery = "SELECT MAX(id) FROM feedback";
            $stmt = $this->conn->prepare($maxIdQuery);
            $stmt->execute();
            $maxId = $stmt->fetchColumn();

            // Đặt AUTO_INCREMENT tiếp theo là MAX(id) + 1
            $resetQuery = "ALTER TABLE feedback AUTO_INCREMENT = " . ($maxId + 1);
        }

        // Thực thi câu lệnh ALTER TABLE để thiết lập AUTO_INCREMENT
        $this->conn->prepare($resetQuery)->execute();

        try {
            $stmt = $this->conn->prepare("INSERT INTO feedback (user_id, name, email, order_id, message, rating, created_at) 
                                          VALUES (?, ?, ?, ?, ?, ?, NOW())");

            // Đánh dấu note của order_id là feedbacked
            $note_stmt = $this->conn->prepare("UPDATE orders SET note = 'feedbacked' WHERE id = ?");
            $note_stmt->execute([$order_id]);

            // Thực thi câu lệnh INSERT INTO để thêm feedback vào bảng feedback
            return $stmt->execute([$user_id, $name, $email, $order_id, $user_message, $rating]);
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return false;
        }
    }

    // Lấy danh sách Feedback của User
    public function getUserFeedback($user_id)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM feedback WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return [];
        }
    }

    // Chỉnh sửa feedback
    public function updateFeedback($feedback_id, $user_id, $message, $rating)
    {
        $sql = "UPDATE feedback SET message = :message, rating = :rating, updated_at = NOW() 
                WHERE id = :feedback_id AND user_id = :user_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
        $stmt->bindParam(':feedback_id', $feedback_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Xóa feedback
    public function deleteFeedback($feedback_id, $user_id)
    {
        // Cập nhật note của order_id thành 'unfeedbacked'
        $note_stmt = $this->conn->prepare("UPDATE orders SET note = 'unfeedbacked' WHERE id = (SELECT order_id FROM feedback WHERE id = ?)");
        $note_stmt->execute([$feedback_id]);

        // Thực hiện xóa feedback
        $stmt = $this->conn->prepare("DELETE FROM feedback WHERE id = ? AND user_id = ?");
        return $stmt->execute([$feedback_id, $user_id]);
    }

    // ------------------------------------------
    // Xử lý VOUCHER
    // ------------------------------------------
    public function getUserVouchers($user_id)
    {
        $stmt = $this->conn->prepare("SELECT v.id, v.code, v.description, v.expiration_date, uv.status, 
                                             v.discount_amount, v.min_order_value, uv.used_at
                                      FROM user_voucher uv
                                      JOIN vouchers v ON uv.voucher_id = v.id
                                      WHERE uv.status = 'unused' AND uv.user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //
    public function checkFeedbackExists($user_id, $order_id)
    {
        $query = "SELECT COUNT(*) FROM feedback WHERE user_id = :user_id AND order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':user_id' => $user_id,
            ':order_id' => $order_id
        ]);
        return $stmt->fetchColumn() > 0; // Trả về true nếu có feedback
    }

    // Thêm thông báo liên quan đến người dùng
    public function addNotification($user_id, $message)
    {
        // Kiểm tra nếu bảng products trống, thì reset AUTO_INCREMENT về 1
        $query = "SELECT COUNT(*) FROM notifications";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $rowCount = $stmt->fetchColumn();

        // Nếu bảng trống, reset AUTO_INCREMENT về 1
        if ($rowCount == 0) {
            $resetQuery = "ALTER TABLE notifications AUTO_INCREMENT = 1";
        } else {
            // Nếu bảng có dữ liệu, lấy giá trị MAX(id) và set AUTO_INCREMENT tiếp theo
            $maxIdQuery = "SELECT MAX(id) FROM notifications";
            $stmt = $this->conn->prepare($maxIdQuery);
            $stmt->execute();
            $maxId = $stmt->fetchColumn();

            // Đặt AUTO_INCREMENT tiếp theo là MAX(id) + 1
            $resetQuery = "ALTER TABLE notifications AUTO_INCREMENT = " . ($maxId + 1);
        }

        // Thực thi câu lệnh ALTER TABLE để thiết lập AUTO_INCREMENT
        $this->conn->prepare($resetQuery)->execute();

        $stmt = $this->conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (:user_id, :message, 0, NOW())");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        $stmt->execute();
    }
}

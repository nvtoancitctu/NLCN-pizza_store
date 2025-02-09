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
     * Kiểm tra thông tin đăng nhập
     * @param string $email - Địa chỉ email
     * @param string $password - Mật khẩu
     * @return mixed - Thông tin người dùng nếu thành công, false nếu thất bại
     */
    public function login($email, $password)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
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
    public function updateUserProfile($user_id, $name, $phone, $address)
    {
        // Cập nhật thông tin người dùng trong database
        $query = "UPDATE " . $this->table . " SET name = :name, phone = :phone, address = :address WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Bind giá trị vào các tham số
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

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

    /**
     * Thêm thông tin liên hệ của người dùng vào cơ sở dữ liệu.
     *
     * @param int $user_id - ID người dùng
     * @param string $name - Tên người gửi
     * @param string $email - Email người gửi
     * @param string $message - Tin nhắn người gửi
     * 
     * @return bool - Trả về true nếu thành công, ném ngoại lệ nếu thất bại
     *
     * @throws Exception - Nếu có lỗi trong quá trình thực thi câu truy vấn
     */
    public function addContact($user_id, $name, $email, $message)
    {
        $stmt = $this->conn->prepare("INSERT INTO contact (user_id, name, email, message) VALUES (:user_id, :name, :email, :message)");

        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $this->conn->errorInfo()[2]);
        }

        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':message', $message, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            throw new Exception("Failed to save message: " . implode(" ", $stmt->errorInfo()));
        }

        return true;
    }
}

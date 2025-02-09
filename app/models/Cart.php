<?php

class Cart
{
    private $conn;
    private $table = 'cart';

    public $id;
    public $user_id;
    public $product_id;
    public $quantity;
    public $created_at;

    // Khởi tạo lớp Cart với tham số kết nối cơ sở dữ liệu
    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Lấy tất cả sản phẩm trong giỏ hàng của người dùng
     * @param int $user_id - ID của người dùng
     * @return array - Danh sách sản phẩm trong giỏ hàng
     */
    public function getCartItems($user_id)
    {
        $query = "SELECT 
                  c.id, 
                  c.product_id, 
                  c.quantity, 
                  c.size,
                  p.name,
                  p.price, 
                  p.image,
                  -- Tính giá hiển thị (không áp dụng tăng giá khi có khuyến mãi)
                  CASE 
                      WHEN p.discount > 0 AND (p.discount_end_time IS NULL OR p.discount_end_time >= NOW()) 
                      THEN p.discount
                      WHEN c.size = 'M' THEN p.price * 1.3
                      WHEN c.size = 'L' THEN p.price * 1.7
                      ELSE p.price
                  END AS effective_price,
                  -- Tính tổng giá từng sản phẩm
                  CASE 
                      WHEN p.discount > 0 AND (p.discount_end_time IS NULL OR p.discount_end_time >= NOW()) 
                      THEN (p.discount * c.quantity)
                      WHEN c.size = 'M' THEN (p.price * 1.3 * c.quantity)
                      WHEN c.size = 'L' THEN (p.price * 1.7 * c.quantity)
                      ELSE (p.price * c.quantity)
                  END AS total_price,
                  -- Tổng giá trị toàn bộ giỏ hàng
                  SUM(
                      CASE 
                          WHEN p.discount > 0 AND (p.discount_end_time IS NULL OR p.discount_end_time >= NOW()) 
                          THEN (p.discount * c.quantity)
                          WHEN c.size = 'M' THEN (p.price * 1.3 * c.quantity)
                          WHEN c.size = 'L' THEN (p.price * 1.7 * c.quantity)
                          ELSE (p.price * c.quantity)
                      END
                  ) OVER() AS total_cart_price
              FROM " . $this->table . " c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thêm sản phẩm vào giỏ hàng, hoặc cập nhật nếu sản phẩm đã tồn tại
     * @param int $user_id - ID người dùng
     * @param int $product_id - ID sản phẩm
     * @param int $quantity - Số lượng sản phẩm
     * @return bool - Trạng thái thành công của việc thêm/cập nhật
     */
    public function addToCart($user_id, $product_id, $quantity, $size)
    {
        // Kiểm tra nếu bảng cart trống, thì reset AUTO_INCREMENT về 1
        $query = "SELECT COUNT(*) FROM cart";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $rowCount = $stmt->fetchColumn();

        // Nếu bảng trống, reset AUTO_INCREMENT về 1
        if ($rowCount == 0) {
            $resetQuery = "ALTER TABLE cart AUTO_INCREMENT = 1";
        } else {
            // Nếu bảng có dữ liệu, lấy giá trị MAX(id) và set AUTO_INCREMENT tiếp theo
            $maxIdQuery = "SELECT MAX(id) FROM cart";
            $stmt = $this->conn->prepare($maxIdQuery);
            $stmt->execute();
            $maxId = $stmt->fetchColumn();

            // Đặt AUTO_INCREMENT tiếp theo là MAX(id) + 1
            $resetQuery = "ALTER TABLE cart AUTO_INCREMENT = " . ($maxId + 1);
        }

        // Thực thi câu lệnh ALTER TABLE để thiết lập AUTO_INCREMENT
        $this->conn->prepare($resetQuery)->execute();

        $query = "SELECT id FROM " . $this->table . " WHERE user_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $product_id]);

        if ($stmt->rowCount() > 0) {
            $query = "UPDATE " . $this->table . " SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$quantity, $user_id, $product_id]);
        } else {
            $query = "INSERT INTO " . $this->table . " (user_id, product_id, quantity, size) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$user_id, $product_id, $quantity, $size]);
        }
    }

    /**
     * Cập nhật số lượng và size của sản phẩm trong giỏ hàng
     * @param int $cart_id - ID của mục giỏ hàng
     * @param int $quantity - Số lượng mới
     * @param string $size - Size mới (S, M, L)
     * @return bool - Trạng thái thành công của việc cập nhật
     */
    public function updateCartItem($cart_id, $quantity, $size)
    {
        // Câu lệnh SQL cập nhật cả quantity và size
        $query = "UPDATE " . $this->table . " SET quantity = ?, size = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$quantity, $size, $cart_id]);
    }

    /**
     * Xóa một sản phẩm khỏi giỏ hàng
     * @param int $cart_id - ID của mục giỏ hàng
     * @return bool - Trạng thái thành công của việc xóa
     */
    public function deleteCartItem($cart_id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$cart_id]);
    }

    // /**
    //  * Xóa toàn bộ giỏ hàng của người dùng
    //  * @param int $user_id - ID của người dùng
    //  * @return bool - Trạng thái thành công của việc xóa
    //  */
    public function clearUserCart($user_id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id]);
    }

    /**
     * Lấy tổng số lượng sản phẩm trong giỏ hàng của người dùng
     * @param int $user_id - ID của người dùng
     * @return int - Tổng số lượng sản phẩm
     */
    public function getCartItemCount($user_id)
    {
        $stmt = $this->conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0; // Trả về 0 nếu không có sản phẩm nào trong giỏ
    }
}

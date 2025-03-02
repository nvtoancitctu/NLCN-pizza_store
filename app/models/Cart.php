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
        p.price,
        p.name, 
        p.image,
        -- Chọn giá hiển thị (ưu tiên discount nếu có)
        CASE 
            WHEN p.discount > 0 AND (p.discount_end_time IS NULL OR p.discount_end_time >= NOW()) 
            THEN p.discount
            ELSE p.price
        END AS base_price,

        -- Giá sau khi nhân hệ số size
        CASE 
            WHEN p.discount > 0 AND (p.discount_end_time IS NULL OR p.discount_end_time >= NOW()) AND c.size = 'M' 
            THEN p.discount * 1.2
            WHEN p.discount > 0 AND (p.discount_end_time IS NULL OR p.discount_end_time >= NOW()) AND c.size = 'L' 
            THEN p.discount * 1.5
            WHEN p.discount > 0 AND (p.discount_end_time IS NULL OR p.discount_end_time >= NOW()) 
            THEN p.discount
            WHEN c.size = 'M' THEN p.price * 1.2
            WHEN c.size = 'L' THEN p.price * 1.5
            ELSE p.price
        END AS effective_price,

        -- Tổng giá của từng sản phẩm
        CASE 
            WHEN p.discount > 0 AND (p.discount_end_time IS NULL OR p.discount_end_time >= NOW()) AND c.size = 'M' 
            THEN (p.discount * 1.2 * c.quantity)
            WHEN p.discount > 0 AND (p.discount_end_time IS NULL OR p.discount_end_time >= NOW()) AND c.size = 'L' 
            THEN (p.discount * 1.5 * c.quantity)
            WHEN p.discount > 0 AND (p.discount_end_time IS NULL OR p.discount_end_time >= NOW()) 
            THEN (p.discount * c.quantity)
            WHEN c.size = 'M' THEN (p.price * 1.2 * c.quantity)
            WHEN c.size = 'L' THEN (p.price * 1.5 * c.quantity)
            ELSE (p.price * c.quantity)
        END AS total_price

    FROM cart c 
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
        // Kiểm tra xem sản phẩm với cùng size đã có trong giỏ hàng chưa
        $query = "SELECT id, quantity FROM " . $this->table . " WHERE user_id = ? AND product_id = ? AND size = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $product_id, $size]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cartItem) {
            // Nếu sản phẩm đã tồn tại với cùng size, cập nhật số lượng
            $query = "UPDATE " . $this->table . " SET quantity = quantity + ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$quantity, $cartItem['id']]);
        } else {
            // Nếu chưa có, thêm mới vào giỏ hàng
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
        // Lấy thông tin sản phẩm hiện tại trong giỏ hàng
        $query = "SELECT user_id, product_id FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$cart_id]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cartItem) {
            return false; // Nếu không tìm thấy sản phẩm, return false
        }

        $user_id = $cartItem['user_id'];
        $product_id = $cartItem['product_id'];

        // Kiểm tra xem có sản phẩm nào cùng product_id và size đã tồn tại không
        $query = "SELECT id, quantity FROM " . $this->table . " WHERE user_id = ? AND product_id = ? AND size = ? AND id != ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $product_id, $size, $cart_id]);
        $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingItem) {
            // Nếu đã có sản phẩm cùng size, cộng dồn quantity rồi xóa dòng cũ
            $newQuantity = $existingItem['quantity'] + $quantity;

            $query = "UPDATE " . $this->table . " SET quantity = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$newQuantity, $existingItem['id']]);

            // Xóa sản phẩm cũ
            $query = "DELETE FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$cart_id]);
        } else {
            // Nếu chưa có sản phẩm trùng, cập nhật size như bình thường
            $query = "UPDATE " . $this->table . " SET quantity = ?, size = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$quantity, $size, $cart_id]);
        }
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

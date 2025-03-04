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
        p.stock_quantity,
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
        // Kiểm tra sản phẩm có còn hàng không
        $query = "SELECT stock_quantity FROM products WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product || $product['stock_quantity'] == 0) {
            return false;
        }

        // Kiểm tra xem sản phẩm với cùng size đã có trong giỏ hàng chưa
        $query = "SELECT id, quantity FROM " . $this->table . " WHERE user_id = ? AND product_id = ? AND size = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $product_id, $size]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cartItem) {
            // Nếu sản phẩm đã tồn tại với cùng size, cập nhật số lượng
            $query = "UPDATE " . $this->table . " SET quantity = quantity + ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $success = $stmt->execute([$quantity, $cartItem['id']]);
        } else {
            // Nếu chưa có, thêm mới vào giỏ hàng
            $query = "INSERT INTO " . $this->table . " (user_id, product_id, quantity, size) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $success = $stmt->execute([$user_id, $product_id, $quantity, $size]);
        }

        // Nếu thêm thành công, cập nhật stock_quantity
        if ($success) {
            $query = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$quantity, $product_id]);
        }

        return false;
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
        $query = "SELECT user_id, product_id, quantity FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$cart_id]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cartItem) {
            return false; // Không tìm thấy sản phẩm trong giỏ hàng
        }

        $user_id = $cartItem['user_id'];
        $product_id = $cartItem['product_id'];
        $old_quantity = $cartItem['quantity'];

        // Lấy số lượng tồn kho
        $query = "SELECT stock_quantity FROM products WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product || $product['stock_quantity'] + $old_quantity < $quantity) {
            return false; // Không đủ hàng để cập nhật số lượng
        }

        // Nếu sản phẩm có cùng size tồn tại, cộng dồn số lượng
        $query = "SELECT id, quantity FROM " . $this->table . " WHERE user_id = ? AND product_id = ? AND size = ? AND id != ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $product_id, $size, $cart_id]);
        $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingItem) {
            $newQuantity = $existingItem['quantity'] + $quantity;

            $query = "UPDATE " . $this->table . " SET quantity = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$newQuantity, $existingItem['id']]);

            // Xóa sản phẩm cũ
            $query = "DELETE FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$cart_id]);
        } else {
            // Cập nhật size và số lượng nếu không có sản phẩm cùng size
            $query = "UPDATE " . $this->table . " SET quantity = ?, size = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$quantity, $size, $cart_id]);
        }

        // Cập nhật lại stock_quantity trong bảng products
        $new_stock = $product['stock_quantity'] + $old_quantity - $quantity;
        $query = "UPDATE products SET stock_quantity = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$new_stock, $product_id]);
    }

    /**
     * Xóa một sản phẩm khỏi giỏ hàng
     * @param int $cart_id - ID của mục giỏ hàng
     * @return bool - Trạng thái thành công của việc xóa
     */
    public function deleteCartItem($cart_id)
    {
        // Lấy thông tin sản phẩm trước khi xóa
        $query = "SELECT product_id, quantity FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$cart_id]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cartItem) {
            return false; // Không tìm thấy sản phẩm trong giỏ hàng
        }

        $product_id = $cartItem['product_id'];
        $quantity = $cartItem['quantity'];

        // Xóa sản phẩm khỏi giỏ hàng
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$cart_id]);

        // Cập nhật stock_quantity trong bảng products
        $query = "UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$quantity, $product_id]);
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

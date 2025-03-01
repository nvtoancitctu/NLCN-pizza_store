<?php

class Order
{
    private $conn;
    private $table = 'orders';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Tạo đơn hàng mới và lưu vào cơ sở dữ liệu
     *
     * @param int $user_id - ID người dùng
     * @param array $items - Mảng các sản phẩm trong đơn hàng
     * @param string $payment_method - Phương thức thanh toán
     * @param string $address - Địa chỉ giao hàng
     * @return int - ID của đơn hàng vừa tạo
     * @throws InvalidArgumentException - Nếu $items không phải là mảng
     */
    public function createOrder($user_id, $items, $payment_method, $address, $image)
    {
        // Kiểm tra xem $items có phải là mảng không
        if (!is_array($items)) {
            throw new InvalidArgumentException('Items must be an array');
        }

        // Reset AUTO_INCREMENT nếu cần thiết
        $query = "SELECT COUNT(*) FROM orders";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $rowCount = $stmt->fetchColumn();

        if ($rowCount == 0) {
            $resetQuery = "ALTER TABLE orders AUTO_INCREMENT = 1";
        } else {
            $maxIdQuery = "SELECT MAX(id) FROM orders";
            $stmt = $this->conn->prepare($maxIdQuery);
            $stmt->execute();
            $maxId = $stmt->fetchColumn();
            $resetQuery = "ALTER TABLE orders AUTO_INCREMENT = " . ($maxId + 1);
        }
        $this->conn->prepare($resetQuery)->execute();

        // Tạo đơn hàng với hình ảnh ban đầu (nếu có)
        $query = "INSERT INTO orders (user_id, total, payment_method, address, images) VALUES (?, 0, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $payment_method, $address, $image]);

        // Lấy ID đơn hàng vừa được tạo
        $order_id = $this->conn->lastInsertId();

        // Nếu có hình ảnh, đổi tên ảnh theo định dạng: order_id + "_" + YYYY-MM-DD + extension
        if (!empty($image)) {
            $ext = pathinfo($image, PATHINFO_EXTENSION);
            $newImageName = $order_id . "_" . date("Y-m-d") . "." . $ext;
            $oldPath = "banking_images/" . $image;
            $newPath = "banking_images/" . $newImageName;
            if (file_exists($oldPath)) {
                // Đổi tên file trên server
                if (rename($oldPath, $newPath)) {
                    // Cập nhật trường images trong bảng orders với tên ảnh mới
                    $updateImageQuery = "UPDATE orders SET images = :image WHERE id = :order_id";
                    $updateStmt = $this->conn->prepare($updateImageQuery);
                    $updateStmt->bindParam(':image', $newImageName, PDO::PARAM_STR);
                    $updateStmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
                    $updateStmt->execute();
                    // Cập nhật biến $image nếu cần sử dụng sau này
                    $image = $newImageName;
                }
            }
        }

        // Thêm các sản phẩm vào bảng order_items
        foreach ($items as $item) {
            $productPrice = $this->getProductPrice($item['product_id']); // Lấy giá gốc của sản phẩm
            $this->addOrderItem($order_id, $item['product_id'], $item['quantity'], $productPrice, $item['size']);
            $this->updateProductPriceInOrderItems($order_id, $item['product_id'], $item['size']); // Cập nhật giá theo size và khuyến mãi
        }

        // Tính tổng giá trị đơn hàng từ bảng order_items
        $totalQuery = "SELECT SUM(price * quantity) AS total FROM order_items WHERE order_id = :order_id";
        $totalStmt = $this->conn->prepare($totalQuery);
        $totalStmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $totalStmt->execute();
        $total = $totalStmt->fetchColumn();

        // Cộng phí giao hàng
        $shippingFee = 2.99;
        $total += $shippingFee;

        // Cập nhật tổng tiền vào bảng orders
        $updateOrderQuery = "UPDATE orders SET total = :total WHERE id = :order_id";
        $updateOrderStmt = $this->conn->prepare($updateOrderQuery);
        $updateOrderStmt->bindParam(':total', $total, PDO::PARAM_STR);
        $updateOrderStmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $updateOrderStmt->execute();

        return $order_id;
    }

    /**
     * Thêm sản phẩm vào đơn hàng
     *
     * @param int $order_id - ID đơn hàng
     * @param int $product_id - ID sản phẩm
     * @param int $quantity - Số lượng sản phẩm
     * @param string $size - Kích thước sản phẩm (S, M, L)
     * @return bool - Trạng thái thành công của thao tác
     */
    public function addOrderItem($order_id, $product_id, $quantity, $finalPrice, $size)
    {
        // Truy vấn lấy thông tin giá gốc, giá khuyến mãi và thời gian khuyến mãi
        $productQuery = "SELECT price, discount, discount_end_time 
                     FROM products 
                     WHERE id = :product_id";
        $productStmt = $this->conn->prepare($productQuery);
        $productStmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $productStmt->execute();
        $product = $productStmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception("Product not found for ID: $product_id");
        }

        // Lấy giá gốc và giá khuyến mãi
        $basePrice = $product['price'];
        $discountPrice = $product['discount'];
        $discountEndTime = $product['discount_end_time'];

        // Kiểm tra nếu giá khuyến mãi còn hiệu lực
        $finalPrice = $basePrice; // Giá mặc định là giá gốc
        if ($discountPrice > 0 && (!isset($discountEndTime) || strtotime($discountEndTime) >= time())) {
            $finalPrice = $discountPrice; // Áp dụng giá khuyến mãi
        }

        // Điều chỉnh giá theo kích thước
        switch (strtoupper($size)) {
            case 'M':
                $finalPrice *= 1.3; // Tăng 30% cho size M
                break;
            case 'L':
                $finalPrice *= 1.7; // Tăng 70% cho size L
                break;
            case 'S':
            default:
                // Không thay đổi giá cho size S hoặc kích thước không xác định
                break;
        }

        // Kiểm tra nếu bảng order_items trống, thì reset AUTO_INCREMENT về 1
        $query = "SELECT COUNT(*) FROM order_items";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $rowCount = $stmt->fetchColumn();

        // Nếu bảng trống, reset AUTO_INCREMENT về 1
        if ($rowCount == 0) {
            $resetQuery = "ALTER TABLE order_items AUTO_INCREMENT = 1";
        } else {
            // Nếu bảng có dữ liệu, lấy giá trị MAX(id) và set AUTO_INCREMENT tiếp theo
            $maxIdQuery = "SELECT MAX(id) FROM order_items";
            $stmt = $this->conn->prepare($maxIdQuery);
            $stmt->execute();
            $maxId = $stmt->fetchColumn();

            // Đặt AUTO_INCREMENT tiếp theo là MAX(id) + 1
            $resetQuery = "ALTER TABLE order_items AUTO_INCREMENT = " . ($maxId + 1);
        }

        // Thực thi câu lệnh ALTER TABLE để thiết lập AUTO_INCREMENT
        $this->conn->prepare($resetQuery)->execute();

        // Chèn sản phẩm vào bảng order_items với giá đã được tính toán
        $query = "INSERT INTO order_items (order_id, product_id, quantity, price, size) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$order_id, $product_id, $quantity, $finalPrice, $size]);
    }

    /**
     * Cập nhật giá từng sản phẩm trong bảng order_items
     *
     * @param int $order_id - ID đơn hàng
     * @param int $product_id - ID sản phẩm cần cập nhật
     * @param string $size - Kích thước sản phẩm (S, M, L)
     * @return void
     */
    public function updateProductPriceInOrderItems($order_id, $product_id, $size)
    {
        // Truy vấn lấy thông tin giá gốc, giá khuyến mãi và thời gian khuyến mãi
        $productQuery = "SELECT price, discount, discount_end_time 
                     FROM products 
                     WHERE id = :product_id";
        $productStmt = $this->conn->prepare($productQuery);
        $productStmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $productStmt->execute();
        $product = $productStmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception("Product not found for ID: $product_id");
        }

        // Lấy giá gốc và giá khuyến mãi
        $basePrice = $product['price'];
        $discountPrice = $product['discount'];
        $discountEndTime = $product['discount_end_time'];

        // Kiểm tra nếu giá khuyến mãi còn hiệu lực
        $finalPrice = $basePrice; // Giá mặc định là giá gốc
        if ($discountPrice > 0 && (!isset($discountEndTime) || strtotime($discountEndTime) >= time())) {
            $finalPrice = $discountPrice; // Áp dụng giá khuyến mãi
        }

        // Điều chỉnh giá theo kích thước
        switch (strtoupper($size)) {
            case 'M':
                $finalPrice *= 1.3; // Tăng 30% cho size M
                break;
            case 'L':
                $finalPrice *= 1.7; // Tăng 70% cho size L
                break;
            case 'S':
            default:
                // Không thay đổi giá cho size S hoặc kích thước không xác định
                break;
        }

        // Cập nhật giá sản phẩm trong bảng order_items
        $updateQuery = "UPDATE order_items 
                    SET price = :price 
                    WHERE order_id = :order_id AND product_id = :product_id";

        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->bindParam(':price', $finalPrice, PDO::PARAM_STR);
        $updateStmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $updateStmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $updateStmt->execute();
    }

    /**
     * Lấy giá sản phẩm, kiểm tra xem có giá giảm hay không
     *
     * @param int $product_id - ID sản phẩm
     * @return float - Giá của sản phẩm (có thể là giá gốc hoặc giá giảm)
     */
    public function getProductPrice($productId)
    {
        $stmt = $this->conn->prepare("SELECT price FROM products WHERE id = :id");
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception("Product not found with ID: $productId.");
        }

        return $product['price'];
    }

    /** Lấy danh sách tất cả đơn hàng */
    public function getAllOrders()
    {
        $query = "SELECT o.id, u.name AS customer_name, o.total, o.status, o.created_at, o.payment_method, o.images 
                FROM orders o
                JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Xóa đơn hàng */
    public function deleteOrder($id)
    {
        // Truy vấn để lấy tên file ảnh từ CSDL
        $stmt = $this->conn->prepare("SELECT images FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order && !empty($order['images'])) {
            $imagePath = "banking_images/" . $order['images'];

            // Kiểm tra xem file có tồn tại không, nếu có thì xóa
            if (file_exists($imagePath)) {
                unlink($imagePath); // Xóa file ảnh
            }
        }

        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    /** 
     * Cập nhật đơn hàng an toàn, tuân thủ các tiêu chuẩn bảo mật 
     * @param int $order_id - ID của đơn hàng cần cập nhật
     * @param string $name - Tên khách hàng (được làm sạch)
     * @param float $total - Tổng tiền đơn hàng
     * @param string $status - Trạng thái đơn hàng (được kiểm tra hợp lệ)
     * @return bool - Trả về true nếu cập nhật thành công, ngược lại trả về false
     */
    public function updateOrder($order_id, $name, $total, $status)
    {
        // Kiểm tra order_id hợp lệ (phải là số nguyên dương)
        if (!filter_var($order_id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
            return false;
        }

        // Làm sạch dữ liệu đầu vào để tránh XSS
        $name = htmlspecialchars(strip_tags(trim($name)));

        // Kiểm tra total hợp lệ (phải là số dương)
        if (!filter_var($total, FILTER_VALIDATE_FLOAT) || $total < 0) {
            return false;
        }

        // Danh sách trạng thái hợp lệ
        $valid_statuses = ['pending', 'processing', 'completed', 'cancelled'];
        if (!in_array($status, $valid_statuses, true)) {
            return false;
        }

        try {
            $sql = "UPDATE orders SET total = :total, status = :status WHERE id = :order_id";
            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':total', $total, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                print_r($stmt->errorInfo()); // Debug lỗi SQL
                return false;
            }

            $sql = "UPDATE users SET customer_name = :name WHERE id = (SELECT user_id FROM orders WHERE id = :order_id)";
            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);

            return true;
        } catch (PDOException $e) {
            error_log("Lỗi cập nhật đơn hàng: " . $e->getMessage());
            return false;
        }
    }

    // Lấy đơn hàng theo mã đơn hàng
    public function getOrderById($order_id)
    {
        // Kiểm tra xem order_id có hợp lệ không
        if (!$order_id || $order_id <= 0) {
            return null;
        }

        try {
            $sql = "SELECT o.id, u.name AS customer_name, o.total, o.status, o.created_at 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.id 
                    WHERE o.id = :order_id LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $stmt->execute();

            // Lấy dữ liệu từ DB
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            return $order ?: null; // Trả về null nếu không tìm thấy đơn hàng
        } catch (PDOException $e) {
            error_log("Error fetching order: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy chi tiết đơn hàng bao gồm các sản phẩm và tổng giá trị
     * @param int $order_id - ID đơn hàng
     * @param int $user_id - ID người dùng
     * @return array|null - Mảng chi tiết đơn hàng hoặc null nếu không tìm thấy
     */
    public function getOrderDetails($order_id, $user_id)
    {
        $query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$order_id, $user_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return null;
        }

        $query = "SELECT 
                    oi.product_id, 
                    oi.size,
                    p.name, 
                    p.image, 
                    oi.quantity, 
                    oi.price,
                    oi.price AS price_to_display,
                    (oi.price * oi.quantity) AS total_price

                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$order_id]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $orderTotal = array_sum(array_column($orderItems, 'total_price'));

        $order['items'] = $orderItems;
        $order['total'] = $orderTotal + 2.99;

        return $order;
    }

    /**
     * Lấy danh sách đơn hàng của người dùng
     * @param int $user_id - ID người dùng
     * @return array - Danh sách đơn hàng
     */
    public function getOrdersByUserId($user_id)
    {
        $query = "SELECT * FROM orders WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy chi tiết mặt hàng trong đơn hàng theo ID đơn hàng
     * @param int $order_id - ID đơn hàng
     * @return array - Danh sách mặt hàng trong đơn hàng
     */
    public function getOrderDetailsByOrderId($order_id)
    {
        $query_order_items = "SELECT 
                                p.name,
                                oi.size,
                                oi.price,
                                oi.quantity,
                                oi.price AS price_to_display,
                                (oi.price * oi.quantity) AS total_price 
                            FROM 
                                order_items oi
                            JOIN 
                                products p ON oi.product_id = p.id 
                            WHERE 
                                oi.order_id = :order_id";

        $stmt_items = $this->conn->prepare($query_order_items);
        $stmt_items->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt_items->execute();

        return $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thống kê doanh thu dựa trên khoảng thời gian yêu cầu.
     *
     * @param string $timePeriod Thời gian thống kê cần lấy, có thể là 'daily', 'monthly' hoặc 'yearly'.
     *                           - 'daily': Doanh thu theo ngày.
     *                           - 'monthly': Doanh thu theo tháng.
     *                           - 'yearly': Doanh thu theo năm.
     * @return array Mảng chứa các bản ghi doanh thu, mỗi bản ghi có 'date' (ngày hoặc khoảng thời gian) và 'revenue' (doanh thu).
     * @throws Exception Nếu tham số $timePeriod không hợp lệ.
     */
    public function getSalesStatistics($timePeriod)
    {
        $query = '';

        switch ($timePeriod) {
            case 'daily':
                // Thống kê theo ngày
                $query = "SELECT DATE(created_at) AS date, SUM(total) AS revenue FROM orders GROUP BY DATE(created_at)";
                break;
            case 'monthly':
                // Thống kê theo tháng
                $query = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS date, SUM(total) AS revenue FROM orders GROUP BY DATE_FORMAT(created_at, '%Y-%m')";
                break;
            case 'weekly':
                // Thống kê theo tuần, gồm năm và tuần
                $query = "SELECT CONCAT(YEAR(created_at), '-W', WEEK(created_at)) AS date, SUM(total) AS revenue 
                      FROM orders 
                      GROUP BY YEAR(created_at), WEEK(created_at)";
                break;
            case 'yearly':
                // Thống kê theo năm
                $query = "SELECT YEAR(created_at) AS date, SUM(total) AS revenue FROM orders GROUP BY YEAR(created_at)";
                break;
            case 'payment_method':
                // Thống kê theo phương thức thanh toán
                $query = "SELECT payment_method AS method, SUM(total) AS revenue FROM orders GROUP BY payment_method";
                break;
            case 'product':
                // Thống kê theo sản phẩm
                $query = "SELECT oi.product_id, p.name AS product_name, SUM(oi.quantity * oi.price) AS revenue 
                      FROM order_items oi
                      JOIN products p ON oi.product_id = p.id
                      GROUP BY oi.product_id";
                break;
            default:
                throw new Exception("Invalid time period provided.");
        }

        // Chuẩn bị và thực thi truy vấn
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        // Trả về kết quả dưới dạng mảng
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

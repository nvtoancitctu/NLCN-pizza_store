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
    public function createOrder($user_id, $items, $payment_method, $address, $image, $voucher_code)
    {
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

        // Tạo đơn hàng
        $query = "INSERT INTO orders (user_id, total, payment_method, address, images, voucher_id) VALUES (?, 0, ?, ?, ?, NULL)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id, $payment_method, $address, $image]);

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

        $total = 0.0;
        foreach ($items as $item) {
            $total += $item['total_price'];
            $this->addOrderItem($order_id, $item['product_id'], $item['quantity'], $item['effective_price'], $item['size']);
        }

        // Cộng phí giao hàng
        $shippingFee = ($total < 100) ? 1.50 : 0;
        $total += $shippingFee;

        // Cập nhật tổng tiền và voucher_id vào đơn hàng
        $updateOrderTotal = "UPDATE orders SET total = :total WHERE id = :order_id";
        $updateOrderStmt = $this->conn->prepare($updateOrderTotal);
        $updateOrderStmt->execute(['total' => $total, 'order_id' => $order_id]);

        if (!empty($voucher_code)) {
            // Lấy thông tin voucher
            $voucherQuery = "SELECT id, discount_amount FROM vouchers WHERE code = :voucher_code";
            $voucherStmt = $this->conn->prepare($voucherQuery);
            $voucherStmt->execute(['voucher_code' => $voucher_code]);
            $voucher = $voucherStmt->fetch(PDO::FETCH_ASSOC);

            if ($voucher) {
                $voucher_id = $voucher['id'];
                $discount_amount = $voucher['discount_amount'];

                // Cập nhật voucher_id vào đơn hàng
                $updateVoucherID = "UPDATE orders SET voucher_id = :voucher_id WHERE id = :order_id";
                $updateVoucherIDStmt = $this->conn->prepare($updateVoucherID);
                $updateVoucherIDStmt->execute(['voucher_id' => $voucher_id, 'order_id' => $order_id]);

                // Cập nhật giá trị total khi áp dụng voucher
                $updateVoucherTotal = "UPDATE orders SET total = GREATEST(0, total - :discount_amount) WHERE id = :order_id";
                $updateVoucherTotalStmt = $this->conn->prepare($updateVoucherTotal);
                $updateVoucherTotalStmt->execute(['discount_amount' => $discount_amount, 'order_id' => $order_id]);

                // Cập nhật trạng thái voucher khi người dùng đã sử dụng
                $updateVoucherStatus = "UPDATE user_voucher SET status = 'used', used_at = NOW() 
                                        WHERE user_id = :user_id 
                                        AND voucher_id = :voucher_id 
                                        AND status = 'unused' 
                                        LIMIT 1";
                $updateVoucherStatusStmt = $this->conn->prepare($updateVoucherStatus);
                $updateVoucherStatusStmt->execute(['user_id' => $user_id, 'voucher_id' => $voucher_id]);
            }
        }

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

    /**
     * Lấy chi tiết đơn hàng bao gồm các sản phẩm và tổng giá trị
     * @param int $order_id - ID đơn hàng
     * @param int $user_id - ID người dùng
     * @return array|null - Mảng chi tiết đơn hàng hoặc null nếu không tìm thấy
     */
    public function getOrderDetails($order_id, $user_id)
    {
        // Lấy thông tin đơn hàng, bao gồm thông tin voucher (nếu có)
        $query = "SELECT o.*, v.code, v.discount_amount, v.description
                FROM orders o
                LEFT JOIN vouchers v ON o.voucher_id = v.id
                WHERE o.id = ? AND o.user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$order_id, $user_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return null;
        }

        // Lấy danh sách sản phẩm trong đơn hàng
        $query = "SELECT oi.size, oi.quantity, oi.price, p.name, p.image, 
                        (oi.quantity * oi.price) AS total_price
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$order_id]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Cộng tổng tiền sản phẩm (trước khi áp dụng voucher)
        $orderTotal = array_sum(array_column($orderItems, 'total_price'));
        $order['order_total'] = $orderTotal;

        // Thêm danh sách sản phẩm vào đơn hàng
        $order['items'] = $orderItems;

        // Gán tổng tiền từ bảng orders (đã áp dụng voucher nếu có)
        $order['final_total'] = $order['total'];

        // Cập nhật phí vận chuyển nếu chưa có
        $order['shipping_fee'] = ($orderTotal < 100.00) ? 1.50 : 0.00;

        return $order;
    }

    /**
     * Lấy danh sách đơn hàng của người dùng
     * @param int $user_id - ID người dùng
     * @return array - Danh sách đơn hàng
     */
    public function getOrdersByUserId($user_id)
    {
        $query = "SELECT 
                o.id, 
                o.address, 
                o.created_at, 
                o.total, 
                o.payment_method, 
                o.status, 
                v.code AS voucher_code,
                v.description
              FROM orders o
              LEFT JOIN vouchers v ON o.voucher_id = v.id
              WHERE o.user_id = :user_id";

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
        $query_order_items = "SELECT p.name, oi.size, oi.price, oi.quantity,
                                     oi.price AS price_to_display,
                                    (oi.price * oi.quantity) AS total_price 
                            FROM order_items oi
                            JOIN products p ON oi.product_id = p.id 
                            WHERE oi.order_id = :order_id";

        $stmt_items = $this->conn->prepare($query_order_items);
        $stmt_items->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt_items->execute();

        return $stmt_items->fetchAll(PDO::FETCH_ASSOC);
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

    //------------------------------------------------
    // QUẢN LÝ ĐƠN HÀNG (Cập nhật trạng thái - status)
    //------------------------------------------------

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

    //------------------------------------------------
    // THỐNG KÊ DOANH THU - CHI
    //------------------------------------------------

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
                $query = "SELECT DATE(created_at) AS date, SUM(total) AS revenue, SUM(total_quantity) AS total_quantity
                      FROM (SELECT created_at, total, (SELECT SUM(quantity) FROM order_items WHERE order_id = orders.id) AS total_quantity FROM orders) AS sub
                      GROUP BY DATE(created_at)";
                break;
            case 'monthly':
                // Thống kê theo tháng
                $query = "SELECT DATE_FORMAT(created_at, '%Y-%m') AS date, SUM(total) AS revenue, SUM(total_quantity) AS total_quantity
                      FROM (SELECT created_at, total, (SELECT SUM(quantity) FROM order_items WHERE order_id = orders.id) AS total_quantity FROM orders) AS sub
                      GROUP BY DATE_FORMAT(created_at, '%Y-%m')";
                break;
            case 'yearly':
                // Thống kê theo năm
                $query = "SELECT YEAR(created_at) AS date, SUM(total) AS revenue, SUM(total_quantity) AS total_quantity
                      FROM (SELECT created_at, total, (SELECT SUM(quantity) FROM order_items WHERE order_id = orders.id) AS total_quantity FROM orders) AS sub
                      GROUP BY YEAR(created_at)";
                break;
            case 'payment_method':
                // Thống kê theo phương thức thanh toán
                $query = "SELECT payment_method AS method, SUM(total) AS revenue, SUM(total_quantity) AS total_quantity
                      FROM (SELECT payment_method, total, (SELECT SUM(quantity) FROM order_items WHERE order_id = orders.id) AS total_quantity FROM orders) AS sub
                      GROUP BY payment_method";
                break;
            case 'product':
                // Thống kê theo sản phẩm
                $query = "SELECT oi.product_id, p.name AS product_name, SUM(oi.quantity * oi.price) AS revenue, SUM(oi.quantity) AS total_quantity
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

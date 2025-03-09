<?php

class Product
{
    private $conn;
    private $table = 'products';

    public $id;
    public $name;
    public $description;
    public $price;
    public $image;
    public $category_id;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ------------------------------------------
    // Phương thức CRUD
    // ------------------------------------------

    /**
     * Thêm sản phẩm mới
     * @param string $name
     * @param string $description
     * @param float $price
     * @param string $image
     * @param int $category_id
     * @param float|null $discount
     * @param string|null $discount_end_time
     * @return bool
     */
    public function createProduct($name, $description, $price, $image, $category_id, $discount = null, $discount_end_time = null)
    {
        // Kiểm tra nếu bảng products trống, thì reset AUTO_INCREMENT về 1
        $query = "SELECT COUNT(*) FROM products";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $rowCount = $stmt->fetchColumn();

        // Nếu bảng trống, reset AUTO_INCREMENT về 1
        if ($rowCount == 0) {
            $resetQuery = "ALTER TABLE products AUTO_INCREMENT = 1";
        } else {
            // Nếu bảng có dữ liệu, lấy giá trị MAX(id) và set AUTO_INCREMENT tiếp theo
            $maxIdQuery = "SELECT MAX(id) FROM products";
            $stmt = $this->conn->prepare($maxIdQuery);
            $stmt->execute();
            $maxId = $stmt->fetchColumn();

            // Đặt AUTO_INCREMENT tiếp theo là MAX(id) + 1
            $resetQuery = "ALTER TABLE products AUTO_INCREMENT = " . ($maxId + 1);
        }

        // Thực thi câu lệnh ALTER TABLE để thiết lập AUTO_INCREMENT
        $this->conn->prepare($resetQuery)->execute();

        $query = "INSERT INTO " . $this->table . " (name, description, price, image, category_id, discount, discount_end_time) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$name, $description, $price, $image, $category_id, $discount, $discount_end_time]);
    }

    /**
     * Lấy tất cả sản phẩm
     * @return array
     */
    public function getAllProducts()
    {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy sản phẩm theo ID
     * @param int $id
     * @return array|null
     */
    public function getProductById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //----------------------------------
    // QUẢN TRỊ VIÊN
    //----------------------------------
    /**
     * Cập nhật sản phẩm
     * @param int $id
     * @param string $name
     * @param string $description
     * @param float $price
     * @param string $image
     * @param int $category_id
     * @param float|null $discount
     * @param string|null $discount_end_time
     * @return bool
     */
    public function updateProduct($id, $name, $description, $price, $image, $category_id, $discount, $discount_end_time)
    {
        $query = "UPDATE " . $this->table . " SET name = ?, description = ?, price = ?, image = ?,
                                            category_id = ?, discount = ?, discount_end_time = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            // In lỗi khi chuẩn bị câu lệnh không thành công
            echo "Failed to prepare statement: " . $this->conn->error;
            return false;
        }

        $result = $stmt->execute([$name, $description, $price, $image, $category_id, $discount, $discount_end_time, $id]);

        if (!$result) {
            // In lỗi khi thực thi câu lệnh không thành công
            echo "Failed to execute statement: " . $stmt->error;
        }

        return $result;
    }

    /**
     * Xóa sản phẩm
     * @param int $id
     * @return bool
     */
    public function deleteProduct($id)
    {
        // Truy vấn để lấy tên file ảnh từ CSDL
        $stmt = $this->conn->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product && !empty($product['image'])) {
            $imagePath = "images/" . $product['image'];

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
     * Tìm kiếm sản phẩm theo từ khóa
     * @param string $searchTerm
     * @return array
     */
    public function searchProducts($searchTerm)
    {
        $query = "SELECT * FROM products 
                WHERE name LIKE :searchTerm
                OR id LIKE :searchTerm
                OR description LIKE :searchTerm 
                OR price LIKE :searchTerm 
                OR discount LIKE :searchTerm";
        $stmt = $this->conn->prepare($query);
        // Thêm ký tự "%" vào từ khóa để tìm kiếm bất kỳ từ nào có chứa $searchTerm
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy sản phẩm theo danh mục với phân trang
     * @param int $category_id
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getProductsByCategoryWithPagination($category_id = null, $limit, $offset)
    {
        if ($limit <= 0 || $offset < 0) {
            throw new InvalidArgumentException("Invalid limit or offset values");
        }

        $query = "SELECT * FROM " . $this->table;
        if ($category_id !== null) {
            $query .= " WHERE category_id = :category_id";
        }
        $query .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if ($category_id !== null) {
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ------------------------------------------
    // Phương thức format home page
    // ------------------------------------------

    /**
     * Lấy 3 sản phẩm ngẫu nhiên
     * @param int $limit
     * @return array
     */
    public function getRandomProducts($limit = 3)
    {
        $query = "SELECT * FROM " . $this->table . " ORDER BY RAND() LIMIT " . intval($limit);
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy sản phẩm đang giảm giá với thời gian còn lại
     * @return array
     */
    public function getDiscountProduct()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE discount IS NOT NULL AND discount_end_time > NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy danh sách vouchers
    public function getActiveVouchers()
    {
        $stmt = $this->conn->prepare("SELECT * FROM vouchers");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy danh sách pizza được đánh giá cao nhất
    public function getTopRatedPizzas($limit = 4)
    {
        $stmt = $this->conn->prepare("SELECT p.name, fb.rating 
                                      FROM feedback fb 
                                      JOIN order_items oi ON fb.order_id = oi.order_id
                                      JOIN products p ON p.id = oi.product_id
                                      ORDER BY rating DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // // Lấy danh sách combo khuyến mãi
    // public function getComboDeals($limit = 3)
    // {

    //     $stmt = $this->conn->prepare("SELECT name, description, price FROM combo_deals ORDER BY price DESC LIMIT ?");
    //     $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    //     $stmt->execute();
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }

    // // Lấy danh sách sản phẩm đã xem gần đây từ session
    // public function getRecentlyViewed()
    // {
    //     return $_SESSION['recently_viewed'] ?? [];
    // }

    // // Lưu sản phẩm vào danh sách đã xem gần đây
    // public function addRecentlyViewed($product)
    // {
    //     if (!isset($_SESSION['recently_viewed'])) {
    //         $_SESSION['recently_viewed'] = [];
    //     }
    //     array_unshift($_SESSION['recently_viewed'], $product);
    //     $_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 5);
    // }

    // Lấy danh sách phản hồi từ khách hàng
    public function getCustomerTestimonials($limit = 4)
    {

        $sql = "SELECT name, message FROM feedback ORDER BY RAND() LIMIT " . intval($limit);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ------------------------------------------
    // Phương thức Danh mục sản phẩm
    // ------------------------------------------

    /**
     * Lấy danh sách tất cả các danh mục
     * @return array
     */
    public function getCategories()
    {
        $query = "SELECT * FROM categories";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách các danh mục khác nhau
     * @return array
     */
    public function getDistinctCategories()
    {
        $query = "SELECT DISTINCT id, name FROM categories";
        $stmt = $this->conn->query($query);
        $categories = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = $row;
        }

        return $categories;
    }

    /**
     * Lấy sản phẩm theo danh mục
     * @param int $category_id
     * @return array
     */
    public function getProductsByCategory($category_id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE category_id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //----------------------------
    // CÁC PHƯƠNG THỨC KHÁC
    //----------------------------
    /**
     * Đếm tổng số sản phẩm trong một danh mục
     * @param int $category_id
     * @return int
     */
    public function countProducts($category_id = null)
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        if ($category_id !== null) {
            $query .= " WHERE category_id = :category_id";
        }

        $stmt = $this->conn->prepare($query);

        if ($category_id !== null) {
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Lấy danh sách ID sản phẩm yêu thích của người dùng
     * 
     * @param int $user_id ID của người dùng
     * @return array Danh sách ID sản phẩm yêu thích
     */
    public function getFavoriteProductIds($user_id)
    {
        $sql = "SELECT product_id FROM favorites WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Lấy danh sách thông tin sản phẩm yêu thích của người dùng
     * 
     * @param int $user_id ID của người dùng
     * @return array Danh sách sản phẩm yêu thích (bao gồm thông tin sản phẩm)
     */
    public function getFavoriteProductList($user_id)
    {
        $sql = "SELECT p.* FROM products p
            JOIN favorites f ON p.id = f.product_id
            WHERE f.user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // XUẤT FILE CSV GỒM TẤT CẢ BẢNG DỮ LIỆU

    public function exportProducts()
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=products.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Name', 'Price', 'Stock', 'Category']);

        $products = $this->conn->getAllProducts(); // Lấy danh sách sản phẩm từ model
        foreach ($products as $product) {
            fputcsv($output, [$product['id'], $product['name'], $product['price'], $product['stock'], $product['category']]);
        }
        fclose($output);
        exit();
    }

    // THÊM SẢN PHẨM MỚI BẰNG CÁCH IMPORT FILE CSV

    public function importOrUpdateProduct($data)
    {
        // Kiểm tra và xử lý giá trị đầu vào từ file CSV
        $productId = $data['ID'] ?? null;
        $name = $data['Name'] ?? 'Unnamed Product';
        $description = $data['Description'] ?? null;
        $price = isset($data['Price']) ? floatval($data['Price']) : 0.0;
        $categoryId = isset($data['Category']) ? intval($data['Category']) : null;
        $discount = isset($data['Discount']) && $data['Discount'] !== '' ? floatval($data['Discount']) : null;
        $discountEndTime = isset($data['Discount End Time']) && $data['Discount End Time'] !== '' ? $data['Discount End Time'] : null;

        // Kiểm tra sản phẩm đã tồn tại trong cơ sở dữ liệu chưa
        $stmt = $this->conn->prepare("SELECT id FROM products WHERE id = :id");
        $stmt->execute([':id' => $productId]);
        $existingProduct = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingProduct) {
            // Nếu sản phẩm đã tồn tại, cập nhật sản phẩm
            $stmt = $this->conn->prepare("
            UPDATE products SET 
                name = :name,
                description = :description,
                price = :price,
                category_id = :category_id,
                discount = :discount,
                discount_end_time = :discount_end_time
            WHERE id = :id
        ");
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':category_id' => $categoryId,
                ':discount' => $discount,
                ':discount_end_time' => $discountEndTime,
                ':id' => $productId
            ]);
        } else {
            // Nếu sản phẩm không tồn tại, thêm sản phẩm mới
            $stmt = $this->conn->prepare("
            INSERT INTO products (id, name, description, price, category_id, discount, discount_end_time) 
            VALUES (:id, :name, :description, :price, :category_id, :discount, :discount_end_time)
        ");
            $stmt->execute([
                ':id' => $productId,
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':category_id' => $categoryId,
                ':discount' => $discount,
                ':discount_end_time' => $discountEndTime
            ]);
        }
    }
}

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
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // ------------------------------------------
    // Phương thức bổ sung
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

    // Các phương thức bổ sung khác...

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

    // Các phương thức bổ sung khác...
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

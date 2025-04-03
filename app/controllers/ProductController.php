<?php

require_once dirname(__DIR__) . '/models/Product.php';

class ProductController
{
  private $productModel;

  public function __construct($conn)
  {
    $this->productModel = new Product($conn);
  }

  // ========================== Lấy danh sách sản phẩm ========================== //

  /**
   * Lấy tất cả sản phẩm hoặc sản phẩm theo danh mục
   */
  public function listProducts($category_id = null)
  {
    if ($category_id) {
      return $this->productModel->getProductsByCategory($category_id);
    } else {
      return $this->productModel->getAllProducts();
    }
  }

  /**
   * Lấy sản phẩm theo danh mục với phân trang
   */
  public function getProductsByCategoryWithPagination($category_id = null, $limit, $offset)
  {
    return $this->productModel->getProductsByCategoryWithPagination($category_id, $limit, $offset);
  }

  /**
   * Lấy danh sách sản phẩm yêu thích của người dùng
   */
  public function getFavoriteProductList($user_id)
  {
    return $this->productModel->getFavoriteProductList($user_id);
  }

  /**
   * Lấy danh sách ID sản phẩm yêu thích của người dùng
   */
  public function getFavoriteProductIds($user_id)
  {
    return $this->productModel->getFavoriteProductIds($user_id);
  }

  /**
   * Lấy sản phẩm đang giảm giá
   */
  public function getDiscountProduct()
  {
    return $this->productModel->getDiscountProduct();
  }

  /**
   * Lấy sản phẩm ngẫu nhiên
   */
  public function getRandomProducts($limit = 3)
  {
    return $this->productModel->getRandomProducts($limit);
  }

  // Lấy danh sách vouchers
  public function getActiveVouchers()
  {
    return $this->productModel->getActiveVouchers();
  }

  // Lấy danh sách pizza được đánh giá cao nhất
  public function getTopRatedPizzas()
  {
    return $this->productModel->getTopRatedPizzas();
  }

  // Lấy danh sách pizza bán chạy nhất
  public function getBestSellerPizzas()
  {
    return $this->productModel->getBestSellerPizzas();
  }

  // Lấy danh sách phản hồi từ khách hàng
  public function getCustomerTestimonials($limit = 4)
  {
    return $this->productModel->getCustomerTestimonials($limit);
  }

  /**
   * Lấy chi tiết sản phẩm
   */
  public function getProductDetails($id)
  {
    return $this->productModel->getProductById($id);
  }

  /**
   * Lấy danh sách danh mục sản phẩm
   */
  public function getCategories()
  {
    return $this->productModel->getCategories();
  }

  // Lấy danh sách các danh mục ngoài danh mục pizza
  public function getAllCategoryNamesExceptPizza()
  {
    return $this->productModel->getAllCategoryNamesExceptPizza();
  }

  // ========================== Quản lý danh mục ========================== //

  /**
   * Xóa danh mục
   */
  public function deleteCategory($id) {}
  /**
   * Đếm tổng số sản phẩm trong danh mục
   */
  public function countProducts($category_id = null)
  {
    return $this->productModel->countProducts($category_id);
  }

  // Lấy số lượng sản phẩm theo loại
  public function countProductsByCategory($category_id)
  {
    return $this->productModel->countProductsByCategory($category_id);
  }

  // ========================== Quản lý sản phẩm ========================== //

  /**
   * Thêm sản phẩm mới
   */
  public function createProduct($name, $description, $price, $image, $category_id, $stock_quantity, $discount, $discount_end_time)
  {
    return $this->productModel->createProduct($name, $description, $price, $image, $category_id, $stock_quantity, $discount, $discount_end_time);
  }

  /**
   * Cập nhật thông tin sản phẩm
   */
  public function updateProduct($id, $name, $description, $price, $image, $category_id, $stock_quantity, $discount, $discount_end_time)
  {
    return $this->productModel->updateProduct($id, $name, $description, $price, $image, $category_id, $stock_quantity, $discount, $discount_end_time);
  }

  /**
   * Xóa sản phẩm
   */
  public function deleteProduct($id)
  {
    return $this->productModel->deleteProduct($id);
  }

  // ========================== Tìm kiếm & Xuất nhập dữ liệu ========================== //

  /**
   * Tìm kiếm sản phẩm theo từ khóa
   */
  public function searchProducts($searchTerm)
  {
    return $this->productModel->searchProducts($searchTerm);
  }

  /**
   * Nhập hoặc cập nhật sản phẩm từ dữ liệu bên ngoài
   */
  public function importOrUpdateProduct($data)
  {
    return $this->productModel->importOrUpdateProduct($data);
  }
}

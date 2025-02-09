<?php

require_once dirname(__DIR__) . '/models/Product.php';

class ProductController
{
  private $productModel;

  public function __construct($conn)
  {
    $this->productModel = new Product($conn);
  }

  // Lấy tất cả sản phẩm hoặc sản phẩm theo danh mục
  public function listProducts($category_id = null)
  {
    if ($category_id) {
      return $this->productModel->getProductsByCategory($category_id);
    } else {
      return $this->productModel->getAllProducts();
    }
  }

  // Lấy chi tiết sản phẩm
  public function getProductDetails($id)
  {
    return $this->productModel->getProductById($id);
  }

  // Lấy danh sách danh mục
  public function getCategories()
  {
    return $this->productModel->getCategories();
  }

  // Lấy sản phẩm ngẫu nhiên
  public function getRandomProducts($limit = 3)
  {
    return $this->productModel->getRandomProducts($limit);
  }

  // Lấy sản phẩm đang giảm giá (nếu có)
  public function getDiscountProduct()
  {
    return $this->productModel->getDiscountProduct();
  }

  // Thêm sản phẩm mới
  public function createProduct($name, $description, $price, $image, $category_id, $discount, $discount_end_time)
  {
    return $this->productModel->createProduct($name, $description, $price, $image, $category_id, $discount, $discount_end_time);
  }

  // Cập nhật sản phẩm
  public function updateProduct($id, $name, $description, $price, $image, $category_id, $discount, $discount_end_time)
  {
    return $this->productModel->updateProduct($id, $name, $description, $price, $image, $category_id, $discount, $discount_end_time);
  }

  // Xóa sản phẩm
  public function deleteProduct($id)
  {
    return $this->productModel->deleteProduct($id);
  }

  // Tìm kiếm sản phẩm
  public function searchProducts($searchTerm)
  {
    return $this->productModel->searchProducts($searchTerm);
  }

  // Lấy tên loại sản phẩm
  public function getDistinctCategories()
  {
    return $this->productModel->getDistinctCategories();
  }

  // Đếm tổng số sản phẩm trong danh mục
  public function countProducts($category_id = null)
  {
    return $this->productModel->countProducts($category_id);
  }

  // Lấy sản phẩm theo danh mục với phân trang
  public function getProductsByCategoryWithPagination($category_id = null, $limit, $offset)
  {
    return $this->productModel->getProductsByCategoryWithPagination($category_id, $limit, $offset);
  }

  public function exportProducts()
  {
    return $this->productModel->exportProducts();
  }

  public function importOrUpdateProduct($data)
  {
    return $this->productModel->importOrUpdateProduct($data);
  }
}

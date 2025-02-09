<?php

require_once dirname(__DIR__) . '/models/Cart.php';

class CartController
{
  private $cartModel;

  public function __construct($db)
  {
    $this->cartModel = new Cart($db);
  }

  // Kiểm tra tính hợp lệ của số lượng sản phẩm
  private function isValidQuantity($quantity)
  {
    return is_numeric($quantity) && $quantity > 0;
  }

  // Lấy danh sách sản phẩm trong giỏ hàng của người dùng
  public function viewCart($user_id)
  {
    if (!$user_id) return []; // Nếu không có user_id, trả về mảng rỗng
    return $this->cartModel->getCartItems($user_id);
  }

  // Thêm sản phẩm vào giỏ hàng với số lượng chỉ định nếu số lượng hợp lệ
  public function addToCart($user_id, $product_id, $quantity, $size)
  {
    if ($this->isValidQuantity($quantity)) {
      return $this->cartModel->addToCart($user_id, $product_id, $quantity, $size);
    }
    throw new Exception("Invalid quantity."); // Ném ngoại lệ nếu số lượng không hợp lệ
  }

  // Cập nhật số lượng sản phẩm trong giỏ hàng dựa trên ID giỏ hàng
  public function updateCartItem($cart_id, $quantity, $size)
  {
    if ($this->isValidQuantity($quantity)) {
      return $this->cartModel->updateCartItem($cart_id, $quantity, $size);
    }
    throw new Exception("Invalid quantity."); // Ném ngoại lệ nếu số lượng không hợp lệ
  }

  // Xóa một sản phẩm khỏi giỏ hàng dựa trên ID giỏ hàng
  public function deleteCartItem($cart_id)
  {
    return $this->cartModel->deleteCartItem($cart_id);
  }

  // Xóa toàn bộ sản phẩm trong giỏ hàng của người dùng
  public function clearCart($user_id)
  {
    if ($user_id) {
      return $this->cartModel->clearUserCart($user_id);
    }
    throw new Exception("Invalid user ID."); // Ném ngoại lệ nếu user_id không hợp lệ
  }
}

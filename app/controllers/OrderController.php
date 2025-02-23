<?php

require_once dirname(__DIR__) . '/models/Order.php';

class OrderController
{
  private $orderModel;

  public function __construct($db)
  {
    // Khởi tạo đối tượng OrderModel, giúp tương tác với dữ liệu đơn hàng
    $this->orderModel = new Order($db);
  }

  // Tạo mới một đơn hàng
  public function createOrder($user_id, $total, $payment_method, $address)
  {
    return $this->orderModel->createOrder($user_id, $total, $payment_method, $address);
  }

  // Thêm sản phẩm vào đơn hàng
  public function addOrderItem($order_id, $product_id, $quantity, $price, $size)
  {
    return $this->orderModel->addOrderItem($order_id, $product_id, $quantity, $price, $size);
  }

  // Lấy tất cả đơn hàng
  public function getAllOrders()
  {
    return $this->orderModel->getAllOrders();
  }

  // Lấy tất cả đơn hàng
  public function deleteOrder($id)
  {
    return $this->orderModel->deleteOrder($id);
  }

  // Cập nhật đơn hàng
  public function updateOrder($order_id, $name, $total, $status)
  {
    return $this->orderModel->updateOrder($order_id, $name, $total, $status);
  }

  // Lấy đơn hàng theo ID
  public function getOrderById($order_id)
  {
    return $this->orderModel->getOrderById($order_id);
  }

  // Lấy chi tiết của đơn hàng cụ thể
  public function getOrderDetails($order_id, $user_id)
  {
    return $this->orderModel->getOrderDetails($order_id, $user_id);
  }

  // Lấy tất cả các đơn hàng của một người dùng
  public function getOrdersByUserId($user_id)
  {
    return $this->orderModel->getOrdersByUserId($user_id);
  }

  // Lấy chi tiết của một đơn hàng qua ID đơn hàng
  public function getOrderDetailsByOrderId($order_id)
  {
    return $this->orderModel->getOrderDetailsByOrderId($order_id);
  }

  // Lấy thống kê doanh thu bán hàng theo thời gian
  public function getSalesStatistics($timePeriod)
  {
    return $this->orderModel->getSalesStatistics($timePeriod);
  }
}

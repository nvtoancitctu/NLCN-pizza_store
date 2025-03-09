<?php

require_once dirname(__DIR__) . '/models/User.php';

class UserController
{
  private $userModel;

  public function __construct($db)
  {
    $this->userModel = new User($db);
  }

  // Xử lý đăng ký
  public function register($name, $email, $password)
  {
    return $this->userModel->register($name, $email, $password);
  }

  // Xử lý đăng nhập
  public function login($email, $password)
  {
    return $this->userModel->login($email, $password);
  }

  // Khóa tài khoản
  public function blockUser($userId, $days)
  {
    return $this->userModel->blockUser($userId, $days);
  }

  // Mở khóa tài khoản
  public function unblockUser($userId)
  {
    return $this->userModel->unblockUser($userId);
  }

  // Lấy tất cả thông tin người dùng
  public function getAllUsers()
  {
    return $this->userModel->getAllUsers();
  }

  // Lấy thông tin người dùng theo ID
  public function getUserById($userId)
  {
    return $this->userModel->getUserById($userId);
  }

  // Cập nhật thông tin người dùng
  public function updateUserProfile($userId, $name, $phone, $address)
  {
    return $this->userModel->updateUserProfile($userId, $name, $phone, $address);
  }

  // Quản lý sản phẩm yêu thích
  public function manageFavorite($userId, $productId)
  {
    if ($this->userModel->isFavorite($userId, $productId)) {
      return $this->userModel->removeFavorite($userId, $productId);
    } else {
      return $this->userModel->addFavorite($userId, $productId);
    }
  }

  // Lấy ds feedback của user
  public function getUserFeedback($user_id)
  {
    return $this->userModel->getUserFeedback($user_id);
  }

  // Thêm feedback
  public function handleAddFeedback($user_id, $name, $email, $order_id, $user_message, $rating)
  {
    try {
      return $this->userModel->handleAddFeedback($user_id, $name, $email, $order_id, $user_message, $rating);
    } catch (Exception $e) {
      return $e->getMessage();
    }
  }

  // Cập nhật feedback
  public function updateFeedback($feedback_id, $user_id, $message)
  {
    return $this->userModel->updateFeedback($feedback_id, $user_id, $message);
  }

  // Xóa phản hồi
  public function deleteFeedback($feedback_id, $user_id)
  {
    return $this->userModel->deleteFeedback($feedback_id, $user_id);
  }
}

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

  // Thêm contact
  public function handleAddContact($userId, $name, $email, $message)
  {
    try {
      return $this->userModel->addContact($userId, $name, $email, $message);
    } catch (Exception $e) {
      return $e->getMessage();
    }
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

  // Gửi phản hồi
  public function submitFeedback()
  {
    $userId = $_SESSION['user_id'] ?? null;
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $message = trim(strip_tags(filter_input(INPUT_POST, 'message', FILTER_DEFAULT)));

    if (!$userId || !$productId || empty($message)) {
      header("Location: /error.php?msg=Invalid feedback");
      exit;
    }

    $this->userModel->addFeedback($userId, $productId, $message);
    header("Location: /product.php?id=" . $productId);
    exit;
  }

  // Xóa phản hồi
  public function deleteFeedback()
  {
    $feedbackId = filter_input(INPUT_POST, 'feedback_id', FILTER_VALIDATE_INT);
    if ($feedbackId) {
      $this->userModel->deleteFeedback($feedbackId);
    }
    header("Location: /admin/feedbacks.php");
    exit;
  }
}

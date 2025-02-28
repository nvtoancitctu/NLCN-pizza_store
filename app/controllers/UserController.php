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

  // Khóa tài khoản
  public function unblockUser($user_id)
  {
    return $this->userModel->unblockUser($user_id);
  }

  // Lấy tất cả thông tin người dùng
  public function getAllUsers()
  {
    return $this->userModel->getAllUsers();
  }

  // Lấy thông tin người dùng theo ID
  public function getUserById($id)
  {
    return $this->userModel->getUserById($id);
  }

  // Cập nhật thông tin người dùng
  public function updateUserProfile($id, $name, $phone, $address)
  {
    return $this->userModel->updateUserProfile($id, $name, $phone, $address);
  }

  // Thêm contact
  public function handleAddContact($user_id, $name, $email, $message)
  {
    try {
      return $this->userModel->addContact($user_id, $name, $email, $message);
    } catch (Exception $e) {
      return $e->getMessage();
    }
  }
}

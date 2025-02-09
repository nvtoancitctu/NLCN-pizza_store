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

  // Lấy thông tin người dùng theo ID
  public function getUserById($id)
  {
    return $this->userModel->getUserById($id);
  }

  // Cập nhật thông tin người dùng
  public function updateUserProfile($id, $name, $phone, $address)
  {
    // Cập nhật thông tin người dùng
    $result = $this->userModel->updateUserProfile($id, $name, $phone, $address);

    // Trả về thông báo thành công hoặc lỗi
    if ($result) {
      return 'Profile updated successfully.';
    } else {
      return 'Error updating profile.';
    }
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

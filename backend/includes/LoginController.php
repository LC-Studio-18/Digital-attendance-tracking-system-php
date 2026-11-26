<?php

require_once __DIR__ . '/Staff.php';

class LoginController
{
  private Staff $staffModel;

  public const GENERIC_ERROR = 'Invalid email or password.';

  public function __construct(Staff $staffModel)
  {
    $this->staffModel = $staffModel;
  }

  public function handleLogin(array $formData): array
  {
    $email = trim($formData['email'] ?? '');
    $password = $formData['password'] ?? '';

    if ($email === '' || $password === '') {
      return $this->fail();
    }

    // Find the user by email
    $user = $this->staffModel->findByEmail($email);

    // unknown email
    if (!$user) {
      return $this->fail();
    }

    // account is not active.
    if ((int)$user['is_active'] !== 1) {
      return $this->fail();
    }

    // Verify the password
    if (!password_verify($password, $user['password_hash'])) {
      return $this->fail();
    }

    // Successful login
    unset($user['password_hash']); // Remove password hash before returning user data
    return [
      'success' => true,
      'user' => $user,
      'error' => null,
    ];
  }
  private function fail(): array
  {
    return [
      'success' => false,
      'user' => null,
      'error' => self::GENERIC_ERROR,
    ];
  }
}
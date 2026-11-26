<?php

class Staff
{
  private PDO $pdo;

  public function __construct(PDO $pdo)
  {
    $this->pdo = $pdo;
  }

  public function findByEmail(string $email): ?array
  {
    $sql = "SELECT id, email, password_hash, role, is_active FROM staff WHERE email = :email LIMIT 1";

    $stmt = $this->pdo->prepare($sql);
    $stmt -> execute([':email' => $email]);
    $user = $stmt -> fetch(PDO::FETCH_ASSOC);
    return $user ?: null;
  }
}
<?php

/**
 * 25.08.2020
 * File: Base
 * Encoding: UTF-8
 * Author: MaXL
 * */

declare(strict_types=1);

namespace maxl\fias\providers;

class Base
{
  protected \PDO $pdo;
  
  public function __construct(string $dsn, string $username, $password)
  {
    $this->pdo = new \PDO(
      $dsn,
      $username,
      $password
    );
  }
}

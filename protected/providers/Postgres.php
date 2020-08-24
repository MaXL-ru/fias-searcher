<?php

/**
 * 24.08.2020
 * File: Postgres
 * Encoding: UTF-8
 * Author: MaXL
 * */

declare(strict_types=1);

namespace maxl\fias\providers;

class Postgres
{
  private \Pdo $_pdo;
  
  public function __construct(string $dsn, string $username, $password)
  {
    $this->_pdo = new \PDO(
      $dsn,
      $username,
      $password
    );
  }
  
  public function findRegions(): array
  {
    return $this->_fetchFromQuery(
      '
        SELECT
          "OFFNAME" AS name, "AOGUID" AS guid
        FROM "ADDROB"
        WHERE "AOLEVEL" = 1 AND "NEXTID" IS NULL
        ORDER BY "OFFNAME"
      '
    );
  }
  
  private function _fetchFromQuery(string $sql): array
  {
    $statement = $this->_pdo->prepare($sql);

    $statement->execute();

    return $statement->fetchAll(\PDO::FETCH_ASSOC);
  }
}

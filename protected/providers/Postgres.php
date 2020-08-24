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
  private const FIAS_LEVEL_ID_REGION = 1;
  private const FIAS_LEVEL_ID_CITY   = 4;
  
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
        WHERE "AOLEVEL" = :levelId AND "NEXTID" IS NULL
        ORDER BY "OFFNAME"
      ',
      [
        ':levelId' => self::FIAS_LEVEL_ID_REGION
      ]
    );
  }
  
  public function findCities(string $regionGuid): array
  {
    return $this->_fetchFromQuery(
      '
        SELECT
          "OFFNAME" AS text, "AOGUID" AS value
        FROM "ADDROB"
        WHERE "AOLEVEL" = :levelId AND "PARENTGUID" = :regionGuid AND
          "NEXTID" IS NULL
        ORDER BY "OFFNAME"
      ',
      [
        ':levelId'    => self::FIAS_LEVEL_ID_CITY,
        ':regionGuid' => $regionGuid
      ]
    );
  }
  
  private function _fetchFromQuery(string $sql, array $params = []): array
  {
    $statement = $this->_pdo->prepare($sql);

    foreach ($params as $name => $value) {
      $statement->bindValue($name, $value);
    }

    $statement->execute();

    return $statement->fetchAll(\PDO::FETCH_ASSOC);
  }
}

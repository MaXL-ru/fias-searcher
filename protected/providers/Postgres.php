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
  private const FIAS_LEVEL_ID_STREET = 7;
  
  private \PDO $_pdo;
  
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
    return $this->_findMainAddrRecords(self::FIAS_LEVEL_ID_REGION, null);
  }
  
  public function findCities(string $regionGuid): array
  {
    return $this->_findMainAddrRecords(self::FIAS_LEVEL_ID_CITY, $regionGuid);
  }

  public function findStreets(string $cityGuid): array
  {
    return $this->_findMainAddrRecords(self::FIAS_LEVEL_ID_STREET, $cityGuid);
  }
  
  private function _findMainAddrRecords(
    int $levelId,
    ?string $parentGuid
  ): array {
    $where = [
      '"NEXTID" IS NULL',
      '"AOLEVEL" = :levelId'
    ];
    $params = [':levelId' => $levelId];
    
    if ($parentGuid !== null) {
      $where[] = '"PARENTGUID" = :parentGuid';
      $params[':parentGuid'] = $parentGuid;
    }
    
    $whereSql = implode(' AND ', $where);
    
    return $this->_fetchFromQuery(
      '
        SELECT
          "OFFNAME" AS text, "AOGUID" AS value
        FROM "ADDROB"
        WHERE ' . $whereSql . '
        ORDER BY "OFFNAME"
      ',
      $params
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

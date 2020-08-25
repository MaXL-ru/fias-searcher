<?php

/**
 * 25.08.2020
 * File: MariadDB
 * Encoding: UTF-8
 * Author: MaXL
 * */

declare(strict_types=1);

namespace maxl\fias\providers;

class MariaDB extends Base
{
  public function insertAddresses(array $addresses): bool
  {
    $params = [];
    $valueSql = [];
    
    foreach ($addresses as $i => $address) {
      $valueSql[] = "(:a$i)";
      $params[":a$i"] = implode(' ', $address);
    }
    
    $statement = $this->pdo->prepare(
      "INSERT INTO `fiasdb`.`addresses_full` VALUES " . implode(',', $valueSql)
    );
    
    array_walk(
      $params,
      function ($value, $name) use ($statement): void {
        $statement->bindValue($name, $value);
      }
    );
    
    return $statement->execute();
  }
}

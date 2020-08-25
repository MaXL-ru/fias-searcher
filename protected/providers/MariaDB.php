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
      $valueSql[] = "(:r$i, :c$i, :s$i, :h$i)";
      
      $params[":r$i"] = $address['regionName'];
      $params[":c$i"] = $address['cityName'];
      $params[":s$i"] = $address['streetName'];
      $params[":h$i"] = $address['house'];
    }
    
    $statement = $this->pdo->prepare(
      "INSERT INTO `fiasdb`.`addresses` VALUES " . implode(',', $valueSql)
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

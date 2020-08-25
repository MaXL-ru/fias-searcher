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
    return $this->_fetchFromQuery(
      '
        SELECT
          "SHORTNAME" || \'. \' || "FORMALNAME" AS text, "AOGUID" AS value
        FROM "ADDROB"
        WHERE "NEXTID" IS NULL AND "AOLEVEL" = :levelId AND
          "PARENTGUID" = :parentGuid
        ORDER BY "OFFNAME"
      ',
      [
        ':levelId' => self::FIAS_LEVEL_ID_STREET,
        ':parentGuid' => $cityGuid
      ]
    );
  }

  public function findHouses(string $streetGuid): array
  {
    $houses = $this->_fetchFromQuery(
      '
        SELECT
          "HOUSENUM",
          "BUILDNUM",
          "STRUCNUM",
          "HOUSEGUID" AS value
        FROM "HOUSE"
        WHERE "AOGUID" = :streetGuid AND "ENDDATE" > NOW()
        ORDER BY
          NULLIF(regexp_replace("HOUSENUM", \'\D\', \'\', \'g\'), \'\')::int,
          regexp_replace("HOUSENUM", \'\d\', \'\', \'g\')
      ',
      [
        ':streetGuid' => $streetGuid
      ]
    );
    
    return array_map(
      fn (array $h): array => [
        'text'  => $this->_createFullHouseNum($h),
        'value' => $h['value']
      ],
      $houses
    );
  }
  
  public function search(
    string $regionId,
    string $cityGuid,
    string $streetGuid,
    string $houseGuid
  ): array {
    $where = [
      'region."AOGUID" = :regionGuid',
      'city."AOGUID"   = :cityGuid',
      'street."AOGUID" = :streetGuid',
      'region."NEXTID" IS NULL',
      'city."NEXTID" IS NULL',
      'street."NEXTID" IS NULL',
      'house."ENDDATE" > NOW()'
    ];
    $params = [
      ':regionGuid' => $regionId,
      ':cityGuid'   => $cityGuid,
      ':streetGuid' => $streetGuid,
    ];
    
    if ($houseGuid) {
      $where[] = 'house."HOUSEGUID" = :houseGuid';
      $params[':houseGuid'] = $houseGuid;
    }
    
    $addresses = $this->_fetchFromQuery(
      '
        SELECT
          region."OFFNAME" AS regionname,
          city."OFFNAME" AS cityname,
          street."SHORTNAME" || \'. \' || street."FORMALNAME" AS streetname,
          house."HOUSENUM",
          house."BUILDNUM",
          house."STRUCNUM",
          house."POSTALCODE" AS postalcode
          
        FROM "ADDROB" AS region
        
        INNER JOIN "ADDROB" AS city ON
          city."PARENTGUID" = region."AOGUID"
          
        INNER JOIN "ADDROB" AS street ON
          street."PARENTGUID" = city."AOGUID"
          
        INNER JOIN "HOUSE" AS house ON
          house."AOGUID" = street."AOGUID"
        
        WHERE ' . implode(' AND ', $where) .
        '
        ORDER BY
          NULLIF(regexp_replace(house."HOUSENUM", \'\D\', \'\', \'g\'), \'\')::int,
          regexp_replace(house."HOUSENUM", \'\d\', \'\', \'g\')
        ',
      $params
    );
    
    return array_map(
      fn (array $address): array  => [
        'regionName' => $address['regionname'],
        'cityName' => $address['cityname'],
        'streetName' => $address['streetname'],
        'house' => $this->_createFullHouseNum($address),
        'postalcode' => $address['postalcode']
      ],
      $addresses
    );
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
  
  private function _createFullHouseNum(array $h): string
  {
    return implode(
      ', ',
      array_filter(
        [
          $h['HOUSENUM'] ? 'д. ' . $h['HOUSENUM'] : null,
          $h['BUILDNUM'] ? 'корп. ' . $h['BUILDNUM'] : null,
          $h['STRUCNUM'] ? 'стр. ' . $h['STRUCNUM'] : null
        ]
      )
    );
  }
}

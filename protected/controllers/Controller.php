<?php

/**
 * 24.08.2020
 * File: Controller
 * Encoding: UTF-8
 * Author: MaXL
 * */

declare(strict_types=1);

namespace maxl\fias\controllers;

class Controller
{
  private const E_INVALID_PARAMS = 'Invalid action params';
  private const E_CANNOT_INSERT_TO_MARIADB = 'Cannot insert items to maria db';
  
  private \maxl\fias\views\WebViewer $_viewer;
  
  private \maxl\fias\providers\Postgres $_postgresProvider;
  
  public function __construct(\maxl\fias\views\WebViewer $viewer)
  {
    $this->_viewer = $viewer;
    
    $this->_initDataProviders();
  }
  
  public function actionIndex(): void
  {
    $this->_viewer->render(
      'index',
      [
        'regions' => $this->_postgresProvider->findRegions()
      ]
    );
  }
  
  public function actionCities(): void
  {
    $regionId = (string)$this->_fromGet('regionGuid');
    
    if (!$regionId) {
      throw new \Exception(self::E_INVALID_PARAMS);
    }
    
    $this->_viewer->renderJson(
      $this->_postgresProvider->findCities($regionId)
    );
  }

  public function actionStreets(): void
  {
    $cityGuid = (string)$this->_fromGet('cityGuid');

    if (!$cityGuid) {
      throw new \Exception(self::E_INVALID_PARAMS);
    }

    $this->_viewer->renderJson(
      $this->_postgresProvider->findStreets($cityGuid)
    );
  }

  public function actionHouses(): void
  {
    $streetGuid = (string)$this->_fromGet('streetGuid');
    
    if (!$streetGuid) {
      throw new \Exception(self::E_INVALID_PARAMS);
    }
    
    $this->_viewer->renderJson(
      $this->_postgresProvider->findHouses($streetGuid)
    );
  }

  public function actionSearch(): void
  {
    $form = $this->_loadSearchForm();

    $this->_viewer->renderJson(
      array_map(
        fn (array $address) => array_values($address),
        array_values(
          $this->_postgresProvider->search(
            $form->regionId,
            $form->cityGuid,
            $form->streetGuid,
            $form->houseGuid
          )
        )
      )
    );
  }
  
  public function actionExportToMariaDb(): void
  {
    $form = $this->_loadSearchForm();
    
    $mariaDbProvider = $this->_initMariaDb();

    $isInserted = $mariaDbProvider->insertAddresses(
      $this->_postgresProvider->search(
        $form->regionId,
        $form->cityGuid,
        $form->streetGuid,
        $form->houseGuid
      )
    );
    
    $this->_viewer->renderJson(
      $isInserted ?
        ['isOk' => true]
        :
        ['error' => self::E_CANNOT_INSERT_TO_MARIADB]
    );
  }
  
  private function _initDataProviders(): void
  {
    $this->_postgresProvider = new \maxl\fias\providers\Postgres(
      \MaxlFiasConfig::DB['postgres']['dsn'],
      \MaxlFiasConfig::DB['postgres']['username'],
      \MaxlFiasConfig::DB['postgres']['password']
    );
  }
  
  private function _initMariaDb(): \maxl\fias\providers\MariaDB
  {
    return new \maxl\fias\providers\MariaDB(
      \MaxlFiasConfig::DB['mariadb']['dsn'],
      \MaxlFiasConfig::DB['mariadb']['username'],
      \MaxlFiasConfig::DB['mariadb']['password']
    );
  }

  private function _loadSearchForm(): object
  {
    $form = new class {
      public string $regionId;
      public string $cityGuid;
      public string $streetGuid;
      public ?string $houseGuid;
    };
    
    $form->regionId = (string)$this->_fromGet('regionGuid');
    $form->cityGuid = (string)$this->_fromGet('cityGuid');
    $form->streetGuid = (string)$this->_fromGet('streetGuid');
    $form->houseGuid = (string)$this->_fromGet('houseGuid');
    
    if (!$form->regionId || !$form->cityGuid || !$form->streetGuid) {
      throw new \Exception(self::E_INVALID_PARAMS);
    }
    
    return $form;
  }
  
  private function _fromGet(string $name)
  {
    return $_GET[$name] ?? null;
  }
}

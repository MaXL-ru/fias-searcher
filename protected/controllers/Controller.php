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
  
  private function _initDataProviders(): void
  {
    $this->_postgresProvider = new \maxl\fias\providers\Postgres(
      \MaxlFiasConfig::DB['postgres']['dsn'],
      \MaxlFiasConfig::DB['postgres']['username'],
      \MaxlFiasConfig::DB['postgres']['password']
    );
  }
  
  private function _fromGet(string $name)
  {
    return $_GET[$name] ?? null;
  }
}

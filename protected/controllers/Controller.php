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
  
  private function _initDataProviders(): void
  {
    $this->_postgresProvider = new \maxl\fias\providers\Postgres(
      \MaxlFiasConfig::DB['postgres']['dsn'],
      \MaxlFiasConfig::DB['postgres']['username'],
      \MaxlFiasConfig::DB['postgres']['password']
    );
  }
}

<?php

/**
 * 24.08.2020
 * File: index
 * Encoding: UTF-8
 * Author: MaXL
 * */

class MaxlFiasConfig
{
  public const DB = [
    'postgres' => [
      'dsn'      => 'pgsql:host=127.0.0.1;port=5432;dbname=fiasdb',
      'username' => 'postgres',
      'password' => 'Neu8cC3BKTpRywaN'
    ]
  ];
};

// init autoloader
require_once __DIR__ . '/../protected/sys/Autoloader.php';
(new maxl\fias\sys\Autoloader(__DIR__ . '/../protected'))->register();

// init viewer
$viewer = new maxl\fias\views\WebViewer();

// run controller action
try {
  $action = $_GET['action'] ?? 'index';
  $controller = new maxl\fias\controllers\Controller($viewer);
  $actionMethodName = 'action' . ucfirst($action);

  if (!method_exists($controller, $actionMethodName)) {
    throw new \Exception('Invalid call');
  }

  $controller->$actionMethodName();
} catch (\Exception $e) {
  $viewer->renderJson(['error' => $e->getMessage()]);
}

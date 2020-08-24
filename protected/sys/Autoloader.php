<?php

/**
 * 24.08.2020
 * File: Autoloader
 * Encoding: UTF-8
 * Author: MaXL
 * */

namespace maxl\fias\sys;

class Autoloader
{
  private const PHP_FILE_EXTENSION = '.php';

  private const E_CLASS_NOT_FOUND = 'Class not found';
  
  private string $_baseDir;
  
  public function __construct(string $baseDir)
  {
    $this->_baseDir = $baseDir;
  }
  
  public function register(): void
  {
    spl_autoload_register(
      function (string $className): void {
        $fileName = $this->_classNameToFileName($className);
        
        if (!file_exists($fileName)) {
          throw new \Exception(self::E_CLASS_NOT_FOUND . ': ' . $className);
        }
        
        require_once $fileName;
      }
    );
  }
  
  private function _classNameToFileName(string $className): string
  {
    return $this->_baseDir . str_replace(
      '\\',
      DIRECTORY_SEPARATOR,
      preg_replace('~^maxl\\\fias~', '', $className)
    ) . self::PHP_FILE_EXTENSION;
  }
}

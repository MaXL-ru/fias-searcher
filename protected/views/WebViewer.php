<?php

/**
 * 24.08.2020
 * File: WebViewer
 * Encoding: UTF-8
 * Author: MaXL
 * */

declare(strict_types=1);

namespace maxl\fias\views;

class WebViewer
{
  private const TEMPLATE_FILE_NAME_EXTENSION = '.php';
  
  private const E_VIEWER_NOT_FOUND = 'Can`t find viewer';
  
  private string $_templatesPath = __DIR__ . '/templates/';
  
  public function render(string $fileName, array $params = []): void
  {
    $viewerFileName = $this->_getViewerFullFileName($fileName);
    
    extract($params);
    
    require_once $viewerFileName;
  }
  
  private function _getViewerFullFileName($fileName): string
  {
    $fullFileName = $this->_templatesPath . $fileName .
      self::TEMPLATE_FILE_NAME_EXTENSION;
    
    if (!file_exists($fullFileName)) {
      throw new \Exception(self::E_VIEWER_NOT_FOUND . ': ' . $fileName);
    }
    
    return $fullFileName;
  }
}

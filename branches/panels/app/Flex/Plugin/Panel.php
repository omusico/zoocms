<?php

/**
 * @package Flex
 * @subpackage Plugin
 */

/**
 * Flex_Plugin_Panel
 *
 * @package    Flex
 * @subpackage Plugin
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Flex_Plugin_Panel extends Zend_Controller_Plugin_Abstract {
  /**
   * Reference to current panel
   * @var Flex_Panel
   */
  protected $panel = NULL;

  /**
   * Fetch current panel
   *
   */
  public function dispatchLoopStartup() {
    if (!Zend_Layout::getMvcInstance ()->isEnabled()) {
      // No layout, no panel content
      return;
    }
    $factory = new Flex_Panel_Factory ( );
    $this->panel = $factory->getCurrentPanel();
    if ($this->panel && $this->panel->theme) {
      // No panel to display
      Zend_Layout::getMvcInstance()->setLayout($theme)->setLayoutPath(ZfApplication::$_doc_root.'/themes/');
    }
  }

  /**
   * Fetch and assign block content to view
   *
   */
  public function dispatchLoopShutdown() {
    if (!Zend_Layout::getMvcInstance ()->isEnabled()) {
      // No layout, no panel content
      return;
    }
    if (!$this->panel) {
      // No panel to display
      return;
    }
    $this->panel->loadBlocks()->render();
    $module = ucfirst(Zend_Controller_Front::getInstance()->getRequest()->getModuleName());
    $controller = ucfirst(Zend_Controller_Front::getInstance()->getRequest()->getControllerName());
    $action = ucfirst(Zend_Controller_Front::getInstance()->getRequest()->getActionName());
    $pagetitle = str_replace(array('%module', '%controller', '%action'),
                             array($module, $controller, $action),
                             $this->panel->title);
    Zend_Layout::getMvcInstance()->getView()->assign('pagetitle', $pagetitle);
  }

}
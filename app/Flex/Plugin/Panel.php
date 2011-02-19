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
   * Fetch and assign block content to view
   *
   */
  public function dispatchLoopShutdown() {
    if (!Zend_Layout::getMvcInstance ()->isEnabled()) {
      // No layout, no panel content
      return;
    }
    $factory = new Flex_Panel_Factory ( );
    $panel = $factory->getCurrentPanel();
    if (!$panel) {
      // No panel to display
      return;
    }
    $panel->loadBlocks()->render();
    $module = ucfirst(Zend_Controller_Front::getInstance()->getRequest()->getModuleName());
    $controller = ucfirst(Zend_Controller_Front::getInstance()->getRequest()->getControllerName());
    $action = ucfirst(Zend_Controller_Front::getInstance()->getRequest()->getActionName());
    $pagetitle = str_replace(array('%module', '%controller', '%action'),
                             array($module, $controller, $action),
                             $panel->title);
    Zend_Layout::getMvcInstance()->getView()->assign('pagetitle', $pagetitle);
  }

}
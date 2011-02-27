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
  public function postDispatch(Zend_Controller_Request_Abstract $request) {
    if (!Zend_Layout::getMvcInstance ()->isEnabled()) {
      // No layout, no panel content
      return;
    }
    if (!$this->panel) {
      // No panel to display
      return;
    }
    $module = ucfirst($request->getModuleName());
    $controller = ucfirst($request->getControllerName());
    $action = ucfirst($request->getActionName());
    $content = "";
    $context = Zend_Registry::get('context');
    if (isset($context->node) && $context->node->id > 0) {
      $content = $context->node->title;
    }
    $pagetitle = str_replace(array('%module', '%controller', '%action', '%content'),
                             array($module, $controller, $action, $content),
                             $this->panel->title);
    Zend_Layout::getMvcInstance()->getView()->headTitle($pagetitle);
  }

  /**
   * Render panel blocks
   * @return void
   */
  public function  dispatchLoopShutdown() {
    if (!Zend_Layout::getMvcInstance ()->isEnabled()) {
      // No layout, no panel content
      return;
    }
    if (!$this->panel) {
      // No panel to display
      return;
    }
    $this->panel->loadBlocks()->render();
  }
}
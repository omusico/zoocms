<?php

/**
 * @package Zoo
 * @subpackage Plugin
 */

/**
 * Zoo_Plugin_Boot_Http
 *
 * @package    Zoo
 * @subpackage Plugin
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Zoo_Plugin_Boot_Http extends Zoo_Plugin_Boot {

  /**
   * Initiates MVC layout
   *
   * @param Zend_Controller_Request_Abstract $request
   */
  public function routeStartup(Zend_Controller_Request_Abstract $request) {
    // Start Zend_Layout MVC
    Zend_Layout::startMvc();

    $layout = Zend_Layout::getMvcInstance();
    /* @var $layout Zend_Layout */

    $theme = Zend_Registry::isRegistered('config') ? Zend_Registry::get('config')->view->theme : "default";

    $layout->setLayout($theme)->setLayoutPath(ZfApplication::$_doc_root . '/themes/');

    // Set the inflector target:
    $layout->setInflectorTarget(':script/:script.:suffix');

    parent::routeStartup($request);
  }

  /**
   * Sets up view
   * Alters response content type headers
   * Starts session
   *
   * @param Zend_Controller_Request_Abstract $request
   */
  public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
    $this->setupView();

    parent::dispatchLoopStartup($request);

    // Since we're not using the cli sapi, instanciate the http protocol items
    if (!Zend_Session::isStarted() && !Zend_Session::sessionExists()) {
      if ($config = Zoo::getConfig('session', 'plugin')) {
        $options = $config->toArray();
        if (isset($options['save_path'])) {
          $options['save_path'] = ZfApplication::$_data_path . $options['save_path'];
          if (!file_exists($options['save_path'])) {
            mkdir($options['save_path']);
          }
        }
        Zend_Session::setOptions($options);
        if ($config->save_handler) {
          $savehandlerClass = $config->save_handler;
          Zend_Session::setSaveHandler(new $savehandlerClass()); // Not ready yet
        }
      }
      Zend_Session::start();
    }
  }

  /**
   * Configures view class
   *
   */
  protected function setupView() {
    if (Zend_Registry::isRegistered('config')) {
      $config = Zend_Registry::get('config');

      $renderClass = $config->output->viewrenderer;
      $viewRenderer = new $renderClass();

      $viewClass = $config->output->view->name;
      $viewRenderer->setView(new $viewClass($config->output->view->options->toArray()
      ));
    } else {
      // Config file not detected, use defaults
      $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
      $viewRenderer->setView(new Zoo_View_Php());
    }
    $viewRenderer->setViewSuffix('phtml');
    //make it search for .phtml files
    Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
    //add it to the action helper broker
    // Set the encoding
    $viewRenderer->view->setEncoding("UTF-8");
    $doctypeHelper = new Zend_View_Helper_Doctype();
    $doctypeHelper->doctype('XHTML1_STRICT');

    // Add core module's view helper path
    $viewRenderer->view->addHelperPath(ZfApplication::$_base_path . "/app/Zoo/views/helpers");

    // Add JQuery support
    $viewRenderer->view->addHelperPath('ZendX/JQuery/View/Helper/', 'ZendX_JQuery_View_Helper');
    
    $viewRenderer->view->jQuery()->setVersion('1.5')
                          ->setUIVersion('1.8')
                          ->addStylesheet('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/smoothness/jquery-ui.css');
  }

  /**
   * Sets content type header for Layout-enabled pages
   * Also builds page header
   */
  public function dispatchLoopShutdown() {
    if (Zend_Layout::getMvcInstance()->isEnabled()) {
      $this->getResponse()->setHeader('Content-Type', 'text/html; charset=utf-8');
      $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
      if (!$view->headTitle()->count()) {
        $module = ucfirst(Zend_Controller_Front::getInstance()->getRequest()->getModuleName());
        $controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
        $pagetitle = $module != "Zoo" ? ucwords($module." ".$controller) : ($controller != "index" ? ucfirst($controller) : "" );
        $view->headTitle($pagetitle);
      }
      $sitename = Zend_Registry::isRegistered('config') ? Zend_Registry::get('config')->site->sitename : "ZooCMS";
      $view->headTitle()->prepend($sitename);
      $view->headTitle()->setSeparator(" - ");
    }
  }

}
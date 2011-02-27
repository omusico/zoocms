<?php

/**
 * Bootstrap class file
 *
 * @package Zoo
 * @subpackage Application
 *
 */

/**
 * @package Zoo
 * @subpackage Application
 *
 */
class ZfApplication {

  /**
   * The environment state of your current application
   *
   * @var string
   */
  public static $_environment;
  /**
   * Timestamp of initialization start
   *
   * @var int
   */
  public static $_started;
  /**
   * The path to this file's directory
   *
   * @var string
   */
  public static $_base_path;
  /**
   * Path to document root (a.k.a. location of index.php file)
   *
   * @var string
   */
  public static $_doc_root;
  /**
   * Path to data directory for temporary data
   *
   * @var string
   */
  public static $_data_path;

  /**
   * Sets the environment to load from configuration file
   *
   * @param string $environment - The environment to set
   * @return void
   */
  public function setEnvironment($environment) {
    self::$_environment = $environment;
  }

  /**
   * Returns the environment which is currently set
   *
   * @return string
   */
  public function getEnvironment() {
    return self::$_environment;
  }

  /**
   * Convenience method to bootstrap the application
   *
   * @return mixed
   */
  public function bootstrap($called_from = "") {
    ini_set("memory_limit", "32M");
    self::$_started = microtime(true);
    // Ensure that cookies don't overwrite request parameters
    $_REQUEST = array_merge($_GET, $_POST);

    /**
     * Basic definitions
     */
    self::$_base_path = dirname(__FILE__);
    if (!is_null($called_from)) {
      self::$_doc_root = $called_from;
    } else {
      self::$_doc_root = self::$_base_path . "/document_root";
    }
    self::$_data_path = self::$_base_path . "/data";

    $frontController = self::initialize();
    /* @var $frontController Zend_Controller_Front */

    $response = self::dispatch($frontController);
    self::render($response);
  }

  /**
   * Initialization stage, loads configuration files, sets up includes paths
   * and instantiates the frontController
   *
   * @return Zend_Controller_Front
   */
  public function initialize() {
    // Ensure that session cookies are not available to JavaScript
    ini_set("session.cookie_httponly", 1);
    // Set the include path
    set_include_path(
            self::$_base_path . DIRECTORY_SEPARATOR . 'library'
            . PATH_SEPARATOR
            . self::$_base_path . DIRECTORY_SEPARATOR . "app"
            . PATH_SEPARATOR
            . get_include_path()
    );

    require_once "Zend/Loader/Autoloader.php";
    $autoloader = Zend_Loader_Autoloader::getInstance();
    $autoloader->setFallbackAutoloader(true);

    $locale = new Zend_Locale();
    Zend_Registry::set('Zend_Locale', $locale);

    /*
     * Create an instance of the frontcontroller
     */
    $frontController = Zend_Controller_Front::getInstance();
    if (file_exists(self::$_data_path . '/etc/config.ini')) {
      /*
       * Load the given stage from our configuration file,
       * and store it into the registry for later usage.
       */
      $config = new Zend_Config_Ini(self::$_data_path . '/etc/config.ini');
      Zend_Registry::set('config', $config);

      $frontController->throwExceptions((bool) $config->mvc->exceptions);

      if (PHP_SAPI != "cli") {
        self::$_environment = $config->mode;
        $frontController->registerPlugin(new Zoo_Plugin_Boot_Http(), 5);
      } else {
        $frontController->throwExceptions(true);
        self::$_environment = "cli";
        $frontController->registerPlugin(new Zoo_Plugin_Boot_Cli(), 5);

        Zend_Registry::set("Zend_Locale", new Zend_Locale("en_US"));
        $frontController->setResponse(new Zend_Controller_Response_Cli());
      }

      // Add plugins from plugins.ini
      if (file_exists(self::$_data_path . '/etc/plugins.ini')) {
        $pluginconfig = new Zend_Config_Ini(self::$_data_path . '/etc/plugins.ini', self::$_environment);
        foreach ($pluginconfig->plugins as $name => $plugin) {
          $pluginClass = $plugin->class;
          $frontController->registerPlugin(new $pluginClass(), $plugin->priority);
        }
      }
      if ($config->module->default) {
        $frontController->setDefaultModule($config->module->default);
      } else {
        $frontController->setDefaultModule('zoo');
      }
    } else {
      /**
       * System not installed, go to staging mode, add the default module
       * and register the Http boot plugin
       */
      self::$_environment = "staging";
      $frontController->addControllerDirectory(self::$_base_path . "/app/Zoo/Controllers", "Zoo");
      $frontController->registerPlugin(new Zoo_Plugin_Boot_Http(), 1);
      $frontController->setDefaultModule('Zoo');
    }
    $context = new stdClass();
    Zend_Registry::set('context', $context);
    return $frontController;
  }

  /**
   * Dispatches the request
   *
   * @param  Zend_Controller_Front $frontController - The frontcontroller
   * @return Zend_Controller_Response_Abstract
   */
  public function dispatch(Zend_Controller_Front $frontController, Zend_Controller_Request_Abstract $request = null, Zend_Controller_Response_Abstract $response = null) {
    // Return the response
    $frontController->returnResponse(true);
    return $frontController->dispatch();
  }

  /**
   * Renders the response
   *
   * @param  Zend_Controller_Response_Abstract $response - The response object
   * @return void
   */
  public function render(Zend_Controller_Response_Abstract $response) {
    $response->sendHeaders();
    if (self::$_environment != "cli" && Zend_Layout::getMvcInstance()->isEnabled()) {
      echo Zend_Layout::getMvcInstance()->render();
    } else {
      echo ($response);
    }
  }

  /**
   * Automatic class discovery and inclusion
   *
   * @param string $path
   * @return string
   */
  public static function autoload($path) {
    include str_replace('_', '/', $path) . '.php';
    return $path;
  }

}

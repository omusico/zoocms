<?php
/**
 * @package Zoo
 * @subpackage Plugin
 */
/**
 * Zoo_Plugin_Boot
 *
 * @package    Zoo
 * @subpackage Plugin
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Zoo_Plugin_Boot extends Zend_Controller_Plugin_Abstract
{
    /**
     * Adds basic route startup things such as reading module list, adding errorhandler, setting up routes and adding translations
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request) {
        if (Zend_Registry::isRegistered('config')) {
            $frontController =& Zend_Controller_Front::getInstance();

            // Read installed modules list from modules.ini
            $moduleconfig = new Zend_Config_Ini(ZfApplication::$_data_path . '/etc/modules.ini', ZfApplication::$_environment);
            foreach ($moduleconfig->modules as $module => $folder) {
                $frontController->addControllerDirectory(ZfApplication::$_base_path . "$folder", $module);
            }

            // Set error handler
            $config =& Zend_Registry::get("config");
            if ($config->plugin->errorhandler->class) {
                // Front controller will add this plugin automatically - but we may override the class in config
                if ($config->plugin->errorhandler->class != "Zend_Controller_Plugin_ErrorHandler") {
                    $frontController->unregisterPlugin("Zend_Controller_Plugin_ErrorHandler");
                    $errorHandlerClass = $config->plugin->errorhandler->class;
                    $frontController->registerPlugin(new $errorHandlerClass(), 100);
                }
            }

            // Add routes
            if (file_exists(ZfApplication::$_data_path . '/etc/routes.ini')) {
                // Retrieve the router from the frontcontroller
                $router = $frontController->getRouter();
                // Add routes from config file
                $routerconfig = new Zend_Config_Ini(ZfApplication::$_data_path . '/etc/routes.ini');
                $router->addConfig($routerconfig);
            }

            // Add services
            if (file_exists(ZfApplication::$_data_path . '/etc/services.ini')) {
                Zoo::initServices(new Zend_Config_Ini(ZfApplication::$_data_path . '/etc/services.ini'));
            }
        }
    }

    /**
     * Adds module-specific language definitions
     * Sets response content type header
     * Loads module configuration
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
        $this->getResponse()->setHeader('Content-Type', 'text/plain; charset=utf-8');

        // Load module configuration into registry
        Zend_Registry::set('moduleConfig', Zoo::getConfig($request->getModuleName(), 'module'));
    }
}
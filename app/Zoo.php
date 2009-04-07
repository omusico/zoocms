<?php
/**
 * @package    Zoo
 * @subpackage Core
 */

/**
 * Zoo
 *
 * A general static class for configurations and services
 *
 * @package    Zoo
 * @subpackage Core
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Zoo {
    /**
     * Service cache
     *
     * @var array
     */
    static $services = array();
    /**
     * Service registry
     *
     * @var array
     */
    static $registry = array();

    /**
     * Initiate services from a config file, i.e. add them to the registry (no instantiation before a call to getService() )
     *
     * @param Zend_Config $config
     */
    public static function initServices(Zend_Config $config) {
        foreach ($config as $identifier => $service) {
            self::$registry[$identifier] = $service;
        }
    }

    /**
     * Get the instance of a given service
     *
     * @param string $identifier
     * @return Zend_Service
     *
     * @throws Zoo_Exception_Service if service unavailable
     */
    public static function getService($identifier) {
        if (!isset(self::$registry[$identifier])) {
            throw new Zoo_Exception_Service(self::_("Unknown service requested, ".$identifier), 5);
        }
        if (!isset(self::$services[$identifier])) {
            $service = self::$registry[$identifier];
            $thisservice = new $service();
            $config = self::getConfig(str_replace("_Service", "", $service), 'service');
            self::$services[$identifier] =& $thisservice->getService($config);
        }
        return self::$services[$identifier];
    }

    /**
     * Get configuration object for a plugin
     *
     * @param string $name name of plugin
     *
     * @return Zend_Config|false
     */
    public static function getConfig($name, $type = "") {
        $filename = ZfApplication::$_data_path . '/etc/'.($type != "" ? $type.'/' : "").strtolower($name).'.ini';
        if (file_exists($filename)) {
            return new Zend_Config_Ini($filename, ZfApplication::$_environment, array('allowModifications' => true));
        }
        return false;
    }

    /**
     * Translate a string
     *
     * @param $text string
     * @return string translated text
     */
    public static function _($text) {
        return Zoo::translate($text);
    }

    /**
     * Translate a string
     *
     * @param $text string
     * @return string translated text
     */
    public static function translate($text) {
        if (Zend_Registry::isRegistered('Zend_Translate')) {
            $translator = Zend_Registry::get('Zend_Translate');
            if ($translator instanceof Zend_Translate) {
                $translator = $translator->getAdapter();
                return $translator->translate($text);
            }
        }
        return $text;
    }
}
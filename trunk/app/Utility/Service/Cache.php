<?php
/**
 *
 * @package    Utility
 * @subpackage Service
 */

/**
 * Utility_Service_Cache
 *
 * @package    Utility
 * @subpackage Service
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Utility_Service_Cache extends Zoo_Service
{
    /**
     * Cache Service Configuration
     *
     * @var Zend_Config
     */
    private $config;
    /**
     * Private storage of caches
     *
     * @var array
     */
    private $caches = array();

    /**
     * Get cache service meta-object
     *
     * @param Zend_Config $config
     * @return Utility_Service_Cache
     */
    public function &getService(Zend_Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get a cache with a certain prefix/name
     *
     * @param string $prefix
     * @param Zend_Config|null $frontendOptions any options set
     *
     * @return Zend_Cache
     */
    public function getCache($prefix = "default", $frontend = 'Core', Zend_Config $frontendOptions = null, Zend_Config $backendOptions = null ) {
        if (!isset($this->caches[$prefix])) {
            $config = clone $this->config;

            if ($frontendOptions) {
                $config->merge($frontendOptions);
            }

            if ($backendOptions) {
                $config->merge($backendOptions);
            }
            $config->frontendOptions->cache_id_prefix = str_replace(array(".", "/"), "_", Zend_Registry::get('config')->site->host)."_".$prefix."_";
            
            if ($config->backendOptions->cache_dir) {
                $config->backendOptions->cache_dir = ZfApplication::$_base_path.$config->backendOptions->cache_dir.DIRECTORY_SEPARATOR.$prefix;

                if (!is_dir($config->backendOptions->cache_dir)){
                    mkdir($config->backendOptions->cache_dir, 0777);
                }
            }

            $customFrontendNaming = (bool) $config->customFrontendNaming;
            $customBackendNaming = (bool) $config->customBackendNaming;
            $autoload = true;
            $this->caches[$prefix] = Zend_Cache::factory($frontend,
                                                 $config->backend,
                                                 $config->frontendOptions->toArray(),
                                                 $config->backendOptions->toArray(),
                                                 $customFrontendNaming,
                                                 $customBackendNaming,
                                                 $autoload);
        }
        return $this->caches[$prefix];
    }

    /**
     * Route calls to nondefined methods to the default cache - output cache
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments) {
        return call_user_func_array(array($this->getCache('output', 'Output'), $name), $arguments);
    }
}
<?php
/**
 * @package Dbmanager
 * @subpackage Plugin
 */

/**
 * @package Dbmanager
 * @subpackage Plugin
 */
class Dbmanager_Plugin_Database extends Zend_Controller_Plugin_Abstract {
    /**
     * Set default adapter and cache for Zend_Db_Table classes
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function routeStartup($request = null) {
        Zend_Db_Table_Abstract::setDefaultAdapter(Zoo::getService('db')->getDb());
        $frontendOptions = new Zend_Config(array('lifetime' => 86400));
        try {
            $metacache = Zoo::getService('cache')->getCache('metadata', 'Core', $frontendOptions);
            Zend_Db_Table_Abstract::setDefaultMetadataCache($metacache);
        }
        catch (Zoo_Exception_Service $e) {
            // No cache service available
        }
    }
}
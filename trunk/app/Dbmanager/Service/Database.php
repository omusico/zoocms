<?php
/**
 * Dbmanager_Service_Database
 *
 * @package   Dbmanager
 * @subpackage Service
 */

/**
 * @package    Dbmanager
 * @subpackage Service
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class DBManager_Service_Database extends Zoo_Service
{
    /**
     * Service object
     *
     * @var Zend_Db
     */
    private $service;

    /**
     * Get service object
     *
     * @param Zend_Config $config
     * @return Zend_Db
     */
    public function &getService(Zend_Config $config)
    {
        if (!$this->service) {
            $db = Zend_Db::factory($config);
            $db->setFetchMode(Zend_Db::FETCH_OBJ);
            $this->service =& $db;

        }
        return $this->service;
    }
}
?>
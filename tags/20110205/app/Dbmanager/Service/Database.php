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
     * Master database object
     *
     * @var Zend_Db
     */
    private $master;
    
    /**
     * Slave database object
     *
     * @var Zend_Db
     */
    private $slave;
    
    /**
     * Use master db for all queries?
     * 
     * @var bool
     */
    private $use_master = false;

    /**
     * Get service object
     *
     * @param Zend_Config $config
     * @return Zend_Db
     */
    public function &getService(Zend_Config $config)
    {
        if (!$this->master) {
            $db = Zend_Db::factory($config->master);
            $db->setFetchMode(Zend_Db::FETCH_OBJ);
            $this->master =& $db;

            /**
             * If slaves configured, pick one at random
             */
            if ($config->slaves) {
                $slaves = $config->slaves->toArray();
                $slave_rnd = rand(0, count($slaves) - 1);
                $slave_db = Zend_Db::factory($slaves[$slave_rnd]);
                $slave_db->setFetchMode(Zend_Db::FETCH_OBJ);
            }
            else {
                $this->slave =& $db;
            }
        }
        return $this;
    }
    
    /**
     * Get database connection
     * 
     * @param string $type - master or slave
     * @return Zend_Db
     */
    public function getDb($type = "slave") {
        if (in_array($type, array('master', 'slave'))) {
            /**
             * @todo Consider whether using the master db exclusively after first use
             */
            return $this->{$type};
        }
        else {
            throw new Zoo_Exception_Service("Invalid database type $type selected");
        }
    }
}
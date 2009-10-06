<?php
/**
 * @package Dbmanager
 */
/**
 * Module definitions
 *
 * @package   Dbmanager
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class DBManager_Module extends Zoo_Module {
    /**
     * Services provided by the module
     *
     * @var array
     */
    protected $services = array('db' => 'DBManager_Service_Database');
    /**
     * Plugins provided by the module
     *
     * @var array
     */
    protected $plugins = array('DBManager_Plugin_Database');
}
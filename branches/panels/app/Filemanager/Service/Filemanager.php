<?php
/**
 * @package    Filemanager
 * @subpackage Service
 */

/**
 * Filemanager_Service_Filemanager
 *
 * @package    Filemanager
 * @subpackage Service
 * @copyright  Copyright (c) 2009 ZooCMS
 * @version    1.0
 */
class Filemanager_Service_Filemanager extends Zoo_Service {
    /**
     *
     * @var Zend_View
     */
    protected $view;

    /**
     *
     * @staticvar User_Profile_Factory $factory
     * @return User_Profile_Factory
     */
    protected function getFactory() {
        static $factory;
        $factory = new Filemanager_File_Factory();
        return $factory;
    }

    /**
     * Route calls to nondefined methods to the User_Profile_Factory
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments) {
        return call_user_func_array(array($this->getFactory(), $name), $arguments);
    }
}
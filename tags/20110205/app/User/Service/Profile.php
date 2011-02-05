<?php
/**
 * @package    User
 * @subpackage Service
 */

/**
 * User_Service_Profile
 *
 * @package    User
 * @subpackage Service
 * @copyright  Copyright (c) 2009 ZooCMS
 * @version    1.0
 */
class User_Service_Profile extends Zoo_Service {
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
        $factory = new User_Profile_Factory();
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

    public function getUrl($id = 0) {
        if ($id == 0 && Zend_Auth::getInstance()->hasIdentity()) {
            $id = Zend_Auth::getInstance()->getIdentity()->id;
        }
        if ($id == 0) {
            //Still no ID
            return "";
        }
        $router = Zend_Controller_Front::getInstance()->getRouter();
        return $router->assemble(array('id' => $id), 'user_profile');
    }

    public function getProfile($id = 0) {
        if ($id == 0 && Zend_Auth::getInstance()->hasIdentity()) {
            $id = Zend_Auth::getInstance()->getIdentity()->id;
        }
        return $this->getFactory()->find($id)->current();
    }

    public function getProfiles($ids = array()) {
        $ret = array(0 => Zoo::_('Anonymous'));
        $users = $this->getFactory()->fetchAll($this->getFactory()->select()->where('uid IN (?)', $ids));
        if ($users) {
            foreach ($users as $user) {
                $ret[$user->uid] = $user;
            }
        }
        return $ret;

    }
}
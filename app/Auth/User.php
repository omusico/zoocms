<?php

/**
 * User class, connecting users to roles through getGroups() method
 * @package    Auth
 * @subpackage User
 */

/**
 * @package    Auth
 * @subpackage    User
 * @copyright  Copyright (c) 2009 ZooCMS
 * @version    1.0
 */
class Auth_User extends Zend_Db_Table_Row_Abstract{
    protected $groups = array();

    /**
     * Get array of group names/role ids
     *
     * @return Array Acl_Role objects
     */
    public function getGroups($update = false) {
        if (!$this->groups) {
            if (!$update && isset($_SESSION['user_groups'])) {
                $this->groups = $_SESSION['user_groups'];
            }
            else {
                $this->groups = Zoo::getService('acl')->getGroups($this->id);
                $_SESSION['user_groups'] = $this->groups;
            }
        }
        return $this->groups;
    }

    /**
     * Logout the user
     */
    public function logout() {
        unset($_SESSION['user_groups']);
    }
}
<?php

/**
 * Basic role class
 * @package    Acl
 * @subpackage Role
 */

/**
 * Acl_Role
 *
 * @package    Acl
 * @subpackage Role
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */

class Acl_Role extends Zend_Db_Table_Row_Abstract implements Zend_Acl_Role_Interface  {
    /**
     * Get role ID for ACL permission
     *
     * @return string
     */
    function getRoleId() {
        return "group_".$this->name;
    }
}
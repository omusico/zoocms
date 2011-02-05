<?php

/**
 * Basic resource class
 * @package    Acl
 * @subpackage Resource
 */

/**
 * Acl_Resource
 *
 * @package    Acl
 * @subpackage Resource
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */

class Acl_Resource extends Zend_Db_Table_Row_Abstract implements Zend_Acl_Resource_Interface   {
    /**
     * Get resource ID for ACL permission
     *
     * @return string
     */
    function getResourceId() {
        $id = "$this->module";
        if ($this->resource != "") {
            $id .= ".".$this->resource;
        }
        if ($this->itemid > 0) {
            $id .= ".".$this->itemid;
        }
        return $id;
    }
}
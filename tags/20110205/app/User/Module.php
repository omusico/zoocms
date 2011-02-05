<?php
/**
 * @package    User
 * @subpackage Module
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */

class User_Module extends Zoo_Module {
    /**
     * Services required by this module
     * @var array
     */
    protected $dependencies = array('db', 'auth');
}
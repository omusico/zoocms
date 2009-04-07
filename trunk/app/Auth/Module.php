<?php
/**
 * Module definitions
 * @package    Auth
 */

/**
 *
 * @package    Auth
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Auth_Module extends Zoo_Module {
    /**
     * Services provided by this module
     * @var array
     */
    protected $services = array('auth' => 'Auth_Service_Authenticator',
                                'user' => 'Auth_Service_User'
    );
}
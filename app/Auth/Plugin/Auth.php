<?php
/**
 * Authentication plugin
 * @package    Auth
 * @subpackage Plugin
 */

/**
 *
 * @package    Auth
 * @subpackage Plugin
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Auth_Plugin_Auth extends Zend_Controller_Plugin_Abstract {
    /**
     * Set auth storage to save in session
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('zooUserSpace'));
    }
}
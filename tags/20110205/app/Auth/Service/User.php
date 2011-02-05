<?php

/**
 * User service for getting a user object to work with
 * @package    Auth
 * @subpackage Service
 */

/**
 * @package    Auth
 * @subpackage Service
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Auth_Service_User extends Zoo_Service {

    /**
     * Create a new Auth_User object
     *
     * @param array $data
     * @return Auth_User
     */
    function createRow(array $data = array()) {
        $factory = new Auth_User_Factory();
        $user = $factory->createRow($data);
        return $user;
    }

    /**
     * Get currently logged in user identity
     *
     * @return Auth_User
     */
    function getCurrentUser() {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            return Zend_Auth::getInstance()->getIdentity();
        }
        else {
            return $this->createRow();
        }
    }

    /**
     * Get registration form for a new user
     *
     * @return Zend_Form
     */
    function getRegistrationForm() {
        $form = new Zend_Form();
        $form->setAction("/register")->setMethod('post');

        $username = new Zend_Form_Element_Text('username');
        $username->setLabel('Username');
        $username->setRequired(true)->addValidator(new Zend_Validate_StringLength(2,255));

        $password = new Zend_Form_Element_Password('password');
        $password->setRequired(true)->setLabel('Password');

        $submit = new Zend_Form_Element_Submit('save');
        $submit->setLabel('Register');

        $form->addElements(array($username, $password));

        $form->addDisplayGroup(array('username', 'password'), 'user_register', array('legend' => Zoo::_('Register user')));

        try {
            Zoo::getService("hook")->trigger("User", "Registerform", $form);
        }
        catch (Zoo_Exception_Service $e) {
            // Hook service not available - log? Better not, some people may live happily without a hook service
        }

        $form->addElement($submit);
        return $form;
    }

    /**
     * Register a new user
     * 
     * @param array $values
     * @return Auth_User
     */
    function registerUser($values) {
        $factory = new Auth_User_Factory();
        $user = $factory->createRow();
        /**
         * @todo Change to random string for salt
         */
        $user->salt = "dkskwkefj6";
        /**
         * @todo Use preferences to determine status and verification - or use hooks for that?
         */
        $user->status = 0;
        $user->password = md5($user->salt . $values['password']);
        $user->username = $values['username'];
        $user->save();

        return $user;
    }
}
<?php
/**
 * Authentication controller
 * @package    Auth
 * @subpackage Controllers
 */

/**
 *
 * @package    Auth
 * @subpackage Controllers
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Auth_IndexController extends Zend_Controller_Action
{
    /**
     * Perform login or display login form
     *
     */
    public function indexAction()
    {
        if ($this->_request->isPost()) {
            $authAdapter = Zoo::getService('auth')
                                    ->setIdentity($_REQUEST['username'])
                                    ->setCredential($_REQUEST['password']);
            $result = Zend_Auth::getInstance()->authenticate($authAdapter);
            switch ($result->getCode()) {

                case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                    /** do stuff for invalid credential **/
                    try {
                        Zoo::getService("hook")->trigger("User", "Faillogin", $result);
                    }
                    catch (Zoo_Exception_Service $e) {
                        // Hook service not available - log? Better not, some people may live happily without a hook service
                    }
                    break;

                case Zend_Auth_Result::SUCCESS:
                    /** do stuff for successful authentication **/
                    $data = $authAdapter->getResultRowObject(null,'password');
                    $data = Zoo::getService('user')->createRow((array)$data);

                    Zend_Auth::getInstance()->getStorage()->write($data);
                    // Flush user group memberships in session
                    $data->getGroups(true);

                    try {
                        Zoo::getService("hook")->trigger("User", "Login", $data);
                    }
                    catch (Zoo_Exception_Service $e) {
                        // Hook service not available - log? Better not, some people may live happily without a hook service
                    }
                    $this->_redirect(Zend_Controller_Front::getInstance()->getBaseUrl());
                    break;

                default:
                    /** do stuff for other failure **/
                    echo Zoo::_("Something else happened");
                    try {
                        Zoo::getService("hook")->trigger("User", "Faillogin", $result);
                    }
                    catch (Zoo_Exception_Service $e) {
                        // Hook service not available - log? Better not, some people may live happily without a hook service
                    }
                    var_dump($result);
                    break;
            }
        }
        elseif (Zend_Auth::getInstance()->hasIdentity()) {
            try {
                // Redirect to user profile if available
                $this->_redirect(Zoo::getService('profile')->getUrl());
            }
            catch (Zoo_Exception_Service $e) {
                var_dump(Zend_Auth::getInstance()->getIdentity());
            }
        }
        else {
            // Show log in form
            $form = new Zend_Form();
            $form   ->setMethod('post');

            $uname = new Zend_Form_Element_Text('username');
            $uname  ->setLabel('Username')
                    ->setRequired(true)
                    ->addValidator(new Zend_Validate_StringLength(5,45));

            $pword = new Zend_Form_Element_Password('password');
            $pword  ->setLabel('Password')
                    ->setRequired(true);

            $submit = new Zend_Form_Element_Submit('login');
            $submit ->setValue(Zoo::_('Log in'));

            $form->addElements(array($uname, $pword));
            $form->addElement($submit);

            $this->view->form = $form;
        }
    }

    /**
     * Perform user logout
     *
     */
    public function logoutAction() {
        Zend_Auth::getInstance()->clearIdentity();
        Zoo::getService('user')->getCurrentUser()->logout();
        try {
            Zoo::getService("hook")->trigger("User", "Logout", $form);
        }
        catch (Zoo_Exception_Service $e) {
            // Hook service not available - log? Better not, some people may live happily without a hook service
        }
        echo Zoo::_("You are now logged out");
        $this->render("index");
    }

    /**
     * Register a new user
     */
    public function registerAction() {
        $form = Zoo::getService('user')->getRegistrationForm();
        if ($this->_request->isPost() && $form->isValid($_REQUEST)) {
            // Register new user
            $values = $form->getValues();
            if ($user = Zoo::getService('user')->registerUser($values)) {
                try {
                    Zoo::getService("hook")->trigger("User", "Register", $form, $user);
                }
                catch (Zoo_Exception_Service $e) {
                    // Hook service not available - log? Better not, some people may live happily without a hook service
                }
            }
        }
        $this->view->form = $form;
        $this->view->form->populate($_REQUEST);
    }
}

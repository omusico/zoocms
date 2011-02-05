<?php
/**
 * Authentication service
 * @package    Auth
 * @subpackage Service
 */

/**
 * @package    Auth
 * @subpackage Service
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Auth_Service_Authenticator extends Zoo_Service
{
    /**
     * Service object
     *
     * @var Zend_Auth_Adapter_Interface
     */
    private $service;

    /**
     * Get service object instance
     *
     * @param Zend_Config $config
     * @return Zend_Auth_Adapter_Interface
     */
    public function &getService(Zend_Config $config)
    {
        if (!$this->service) {
            $className = "Auth_Adapter_".$config->class;

            switch ($config->class) {
            	default:
                    $this->service = new $class();
                    break;

                case "Db":
                    try {
                        $this->service = new Zend_Auth_Adapter_DbTable(Zoo::getService('db')->getDb('slave'));
                        $this->service
                                    ->setTableName('Auth_User')
                                    ->setIdentityColumn('username')
                                    ->setCredentialColumn('password')
                                    ->setCredentialTreatment('md5(CONCAT(salt, ?)) AND status > 0');

                    }
                    catch (Zoo_Exception_Service $e) {
                        // Revert to Basic authentication
                        $this->service = new Auth_Adapter_Basic();
                    }
            }
        }
        return $this->service;
    }
}

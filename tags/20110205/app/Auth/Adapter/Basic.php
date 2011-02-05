<?php
/**
 * Module definitions
 * @package    Auth
 * @subpackage Adapter
 */

/**
 *
 * @package    Auth
 * @subpackage Adapter
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Auth_Adapter_Basic implements Zend_Auth_Adapter_Interface
{
    private $username;
    private $password;
    private $result = array();

    /**
     * Set username for authentication
     *
     * @param string $username
     *
     * @return Auth_Adapter_Basic
     */
    public function setIdentity($username) {
        $this->username = $username;
        return $this;
    }

    /**
     * Sets password for authentication
     *
     * @param string $password
     *
     * @return Auth_Adapter_Basic
     */
    public function setCredential($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Performs an authentication attempt
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        if ($this->username == "Admin" && $this->password == "Admin") {
            $this->result = array('id' => 1, 'username' => $this->username, 'password' => $this->password, 'status' => 1);
            return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $this->username);
        }
        return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID , $this->username);
    }

    /**
      * getResultRowObject() - Returns the result row as a stdClass object
      *
      * @param  string|array $returnColumns
      * @param  string|array $omitColumns
      * @return stdClass
      */
     public function getResultRowObject($returnColumns = null, $omitColumns = null)
     {
         if (!$this->result) {
             return false;
         }

         $returnObject = new stdClass();

         if (null !== $returnColumns) {
             $availableColumns = array_keys($this->result);
             foreach ( (array) $returnColumns as $returnColumn) {
                 if (in_array($returnColumn, $availableColumns)) {
                     $returnObject->{$returnColumn} = $this->_resultRow[$returnColumn];
                 }
             }
         } elseif (null !== $omitColumns) {

             $omitColumns = (array) $omitColumns;
             foreach ($this->result as $resultColumn => $resultValue) {
                 if (!in_array($resultColumn, $omitColumns)) {
                     $returnObject->{$resultColumn} = $resultValue;
                 }
             }
         } else {
             foreach ($this->result as $resultColumn => $resultValue) {
                 $returnObject->{$resultColumn} = $resultValue;
             }
         }
         return $returnObject;
     }
}


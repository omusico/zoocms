<?php
/**
 * @package    Zoo
 * @subpackage Session
 */
/**
 * Zoo_Session_Savehandler
 *
 * @package    Zoo
 * @subpackage Session
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 * @see http://us3.php.net/manual/en/function.session-set-save-handler.php
 */
class Zoo_Session_Savehandler implements Zend_Session_SaveHandler_Interface {
    protected $_storage;
    /**
     * Open Session - retrieve resources
     *
     * @param string $save_path
     * @param string $name
     */
    public function open($save_path, $name) {
        $this->_storage = Zoo::getService('cache')->getCache('session');
        return true;
    }

    /**
     * Close Session - free resources
     *
     */
    public function close() {
        unset($this->_storage);
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     */
    public function read($id) {
        if ($ret = $this->_storage->load($id)) {
            return $ret;
        }
        return '';
    }

    /**
     * Write Session - commit data to resource
     *
     * @param string $id
     * @param mixed $data
     */
    public function write($id, $data) {
        return $this->_storage->save($data, $id, null, (30*60));
    }

    /**
     * Destroy Session - remove data from resource for
     * given session id
     *
     * @param string $id
     */
    public function destroy($id) {
        return $this->_storage->remove($id);
    }

    /**
     * Garbage Collection - remove old session data older
     * than $maxlifetime (in seconds)
     *
     * @param int $maxlifetime
     */
    public function gc($maxlifetime) {
        return true;
    }
}
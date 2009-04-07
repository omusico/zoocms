<?php
/**
 * @package    ZooLib
 * @subpackage Session
 */
/**
 * Zoo_Session_Savehandler
 *
 * @package    ZooLib
 * @subpackage Session
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 * @see http://us3.php.net/manual/en/function.session-set-save-handler.php
 */
class Zoo_Session_Savehandler implements Zend_Session_SaveHandler_Interface {
    /**
     * Open Session - retrieve resources
     *
     * @param string $save_path
     * @param string $name
     */
    public function open($save_path, $name) {
        return true;
    }

    /**
     * Close Session - free resources
     *
     */
    public function close() {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     */
    public function read($id) {
        /*$sql = sprintf('SELECT sess_data FROM %s WHERE sess_id = %s', $this->db->prefix('session'), $this->db->quoteString($id));
        if (false != $result = $this->db->query($sql)) {
            if (list($sess_data) = $this->db->fetchRow($result)) {
                return $sess_data;
            }
        }
        return '';*/
    }

    /**
     * Write Session - commit data to resource
     *
     * @param string $id
     * @param mixed $data
     */
    public function write($id, $data) {
        /*$sess_id = $this->db->quoteString($id);
		$sql = sprintf('UPDATE %s SET sess_updated = %u, sess_data = %s WHERE sess_id = %s', $this->db->prefix('session'), time(), $this->db->quoteString($data), $sess_id);
	    $this->db->queryF($sql);
        if (!$this->db->getRowsNum()) {
			$sql = sprintf('INSERT INTO %s (sess_id, sess_updated, sess_ip, sess_data) VALUES (%s, %u, %s, %s)', $this->db->prefix('session'), $sess_id, time(), $this->db->quoteString($_SERVER['REMOTE_ADDR']), $this->db->quoteString($data));
    		if (!$this->db->queryF($sql)) {
                return false;
            }
		}
		return true;*/
    }

    /**
     * Destroy Session - remove data from resource for
     * given session id
     *
     * @param string $id
     */
    public function destroy($id) {
        /*$sql = sprintf('DELETE FROM %s WHERE sess_id = %s', $this->db->prefix('session'), $this->db->quoteString($id));
        return (bool) $this->db->queryF($sql);*/
    }

    /**
     * Garbage Collection - remove old session data older
     * than $maxlifetime (in seconds)
     *
     * @param int $maxlifetime
     */
    public function gc($maxlifetime) {
        /*$mintime = time() - intval($maxlifetime);
		$sql = sprintf('DELETE FROM %s WHERE sess_updated < %u', $this->db->prefix('session'), $mintime);
        return $this->db->queryF($sql);*/
    }
}
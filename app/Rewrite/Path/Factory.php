<?php
/**
 * @package  Rewrite
 * @subpackage Path
 *
 */

/**
 * @package  Rewrite
 * @subpackage Path
 *
 */
class Rewrite_Path_Factory extends Zoo_Db_Table {
	public function findPath($path) {
	    $cacheid = "rewrite_".str_replace(array('/', '-', '?', '=', '%', '&', '+', '(', ')'), '_', $path);
	    try {
	        $ret = Zoo::getService('cache')->load($cacheid);
	        if (!$ret) {
	            $ret = $this->fetchRow($this->select()->where('path = ?', $path));
	            if ($ret) {
	                Zoo::getService('cache')->save($ret, $cacheid, array('node_'.$ret->nid));
	            }	            
	        }
	    }
	    catch (Zoo_Exception_Service $e) {
	        $ret = $this->fetchRow($this->select()->where('path = ?', $path));
	    }
		return $ret;
	}
}
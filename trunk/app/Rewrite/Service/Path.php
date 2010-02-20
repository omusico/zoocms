<?php
/**
 * @package    Rewrite
 * @subpackage Service
 */

/**
 * @package    Rewrite
 * @subpackage Service
 * @copyright  Copyright (c) 2010 ZooCMS
 * @version    1.0
 */
 class Rewrite_Service_Path extends Zoo_Service {
 	/**
     *
     * @staticvar Rewrite_Path_Factory $factory
     * @return Rewrite_Path_Factory
     */
    public function getFactory() {
        static $factory;
        $factory = new Rewrite_Path_Factory();
        return $factory;
    }
    
    /**
     * Get path for a content node
     * 
     * @param int $id
     * @return string
     */
 	public function getNodeUrl($id) {
	 	$found = $this->getFactory()->find ( $id );
		if ($found->count () == 0) {
			throw new Rewrite_Exception_PathNotFound();
		}
		return $found->current()->path;
 	}
 	
 	/**
 	 * Find a path in the database
 	 * 
 	 * @param $path
 	 * @return Rewrite_Path
 	 */
     public function findPath($path) {
	    $cacheid = "rewrite_".str_replace(array('.', '/', '-', '?', '=', '%', '&', '+', '(', ')'), '_', $path);
	    try {
	        $ret = Zoo::getService('cache')->load($cacheid);
	        if (!$ret) {
	            $ret = $this->getFactory()->fetchRow($this->getFactory()->select()->where('path = ?', $path));
	            if ($ret) {
	                Zoo::getService('cache')->save($ret, $cacheid, array('node_'.$ret->nid));
	            }	            
	        }
	    }
	    catch (Zoo_Exception_Service $e) {
	        $ret = $this->getFactory()->fetchRow($this->getFactory()->select()->where('path = ?', $path));
	    }
		return $ret;
	}
 }

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
 }

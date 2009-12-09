<?php
/**
 * @package Flex
 * @subpackage Block
 */

/**
 * @package    Flex
 * @subpackage Block
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Flex_Block_Visibility extends Zend_Db_Table {

    /**
     * Fetch IDs of blocks that are visible on this page
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return array
     */
    public function getVisibleBlocks(Zend_Controller_Request_Abstract $request) {
        $groups = array(0);
        try {
            $roles = Zoo::getService('user')->getCurrentUser()->getGroups();
            $groups = array_keys($roles);
        }
        catch (Zoo_Exception_Service $e) {
            // Do nothing
        }
        $select = $this->select()
                    ->where('module IN (?)', array('',$request->getModuleName()))
                    ->where('controller IN (?)', array('', $request->getControllerName()))
                    ->where('action IN (?)', array('', $request->getActionName()))
                    ->where('id IN (?)', array(0,$request->getParam('id',0)))
                    ->where('group_id IN (?)', $groups);

        return $this->fetchAll($select);
    }
}
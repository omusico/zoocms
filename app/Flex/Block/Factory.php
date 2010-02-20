<?php
/**
 * @package Flex
 * @subpackage Block
 */

/**
 * Flex_Block_Factory
 *
 * @package    Flex
 * @subpackage Block
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */

class Flex_Block_Factory extends Zoo_Db_Table {

    /**
     * Fetch blocks that are visible on this page
     *
     * @return array
     */
    public function getBlocks() {
        $ret = array();
        $groups = array(0);
        try {
            $roles = Zoo::getService('user')->getCurrentUser()->getGroups();
            $groups = array_keys($roles);
        }
        catch (Zoo_Exception_Service $e) {
            // Do nothing
        }
        
        $cacheid = str_replace(array("/", "?", "=", "&", "-", '.'), "_", "Flex_blocks_page".Zend_Controller_Front::getInstance()->getRequest()->getRequestUri()."_".implode("_", $groups));
        try {
            $ret = Zoo::getService("cache")->load($cacheid);
        }
        catch (Zoo_Exception_Service $e) {
            // @todo: Remove this comment: I'm beginning to tire of writing try-catch for services
        }
        if (!$ret) {
            $ret = $this->fetchFromDb();
            try {
                Zoo::getService('cache')->save($ret, $cacheid, array(), null);
            }
            catch (Zoo_Exception_Service $e) {
                // No caching available
            }
        }
        // Return array ordered by block position
        return $ret;
    }

    /**
     * Fetch block objects to be displayed on the current page
     * @return array
     */
    private function fetchFromDb() {
        $ret = array();

        $request = Zend_Controller_Front::getInstance()->getRequest();

        // Get visible block IDs
        $visible_factories[] = new Flex_Block_Visibility();
        /**
         * @todo add content visibility factory - dynamically... Hook?
         */

        $visibles = array();
        foreach ($visible_factories as $factory) {
            $visibles[] = $factory->getVisibleBlocks($request);
        }

        $blockids = array();
        $continue = false;
        foreach ($visibles as $visible){
            if ($visible->count() > 0) {
                $continue = true;
                foreach ($visible as $visible_block) {
                    $blockids[] = $visible_block->block_id;
                }
            }
        }

        if (!$continue) {
            return $ret;
        }

        // Get the corresponding blocks
        $blocks = $this->fetchAll($this->select()->where("id IN (?)", $blockids)->order('weight'));

        if (!$blocks) {
            return $ret;
        }

        // Instantiate specific block
        foreach ($blocks as $block) {
            $class = $block->type;
            $ret[$block->position][] = new $class($block->toArray());
        }
        return $ret;
    }
}
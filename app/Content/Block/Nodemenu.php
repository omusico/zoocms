<?php
/**
 * @package Content
 * @subpackage Block
 */

/**
 * @package    Content
 * @subpackage Block
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Content_Block_Nodemenu extends Zoo_Block_Abstract  {
    /**
     * Get cache ID for the block
     * @see Zoo_Block_Abstract#getCacheId()
     */   
    function getCacheId() {
        if (Zend_Registry::isRegistered('content_id') && Zend_Registry::get('content_id') > 0 ) {
            return get_class($this)."_".Zend_Registry::get('content_id'); 
        }
        return false;
    }
    /**
     * Retrieve node menu items
     * 
     * @return array
     * 
     * @uses Hook_Node::nodeMenu()
     */
    function getTemplateVars() {
        if (Zend_Registry::getInstance()->isRegistered('content_id')) {
            $item = Zoo::getService('content')->load(Zend_Registry::get('content_id'), 'Menu');
            $menu = new Zend_Navigation($item->hooks['menu']);
            return array('menu' => $menu);
        }
        return false;
    }
}
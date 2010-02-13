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
     * Retrieve node menu items
     * 
     * @return array
     * 
     * @uses Hook_Node::nodeMenu()
     */
    function getTemplateVars() {
        if (Zend_Registry::getInstance()->isRegistered('content_id')) {
            $item = Zoo::getService('content')->load(Zend_Registry::getInstance()->get('content_id'), 'Menu');
            $menu = new Zend_Navigation($item->hooks['menu']);
            return array('menu' => $menu);
        }
        return false;
    }
}
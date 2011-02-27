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
class Content_Block_Nodemenu extends Zoo_Block_Abstract {

  /**
   * Get cache ID for the block
   * @see Zoo_Block_Abstract#getCacheId()
   */
  function getCacheId() {
    $context = Zend_Registry::get('context');
    if (isset($context->node) && $context->node->id > 0) {
      return get_class($this) . "_" . $context->node->id;
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
    $context = Zend_Registry::get('context');
    if (isset($context->node) && $context->node->id > 0) {
      $item = Zoo::getService('content')->load($context->node->id, 'Menu');
      $menu = new Zend_Navigation($item->hooks['menu']);
      return array('menu' => $menu);
    }
    return false;
  }

}
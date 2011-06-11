<?php
/**
 * @package    Navigate
 * @subpackage Menu
 */

/**
 * @package    Navigate
 * @subpackage Menu
 * @copyright  Copyright (c) 2010 ZooCMS
 * @version    1.0
 */
class Navigate_Menu_Item extends Zend_Db_Table_Row_Abstract {
    /**
     * id
     * pid (parent - another menu item)
     * title
     * destination (numeric = content node id, else url)
     * target
     * active
     * weight
     */
  protected $_node = null;
  protected $_resource = '';
  protected $_privilege = '';
  
  function init() {
    if (is_numeric($this->destination)) {
      $this->_node = Zoo::getService('content')->load($this->destination, 'List');
      $this->_resource = 'content.node';
      $this->_privilege = 'index.' . $this->_node->type;
    }
  }
  /**
   * Get URL for this menu item
   * @return type string
   */
  function url() {
    return $this->_node ? $this->_node->url() : $this->destination;
  }
  
  /**
   * Return ACL resource for access check
   * @return type string
   */
  function resource() {
    return $this->_resource; 
  }
  
  function privilege() {
    return $this->_privilege;
  }
}
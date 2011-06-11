<?php
/**
 * @package  Navigate
 * @subpackage Menu
 *
 */

/**
 * @package  Navigate
 * @subpackage Menu
 *
 */
class Navigate_Menu_Item_Factory extends Zoo_Db_Table {
  
  /**
   * Get all menu items from a given ID and down the hierarchy
   * 
   * @param type $root
   * @return type Zend_Db_Table_Rowset_Abstract
   */
  public function fetchWithChildren($root = 0) {
    $select = $this->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
    $select->setIntegrityCheck(false)
       ->where('active = ?', 1)
       ->joinUsing('Navigate_Menu_Hierarchy', 'id', array())
       ->where('Navigate_Menu_Hierarchy.pid = ?', $root);
    return $this->fetchAll($select);
  }
}
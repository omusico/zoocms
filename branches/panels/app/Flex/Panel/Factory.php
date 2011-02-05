<?php

class Flex_Panel_Factory  extends Zoo_Db_Table {
  /**
   * Get panel for currently viewed page
   * 
   * @return Flex_Panel
   */
  function getCurrentPanel() {
    if ($node = Zend_Registry::get('context')->node) {
      
    }
    return false;
  }
}
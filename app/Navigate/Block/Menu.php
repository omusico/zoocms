<?php

/**
 * @package Navigate
 * @subpackage Block
 */

/**
 * @package    Navigate
 * @subpackage Block
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Navigate_Block_Menu extends Zoo_Block_Abstract {

  /**
   * Retrieve node listing
   *
   * @return array
   */
  function getTemplateVars() {
    if (!isset($this->options['menu'])) {
      $this->options['menu'] = 0;
    }
    return array(
      'menu' => Zoo::getService('menu')->getContentMenu($this->options['menu'])
    );
  }

  /**
   * Get cache ID - differs depending on URL, so no cache - the navigation object itself is cached
   * @see library/Zoo/Block/Zoo_Block_Abstract#getCacheId()
   */
  function getCacheId() {
    return false;
  }

  /**
   * @return bool|Zend_Form_Subform
   */
  function getOptions() {
    $menus = Zoo::getService('menu')->fetchAll();
    // Build menu tree
    $tree = new Zoo_Object_Tree($menus, 'id', 'pid');

    $options = $tree->getIndentedArray('title', 0, '-');
    
    $form = new Zend_Form_SubForm();
    
    $menu_select = new Zend_Form_Element_Select('menu');
    $menu_select->setLabel('Menu');
    $menu_select->addMultiOptions($options);
    $form->addElement($menu_select);
    return $form;
  }
}
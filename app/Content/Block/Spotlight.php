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
class Content_Block_Spotlight extends Zoo_Block_Abstract {

  private $can_edit = false;
  private $content_id = 0;

  /**
   * Retrieve galleries listing
   *
   * @return array
   */
  function getTemplateVars() {
    $item = Zoo::getService('content')->getContent($this->getSelectOptions(), 0, 1);
    return array('item' => array_shift($item), 'can_edit' => $this->can_edit, 'id' => $this->content_id);
  }

  /**
   * Get cache ID - differs depending on whether the user can edit the content or not
   * @see library/Zoo/Block/Zoo_Block_Abstract#getCacheId()
   */
  function getCacheId() {
    $select = Zoo::getService('content')->getContentSelect($this->getSelectOptions(), 0, 1);
    $rowset = Zoo::getService('content')->fetchAll($select);
    foreach ($rowset as $item) {
      // Will only be one item
      $this->content_id = $item->id;
      try {
        $this->can_edit = Zoo::getService('acl')->checkItemAccess($item, 'edit');
      } catch (Zoo_Exception_Service $e) {
        // No acl service installed
      }
      return parent::getCacheId() . ($this->can_edit ? "_edit" : "");
    }
  }

  /**
   * Get the options used for selecting the spotlight node
   * @return array
   */
  private function getSelectOptions() {
    return array('active' => true,
        'nodetype' => 'content_node',
        'viewtype' => 'Display',
        'render' => true);
  }

}
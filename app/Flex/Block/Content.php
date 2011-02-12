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
class Flex_Block_Content extends Zoo_Block_Abstract {

  public $template = "content";

  /**
   * Get cache ID for this block - will be used only if content is empty
   * @return string
   */
  function getCacheId() {
    $content_key = Zend_Layout::getMvcInstance()->getContentKey();
    if (!Zend_Layout::getMvcInstance()->{$content_key}) {
      $this->cache_time = 3600;
      return get_class($this) . "_" . $this->id;
    }
    else {
      // Disable cache?
      $this->cache_time = 0;
    }
  }

  /**
   * Return an array of vars to be assigned to the Zend_View_Abstract object for use in the block's template
   *
   * @return array
   */
  function getTemplateVars() {
    $content_key = Zend_Layout::getMvcInstance()->getContentKey();
    return array('content' => Zend_Layout::getMvcInstance()->{$content_key});
  }

}
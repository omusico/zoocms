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
   * Fetch block class objects
   *
   * @return array
   */
  public function getBlocks($blockids) {
    $ret = array();

    $blocks = $this->find($blockids);

    if ($blocks) {
      // Instantiate specific block
      foreach ($blocks as $block) {
        $class = $block->type;
        $ret[$block->id] = new $class($block->toArray());
      }
    }
    return $ret;
  }
}
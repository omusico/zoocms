<?php
/**
 *
 * @package    Flex
 * @subpackage Panel
 */

/**
 * Flex_Panel_Block_Factory
 *
 * @package    Flex
 * @subpackage Panel
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Flex_Panel_Block_Factory extends Zoo_Db_Table {
  /**
   * Get all blocks for a given panel, including blocks that fall down from parent panels
   * ordered by region
   *
   * @param Flex_Panel $panel
   * @return array
   */
  function getPanelBlocks($panel) {
    $ret = array();
    $panel_blocks = $this->fetchAll($this->select()
                                      ->where("panel_id = ?", $panel->id)
                                      ->order('weight'));

    $blockids = array();
    foreach ($panel_blocks as $block) {
      $blockids[] = $block->block_id;
    }

    $parents = $panel->getAllParents();
    $parent_blocks = array();

    if ($parents) {
      $parentids = array();
      foreach ($parents as $parent) {
        $parentids[] = $parent->id;
      }
      // Fetch from parent panel where falldown is true
      $parent_blocks = $this->fetchAll($this->select()
                              ->where("panel_id IN (?)", $parentids)
                              ->where('falldown = ?', 1)
                              ->order('weight'));
      foreach ($parent_blocks as $block) {
        $blockids[] = $block->block_id;
      }
    }

    $block_factory = new Flex_Block_Factory();
    $block_instances = $block_factory->getBlocks($blockids);

    // Parent blocks go first
    foreach ($parent_blocks as $block) {
      if (isset($block_instances[$block->block_id])) {
        $block_instances[$block->block_id]->panel = $panel;
        $block_instances[$block->block_id]->panel_block = $block;
        $ret[$block->region][] = $block_instances[$block->block_id];
      }
    }
    foreach ($panel_blocks as $block) {
      if (isset($block_instances[$block->block_id])) {
        $block_instances[$block->block_id]->panel = $panel;
        $block_instances[$block->block_id]->panel_block = $block;
        $ret[$block->region][] = $block_instances[$block->block_id];
      }
    }
    // Post-fetch hook
    Zoo::getService('hook')->trigger('panel', 'blocks', $panel, $ret);
    return $ret;
  }
}
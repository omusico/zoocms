<?php
/**
 * @package    Search
 * @subpackage Hook
*/

/**
 * @package    Search
 * @subpackage Hook
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Search_Hook_Node extends Zoo_Hook_Abstract {
    /**
     * Hook for node save - index node in search index
     *
     * @param Zend_Form $form
     * @param array $arguments
     */
    public function nodeSave(&$form, &$arguments) {
        $item = array_shift($arguments);
        Zoo::getService('search')->index($item);
    }
}
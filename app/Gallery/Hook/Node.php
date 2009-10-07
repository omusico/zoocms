<?php
/**
 * @package Gallery
 * @subpackage Hook
 */

/**
 * @package    Gallery
 * @subpackage Hook
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */

class Gallery_Hook_Node extends Zoo_Hook_Abstract {
    /**
     * Hook for node display - fetches Gallery Node
     *
     * @param Zoo_Content_Interface $item
     *
     * @return void
     */
    public function nodeDisplay(&$item) {
        if ($item->type == "gallery_node") {
            // Find files connected to the gallery
            $factory = new Gallery_Node_Factory();
            $item->hooks['gallery_nodes'] = $factory->getGalleryImages($item);
        }
    }

    /**
     * Hook for node listing - fetches Filemanager Node information
     *
     * @param array $items
     *
     * @return void
     *
     * @todo Change to fetch all information for all Filemanager nodes in one go
     */
    public function nodeList(&$items) {
        
    }

    /**
     * Hook for node form - if type is Filemanager Node, add extra fields
     *
     * @param Zend_Form $form
     * @param array $arguments
     */
    public function nodeForm(Zend_Form &$form, &$arguments) {
        $item =& array_shift($arguments);
        if ($item->type == "gallery_node") {
            // Add image connector
        }
    }

    /**
     * Hook for node save - if type is Gallery Node, save connected images
     *
     * @param Zend_Form $form
     * @param array $arguments
     */
    public function nodeSave(&$form, &$arguments) {
        $item = array_shift($arguments);
        $arguments = array_shift($arguments);
        if ($item->type == "gallery_node") {
            
        }
    }
}
<?php
/**
 * @package Gallery
 * @subpackage Block
 */

/**
 * @package    Gallery
 * @subpackage Block
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Gallery_Block_List extends Zoo_Block_Abstract  {
    public $template = "list";
    
    /**
     * Retrieve galleries listing
     * 
     * @return array
     */
    function getTemplateVars() {
        $galleries = Zoo::getService('content')->getContent(
                                                    array('active' => true,
                                                    	  'nodetype' => 'gallery_node',
                                                          'order' => 'published'),
                                                    0,
                                                    0);
        $tree = new Zoo_Object_Tree($galleries, 'id', 'pid');
        return array('galleries' => $tree->toArray());
    }
}
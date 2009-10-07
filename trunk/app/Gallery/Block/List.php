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
                                                          'order' => 'title'));
        return array('galleries' => $galleries);
    }
}
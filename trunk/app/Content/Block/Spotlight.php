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
class Content_Block_Spotlight extends Zoo_Block_Abstract  {
    public $template = "spotlight";
    
    /**
     * Retrieve galleries listing
     * 
     * @return array
     */
    function getTemplateVars() {
    	$item = Zoo::getService('content')->getContent(array('active' => true,
                                                             'nodetype' => 'content_node',
    													  	 'viewtype' => 'Display',
                                                             'render' => true), 0, 1);
        return array('item' => array_shift($item));
    }
}
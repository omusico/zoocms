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
class Content_Block_Admin extends Zoo_Block_Abstract  {
    public $template = "admin";
    
    /**
     * Retrieve galleries listing
     * 
     * @return array
     */
    function getTemplateVars() {
        return array('types' => Zoo::getService('content')->getTypes());
    }
}
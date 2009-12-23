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
    	$item = Zoo::getService('content')->getContent($this->getSelectOptions(), 0, 1);
    	$can_edit = false;
    	try {
	        $can_edit = Zoo::getService('acl')->checkItemAccess($item, 'edit');
        }
        catch (Zoo_Exception_Service $e) {
        	// No acl service installed
        }
        return array('item' => array_shift($item), 'can_edit' => $can_edit);
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
    		$can_edit = "";
	    	try {
		        $can_edit = Zoo::getService('acl')->checkItemAccess($item, 'edit') ? "_edit" : "";
	        }
	        catch (Zoo_Exception_Service $e) {
	        	// No acl service installed
	        }
    		return parent::getCacheId().$can_edit;
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
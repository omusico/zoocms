<?php
/**
 * @package Gallery
 * @subpackage Controllers
 */

/**
 * Gallery display
 *
 * @package Gallery
 * @subpackage Controllers
 * @copyright  Copyright (c) 2009 ZooCMS
 * @version    1.0
 * @author ZooCMS
 */
class Gallery_GalleryController extends Zoo_Controller_Action {
	protected $item;
	
	public function init() {
		$id = intval($this->getRequest()->getParam('id'));
        /**
         * @todo more generic sanitation than intval??
         */
        Zend_Registry::set('content_id', $id);

        $found = Zoo::getService('content')->find($id);
        if ($found->count() == 0) {
            throw new Zend_Controller_Action_Exception(Zoo::_("Content not found"), 404);
        }
        $item = $found->current();
        
        try {
	    	if (!Zoo::getService('acl')->checkItemAccess($item, 'edit')) {
	        	throw new Exception(Zoo::_("Access denied - insufficient privileges"), 403);
	        }
        }
    	catch (Zoo_Exception_Service $e) {
        	// No acl service installed
        }

        $this->view->assign('item', $item);
        $this->item =& $item;
	}
}
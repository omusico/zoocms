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
	
    public function imagesAction() {
        $this->view->headScript()->appendFile('/js/infusion/InfusionAll.js', 'text/javascript');
		$this->view->headScript()->appendFile('/js/infusion/framework/core/js/ProgressiveEnhancement.js', 'text/javascript');

		$this->view->headLink()->appendStylesheet('/js/infusion/framework/fss/css/fss-layout.css');
		$this->view->headLink()->appendStylesheet('/js/infusion/components/uploader/css/Uploader.css');
    }
    
    public function reorderAction() {
		// Find files connected to the gallery
		$this->item->hooks ['gallery_nodes'] = Zoo::getService('link')->getLinkedNodes($this->item, 'gallery_image');
		
		// Add Infusion JS
		$layout = Zend_Layout::getMvcInstance ();
		$theme_folder = Zend_Controller_Front::getInstance ()->getBaseUrl () . "/themes/" . $layout->getLayout ();
		
		$this->view->headScript()->appendFile('/js/infusion/InfusionAll.js', 'text/javascript');
		$this->view->headLink()->appendStylesheet('/js/infusion/framework/fss/css/fss-layout.css');
		$this->view->headLink()->appendStylesheet('/js/infusion/components/reorderer/css/Reorderer.css');
		$this->view->headLink()->appendStylesheet('/js/infusion/components/reorderer/css/ImageReorderer.css');
		$this->view->headLink()->appendStylesheet('http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/smoothness/jquery-ui.css');
		
		$this->view->assign('pagetitle', $this->item->title);
    }
    
    public function removeAction() {
    	if ($this->getRequest()->isPost()) {
    		Zoo::getService('link')->remove($this->item->id, intval($this->getRequest()->getParam('image')), 'gallery_image');
    		echo "Image removed";
    	}
    	else {
    		echo Zoo::_("Are you sure, you want to remove %s from the gallery?", $this->item->title);
    	}
    	$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
    }
    
    public function performreorderAction() {
    	$gallery = $this->item;
        // Reorder files
        //Target is on the form gallery:::gallery-thumbs:::lightbox-cell:[ID]
        $targetId = intval(substr($this->getRequest()->getParam('target'), strrpos($this->getRequest()->getParam('target'), ":")+1));
        // -1 = before, 1 = after
        $position = $this->getRequest()->getParam('position');
        Zoo::getService('link')->update('gallery_image', $gallery, $this->getRequest()->getParam('movedId'), $targetId, $position);
        
        try {
        	Zoo::getService('cache')->remove(get_class($gallery)."_".$gallery->id);
        	Zoo::getService('cache')->remove(get_class($gallery)."_".$gallery->id."_edit");
        }
        catch (Zoo_Exception_Service $e) {
        	// No caching service installed, nothing to remove
        }
    	
        $this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
    }
}
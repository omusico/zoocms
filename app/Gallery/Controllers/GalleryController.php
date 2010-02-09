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
	    	if (!Zoo::getService('acl')->checkItemAccess($item)) {
	        	throw new Exception(Zoo::_("Access denied - insufficient privileges"), 403);
	        }
        }
    	catch (Zoo_Exception_Service $e) {
        	// No acl service installed
        }

        $this->view->assign('item', $item);
        $this->item =& $item;
        $this->view->assign('pagetitle', $item->title);
	}
	
	public function imageAction() {
	    $id = $this->getRequest()->getParam('file_id');
	    $cacheid = "Gallery_nodeDisplay_".$id;
	    $content = $this->checkCache($cacheid);
        if (!$content) {
            $item = Zoo::getService ( 'content' )->load($id, 'Display');
            $this->view->assign('image', $item);
            
            // Get previous/next image
            $this->view->previous = Zoo::getService('link')->getPrevious($this->item->id, $item->id, 'gallery_image');
            $this->view->next = Zoo::getService('link')->getNext($this->item->id, $item->id, 'gallery_image');
            
            // Set colours
            $bg_image_style = $bg_color_style = "";
            $top_image = Zoo::getService('link')->getLinkedNodes($this->item, 'top_image');
            if (count($top_image) > 0) {
            	$this->item->hooks['top_image'] = $top_image[0];
            }
            
        	$bg_image = Zoo::getService('link')->getLinkedNodes($this->item, 'bg_image');
            if (count($bg_image) > 0) {
            	$bg_image_style = "background-image: url('".$bg_image[0]->hooks['filemanager_file']->getUrl()."');";
            }
            // Find Gallery node extra information
            $factory = new Gallery_Node_Factory();
            $extra = $factory->find($this->item->id)->current();
            if ($extra) {
            	if (substr($extra->bgcolor, 0, 1) != "#") {
            		$extra->bgcolor = "#".$extra->bgcolor;
            	}
            	$bg_color_style = "background-color: ".$extra->bgcolor.";";
            }
            if ($bg_image_style || $bg_color_style) {
            	$this->view->headStyle()->appendStyle(".gallery-node-item {{$bg_image_style}{$bg_color_style}}");
            }
            
            $content = $this->getContent();
            $this->cache($content, $cacheid, array('gallery', 'gallery_'.$item->pid, 'node_'.$item->pid));
        }
        
        $this->renderContent($content);
	}
}
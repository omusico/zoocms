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
     * Add menu items to a node edit block menu
     * @param Zoo_Content_Service $item
     * @return void
     */
    public function nodeMenu($item) {
        if ($item->type == "gallery_node") {
            $item->hooks['menu'][] = new Zend_Navigation_Page_Mvc(array('label' => Zoo::_('Images'),
                                                                        'route' => 'default',
            															'module' => 'filemanager',
            															'controller' => 'file', 
            															'action' => 'browse',
                                                                        'target' => '_blank', 
            															'params' => array('connectTo' => $item->id,
                                                                                          'type' => 'gallery_image')));
        }
    }
    
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
            $item->hooks['gallery_nodes'] = Zoo::getService('link')->getLinkedNodes($item, 'gallery_image');
            
            $bg_image_style = $bg_color_style = "";
            $top_image = Zoo::getService('link')->getLinkedNodes($item, 'top_image');
            if (count($top_image) > 0) {
            	$item->hooks['top_image'] = $top_image[0];
            }
            
        	$bg_image = Zoo::getService('link')->getLinkedNodes($item, 'bg_image');
            if (count($bg_image) > 0) {
            	$bg_image_style = "background-image: url('".$bg_image[0]->hooks['filemanager_file']->getUrl()."');";
            }
            
            $options = array('nodetype' => 'gallery_node', 
            				 'parent' => $item->id, 
            				 'render' => true,
            				 'order' => 'published ASC');
            $item->hooks['subgalleries'] = Zoo::getService('content')->getContent($options, 0, 0);
            
            // Find Gallery node extra information
            $factory = new Gallery_Node_Factory();
            $extra = $factory->find($item->id)->current();
            if ($extra) {
            	if (substr($extra->bgcolor, 0, 1) != "#") {
            		$extra->bgcolor = "#".$extra->bgcolor;
            	}
            	$bg_color_style = "background-color: ".$extra->bgcolor.";";
            }
            
            $item->hooks ['gallery_config'] = Zoo::getConfig ( 'gallery', 'module' );
            if ($item->hooks ['gallery_config']->lightbox) {
                // Add Lightbox JS
                $view = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'viewRenderer' )->view;
                
                $view->jQuery ()->enable ()->uiEnable ();
                $view->jQuery ()->addJavascriptFile ( '/js/jquery/lightbox/js/jquery.lightbox-0.5.js', 'text/javascript' );
                $view->jQuery ()->addStylesheet ( '/js/jquery/lightbox/css/jquery.lightbox-0.5.css' );
                
                $js = ZendX_JQuery_View_Helper_JQuery::getJQueryHandler () . '(".gallery_node_list a").lightBox({txtImage: "' . Zoo::_ ( 'Image' ) . '", txtOf: "' . Zoo::_ ( 'of' ) . '"});';
                $view->jQuery()->addOnLoad($js);
            }
            if ($bg_image_style || $bg_color_style) {
                $view = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'viewRenderer' )->view;
            	$view->headStyle()->appendStyle(".gallery-node-item {{$bg_image_style}{$bg_color_style}}");
            }
        }
    }

    /**
     * Hook for node listing - fetches Filemanager Node information
     *
     * @param Zoo_Content_Interface $items
     *
     * @return void
     *
     * @todo Change to fetch all information for all Filemanager nodes in one go
     */
    public function nodeList(&$item) {
		if ($item->type == "gallery_node") {
	        $images = Zoo::getService('link')->getLinkedNodes($item, 'gallery_image', null, 4);
	        $ids = array();
	        foreach ($images as $image) {
	            $ids[] = $image->id;
	        }
	        if (count($ids) == 4) {
		        /**
		         * @todo Query the filemanager service for this URL
		         */
		        $urlOptions = array('module' => 'filemanager',
		                            'controller' => 'file',
		                            'action' => 'combine',
		                            'id1' => $ids[0],
		                            'id2' => $ids[1],
		                            'id3' => $ids[2],
		                            'id4' => $ids[3],
		                            'width' => 135,
		                            'height' => 135
		            );
		        $item->hooks['gallery_image'] = Zend_Controller_Front::getInstance()->getRouter()->assemble($urlOptions, 'default', true);
		        $count = Zoo::getService('link')->countLinksByNode($item->id, 'gallery_image');
		        $item->hooks['gallery_imagecount'] = $count->current()->count;
	        }
		}
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
            
        	$bgcolor = new Zoo_Form_Element_ColorPicker('gallery_bgcolor');
        	$bgcolor->setLabel('Background colour');
        	
        	$config = Zoo::getConfig('gallery', 'module');
        	
        	/*
        	$topimage = new Zend_Form_Element_Radio('gallery_topimage');
        	$topimage->setLabel('Top image');
        	$topimage->setOptions(array('escape' => false));
        	$topimages = Zoo::getService('content')->getContent(array('parent' => $config->top_image, 
        															  'nodetype' => 'filemanager_file'));
        	$topimage->addMultiOption(0, Zoo::_("None"));
        	foreach ($topimages as $image) {
        		$topimage->addMultiOption($image->id, $image->title."<br /><img src='".$image->hooks['filemanager_file']->getUrl(200)."' />");
        	}
        	*/
        	$topimage = new Zoo_Form_Element_FileBrowser('gallery_topimage');
        	$topimage->setLabel('Top image');
        	
        	$bgimage = new Zoo_Form_Element_FileBrowser('gallery_bgimage');
        	$bgimage->setLabel('Background image');
        	
        	$form->addElements(array($bgcolor, $topimage, $bgimage));
        	
        	$options = array('legend' => Zoo::_("Gallery extras"));
        	$form->addDisplayGroup(array('gallery_bgcolor', 'gallery_topimage', 'gallery_bgimage'), 'gallery_node', $options);

            if ($item->id > 0) {
                // Fetch extra information
	            $top_image = Zoo::getService('link')->getLinkedNodes($item, 'top_image');
	            $populate['gallery_topimage'] = (count($top_image) > 0) ? $top_image[0]->id : 0; 

	            $bg_image = Zoo::getService('link')->getLinkedNodes($item, 'bg_image');
	            $populate['gallery_bgimage'] = (count($bg_image) > 0) ? $bg_image[0]->id : 0; 
	            
	        	$factory = new Gallery_Node_Factory();
	            $gnode = false;
                // Fetch estate object
                $gnode = $factory->find($item->id)->current();
                if ($gnode) {
                	$populate['gallery_bgcolor'] = $gnode->bgcolor;
                }

                $form->populate($populate);
            }
        }
    }

    /**
     * Hook for node save
     *
     * @param Zend_Form $form
     * @param array $arguments
     */
    public function nodeSave(&$form, &$arguments) {
        $item = array_shift($arguments);
        $arguments = array_shift($arguments);
        if ($item->type == "gallery_node") {
        	$values = $form->getValues();
        	// Remove existing background image link
        	Zoo::getService('link')->remove($item->id, null, 'bg_image');
        	if ($values['gallery_bgimage']) {
            	// Connect image to gallery_node
	        	Zoo::getService('link')->connect($item->id, $values['gallery_bgimage'], 'bg_image');
        		
        	}
        	// Remove existing top image link
        	Zoo::getService('link')->remove($item->id, null, 'top_image');
        	if ($values['gallery_topimage']) {
        		// Connect image to gallery_node
	        	Zoo::getService('link')->connect($item->id, $values['gallery_topimage'], 'top_image');
        		
        	}
        	
        	// Set background
        	$factory = new Gallery_Node_Factory();
            // Save gallery fields
            $gnode = false;
            if ($item->id > 0) {
                // Fetch estate object
                $gnode = $factory->find($item->id)->current();
            }
            if (!$gnode) {
                $gnode = $factory->createRow();
                $gnode->nid = $item->id;
            }

            $arguments = $form->getValues();
            $gnode->bgcolor = $arguments['gallery_bgcolor'];
            $gnode->save();
            
            // Clear block cache for gallery listing block
            Zoo::getService('cache')->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('block_Gallery_Block_List'));
        }
        elseif ($item->type == "filemanager_file") {
        	$connectTo = Zend_Controller_Front::getInstance()->getRequest()->getParam('connectTo');
        	if ($connectTo) {
	        	// Connect image to gallery_node
	        	Zoo::getService('link')->connect($connectTo, $item->id, 'gallery_image');
        	}
        }
    }
}
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
            $item->hooks['gallery_nodes'] = Zoo::getService('link')->getLinkedNodes($item, 'gallery_image');
            
            $bg_image_style = $bg_color_style = "";
            $top_image = Zoo::getService('link')->getLinkedNodes($item, 'top_image');
            if ($top_image->count() > 0) {
            	$item->hooks['top_image'] = $top_image[0];
            }
            
        	$bg_image = Zoo::getService('link')->getLinkedNodes($item, 'bg_image');
            if ($bg_image->count() > 0) {
            	$bg_image_style = "background-image: url('".$bg_image[0]->hooks['filemanager_file']->getUrl()."');";
            }
            
            $options = array('nodetype' => 'gallery_node', 
            				 'parent' => $item->id, 
            				 'render' => true);
            $item->hooks['subgalleries'] = Zoo::getService('content')->getContent($options, 0, 0);
            
            // Find Estate node extra information
            $factory = new Gallery_Node_Factory();
            $extra = $factory->find($item->id)->current();
            if ($extra) {
            	$bg_color_style = "background-color: ".$extra->bgcolor.";";
            }
            
            // Add Lightbox JS
            $layout = Zend_Layout::getMvcInstance();
            $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;

            $theme_folder = Zend_Controller_Front::getInstance()->getBaseUrl()."/themes/".$layout->getLayout();

            $view->headScript()->appendFile('http://ajax.googleapis.com/ajax/libs/prototype/1.6.0.3/prototype.js', 'text/javascript');
            $view->headScript()->appendFile('http://ajax.googleapis.com/ajax/libs/scriptaculous/1.8.1/scriptaculous.js?load=effects,builder', 'text/javascript');
            $view->headScript()->appendFile($theme_folder.'/js/lightbox.js', 'text/javascript');
            $view->headLink()->appendStylesheet($theme_folder."/css/lightbox.css");
            
            if ($bg_image_style || $bg_color_style) {
            	$view->headStyle()->appendStyle(".gallery-node-item {{$bg_image_style}{$bg_color_style}}");
            }
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
    	foreach ($items as $item) {
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
			        $urlOptions = array('module' => 'Filemanager',
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
            // Add Colorpicker JS
            /**
             * 
             * @todo Why is this not part of ZF? They have the view helper, but not the CSS/JS files...
             */
            $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
            $view->jQuery()->addJavascriptFile(Zend_Controller_Front::getInstance()->getBaseUrl().'/js/jquery/colorpicker/js/colorpicker.js', 'text/javascript');
            $view->jQuery()->addStylesheet(Zend_Controller_Front::getInstance()->getBaseUrl()."/js/jquery/colorpicker/css/colorpicker.css");
        	
            /*
            // Ain't working... param values are parsed as strings, not javascript
            $params = array('onSubmit' => 'function(hsb, hex, rgb, el) {
												$(el).val(hex);
												$(el).ColorPickerHide();
											}',
            				'onBeforeShow' => 'function () {$(this).ColorPickerSetColor(this.value);}');
            				*/
            $bgcolor = new ZendX_JQuery_Form_Element_ColorPicker('gallery_bgcolor');
        	$bgcolor->setLabel('Background colour');
        	//$bgcolor->setJqueryParams($params);
        	
        	$config = Zoo::getConfig('gallery', 'module');
        	
        	$topimage = new Zend_Form_Element_Radio('gallery_topimage');
        	$topimage->setLabel('Top image');
        	$topimages = Zoo::getService('content')->getContent(array('parent' => $config->top_image, 
        															  'nodetype' => 'filemanager_file'));
        	$topimage->addMultiOption(0, Zoo::_("None"));
        	foreach ($topimages as $image) {
        		$topimage->addMultiOption($image->id, "<img src='".$image->hooks['filemanager_file']->getUrl()."' />".$image->title);
        	}
        	
        	$bgimage = new Zend_Form_Element_Radio('gallery_bgimage');
        	$bgimage->setLabel('Background image');
        	$bgimages = Zoo::getService('content')->getContent(array('parent' => $config->background_image, 'nodetype' => 'filemanager_file'));
        	$bgimage->addMultiOption(0, Zoo::_("None"));
        	foreach ($bgimages as $image) {
        		$bgimage->addMultiOption($image->id, $image->title);
        	}
        	
        	$form->addElements(array($bgcolor, $topimage, $bgimage));
        	
        	$options = array('legend' => Zoo::_("Gallery extras"));
        	$form->addDisplayGroup(array('gallery_bgcolor', 'gallery_topimage', 'gallery_bgimage'), 'gallery_node', $options);

            if ($item->id > 0) {
                // Fetch extra information
	            $top_image = Zoo::getService('link')->getLinkedNodes($item, 'top_image');
	            $populate['gallery_topimage'] = ($top_image->count() > 0) ? $top_image[0]->id : 0; 

	            $bg_image = Zoo::getService('link')->getLinkedNodes($item, 'bg_image');
	            $populate['gallery_bgimage'] = ($bg_image->count() > 0) ? $bg_image[0]->id : 0; 
	            
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
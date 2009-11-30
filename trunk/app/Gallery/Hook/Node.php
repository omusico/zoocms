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
            
            $options = array('nodetype' => 'gallery_node', 
            				 'parent' => $item->id, 
            				 'render' => true);
            $item->hooks['subgalleries'] = Zoo::getService('content')->getContent($options, 0, 0);
            
            // Add Lightbox JS
            $layout = Zend_Layout::getMvcInstance();
            $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;

            $theme_folder = Zend_Controller_Front::getInstance()->getBaseUrl()."/themes/".$layout->getLayout();

            $view->headScript()->appendFile('http://ajax.googleapis.com/ajax/libs/prototype/1.6.0.3/prototype.js', 'text/javascript');
            $view->headScript()->appendFile('http://ajax.googleapis.com/ajax/libs/scriptaculous/1.8.1/scriptaculous.js?load=effects,builder', 'text/javascript');
            $view->headScript()->appendFile($theme_folder.'/js/lightbox.js', 'text/javascript');
            $view->headLink()->appendStylesheet($theme_folder."/css/lightbox.css");
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
        	
        	$topimage = new Zend_Form_Element_Text('gallery_topimage');
        	$topimage->setLabel('Top image');
        	
        	$bgimage = new Zend_Form_Element_Text('gallery_bgimage');
        	$bgimage->setLabel('Background image');
        	
        	$form->addElements(array($bgcolor, $topimage, $bgimage));
        	
        	$options = array('legend' => Zoo::_("Gallery extras"));
        	$form->addDisplayGroup(array('gallery_bgcolor', 'gallery_topimage', 'gallery_bgimage'), 'gallery_node', $options);

            if ($item->id > 0) {
                // Fetch gallery object
                /*
                $factory = new Gallery_Node_Factory();
                $gnode = $factory->find($item->id)->current();
                if (!$gnode) {
                    $gnode = $factory->createRow();
                }
                $values = $gnode->toArray();
                $populate = array();
                foreach ($values as $key => $value) {
                    $populate['gallery_'.$key] = $value;
                }
                $form->populate($populate);
                */
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
            
        }
        elseif ($item->type == "filemanager_file") {
        	$connectTo = Zend_Controller_Front::getInstance()->getRequest()->getParam('connectTo');
        	if ($connectTo) {
	        	// Connect image to gallery_node
	        	Zoo::getService('link')->getFactory()->connect($connectTo, $item->id, 'gallery_image');
        	}
        }
    }
}
<?php
/**
 * @package Rewrite
 * @subpackage Hook
 */

/**
 * @package    Rewrite
 * @subpackage Hook
 * @copyright  Copyright (c) 2010 ZooCMS
 * @version    1.0
 */
class Rewrite_Hook_Node extends Zoo_Hook_Abstract {

    /**
     * Hook for node form, add extra fields
     *
     * @param Zend_Form $form
     * @param array $arguments
     */
    public function nodeForm(Zend_Form &$form, &$arguments) {
        $item =& array_shift($arguments);
        
        $content_type = Zoo::getService('content')->getType($item->type);
        if ($content_type->has_parent_url == 0) {
			
			$path = new Zend_Form_Element_Text ( 'rewrite_path', array ('size' => 65 ) );
			$path->setLabel ( 'URL' );
			
			$form->addElement ( $path );
			
			$options = array ('legend' => Zoo::_ ( "URL Rewriting" ) );
			$form->addDisplayGroup ( array ('rewrite_path' ), 'rewrite_path_options', $options );
			
			if ($item->id > 0) {
				$factory = new Rewrite_Path_Factory ( );
				$path = $factory->find ( $item->id )->current ();
				if ($path) {
					$form->populate ( array ('rewrite_path' => $path->path ) );
				} else {
					// Find parent's path
					if ($item->pid && $path = $factory->find ( $item->pid )->current ()) {
						$form->populate ( array ('rewrite_path' => $path->path . "/" . $item->id ) );
					} else {
						$form->populate ( array ('rewrite_path' => $item->url () ) );
					}
				}
			}
        }
    }
    /**
     * Hook for node save - save parent (category)
     *
     * @param Zend_Form $form
     * @param array $arguments
     */
    public function nodeSave(&$form, &$arguments) {
        $item = array_shift($arguments);
        $arguments = $form->getValues();
        if (isset($arguments['rewrite_path'])) {
        	$router = Zend_Controller_Front::getInstance()->getRouter();
        	$default_url = $router->assemble(array('id' => $item->id), $item->type);
        	
        	$factory = new Rewrite_Path_Factory ( );
        	$path = $factory->find ( $item->id )->current ();
        	
        	if ($arguments['rewrite_path'] == $default_url) {
        		if ($path) {
        			$path->delete();        			
        		}
        	}
        	else {
				if (! $path) {
					$path = $factory->createRow ();
					$path->nid = $item->id;
				}
            
	        	if ($arguments['rewrite_path'] != $path->path) {
		            $path->path = $arguments['rewrite_path'];
		            $path->save();
	        	}
        	}
        }
    }
}
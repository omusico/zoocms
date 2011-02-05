<?php
/**
 * @package  Flex
 * @subpackage Controllers
 *
 */

/**
 * @package  Flex
 * @subpackage Controllers
 *
 */

class Flex_BlockController extends Zoo_Controller_Action {
    /**
     * Show the block listing
     */
    public function indexAction() {
        $factory = new Flex_Block_Factory();
        $this->view->flexblocks = $factory->fetchAll();
        
    }
    
    /**
     * Save a block
     * @return void
     */
    public function formAction() {
        
        $factory = new Flex_Block_Factory();
        $id = $this->getRequest()->getParam('id', 0);
        if ($id > 0) {
            $block = $factory->find($id)->current();
            try {
	            //if (!Zoo::getService('acl')->checkItemAccess($item, 'edit') && !Zoo::getService('acl')->checkItemAccess($item, 'editown')) {
	                //throw new Exception(Zoo::_("Access denied - insufficient privileges"), 403);
	            //}
            }
	        catch (Zoo_Exception_Service $e) {
	        	// No acl service installed
	        }
	        $form = new Flex_Form_Edit($block);
            if ($this->getRequest()->isPost() && $form->isValid($_REQUEST)) {
                $values = $form->getValues();
                $block->name = $values['name'];
                $block->title = $values['title'];
                $block->position = $values['position'];
                $block->weight = $values['weight'];
                $block->options = $values['options'];
    
                $block->save();
    
                Zoo::getService('cache')->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('block_'.$block->id));
                $url = Zend_Controller_Front::getInstance ()->getRouter ()->assemble ( array ('module' => "flex", 'controller' => 'block', 'action' => 'index' ), 'default' );
        
                $this->_redirect($url);
            }
        }
        else {
            $block = $factory->createRow();
            $block->type = "Block";
            try {
	            //if (!Zoo::getService('acl')->checkItemAccess($item, 'add')) {
	                //throw new Exception(Zoo::_("Access denied - insufficient privileges"), 403);
	            //}
            }
	        catch (Zoo_Exception_Service $e) {
	        	// No acl service installed
	        }
	        $form = new Flex_Form_Add($block);
            if ($this->getRequest()->isPost() && $form->isValid($_REQUEST)) {
                $values = $form->getValues();
                $block->type = $values['type'];
                $block->name = $values['name'];
    
                $block->save();
    
                $url = Zend_Controller_Front::getInstance ()->getRouter ()->assemble ( array ('module' => "flex", 'controller' => 'block', 'action' => 'edit', 'id' => $block->id ), 'default' );
                $this->_redirect($url);
            }
        }

        $this->view->type = $block->type;
        $this->view->form = $form;
        $this->view->form->populate($_REQUEST);
        $this->render('form');
    }
    
/**
     * Delete a block
     * @return void
     */
    public function deleteAction() {
        $factory = new Flex_Block_Factory();
        $id = $this->getRequest()->getParam('id');
        $item = $factory->find($id)->current();
        if ($item) {
        	try {
	            //if (!Zoo::getService('acl')->checkItemAccess($item, 'edit') && !Zoo::getService('acl')->checkItemAccess($item, 'deleteown')) {
	                //throw new Exception(Zoo::_("Access denied - insufficient privileges"), 403);
	            //}
	            $item->delete();
		        try {
		            Zoo::getService('cache')->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('node_'.$item->id, 'nodelist'));
	            }
	            catch (Zoo_Exception_Service $e) {
	                // Hook service not available - log? Better not, some people may live happily without a hook service
	            }
        	}
	        catch (Zoo_Exception_Service $e) {
	        	// No acl service installed
	        }
        }
    }
}

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

  public function init() {
    $ajaxContext = $this->_helper->getHelper('AjaxContext');
    $ajaxContext->addActionContext('form', 'html')
                ->initContext();

  }
  
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
    }
    else {
      $block = $factory->createRow();
      $block->type = $this->getRequest()->getParam('type');
    }
    $form = new Flex_Form_Block($block);
    if ($this->getRequest()->isPost() && $form->isValid($_REQUEST)) {
      $values = $form->getValues();
      $block->name = $values['name'];
      $block->title = $values['title'];
      $block->options = $values['options'];

      $block->save();

      Zoo::getService('cache')->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('block_' . $block->id));
      $url = Zend_Controller_Front::getInstance ()->getRouter()->assemble(array('module' => "flex", 'controller' => 'block', 'action' => 'index'), 'default');

      $this->_redirect($url);
    }

    $this->view->type = $block->type;
    $this->view->form = $form;
    $this->view->form->populate($_REQUEST);
    if ($this->getRequest()->isXmlHttpRequest()) {
      $this->view->form->setAttrib('onsubmit', 'submitForm(this);return false;');
    }
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
      $item->delete();
      try {
        Zoo::getService('cache')->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('node_' . $item->id, 'nodelist'));
      } catch (Zoo_Exception_Service $e) {
        // Cache service
      }
    }
  }

}

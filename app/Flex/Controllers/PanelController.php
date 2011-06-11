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
class Flex_PanelController extends Zoo_Controller_Action {
  /**
   *
   * @var Flex_Panel
   */
  public $panel = NULL;

  public function init() {
    $id = $this->getRequest()->getParam('id', 0);
    if ($id > 0) {
      $factory = new Flex_Panel_Factory();
      $this->panel = $factory->find($id)->current();
    }
  }

  /**
   * Show the panel listing
   */
  public function indexAction() {
    $factory = new Flex_Panel_Factory();
    $this->view->panels = $factory->fetchAll();
  }

  /**
   * Add/Edit a panel
   * @return void
   */
  public function formAction() {
    if ($this->panel) {
      $panel = $this->panel;
    }
    else {
      $factory = new Flex_Panel_Factory();
      $panel = $factory->createRow();
    }
    $form = new Flex_Form_Panel($panel);
    if ($this->getRequest()->isPost() && $form->isValid($_REQUEST)) {
      $values = $form->getValues();
      $panel->parent_id = $values['parent_id'];
      $panel->name = $values['name'];
      $panel->title = $values['title'];
      $panel->layout = $values['layout'];
      //$panel->settings = $values['settings'];
      $panel->category = $values['category'];

      $panel->save();

      Zoo::getService('cache')->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('panel_' . $panel->id));
      $this->_redirect(Zend_Controller_Front::getInstance ()
                              ->getRouter()
                              ->assemble(array('module' => "flex",
                                               'controller' => 'panel',
                                               'action' => 'index'),
                                         'default',
                                         true));
    }

    $this->view->form = $form;
    $this->view->form->populate($_REQUEST);
    $this->render('form');
    
  }

  /**
   * Delete a block
   * @return void
   */
  public function deleteAction() {
    $factory = new Flex_Panel_Factory();
    if ($this->panel) {
      $this->panel->delete();
      try {
        Zoo::getService('cache')->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('panel_' . $this->panel->id));
      } catch (Zoo_Exception_Service $e) {
        // Cache service not available - log? Better not, some people may live happily without a hook service
      }
    }
  }

  /**
   * Set layout settings for panel
   */
  public function layoutAction() {
    if (!$this->panel) {
      $this->_redirect(Zend_Controller_Front::getInstance ()
                                    ->getRouter()
                                    ->assemble(array('module' => "flex",
                                                     'controller' => 'panel',
                                                     'action' => 'index'),
                                               'default',
                                               true));
    }
    // Get layout settings form
    $this->view->form = new Flex_Form_Layout($this->panel);
    if ($this->getRequest()->isPost()) {
      if ($this->view->form->isValid($_REQUEST)) {
        $values = $this->view->form->getValues();
        unset($values['id']);

        foreach ($values as $key => $value) {
          $settings[$key] = $value;
        }
        $this->panel->settings = $settings;
        if ($this->panel->save()) {
          $this->_redirect(Zend_Controller_Front::getInstance ()
                                ->getRouter()
                                ->assemble(array('module' => "flex",
                                                 'controller' => 'panel',
                                                 'action' => 'content',
                                                 'id' => $this->panel->id),
                                           'default',
                                           true));
        }
      }
    }
    $this->render('form');

    // Get regions

    // Get region templates

    // Selector for region templates
  }

  /**
   * Configure content for a panel
   */
  public function contentAction() {
    $this->view->jQuery()->enable()
                          ->uiEnable();
    if ($this->getRequest()->isPost()) {
      $blocks = $this->getRequest()->getParam('block', array());
      $panel_block_factory = new Flex_Panel_Block_Factory();
      if ($blocks) {
        $regions = array();
        $block_factory = new Flex_Block_Factory();
        $block_options = $this->getRequest()->getParam('options', array());
        foreach ($blocks as $block) {
          if (!is_numeric($block) && substr($block, 0, 4) != "new-") {
            // region
            $current_region = $block;
            $regions[$current_region] = array();
          }
          else {
            if (is_numeric($block)) {
              $block_obj = $block_factory->find($block)->current();
              $panel_block = $panel_block_factory->fetchRow($panel_block_factory->select()
                                                              ->where('panel_id = ?', $this->panel->id)
                                                              ->where('block_id = ?', $block_obj->id)
                                                           );
            }
            else {
              $block_obj = $block_factory->createRow();
              $block_obj->save();
              $panel_block = $panel_block_factory->createRow(array('panel_id' => $this->panel->id,
                                                                   'block_id' => $block_obj->id)
                                                            );
            }

            $regions[$current_region][] = $panel_block;
          }
        }
        foreach ($regions as $region => $blocks) {
          foreach ($blocks as $key => $block) {
            $block->region = $region;
            $block->weight = $key;
            $block->save();
          }
        }
      }
      else {
        // Remove all blocks from panel
        $panel_block_factory->delete("panel_id = " . $this->panel->id);
      }
      // Clear cache for panel
      Zoo::getService('cache')->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('panel_' . $this->panel->id));
    }

    // Render blocks on the panel in admin view
    $layout = $this->panel->loadBlocks()->getLayout();
    $layout->is_admin_page = true;
    $this->view->content = $layout->render($this->panel->blocks);
  }

  /**
   * Configure where the panel is used
   */
  public function usageAction() {
    // Get usage rules for nodes
    // Get usage rules for module/controller/actions

    // Get form for adding node rule
    // Get form for adding node module
    // Checkbox to set as default
  }
}

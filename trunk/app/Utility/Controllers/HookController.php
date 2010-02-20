<?php
/**
 * @package  Utility
 * @subpackage Controllers
 *
 */

/**
 * @package  Utility
 * @subpackage Controllers
 *
 */
class Utility_HookController extends Zend_Controller_Action
{
    /**
     * Display a hook
     *
     */
    function indexAction() {
        $factory = new Utility_Hook_Factory();
        $item = $factory->createRow();
        $hooks = $factory->fetchAll(null, "weight");

        $this->view->form = $item->getForm($hooks);
        $this->view->hooks = $hooks;
        $this->render("form");
    }

    /**
     * Edit a hook
     *
     */
    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $factory = new Utility_Hook_Factory();
        $item = $factory->find($id)->current();
        if ($item) {
            $this->view->form = $item->getForm();
        }

        $this->render("form");
    }

    /**
     * Save a hook
     *
     */
    public function saveAction()
    {
        $factory = new Utility_Hook_Factory();
        if (@$_REQUEST['id'] > 0) {
            $item = $factory->find($_REQUEST['id'])->current();
        }
        else {
            $item = $factory->createRow();
        }

        $form = $item->getForm(@$_REQUEST['id']);

        if ($form->isValid($_REQUEST)) {
            $values = $form->getValues();
            list($type, $action, $class) = explode('_', $values['type']);
            $item->type = $type;
            $item->action = $action;
            $item->weight = $values['weight'];
            $item->class = $class;

            $item->save();

            $this->_helper->redirector->gotoRoute(
                                        array(  'module' => 'utility',
                                                'controller' => 'hook',
                                                'action' => 'index'));
        }
        $this->view->form = $form;
        $this->view->form->populate($_REQUEST);

        $factory = new Utility_Hook_Factory();
        $this->view->hooks = $factory->fetchAll(null, "weight");

        $this->render('form');
    }

}

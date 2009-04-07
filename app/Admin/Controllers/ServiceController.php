<?php
/**
 * Admin service management controller
 * @package Admin
 * @subpackage Controllers
 */

/**
 * @package Admin
 * @subpackage Controllers
 */
class Admin_ServiceController extends Zoo_Controller_Action
{
    /**
     * Manage services
     *
     */
    function indexAction() {
        $this->view->form = $this->getForm();
        $this->render('form');
    }

    /**
     * Save service configuration
     *
     */
    function saveAction() {
        $form = $this->getForm();

        if ($form->isValid($_REQUEST)) {
            $values = $form->getValues();

            $this->_helper->redirector->gotoRoute(array('id' => $item->id), $item->type);
        }
        $this->view->form = $form;
        $this->view->form->populate($_REQUEST);
        $this->render('form');
    }

    /**
     * Get a form for managing the services
     *
     */
    function getForm() {
        $modules = Zoo::getConfig('modules');

        $services = Zoo::getConfig('services');

    }
}
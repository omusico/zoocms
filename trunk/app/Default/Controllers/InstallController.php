<?php
/**
 * @package Default
 * @subpackage Controllers
 *
 */

/**
 * @package Default
 * @subpackage Controllers
 *
 */
class InstallController extends Zend_Controller_Action
{
    /**
     * Initiate system installation
     *
     */
    public function indexAction()
    {
        $settingsform = new Default_Form_Settings();

        $modulesform = new Default_Form_Modules();

        $form = new Zend_Form();
        $form->setSubFormDecorators(array(
                                        'FormElements',
                                        'Fieldset'
                                    ));
        $form->addSubForm($settingsform, 'settings');
        $form->addSubForm($modulesform, 'modules');
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            var_dump($formData);
            if ($form->isValid($formData)) {
//                $this->_forward('install');
            }
            $form->populate($formData);
        }
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Install');

        $form->addElements(array($submit));


        $this->view->form = $form;
    }

    /**
     * Perform system installation
     *
     */
    public function installAction() {
        var_dump($this->getRequest()->getParams());
    }
}

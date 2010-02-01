<?php
/**
 * @package    Guestbook
 * @subpackage Hook
 */

/**
 * @package   Guestbook
 * @subpackage Hook
 * @copyright  Copyright (c) 2009 ZooCMS
 * @version    1.0
 */
class Guestbook_Hook_Node extends Zoo_Hook_Abstract {
    /**
     * Hook for node display - fetches Guestbook Node information, if the node is an Guestbook Node
     *
     * @param Zoo_Content_Interface $item
     *
     * @return void
     */
    public function nodeDisplay(&$item) {
        if ($item->type == "guestbook_entry") {
            // Find Guestbook node extra information
            $factory = new Guestbook_Node_Factory();
            $guestbook = $factory->find($item->id)->current();
            $item->hooks['guestbook_entry'] = $guestbook;
            
            try {
            	$item->hooks['can_edit'] = Zoo::getService('acl')->checkItemAccess($item, 'edit');
            }
            catch (Zoo_Exception_Service $e) {
            	$item->hooks['can_edit'] = false;
            }
        }
    }

    /**
     * Hook for node listing - fetches Guestbook Node information
     *
     * @param Zoo_Content_Interface $items
     *
     * @return void
     *
     * @todo Change to fetch all information for all guestbook nodes in one go
     */
    public function nodeList(&$item) {
        $this->nodeDisplay($item);
    }

    /**
     * Hook for node form - if type is Guestbook Node, add extra fields
     *
     * @param Zend_Form $form
     * @param array $arguments
     */
    public function nodeForm(Zend_Form &$form, &$arguments) {
        $item =& array_shift($arguments);
        if ($item->type == "guestbook_entry") {
            // Add guestbook fields
            $name = new Zend_Form_Element_Text('guestbook_name', array('size' => 35));
            $name->setLabel('Name');
            $name->setRequired(true);

            $email = new Zend_Form_Element_Text('guestbook_email', array('size' => 35));
            $email->setLabel('Email');
            $email->setRequired(true)->addValidator(new Zend_Validate_StringLength(6))->addValidator(new Zend_Validate_EmailAddress());

            $url = new Zend_Form_Element_Text('guestbook_homepage', array('size' => 35));
            $url->setLabel('Homepage');
            $url->setRequired(false)->addValidator(new Zend_Validate_StringLength(4))->addValidator(new Zend_Validate_Hostname());

            $form->addElements(array($name, $email, $url));

            $options = array('legend' => Zoo::_("Guest information"));
            $form->addDisplayGroup(array('guestbook_name', 'guestbook_email', 'guestbook_homepage'), 'guestbook_add', $options);

            if ($item->id > 0) {
                // Fetch guestbook object
                $factory = new Guestbook_Node_Factory();
                $guestbook = $factory->find($item->id)->current();
                if (!$guestbook) {
                    $guestbook = $factory->createRow();
                }
                $values = $guestbook->toArray();
                $populate = array();
                foreach ($values as $key => $value) {
                    $populate['guestbook_'.$key] = $value;
                }
                $form->populate($populate);
            }
        }
    }

    /**
     * Hook for node save - if type is Guestbook Node, save extra fields
     *
     * @param Zend_Form $form
     * @param array $arguments
     */
    public function nodeSave(Zend_Form $form, &$arguments) {
        $item = array_shift($arguments);
        if ($item->type == "guestbook_entry") {
            $factory = new Guestbook_Node_Factory();
            // Save guestbook fields
            $guestbook = false;
            if ($item->id > 0) {
                // Fetch guestbook object
                $guestbook = $factory->find($item->id)->current();
            }
            if (!$guestbook) {
                $guestbook = $factory->createRow();
            }

            $arguments = $form->getValues();
            $guestbook->nid = $item->id;
            $guestbook->name = $arguments['guestbook_name'];
            $guestbook->email = $arguments['guestbook_email'];
            if ($arguments['guestbook_homepage'] && substr($arguments['guestbook_homepage'], 0, 7) != "http://") {
            	$arguments['guestbook_homepage'] = "http://".$arguments['guestbook_homepage'];
            }
            $guestbook->homepage = $arguments['guestbook_homepage'];
            $guestbook->save();
            
            try {
            	Zoo::getService('cache')->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("guestbook_list"));
            }
            catch (Zoo_Exception_Service $e) {
            	// No caching service installed, no cache to clean
            }
        }
    }
}
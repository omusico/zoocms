<?php
/**
 * Form for adding comments
 * @package    Comments
 */

/**
 * @package    Comments
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Comments_Form extends Zend_Form {
    /**
     * Set form elements for a Comments object
     *
     * @param Content_Node $target
     * @param array $options
     */
    function __construct(Content_Node $target, $options = array()) {
        parent::__construct($options);

        $this->setAction('/content/node/save')->setMethod('post');

        $title = new Zend_Form_Element_Text('title', array('class' => 'comments_title'));
        $title->setLabel('Title');
        $title->setRequired(true)->addValidator(new Zend_Validate_StringLength(2,45));

        $content = new Zend_Form_Element_Textarea('comment');
        $content->setRequired(true)->setLabel('Comment')->setAttrib('rows', 10)->setAttrib('cols', 50);

        $submit = new Zend_Form_Element_Submit('save');
        $submit->setLabel('save');

        $this->addElements(array($title, $content));

        try {
            Zoo::getService("hook")->trigger("Node", "Form", $this, $target);
        }
        catch (Zoo_Exception_Service $e) {
            // Hook service not available - log? Better not, some people may live happily without a hook service
        }

        $this->addElement($submit);
        if ($target->id > 0) {
            $id_ele = new Zend_Form_Element_Hidden('id');
            $id_ele->setValue(intval($target->id));
            $this->addElement($id_ele);
        }
        $this->addElement(new Zend_Form_Element_Hidden('type', array('value' => $target->type)));
        $this->addElement(new Zend_Form_Element_Hidden('pid', array('value' => $target->pid)));

        $this->populate($target->toArray());
    }
}
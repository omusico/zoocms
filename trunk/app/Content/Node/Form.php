<?php
/**
 * @package Content
 * @subpackage Node
 */

/**
 * @package Content
 * @subpackage Node
 */
class Content_Node_Form extends Zend_Form {
    /**
     * Set form elements for a Content_Node object
     *
     * @param Content_Node $target
     * @param string $action
     * @param array $options
     */
    function __construct(Content_Node $target, $action, $options = array()) {
        parent::__construct($options);

        $this->setAction($action)->setMethod('post');

        $title = new Zend_Form_Element_Text('title', array('class' => 'content_title'));
        $title->setLabel('Title');
        $title->setRequired(true)->addValidator(new Zend_Validate_StringLength(5,255));

        $content = new Zend_Form_Element_Textarea('content');
        $content->setRequired(true)->setLabel('Content')->setAttrib('cols', 50);

        $submit = new Zend_Form_Element_Submit('save');
        $submit->setLabel('Save');

        $this->addElements(array($title, $content));

        $factory = new Content_Type_Factory();
        $type = $factory->fetchRow($factory->select()->where('type = ?', $target->type));
        $legend = $target->id > 0 ? Zoo::_("Edit %s") : Zoo::_("Add %s");
        $legend = sprintf($legend, $type->name);
        $this->addDisplayGroup(array('title', 'content'), 'content_add', array('legend' => $legend ));

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
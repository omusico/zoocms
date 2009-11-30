<?php
/**
 * @package Content
 * @subpackage Node
 */

/**
 * @package Content
 * @subpackage Node
 */
class Content_Node_Form extends ZendX_JQuery_Form {
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
        $this->setAttrib('id', 'content_form');

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
        
    	if ($type->has_publishdate_select) {
        	// Add publish date and approval settings
        	$status = new Zend_Form_Element_Radio('status', array('class' => 'content_status'));
        	$status->setLabel('Status');
        	$status->addMultiOption(0, Zoo::_('Unpublished'));
        	$status->addMultiOption(1, Zoo::_('Published'));
        	//$status->addMultiOption(2, Zoo::_('Ready for review'));
        	
        	$publishdate = new ZendX_JQuery_Form_Element_DatePicker('published');
        	$publishdate->setLabel('Publish date');
        	
        	$target->published = date('d M y', $target->published);
        	$this->addElements(array($status, $publishdate));
        	$this->addDisplayGroup(array('status', 'published'), 'content_publish', array('legend' => Zoo::_("Publish settings")));
        	
        	//Workaround for JQuery Theme
        	/**
        	 * @todo replace with unified jquery UI theme selector
        	 */
        	$view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
        	$view->headLink()->appendStylesheet('http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/smoothness/jquery-ui.css');
        }

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
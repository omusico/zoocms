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

        $type = Zoo::getService('content')->getType($target->type);
        try {
            Zoo::getService("hook")->trigger("Node", "Form", $this, $target);
        }
        catch (Zoo_Exception_Service $e) {
            // Hook service not available - log? Better not, some people may live happily without a hook service
        }
    	if ($type->has_publishdate_select) {
        	// Add publish date and approval settings
        	$status = new Zend_Form_Element_Radio('status', array('class' => 'content_status'));
        	$status->setLabel('Status');
        	$status->addMultiOption(0, Zoo::_('Unpublished'));
        	$status->addMultiOption(1, Zoo::_('Published'));
        	//$status->addMultiOption(2, Zoo::_('Ready for review'));
        	
        	$publishdate = new ZendX_JQuery_Form_Element_DatePicker('published');
        	$publishdate->setLabel('Publish date');
        	
        	$this->addElements(array($status, $publishdate));
        	$this->addDisplayGroup(array('status', 'published'), 'content_publish', array('legend' => Zoo::_("Publish settings")));
        	
        	//Workaround for JQuery Theme
        	/**
        	 * @todo replace with unified jquery UI theme selector
        	 */
        	$view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
        	$view->headLink()->appendStylesheet('http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/smoothness/jquery-ui.css');
        }
        
        $submit = new Zend_Form_Element_Submit('save');
        $submit->setLabel('Save');
        $this->addElement($submit);
        if ($target->id > 0) {
            $id_ele = new Zend_Form_Element_Hidden('id');
            $id_ele->setValue(intval($target->id));
            $this->addElement($id_ele);
        }
        else {
            $target->status = 0;
        }
        $this->addElement(new Zend_Form_Element_Hidden('type', array('value' => $target->type)));
        $this->addElement(new Zend_Form_Element_Hidden('pid', array('value' => $target->pid)));

        $populate = $target->toArray();
        $populate['published'] = date("d M y", $target->published ? $target->published : time());
        $this->populate($populate);
    }
}
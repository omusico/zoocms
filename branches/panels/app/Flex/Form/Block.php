<?php
/**
 * @package Flex
 * @subpackage Form
 */

/**
 * @package Flex
 * @subpackage Form
 */
class Flex_Form_Block extends Zend_Form {
    /**
     * Object /edited by the form
     *
     * @var Flex_Block
     */
    private $target;
    
    /**
     * Set form elements 
     *
     * @param Zoo_Block $target
     * @param array $options
     */
    function __construct(Flex_Block $target, $options = array()) {
        $this->target = $target;
        parent::__construct ( $options );
    }
    
    /**
     * Form initialization
     *
     * @return void
     */
    public function init() {
        $url = Zend_Controller_Front::getInstance ()->getRouter ()->assemble ( array ('module' => "flex", 'controller' => 'block', 'action' => 'form' ), 'default' );
        $this->setAction ( $url )->setMethod ( 'post' );
        $this->setLegend(sprintf(Zoo::_('Edit %s'), $this->target->name));
        
        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Name')->setRequired(true);
        $name->setDescription('Administration-side identifier');
        
        $title = new Zend_Form_Element_Text('title');
        $title->setLabel('Title');
        $title->setDescription('User-visible title of block');
        
        $options = $this->getOptionsForm();
        if ($options) {
            $options->setLegend('Options');
            $this->addSubForm($options, 'options', 5);
        }
        
        $submit = new Zend_Form_Element_Submit ( 'save' );
        $submit->setLabel ( 'Save' )->setOrder(100);
        
        $this->addElements ( array ($name, $title) );
        
        $legend = Zoo::_ ( "Basic options" );
        $this->addDisplayGroup ( array ('name', 'title' ), 'block_form', array ('legend' => $legend ) );
        
        $this->addElement ( $submit );
        if ($this->target->id > 0) {
            $id_ele = new Zend_Form_Element_Hidden ( 'id' );
            $id_ele->setValue ( intval ( $this->target->id ) );
            $this->addElement ( $id_ele );
        }
        $this->populate ( $this->target->toArray () );
    
    }
    
    /**
     * Get block-specific options
     * @return Zend_Form_Subform
     */
    function getOptionsForm() {
        $class = $this->target->type;
        $object = new $class($this->target->toArray());
        return $object->getOptions();
    }
}
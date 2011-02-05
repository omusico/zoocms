<?php
/**
 * @package Flex
 * @subpackage Form
 */

/**
 * @package Flex
 * @subpackage Form
 */
class Flex_Form_Edit extends Zend_Form {
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
        
        $position = new Zend_Form_Element_Select('position');
        $position->setLabel('Position');
        $position->setMultiOptions($this->getPositions());
        
        $weight = new Zend_Form_Element_Text ( 'weight' );
        $weight->setLabel ( 'Weight' )->setAttrib ( 'size', 2 );
        $weight->setRequired ( true )->addValidator ( new Zend_Validate_Int ( ) );
        
        $options = $this->getOptionsForm();
        if ($options) {
            $options->setLegend('Options');
            $this->addSubForm($this->getOptionsForm(), 'options', 5);
        }
        
        $submit = new Zend_Form_Element_Submit ( 'save' );
        $submit->setLabel ( 'save' )->setOrder(100);
        
        $this->addElements ( array ($name, $title, $position, $weight ) );
        
        $legend = Zoo::_ ( "Basic options" );
        $this->addDisplayGroup ( array ('name', 'title', 'position', 'weight' ), 'block_form', array ('legend' => $legend ) );
        
        $this->addElement ( $submit );
        if ($this->target->id > 0) {
            $id_ele = new Zend_Form_Element_Hidden ( 'id' );
            $id_ele->setValue ( intval ( $this->target->id ) );
            $this->addElement ( $id_ele );
        }
        $this->populate ( $this->target->toArray () );
    
    }
    
    function getPositions() {
        //@todo change to read from layout
        return array(Zoo_Block::POSITION_RCCENTER => Zoo::_('RCCenter'),
                     Zoo_Block::POSITION_CENTER => Zoo::_('Center'),
                     Zoo_Block::POSITION_RCLEFT => Zoo::_('RCLeft'),
                     Zoo_Block::POSITION_LEFT => Zoo::_('Left'),
                     Zoo_Block::POSITION_RCRIGHT => Zoo::_('RCRight'),
                     Zoo_Block::POSITION_FOOTER => Zoo::_('Footer'),
                     Zoo_Block::POSITION_RIGHT => Zoo::_('Right'),
                     Zoo_Block::POSITION_TOP => Zoo::_('Top'),
                     Zoo_Block::POSITION_TOPLEFT => Zoo::_('Topleft'),
                     Zoo_Block::POSITION_TOPLEFTHALF => Zoo::_('Toplefthalf'),
                     Zoo_Block::POSITION_NAV => Zoo::_('Navigation'),
                     Zoo_Block::POSITION_FULL => Zoo::_('Full width'));
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
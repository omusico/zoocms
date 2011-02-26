<?php

/**
 * @package Flex
 * @subpackage Form
 */

/**
 * @package Flex
 * @subpackage Form
 */
class Flex_Form_Panel extends Zend_Form {

  /**
   * Object /edited by the form
   *
   * @var Flex_Panel
   */
  private $target;

  /**
   * Set form elements
   *
   * @param Flex_Panel $target
   * @param array $options
   */
  function __construct(Flex_Panel $target, $options = array()) {
    $this->target = $target;
    parent::__construct($options);
  }

  /**
   * Form initialization
   *
   * @return void
   */
  public function init() {
    $url = Zend_Controller_Front::getInstance ()->getRouter()->assemble(array('module' => "flex", 'controller' => 'panel', 'action' => 'form'), 'default');
    $this->setAction($url)->setMethod('post');
    $this->setLegend(sprintf(Zoo::_('Edit %s'), $this->target->name));
    $name = new Zend_Form_Element_Text('name');
    $name->setLabel('Name')->setRequired(true);
    $name->setDescription('Administration-side identifier');

    $title = new Zend_Form_Element_Text('title');
    $title->setLabel('Title');
    $title->setDescription('User-visible title of panel');

    $layout = new Zend_Form_Element_Select('layout');
    $layout->setLabel('Layout');
    $layout->setMultiOptions($this->getLayouts());

    $category = new Zend_Form_Element_Text('category');
    $category->setLabel('Category');
    $category->setDescription('Administration-side grouping');
    $category->setRequired(true);

    $submit = new Zend_Form_Element_Submit('save');
    $submit->setLabel('save')->setOrder(100);

    $this->addElements(array($name, $title, $layout, $category));

    $legend = Zoo::_("Basic options");
    $this->addDisplayGroup(array('name', 'title', 'layout', 'category'), 'block_form', array('legend' => $legend));

    $this->addElement($submit);
    if ($this->target->id > 0) {
      $id_ele = new Zend_Form_Element_Hidden('id');
      $id_ele->setValue(intval($this->target->id));
      $this->addElement($id_ele);
    }
    $this->populate($this->target->toArray());
  }

  /**
   * Get available layouts
   * @return array
   */
  function getLayouts() {
    $ret = array();
    $dir = new DirectoryIterator(ZfApplication::$_base_path . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Flex' . DIRECTORY_SEPARATOR . 'Layout');
    foreach ($dir as $file) {
      $layoutname = basename($file->getFilename(), '.php');
      if ($file->isFile() && $layoutname != "Abstract") {
        $ret["Flex_Layout_" . $layoutname] = $layoutname;
      }
    }
    return $ret;
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
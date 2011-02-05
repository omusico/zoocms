<?php

/**
 * @package Flex
 * @subpackage Form
 */

/**
 * @package Flex
 * @subpackage Form
 */
class Flex_Form_Add extends Zend_Form {

  /**
   * Object added/edited by the form
   *
   * @var Flex_Block
   */
  private $target;

  /**
   * Set form elements 
   *
   * @param Zoo_Block_Abstract $target
   * @param array $options
   */
  function __construct(Flex_Block $target, $options = array()) {
    $this->target = $target;
    parent::__construct($options);
  }

  function getAvailableBlocks() {
    $types = array();
    $directory = new DirectoryIterator(ZfApplication::$_base_path . "/app");
    foreach ($directory as $dir) {
      if ($dir->getFilename() == "Flex") {
        continue;
      }
      if (file_exists($dir->getPathname() . "/Block")) {
        $appdir = new DirectoryIterator($dir->getPathname() . "/Block");
        foreach ($appdir as $block) {
          if (!$block->isDot() && !$block->isDir()) {
            $name = substr($block->getFilename(), 0, strpos($block->getFilename(), "."));
            $classname = $dir->getFilename() . "_Block_" . $name;
            $types [$classname] = $classname;
          }
        }
      }
    }
    return $types;
  }

  /**
   * Form initialization
   *
   * @return void
   */
  public function init() {
    $url = Zend_Controller_Front::getInstance ()
                  ->getRouter()
                  ->assemble(array('module' => "flex",
                                   'controller' => 'block',
                                   'action' => 'form'),
                             'default');
    $this->setAction($url)->setMethod('post');

    $type = new Zend_Form_Element_Select('type');
    $type->setLabel('Type');
    $type->setRequired(true);
    $types = $this->getAvailableBlocks();
    $type->addMultiOptions($types);

    $name = new Zend_Form_Element_Text('name');
    $name->setLabel('Name')->setRequired(true);
    $name->setDescription('Administration-side identifier');

    $submit = new Zend_Form_Element_Submit('save');
    $submit->setLabel('save');

    $this->addElements(array($type, $name));

    $legend = $this->target->id > 0 ? Zoo::_("Edit block") : Zoo::_("Add block");
    $this->addDisplayGroup(array('name', 'type'), 'block_form', array('legend' => $legend));

    $this->addElement($submit);
    if ($this->target->id > 0) {
      $id_ele = new Zend_Form_Element_Hidden('id');
      $id_ele->setValue(intval($target->id));
      $this->addElement($id_ele);
    }
    $this->populate($this->target->toArray());
  }

}
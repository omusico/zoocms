<?php

/**
 * @package Flex
 * @subpackage Form
 */

/**
 * @package Flex
 * @subpackage Form
 */
class Flex_Form_Layout extends Zend_Form {

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
    $url = Zend_Controller_Front::getInstance ()->getRouter()->assemble(array('module' => "flex", 'controller' => 'panel', 'action' => 'layout'), 'default');
    $this->setAction($url)->setMethod('post');
    $this->setLegend(sprintf(Zoo::_('Edit %s'), $this->target->name));

    $settings_elements = $this->target->getLayout()->getSettingsFormElements();
    $this->addElements($settings_elements);

    $settings = $this->target->getLayout()->settings;
    $settings['structure'] = json_encode($settings['structure']);

    $legend = Zoo::_('Layout options');
    $this->addDisplayGroup ( $settings_elements, 'layout_settings', array ('legend' => $legend ) );

    $regions = $this->target->getLayout()->getAllRegions();
    $allregions = array();
    foreach (array_keys($regions) as $name) {
      $region = new Zend_Form_Element_Select('region_' . $name);
      $region->setLabel($name)->setMultiOptions($this->getRegionStyles());
      $this->addElement($region);
      $allregions[] = 'region_' . $name ;
    }
    $legend = Zoo::_('Region styles');
    $this->addDisplayGroup ( $allregions, 'region_form', array ('legend' => $legend ) );
    
    $submit = new Zend_Form_Element_Submit('save');
    $submit->setLabel('save')->setOrder(100);
    $this->addElement($submit);
    if ($this->target->id > 0) {
      $id_ele = new Zend_Form_Element_Hidden('id');
      $id_ele->setValue(intval($this->target->id));
      $this->addElement($id_ele);
    }
    $this->populate ( $settings );
    $this->populate($this->target->toArray());
  }

  /**
   * Return available region styles
   * @todo Make generic and load from other modules
   * @return array
   */
  protected function getRegionStyles() {
    $ret = array('standard' => 'Standard',
                 'box' => 'Box');
    try {
      Zoo::getService('hook')->trigger('panel', 'regionStyles', $ret);
    }
    catch (Zoo_Exception_Service $e) {
      // No hooks service available, do nothing
    }
    return $ret;
  }
}
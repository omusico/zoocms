<?php

/**
 *
 * @package    Flex
 * @subpackage Layout
 */

/**
 * Flex_Layout_Grid
 *
 * @package    Flex
 * @subpackage Layout
 * @copyright  © 2008 ZooCMS
 * @version    1.0
 */
class Flex_Layout_Grid extends Flex_Layout_Abstract {
  /**
   * Initialisation, called from parent's constructor
   */
  function init() {
    $this->template = "layout_grid";
    if (!$this->settings) {
      $this->settings = $this->getDefaultSettings();
    }
    $this->parseLayout();
  }

  /**
   * Get default settings if no others are given
   * @return array
   */
  function getDefaultSettings() {
    return array('structure' => json_encode(array(array('name' => 'default', 'width' => '24'))),
                 'add_css' => FALSE,
                 'columns' => 24,
                 'alphaomega' => TRUE,
                 'add_container' => TRUE);
  }

  function getSettingsFormElements() {
    $structure = new Zend_Form_Element_Textarea('structure');
    $structure->setLabel('Structure')->setAllowEmpty(false);

    $add_css = new Zend_Form_Element_Checkbox('add_css');
    $add_css->setLabel('Add css')->setOptions(array('1' => 'Add css'));
    $alphaomega = new Zend_Form_Element_Checkbox('alphaomega');
    $alphaomega->setLabel('Add alpha and omega')->setOptions(array('1' => 'Add alpha and omega'));
    $add_container = new Zend_Form_Element_Checkbox('add_container');
    $add_container->setLabel('Add container element')->setOptions(array('1' => 'Add container element'));
    $columns = new Zend_Form_Element_Select('columns');
    $columns->setLabel('Columns')->setMultiOptions(array(1 => 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24));

    return array($structure, $add_css, $alphaomega, $add_container, $columns);
  }

  /**
   *
   * @return array
   */
  function getAllRegions($structure = array(), $regions = array()) {
    if (!$structure) {
      $structure = $this->settings['structure'];
    }
    foreach ($structure as $layout_region) {
      if (isset($this->settings['region_' . $layout_region['name']])) {
        $layout_region['template'] = $this->settings['region_' . $layout_region['name']];
      }
      $regions[$layout_region['name']] = $layout_region;
      if (isset($layout_region['children'])) {
        $this->getAllRegions($layout_region['children'], $regions);
      }
    }
    return $regions;
  }

  /**
   * Render the grid layout
   * @param array $blocks
   * @return string
   */
  function render($blocks = array()) {
    $this->regionId(NULL, TRUE);
    if ($this->settings['add_css'] || $this->is_admin_page) {
      //drupal_add_css(drupal_get_path('module', 'bem_panels') . '/panels/layouts/grid/grid' . $settings['column_layout'] . '.css');
    }
    if ($this->is_admin_page) {
      //drupal_add_css(drupal_get_path('module', 'bem_panels') . '/panels/layouts/grid/grid' . $settings['column_layout'] . '.admin.css');
    }
    return parent::render($blocks);
  }

  /**
   * Render a grid element, function is recursive so will call it self
   * to create different levels in the grid layout.
   */
  function renderElement($content, $element, $container_width, $outer_layer = FALSE) {
    $remaining = $container_width;
    $gridhtml = '';
    // there are 2 types of levels, one which defines a deck of grid
    // elements and then one which is a grid element.
    $grid_elements = array();
    // might want to refactor this function to use objects instead of casting the variables.
    $element = (array) $element;
    // going through children and adding them to our parse array
    if (!isset($element['width']) && is_array($element)) {
      // check if there are elements to process.
      foreach ($element AS $child) {
        $child = (array) $child;
        // a child need a width else we ignore it.
        if (isset($child['width'])) {
          $grid_elements[] = $child;
        }
      }
    } else {
      $grid_elements[] = $element;
    }

    // @todo: make all grid related classes configurable.
    // @todo: figure out if we should handle cases where push/pull breaks the layout.
    $classes = array(
        'prefix' => ' prefix_',
        'suffix' => ' suffix_',
        'push' => ' push_',
        'pull' => ' pull_',
    );

    foreach ($grid_elements AS $grid_element) {
      // build the actual grid html element.
      $css_class = 'grid_' . $grid_element['width'];
      if (isset($grid_element['css_class']) && !empty($grid_element['css_class'])) {
        $css_class .= ' ' . $grid_element['css_class'];
      }

      // prefix, suffix
      foreach ($classes AS $idx => $class) {
        if (isset($grid_element[$idx])) {
          $css_class .= $class . $grid_element[$idx];
        }
      }
      $grid_element_width = ($grid_element['width'] + @$grid_element['prefix'] + @$grid_element['suffix']);
      if (($remaining - $grid_element_width) < 0) {
        $remaining = $container_width;
        $gridhtml .= '<div class="clear"></div>';
      }

      // If our current remaining is the same as the containerWidth
      // we presume that it's the first element
      if ($container_width == $remaining && !$outer_layer) {
        $css_class .= ' alpha'; // @todo: make class configurable per panel
      }

      if (($remaining - $grid_element_width) == 0 && !$outer_layer) {
        $css_class .= ' omega'; // @todo: make class configurable per panel
      }

      $grid_content = NULL;
      if (isset($grid_element['children'])) {
        // the element got children lets start again!
        $grid_content = "\t" . $this->renderElement($content, $grid_element['children'], $grid_element['width']);
      } else {
        // region-naming
        $name = (isset($grid_element['name']) && !empty($grid_element['name'])) ? $grid_element['name'] : NULL;
        $id = $this->regionId($name);
        $css_class .= ' panel-region ' . $id; 
        $grid_content = isset($content[$id]) ? $content[$id] : "&nbsp;";
      }

      $gridhtml .= '<div class="' . $css_class . '">';
      $gridhtml .= $grid_content;
      $gridhtml .= '</div>' . "\n";

      $remaining -= $grid_element_width;
      if (($remaining) == 0) {
        $remaining = $container_width;
        $gridhtml .= '<div class="clear"></div>' . "\n";
      }
    }
    return $gridhtml;
  }

  /**
   * Parses the JSON and return an array.
   * @param string $layout_unparsed
   * @return array $layout
   */
  function parseLayout() {
    $this->settings['structure'] = json_decode($this->settings['structure'], TRUE);
  }

  /**
   * Helper function that ensures that we don't use the same regionid twice
   * @param string $name [optional]
   * @param boolean $reset [optional - true resets the regions and id]
   */
  function regionId($name = NULL, $reset = FALSE) {
    static $id = 1;
    static $regions = array();

    if ($reset) {
      $id = 1;
      $regions = array();
      return;
    }

    if (!is_null($name)) {
      // Specially named region
      // To keep it simple, we wont handle 2 regions with the same name.
      if (array_key_exists($name, $regions)) {
        return FALSE;
      }
      $region = $regions[$name] = $name;
    } else {
      // numerical named region
      $region = 'region-' . ($id++);
    }

    return $region;
  }

}

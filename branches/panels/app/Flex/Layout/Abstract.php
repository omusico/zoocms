<?php
/**
 *
 * @package    Flex
 * @subpackage Layout
 */

/**
 * Flex_Layout_Abstract
 *
 * @package    Flex
 * @subpackage Layout
 * @copyright  © 2008 ZooCMS
 * @version    1.0
 */
abstract class Flex_Layout_Abstract {
  /**
   * Layout settings
   * @var array
   */
  public $settings = array();

  /**
   * Template to use for rendering this layout
   * @var string
   */
  protected $template = "layout_standard";

  /**
   * @var Zend_View_Abstract
   */
  protected $view;

  /**
   * Whether the layout should display the admin view
   * 
   * @var bool
   */
  public $is_admin_page = FALSE;

  /**
   * Set initial settings and call init()
   *
   * @param array $settings
   */
  function __construct($settings = array()) {
    $this->settings = $settings;
    
    $layout = Zend_Layout::getMvcInstance();
    $this->view = $layout->getView();
    // Add module paths to view scripts
    $this->view->addBasePath(ZfApplication::$_base_path . "/app/Flex/views", "Flex_View");
    $this->view->addScriptPath($layout->getLayoutPath() . "default/templates/Flex/");
    $this->view->addScriptPath($layout->getLayoutPath() . $layout->getLayout() . "/templates/Flex/");
    $this->init();
  }

  /**
   * Override in subclasses
   */
  abstract function init();

  /**
   * override in subclasses
   * @var string $region
   */
  abstract function getAllRegions();

  /**
   * Return layout-specific settings to the layout edit form
   * @return void
   */
  public function getSettingsFormElements() {
    return;
  }

  /**
   * Render the layout
   * @param array $blocks
   * @return string
   */
  function render($blocks = array()) {
    if ($this->is_admin_page) {
      $this->view->jQuery()->enable()->uiEnable();
    }
    $all_regions = $this->getAllRegions();
    foreach ($all_regions as $region) {
      $regions[$region['name']] = $this->renderRegion($region, @$blocks[$region['name']]);
    }
    $this->view->assign('regions', $regions);
    $this->view->assign('layout', $this);
    return $this->view->render($this->template);
  }

  /**
   * Render a panel region
   * @param array $region
   * @param array $blocks
   * @return string
   */
  function renderRegion($region, $blocks = array()) {
    if ($blocks || $this->is_admin_page) {
      $rendered_blocks = array();
      if ($blocks) {
        foreach ($blocks as $block) {
          $block->options['region'] = $region;
          if (!$this->is_admin_page) {
            $rendered_blocks[$block->id] = $block->render();
          }
          else {
            $rendered_blocks[$block->id] = $this->renderAdminBlock($block);
          }
        }
      }
      $this->view->assign('region', $region);
      $this->view->assign('blocks', $rendered_blocks);
      if ($this->is_admin_page) {
        $template = "region_admin";
      }
      else {
        $template = "region_" . (isset($region['template']) && $region['template'] != "" ? $region['template'] : "standard");
      }
      return $this->view->render($template);
    }
    return "";
  }

  /**
   * Returns HTML for a block's admin-view
   * @param Zoo_Block_Abstract $block
   * @return string
   */
  function renderAdminBlock($block) {
    $input = '<input type="hidden" name="block[]" value="' . $block->id . '" />';
    return $input . ($block->title ? $block->title : get_class($block));
  }
}

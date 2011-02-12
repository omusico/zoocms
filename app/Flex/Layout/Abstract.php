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
   * Set initial settings and call init()
   *
   * @param array $settings
   */
  function __construct($settings = array()) {
    $this->settings = $settings;
    $this->view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
    $this->init();
    $layout = Zend_Layout::getMvcInstance();
    // Add module paths to view scripts
    $this->view->addBasePath(ZfApplication::$_base_path . "/app/Flex/views", "Flex_View");
    $this->view->addScriptPath($layout->getLayoutPath() . "default/templates/Flex/");
    $this->view->addScriptPath($layout->getLayoutPath() . $layout->getLayout() . "/templates/Flex/");
  }

  /**
   * Override in subclasses
   */
  abstract function init();

  /**
   * override in subclasses
   * @var string $region
   */
  abstract function getRegion($region);

  /**
   * Render the layout
   * @param array $blocks
   * @return string
   */
  function render($blocks = array()) {
    foreach ($blocks as $region => $regionblocks) {
      $regions[$region] = $this->renderRegion($region, $regionblocks);
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
  function renderRegion($region, $blocks) {
    if ($blocks) {
      $region = $this->getRegion($region);
      foreach ($blocks as $block) {
        $block->options['region'] = $region;
        $rendered_blocks[] = $block->render();
      }
      $this->view->assign('region', $region);
      $this->view->assign('blocks', $rendered_blocks);
      $template = isset($region['template']) && $region['template'] != "" ? $region['template'] : "region_standard";
      return $this->view->render($template);
    }
    return "";
  }
}

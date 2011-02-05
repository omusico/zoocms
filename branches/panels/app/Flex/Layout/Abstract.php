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
class Flex_Layout_Abstract {
  /**
   * Layout settings
   * @var array
   */
  protected $settings = array();

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
    $this->settings = array();
    $this->view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
    $this->init();
  }

  /**
   * Override in subclasses
   */
  abstract function init() { }

  /**
   * Render the layout
   * @param array $blocks
   * @return string
   */
  function render($blocks = array()) {
    foreach ($blocks as $region => $regionblocks) {
      $regions[$region] = $this->renderRegion($regionblocks);
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

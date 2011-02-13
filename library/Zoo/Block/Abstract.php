<?php
/**
 * @package    Zoo
 * @subpackage Block
 */

/**
 * Zoo_Block_Abstract
 *
 * @package    Zoo
 * @subpackage Block
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
abstract class Zoo_Block_Abstract {
  /**
   * Block output template
   *
   * @var string
   */
  public $template;
  /**
   * Block title
   *
   * @var string
   */
  public $title;
  /**
   **
   * Block name
   *
   * @var string
   */
  public $name;

   /**
   * Block content
   *
   * @var string
   */
  public $content = "";

  /**
   * Block weight for ordering blocks relative to each other
   *
   * @var int
   */
  public $weight = 50;
  /**
   * Name of the block's module
   *
   * @var string
   */
  public $module;
  /**
   * Time to cache the module in seconds
   *
   * @var int
   */
  public $cache_time = 2;
  /**
   * Block configuration options
   *
   * @var array
   */
  public $options = array ();

  /**
   * Block style (wrapper template)
   * @var string
   */
  public $style = "";

  /**
   * Block ID
   *
   * @var int
   */
  public $id;

  /**
   * Apply general options
   *
   * @param array $options
   */
  function __construct($options = array()) {
    if (isset ( $options ['id'] )) {
      $this->id = $options ['id'];
    }
    if (isset ( $options ['title'] )) {
      $this->title = $options ['title'];
    }
    if (isset ( $options ['name'] )) {
      $this->name = $options ['name'];
    }
    if (isset ( $options ['options'] )) {
      if (is_string ( $options ['options'] )) {
        $this->options = unserialize ( $options ['options'] );
      }
      elseif (is_array ( $options ['options'] )) {
        $this->options = $options ['options'];
      }
    }
    if (isset ( $options ['cache_time'] )) {
      $this->cache_time = $options ['cache_time'];
    }
    $this->module = substr ( get_class ( $this ), 0, strpos ( get_class ( $this ), "_" ) );
    $this->setTemplate ( strtolower ( substr ( get_class ( $this ), strrpos ( get_class ( $this ), "_" ) + 1 ) ) );
  }

  /**
   * Returns a unique ID for this block
   * Can be overridden in subclasses to depend on e.g. current page or other factors affecting content
   *
   *
   * @return string
   */
  function getCacheId() {
    return get_class ( $this ) . "_" . $this->id;
  }

  /**
   * Get cache tags for the block's content
   * @return array
   */
  function getCacheTags() {
    return array ();
  }

  /**
   * Return an array of vars to be assigned to the Zend_View_Abstract object for use in the block's template
   *
   * @return array
   */
  function getTemplateVars() {
    return array ();
  }

  /**
   * Get template for block
   * Subclasses can either just set the template class member or do calculations in an overriding method to determine it
   *
   * @return string
   */
  function setTemplate($template) {
    $this->template = $template;
  }

  /**
   * Get configuration options for this block
   * @return false|Zend_Form_Subform
   */
  function getOptions() {
    return false;
  }

  /**
   * Generate content for the block
   * @return String
   */
  function render() {
    $view = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'viewRenderer' )->view;
    /* @var $view Zend_View_Abstract */
    $cacheid = $this->getCacheId ();
    $content = "";
    if ($cacheid) {
      try {
        $this->content = Zoo::getService ( "cache" )->load ( $cacheid );
      }
      catch ( Zoo_Exception_Service $e ) {
        // Cache unavailable
      }
    }
    if (! $this->content) {
      $blockview = clone $view;

      $blockview->clearVars ();
      $this->resetViewScripts ( $blockview );
      $this->addLanguage ();

      $vars = $this->getTemplateVars ();
      if ($vars !== false) {
        try {
          // trigger {Module}_Hook_Block->render()
          Zoo::getService('hook')->trigger('block', 'render', $block, $vars);
        }
        catch (Zoo_Exception_Service $e) {
          // No hook service
        }
        $blockview->assign ( $vars );
        $this->content = $blockview->render ( $this->template );
      }
      if ($cacheid && $this->cache_time > 0) {
        try {
          Zoo::getService ( 'cache' )->save ( $this->content,
                                              $cacheid,
                                              array_merge ( array ('block',
                                                                   'block_' . $this->id,
                                                                   'block_' . get_class ( $this ) ),
                                                                   $this->getCacheTags () ),
                                              $this->cache_time );
        }
        catch ( Zoo_Exception_Service $e ) {
          // Cache service not available, do nothing
        }
      }
    }
    $style = $this->panel_block->style ? $this->panel_block->style : $this->style;
    if ($style) {
      $style_view = clone $view;
      $style_view->assign('block', $this);
      $content = $style_view->render($style);
    }
    else {
      $content = $this->content;
    }
    return $content;
  }

/**
   * Reset the view's script paths and set new ones for use in the block
   *
   * @param Zend_View_Abstract $view
   */
  private function resetViewScripts(Zend_View_Abstract $view) {
    $layout = Zend_Layout::getMvcInstance ();
    // Reset view script paths
    $view->setScriptPath ( null );

    $module = ucfirst ( $this->module );
    // Build new ones for blocks
    $view->addBasePath ( ZfApplication::$_base_path . "/app/$module/views", $module . "_View" );
    $view->addScriptPath ( ZfApplication::$_base_path . "/app/$module/views/scripts/blocks" );
    $view->addScriptPath ( $layout->getLayoutPath () . "default/templates/blocks" );
    $view->addScriptPath ( $layout->getLayoutPath () . "default/templates/$module/blocks" );
    $view->addScriptPath ( $layout->getLayoutPath () . $layout->getLayout () . "/templates/blocks" );
    $view->addScriptPath ( $layout->getLayoutPath () . $layout->getLayout () . "/templates/$module/blocks" );
  }

  /**
   * Add language from block's module
   * @todo Only add if not already loaded?
   */
  function addLanguage() {
    try {
      Zoo::getService ( "translator" )->addTranslation ( ZfApplication::$_base_path . "/app/" . ucfirst ( $this->module ) . "/Language",
                                                         null,
                                                         array ('scan' => Zend_Translate::LOCALE_FILENAME ) );
    }
    catch ( Zend_Translate_Exception $e ) {
      // Translation doesn't exist, no biggie, do nothing
    }
  }
}
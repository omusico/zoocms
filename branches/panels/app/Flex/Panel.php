<?php

/**
 *
 * @package    Flex
 * @subpackage Panel
 */

/**
 * Flex_Panel
 *
 * @package    Flex
 * @subpackage Panel
 * @copyright  © 2008 ZooCMS
 * @version    1.0
 */
class Flex_Panel extends Zend_Db_Table_Row_Abstract {
  /**
   *
   * @var array
   */
  public $blocks = array();

  /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init()
    {
        //@todo find a better solution to serialized array data types?
        if (is_string($this->_data['settings'])) {
            if ($this->_data['settings'] != "") {
                $this->settings = unserialize($this->_data['settings']);
            }
            else {
                $this->settings = array();
            }
        }
    }
    
    protected function _insert() {
        $this->settings = serialize($this->settings);
    }

    protected function _postInsert() {
        $this->settings = unserialize($this->settings);
    }

    protected function _update() {
        $this->settings = serialize($this->settings);
    }

    protected function _postUpdate() {
        $this->settings = unserialize($this->settings);
    }

  /**
   * Get all Flex_Panel parents to this panel
   *
   * @param array $ret
   * @return array
   */
  function getAllParents($ret = array()) {
    if ($this->parent_id > 0) {
      $parent = $this->_table->find($this->parent_id)->current();
      $ret[] = $parent;
      return $parent->getAllParents($ret);
    }
    return $ret;
  }

  /**
   * Get layout for this panel
   * 
   * @return Flex_Layout
   */
  function getLayout() {
    $layout = $this->layout;
    return new $layout($this->settings);
  }

  /**
   * Load blocks for this panel
   *
   * @return Flex_Panel
   */
  function loadBlocks() {
    $ret = array();
    $groups = array(0);
    try {
      $groups = array_keys( Zoo::getService('user')->getCurrentUser()->getGroups() );
    } catch (Zoo_Exception_Service $e) {
      // Do nothing
    }

    $cacheid = "Flex_panel_" . $this->id . "_" . implode("_", $groups);
    try {
      $ret = Zoo::getService("cache")->load($cacheid);
    } catch (Zoo_Exception_Service $e) {
      // @todo: Remove this comment: I'm beginning to tire of writing try-catch for services
    }
    if (!$ret) {
      $factory = new Flex_Panel_Block_Factory();
      $ret = $factory->getPanelBlocks($this);

      try {
        // If no cache service available, no need to do cache tag processing
        $cache = Zoo::getService('cache');
        $tags = array('panel', 'panel_' . $this->id);
        foreach ($ret as $region => $blocks) {
          foreach ($blocks as $block) {
            $tags[] = "block_" . $block->id;
          }
        }
        $cache->save($ret, $cacheid, $tags, null);
      } catch (Zoo_Exception_Service $e) {
        // No caching available
      }
    }

    $this->blocks = $ret;
    // Allow for method chaining
    return $this;
  }

  /**
   * Render a panel - i.e. sub-panels and blocks in this panel
   * Assigns to the viewRenderer's view
   */
  function render($admin = false) {
    if ($this->blocks) {
      // Assign to general content
      $layout = $this->getLayout();
      Zend_Layout::getMvcInstance()->assign(Zend_Layout::getMvcInstance()->getContentKey(),
                                            $layout->render($this->blocks));
    }
    return;
  }

}


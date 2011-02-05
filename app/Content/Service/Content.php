<?php

/**
 * @package    Content
 * @subpackage Service
 */

/**
 * Content_Service_Content
 *
 * @package    Content
 * @subpackage Service
 * @copyright  Copyright (c) 2009 ZooCMS
 * @version    1.0
 */
class Content_Service_Content extends Zoo_Service {

  /**
   * @var string
   */
  public $action = "index";
  /**
   * @var string
   */
  public $controller = "node";
  /**
   * @var string
   */
  public $module = "content";
  /**
   *
   * @var Zend_View
   */
  protected $view;
  /**
   * Array of available content types
   *
   * @var array
   */
  protected $types = array();

  /**
   *
   * @staticvar Content_Node_Factory $factory
   * @return Content_Node_Factory
   */
  public function getFactory() {
    static $factory;
    $factory = new Content_Node_Factory();
    return $factory;
  }

  /**
   * Route calls to nondefined methods to the Content_Node_Factory
   *
   * @param string $name
   * @param array $arguments
   *
   * @return mixed
   */
  public function __call($name, $arguments) {
    return call_user_func_array(array($this->getFactory(), $name), $arguments);
  }

  /**
   * Get Zend_Db_Table_Select object to retrieve content
   * 
   * @param array $options
   * @param int $start
   * @param int $limit
   * @return Zend_Db_Table_Select
   */
  function getContentSelect($options = array(), $start = 0, $limit = 20) {
    $this_table_name = $this->getFactory()->info(Zend_Db_Table_Abstract::NAME);
    $select = $this->getFactory()->select()->from(array('c' => $this_table_name));
    if (isset($options['active']) && $options['active'] == true) {
      $select->where('status = ?', 1)
              ->where('published <= ?', time());
    }
    if (isset($options['ids']) && is_array($options['ids']) && count($options['ids']) > 0) {
      $select->where('id IN (?)', $options['ids']);
    }
    if (isset($options['group'])) {
      $type_table = new Content_Type_Factory();
      $type_table_name = $type_table->info(Zend_Db_Table_Abstract::NAME);
      $select->join(array('t' => $type_table_name), "c.type = t.type", array());
      $select->where('`group` = ?', $options['group']);
    }
    if (isset($options['nodetype'])) {
      $select->where('type = ?', $options['nodetype']);
    }
    if (isset($options['parent']) && $options['parent'] != 0) {
      $select->where('pid = ?', $options['parent']);
    }
    if (isset($options['author'])) {
      $select->where('uid = ?', $options['author']);
    }

    if (isset($options['order'])) {
      $select->order($options['order']);
    } else {
      $select->order("published DESC");
    }

    $select->limit($limit, $start);
    return $select;
  }

  /**
   *
   * @param array $options
   * @param int $start
   * @param int $limit
   * @return array
   */
  function getContent($options = array(), $start=0, $limit = 20) {
    if (!isset($options['viewtype'])) {
      $options['viewtype'] = "List";
    }
    if (!isset($options['hooks'])) {
      $options['hooks'] = true;
    }
    $select = $this->getContentSelect($options, $start, $limit);

    $items = $this->fetchAll($select);

    $ret = array();
    foreach ($items as $key => $item) {
      $ret[] = $this->loadFromNode($item, $options['viewtype']);
    }
    if (!isset($options['render']) || $options['render'] == false) {
      return $ret;
    }
    return count($ret) > 0 ? $this->getRenderedContent($ret, $options['viewtype']) : array();
  }

  /**
   *
   * @param int|array $id
   * @param string $type
   * @return array Rendered content items
   */
  function getRenderedContent($ids, $type = 'List') {
    if (!is_array($ids)) {
      $ids = array($ids);
    }

    foreach ($ids as $id) {
      if (!is_object($id)) {
        $item = $this->load($id, $type);
      } else {
        $item = $id;
      }
      $can_edit = Zoo::getService('acl')->checkItemAccess($item, 'edit');
      $cacheid = "Content_node" . $type . "_" . $item->id . ($can_edit ? "_edit" : "");
      try {
        $cached = Zoo::getService("cache")->load($cacheid);
        if ($cached) {
          $content[] = $cached;
        }
      } catch (Zoo_Exception_Service $e) {
        // Cache service unavailable, set content to empty string
        $cached = false;
      }
      if (!$cached) {
        // Render content item
        if (!($this->view)) {
          $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
          /* @var $view Zend_View_Abstract */
          // Don't clone the view until it is needed
          $this->view = clone $view;
          $this->view->clearVars();
        }

        list($module, $nodetype) = explode('_', $item->type);
        $this->resetView($module, $nodetype);
        $this->addLanguage($module);

        $this->view->assign('item', $item);
        $this->view->assign('can_edit', $can_edit);
        $rendered = $this->view->render($type == "Display" ? "index" : $type);
        $content[] = $rendered;

        try {
          Zoo::getService('cache')->save($rendered,
                  $cacheid,
                  array('node' . $type, 'node_' . $item->id),
                  null);
        } catch (Zoo_Exception_Service $e) {
          // Cache service not available, do nothing
        }
      }
    }
    return $content;
  }

  /**
   *
   * @param array $ids
   * @param string $group
   * @return Zend_Db_Table_Rowset
   */
  function countChildren($ids, $group = "") {
    $factory = $this->getFactory();
    $select = $factory->select()
                    ->from(array('c' => $factory->info(Zend_Db_Table_Abstract::NAME)),
                            array('pid', 'count' => 'COUNT(*)'))
                    ->where('pid IN (?)', $ids);
    if ($group != "") {
      $type_table = new Content_Type_Factory();
      $type_table_name = $type_table->info(Zend_Db_Table_Abstract::NAME);
      $select->join(array('t' => $type_table_name), "c.type = t.type", array());
      $select->where('`group` = ?', $group);
    }
    $select->group('pid');
    $ret = $factory->fetchAll($select);
    $counts = array();
    foreach ($ret as $child_count) {
      $counts[$child_count->pid] = $child_count->count;
    }
    return $counts;
  }

  /**
   * Reset the view's script paths and set new ones
   * @todo: Move elsewhere - it shouldn't be in a service
   *
   * @param string $module
   * @param string $type
   */
  private function resetView($module, $type) {
    $module = ucfirst($module);
    $layout = Zend_Layout::getMvcInstance();
    // Reset view script paths
    $this->view->setScriptPath(null);

    // Build new ones for blocks
    $this->view->addBasePath(ZfApplication::$_base_path . "/app/" . ucfirst($module) . "/views", ucfirst($module) . "_View");
    $this->view->addScriptPath(ZfApplication::$_base_path . "/app/Content/views/scripts/$type");
    $this->view->addScriptPath(ZfApplication::$_base_path . "/app/" . ucfirst($module) . "/views/scripts/$type");
    $this->view->addScriptPath($layout->getLayoutPath() . "default/templates/" . ucfirst($module) . "/$type");
    $this->view->addScriptPath($layout->getLayoutPath() . $layout->getLayout() . "/templates/" . ucfirst($module) . "/$type");
  }

  /**
   * Add language from block's module
   * @todo Only add if not already loaded?
   *
   * @param string $module
   */
  function addLanguage($module) {
    try {
      Zoo::getService("translator")->addTranslation(
              ZfApplication::$_base_path . "/app/" . ucfirst($module) . "/Language",
              null,
              array('scan' => Zend_Translate::LOCALE_FILENAME));
    } catch (Zend_Translate_Exception $e) {
      // Translation doesn't exist, no biggie, do nothing
    }
  }

  /**
   *
   * @param Zoo_Node_Interface $item
   * @return Zend_Db_Table_Rowset
   */
  function getFilters($item) {
    $factory = new Content_Node_Filter_Factory();
    return $factory->fetchAll($factory->select()
                    ->where('id = ? ', $item->id));
  }

  /**
   *
   * Add or remove a filter from a content item
   *
   * @param Content_Node_Interface $item
   * @param int $filter_id
   * @param int $value
   * @return bool
   */
  function setFilter($item, $filter_id, $value) {
    $factory = new Content_Node_Filter_Factory();
    $nodefilter = $factory->fetchRow($factory->select()
                            ->where('id = ? ', $item->id)
                            ->where('filter_id = ?', $filter_id));
    if (!$nodefilter && $value) {
      // Add filter
      $nodefilter = $factory->createRow(array('id' => $item->id,
                  'filter_id' => $filter_id));
      return $nodefilter->save();
    } elseif ($nodefilter && !$value) {
      // Remove filter
      return $nodefilter->delete();
    }
    return true;
  }

  /**
   * Get content type for an identifier
   *
   * @param string $identifier
   * @return Content_Type
   */
  function getType($identifier) {
    if (!$this->types) {
      $this->loadTypes();
    }
    if (!isset($this->types[$identifier])) {
      throw new Exception('Content type ' . htmlspecialchars($identifier) . ' not found');
    }
    return $this->types[$identifier];
  }

  /**
   * Get array of all available types
   *
   * @return array
   */
  function getTypes() {
    if (!$this->types) {
      $this->loadTypes();
    }
    return $this->types;
  }

  /**
   * Load content types from data source
   */
  protected function loadTypes() {
    $type_factory = new Content_Type_Factory();
    $types = $type_factory->fetchAll();
    foreach ($types as $type) {
      $this->types[$type->type] = $type;
    }
  }

  /**
   * Load a node from cache, or fetch from db, run hooks and cache result
   * 
   * @param int $node
   * @param string $type
   * @return Zoo_Content_Interface
   */
  public function load($id, $type) {
    // Call hooks for items
    try {
      $cacheid = "load_node_" . $id . '_' . $type;
      $node = Zoo::getService('cache')->load($cacheid);
      if (!$node) {
        $node = $this->find($id)->current();
        $node = Zoo::getService("hook")->trigger("Node", ucfirst($type), $node);
        Zoo::getService('cache')->save($node, $cacheid, array('node_' . $id));
      }

      return $node;
    } catch (Zoo_Exception_Service $e) {
      $node = $this->find($id)->current();
      return Zoo::getService("hook")->trigger("Node", ucfirst($type), $node);
    }
  }

  /**
   * Load a node from cache, or fetch from db, run hooks and cache result
   *
   * @param Zoo_Content_Interface $node
   * @param string $type
   * @return Zoo_Content_Interface
   */
  public function loadFromNode(&$node, $type) {
    // Call hooks for items
    try {
      $id = $node->id;
      $cacheid = "load_node_" . $id . '_' . $type;
      $node = Zoo::getService('cache')->load($cacheid);
      if (!$node) {
        $node = $this->find($id)->current();
        Zoo::getService("hook")->trigger("Node", ucfirst($type), $node);
        Zoo::getService('cache')->save($node, $cacheid, array('node_' . $id));
      }
      return $node;
    } catch (Zoo_Exception_Service $e) {
      return Zoo::getService("hook")->trigger("Node", ucfirst($type), $node);
    }
  }

}
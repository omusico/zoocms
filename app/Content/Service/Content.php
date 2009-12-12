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
        $this_table_name = $this->getFactory()->info(Zend_Db_Table_Abstract::NAME);
        $select = $this->getFactory()->select()->from(array('c' => $this_table_name));
        if (isset($options['active']) && $options['active'] == true) {
            $select->where('status = ?', 1)
                    ->where('published <= ?', time());
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
        }
        else {
            $select->order("published DESC");
        }
        
   		$select->limit($limit, $start);
        
        $items = $this->fetchAll($select);
        if (!isset($options['render']) || $options['render'] == false) {
        	if ($options ['hooks']) {
				// Call hooks for items
				try {
					Zoo::getService ( "hook" )->trigger ( "Node", ucfirst ( $options ['viewtype'] ), $items );
				} catch ( Zoo_Exception_Service $e ) {
					// Hook service not available - log? Better not, some people may live happily without a hook service
				}
			}
            return $items;
        }

        $ids = array();
        foreach ($items as $item) {
            $ids[] = $item->id;
        }
        return count($ids) > 0 ? $this->getRenderedContentById($ids, $options['viewtype']) : array();
    }

    /**
     *
     * @param int|array $id
     * @param string $type
     * @return array Rendered content items
     */
    function getRenderedContentById($ids, $type = 'List') {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        foreach ($ids as $id) {
            
            $cacheid = "Content_node".$type."_".$id;
            try {
                $cached = Zoo::getService("cache")->load($cacheid);
                $content[] = $cached;
            }
            catch (Zoo_Exception_Service $e) {
                // Cache service unavailable, set content to empty string
                $cached = false;
            }
            if (!$cached) {
                $found = $this->find($id);
                if ($found->count() == 0) {
                    continue;
                }
                $item = $found->current();
                // Call hooks for items
                try {
                    $hookitems = $type == "Display" ? $item : array($item);
                    Zoo::getService("hook")->trigger("Node", ucfirst($type), $hookitems);
                }
                catch (Zoo_Exception_Service $e) {
                    // Hook service not available - log? Better not, some people may live happily without a hook service
                }
                // Render content item
                if (!($this->view)) {
                    $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
                    /* @var $view Zend_View_Abstract */
                    // Don't clone the view until it is needed
                    $this->view = clone $view;
                    $this->view->clearVars();
                }
                    
                $module = substr($item->type, 0, strpos($item->type, "_"));
                $this->resetView($module);
                $this->addLanguage($module);

                $this->view->assign('item', $item);
                $rendered = $this->view->render($type == "Display" ? "index.phtml" : $type);
                $content[] = $rendered;

                try {
                    Zoo::getService('cache')->save($rendered,
                        $cacheid,
                        array('node'.$type, 'node_'.$item->id),
                        null);
                }
                catch (Zoo_Exception_Service $e) {
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
                            ->from( array('c' => $factory->info(Zend_Db_Table_Abstract::NAME)),
                                    array('pid', $group => 'COUNT(*)'))
                            ->where('pid IN (?)', $ids);
        if ($group != "") {
            $type_table = new Content_Type_Factory();
            $type_table_name = $type_table->info(Zend_Db_Table_Abstract::NAME);
            $select->join(array('t' => $type_table_name), "c.type = t.type", array());
            $select->where('`group` = ?', $group);
        }
        $select->group('pid');
        return $factory->fetchAll($select);
    }

    /**
     * Reset the view's script paths and set new ones for use in the block
     *
     * @param Zend_View_Abstract $view
     * @param Zoo_Block_Abstract $block
     */
    private function resetView($module) {
        $module = ucfirst($module);
        $layout = Zend_Layout::getMvcInstance();
        // Reset view script paths
        $this->view->setScriptPath(null);

        // Build new ones for blocks
        $this->view->addBasePath(ZfApplication::$_base_path."/app/$module/Views", $module."_View");
        $this->view->addScriptPath(ZfApplication::$_base_path."/app/Content/Views/scripts/node");
        $this->view->addScriptPath(ZfApplication::$_base_path."/app/$module/Views/scripts/node");
        $this->view->addScriptPath($layout->getLayoutPath()."default/templates/$module/node");
        $this->view->addScriptPath($layout->getLayoutPath().$layout->getLayout()."/templates/$module/node");
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
                                                ZfApplication::$_base_path."/app/".$module."/Language",
                                                null,
                                                array('scan' => Zend_Translate::LOCALE_FILENAME ));
        }
        catch (Zend_Translate_Exception $e) {
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
        }
        elseif ($nodefilter && !$value) {
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
        if( !isset($this->types[$identifier])) {
            throw new Exception('Content type '.htmlspecialchars($identifier).' not found');
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
}
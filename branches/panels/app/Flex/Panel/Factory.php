<?php

class Flex_Panel_Factory extends Zoo_Db_Table {

  /**
   * Get panel for currently viewed page
   * 
   * @return Flex_Panel
   */
  function getCurrentPanel() {
    $panel = false;
    if ($node = Zend_Registry::get('context')->node) {
      $cacheId = "node_" . $node->nid;
      // Look first in cache
      if (!$panel = $this->getFromCache($cacheId)) {
        // Node-to-panel relation?
        // SELECT panel_id from panel_node_relation where nid=$nid
        $factory = new Flex_Panel_Node_Factory();
        $select = $factory->select()
                        ->where('nid IN (?)', array(0, $node->nid))
                        ->where('parent IN (?)', array(0, $node->parent))
                        ->where('nodetype IN (?)', array('', $node->type))
                        ->order(array('nid DESC',
                            'parent DESC',
                            'nodetype DESC'));
        $panel_row = $factory->fetchRow($select);
        if ($panel_row) {
          $panel = $this->getPanel($panel_row->panel_id);
        }
        else {
          // Nodetype-and-parent-to-panel relation? (recursive)
          // SELECT panel_id from panel_node_relation where parent=$pid AND nodetype=$nodetype
          $pid = $node->pid;
          while ($pid) {
            $parents[] = $pid;
            $parent = Zoo::getService('content')->load($pid, 'list');
            $pid = $parent ? $parent->pid : false;
          }
          foreach ($parents as $parent) {
            $select = $factory->select()
                            ->where('parent = ?', $parent->nid)
                            ->where('nodetype = ?', $node->type);
            $panel_row = $factory->fetchRow($select);
            if ($panel_row) {
              $panel = $this->getPanel($panel_row->panel_id);
              break;
            }
          }
          if (!$panel) {
            // All-nodetypes-and-parent-to-panel relation? (recursive)
            // SELECT panel_id from panel_node_relation where parent=$pid AND nodetype=''
            foreach ($parents as $parent) {
              $select = $factory->select()
                              ->where('parent = ?', $parent->nid)
                              ->where('nodetype = ?', '');
              $panel_row = $factory->fetchRow($select);
              if ($panel_row) {
                $panel = $this->getPanel($panel_row->panel_id);
                break;
              }
            }
          }
          if (!$panel) {
            // Nodetype-to-panel relation?
            // SELECT panel_id from panel_node_relation where parent=0 AND nodetype=$nodetype
            $select = $factory->select()
                            ->where('parent = ?', 0)
                            ->where('nodetype = ?', $node->type);
            $panel_row = $factory->fetchRow($select);
            if ($panel_row) {
              $panel = $this->getPanel($panel_row->panel_id);
            }
          }
        }
      }
    }

    if (!$panel) {
      // No node page or no panel found
      // First check the cache
      $request = Zend_Controller_Front::getInstance()->getRequest();
      $module = $request->getModuleName();
      $controller = $request->getControllerName();
      $action = $request->getActionName();
      if (!$cacheId) {
        $cacheId = $module . "_" . $controller . "_" . $action;
      }

      if ($node || !$panel = $this->getFromCache($cacheId)) {
        // Module-and-controller-and-action-to-panel relation?
        // Module-and-controller-to-panel relation?
        // Module-to-panel relation?
        $factory = new Flex_Panel_Module_Factory();
        $select = $factory->select()
                        ->where('module IN (?)', array('', $request->getModuleName()))
                        ->where('controller IN (?)', array('', $request->getControllerName()))
                        ->where('action IN (?)', array('', $request->getActionName()))
                        ->order(array('action DESC',
                            'controller DESC',
                            'module DESC'));

        $panel_row = $factory->fetchRow($select);
        if ($panel_row) {
          $panel = $this->getPanel($panel_row->panel_id);
        }
      }
    }
    if (!$panel) {
      // Fallback to default panel
      $panel = $this->getPanel();
    }
    $this->setCache($cacheId, $panel);
    return $panel;
  }

  /**
   * Fetch a given panel
   * @param int $id
   * @return Flex_Panel
   */
  function getPanel($id = 0) {
    if ($id == 0) {
      // Fetch from configuration
      $id = Zoo::getConfig('Flex', 'panel')->panel->default;
    }
    return $this->find($id)->current();
  }

  /**
   * Check if the panel is cached
   * @param string $cacheId
   * @return boolean 
   */
  function getFromCache($cacheId) {
    try {
      $content = Zoo::getService("cache")->load($cacheid);
    }
    catch (Zoo_Exception_Service $e) {
      $content = FALSE;
    }
    return $content;
  }

  /**
   * Cache panel
   * @param string $cacheId
   * @param Flex_Panel $panel
   */
  function setCache($cacheId, $panel) {
    try {
      Zoo::getService('cache')->save( $panel,
                                      $cacheid,
                                      array('panel_page',
                                            'panel_' . $panel->id),
                                      NULL);
    }
    catch (Zoo_Exception_Service $e) {
      // Cache service not available, do nothing
    }
  }

}
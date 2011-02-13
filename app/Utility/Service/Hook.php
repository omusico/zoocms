<?php

/**
 * @package Utility
 * @subpackage Service
 */

/**
 * Utility_Service_Hook
 *
 * @package    Utility
 * @subpackage Service
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Utility_Service_Hook extends Zoo_Service {

  /**
   * trigger a hook event
   * Additional parameters other than $type and $action will be sent to the action method
   *
   * @param string $type event type (e.g. "Node")
   * @param string $action performed action (e.g. "Display")
   * @param mixed  $target additional parameters for hook action
   */
  public function trigger($type, $action, &$target = null) {
    try {
      $hooks = $this->getHooks($type, $action);
    } catch (Exception $e) {
      /**
       * @todo If dev-mode, report error
       */
      return;
    }
    if (count($hooks) > 0) {
      $method = strtolower($type) . $action;
      foreach ($hooks as $hook) {
        try {
          $arguments = array();
          $arguments = array_slice(func_get_args(), 3);
          $hook->$method($target, $arguments);
        } catch (Exception $e) {
          /**
           * Log failed hook call
           */
          echo $e->getMessage() . $e->getTraceAsString();
        }
      }
    }
    return $target;
  }

  /**
   * Get hook action objects for a given event
   *
   * @param string $type
   * @param string $action
   * @return array
   * @throws Zend_Db_Exception if trouble with database or tables
   */
  protected function getHooks($type, $action) {
    $cacheId = "flex_hooks_{$type}_{$action}";
    $ret = array();
    try {
      $ret = Zoo::getService("cache")->load($cacheId);
      if ($ret == array('none')) {
        return array();
      }
    }
    catch (Zoo_Exception_Service $e) {
      // No cache service
    }
    if (!$ret) {
      $factory = new Utility_Hook_Factory();
      $hooks = $factory->fetchAll(
                      array('type = ?' => $type,
                          'action = ?' => $action),
                          'weight ASC'
      );
      if ($hooks->count() > 0) {
        foreach ($hooks as $hook) {
          $class = $hook->class . "_Hook_" . $type;
          $ret[] = new $class();
        }
      }
      if (!$ret) {
        $ret = array('none');
      }
      try {
        Zoo::getService("cache")->save($ret,
                                      $cacheId,
                                      array('hooks'));
      } catch (Zoo_Exception_Service $e) {
        // No cache service
      }
    }
    if ($ret == array('none')) {
      return array();
    }
    return $ret;
  }

}
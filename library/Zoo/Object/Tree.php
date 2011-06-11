<?php

/**
 * @package Zoo
 * @subpackage Object
 */

/**
 * A tree structures with objects as nodes
 *
 * @package     Zoo
 * @subpackage	Object
 *
 * @copyright   Copyright (c) 2008 ZooCMS
 * @version     1.0
 * @credits     XOOPS (xoops.org)
 */
class Zoo_Object_Tree {
  /*   * #@+
   * @access	private
   */

  private $_parentId;
  private $_myId;
  private $_rootId = null;
  private $_tree = array();
  private $_objects;
  /*   * #@- */

  /**
   * Constructor
   *
   * @param   array      $objectArr  Array of objects
   * @param   string     $myId       attribute name of object ID
   * @param   string     $parentId   attribute name of parent object ID
   * @param   string     $rootId     attribute name of root object ID
   * */
  function __construct($objects, $myId, $parentId, $rootId = null) {
    $this->_objects = $objects;
    $this->_myId = $myId;
    $this->_parentId = $parentId;
    if (isset($rootId)) {
      $this->_rootId = $rootId;
    }
    $this->_initialize();
  }

  /**
   * Initialize the object
   *
   * @access	private
   * */
  function _initialize() {
    foreach ($this->_objects as $object) {
      $key1 = $object->{$this->_myId};
      $this->_tree[$key1]['obj'] = $object;
      $key2 = $object->{$this->_parentId};
      $this->_tree[$key1]['parent'] = $key2;
      $this->_tree[$key2]['child'][] = $key1;
      if (isset($this->_rootId)) {
        $this->_tree[$key1]['root'] = $object->{$this->_rootId};
      }
    }
  }

  /**
   * Get the tree
   *
   * @return  array   Associative array comprising the tree
   * */
  function &getTree() {
    return $this->_tree;
  }

  /**
   * returns an object from the tree specified by its id
   *
   * @param   string  $key    ID of the object to retrieve
   * @return  object  Object within the tree
   * */
  function &getByKey($key) {
    return $this->_tree[$key]['obj'];
  }

  /**
   * returns an array of all the first child object of an object specified by its id
   *
   * @param   string  $key    ID of the parent object
   * @return  array   Array of children of the parent
   * */
  function getFirstChild($key) {
    $ret = array();
    if (isset($this->_tree[$key]['child'])) {
      foreach ($this->_tree[$key]['child'] as $childkey) {
        $ret[$childkey] = & $this->_tree[$childkey]['obj'];
      }
    }
    return $ret;
  }

  /**
   * returns an array of all child objects of an object specified by its id
   *
   * @param   string     $key    ID of the parent
   * @param   array   $ret    (Empty when called from client) Array of children from previous recursions.
   * @return  array   Array of child nodes.
   * */
  function getAllChild($key, $ret = array()) {
    if (isset($this->_tree[$key]['child'])) {
      foreach ($this->_tree[$key]['child'] as $childkey) {
        $ret[$childkey] = & $this->_tree[$childkey]['obj'];
        $children = & $this->getAllChild($childkey, $ret);
        foreach (array_keys($children) as $newkey) {
          $ret[$newkey] = & $children[$newkey];
        }
      }
    }
    return $ret;
  }

  /**
   * returns an array of all parent objects.
   * the key of returned array represents how many levels up from the specified object
   *
   * @param   string     $key    ID of the child object
   * @param   array   $ret    (empty when called from outside) Result from previous recursions
   * @param   int $uplevel (empty when called from outside) level of recursion
   * @return  array   Array of parent nodes.
   * */
  function getAllParent($key, $ret = array(), $uplevel = 1) {
    if (isset($this->_tree[$key]['parent']) && isset($this->_tree[$this->_tree[$key]['parent']]['obj'])) {
      $ret[$uplevel] = & $this->_tree[$this->_tree[$key]['parent']]['obj'];
      $parents = & $this->getAllParent($this->_tree[$key]['parent'], $ret, $uplevel + 1);
      foreach (array_keys($parents) as $newkey) {
        $ret[$newkey] = & $parents[$newkey];
      }
    }
    return $ret;
  }

  /**
   * Get an array of all objects with child objects indented with a prefix
   *
   * @param   string  $fieldName   Name of the member variable from the
   *  node objects that should be used as the title for the options.
   * @param   int $key         ID of the object to display as the root of select options
   * @param   string  $prefix_orig  String to indent items at deeper levels
   * @param   int     $exclude     ID of object that should NOT be included in the result
   * @param   string  $ret         (reference to a string when called from outside) Result from previous recursions
   * @param   string  $prefix_curr  String to indent the current item
   * @return array
   *
   * @access	private
   * */
  function getIndentedArray($fieldName, $key, $prefix_orig, $exclude = 0, &$ret = array(), $prefix_curr = '') {
    if ($key > 0 && $key != $exclude) {
      $prefix_curr .= $prefix_orig;
      $value = $this->_tree[$key]['obj']->{$this->_myId};
      $ret[$value] = $prefix_curr . ' ' . $this->_tree[$key]['obj']->$fieldName;
    }
    if ($exclude == 0 || $key !== $exclude) {
      if (isset($this->_tree[$key]['child']) && !empty($this->_tree[$key]['child'])) {
        foreach ($this->_tree[$key]['child'] as $childkey) {
          $this->getIndentedArray($fieldName, $childkey, $prefix_orig, $exclude, $ret, $prefix_curr);
        }
      }
    }
    return $ret;
  }

  function toArray($key = 0, &$ret = array()) {
    $children = $this->getFirstChild($key);
    if ($this->getByKey($key)) {
      $ret [$key] ['element'] = $this->getByKey($key);
    }
    if ($children) {
      foreach ($children as $child) {
        $this->toArray($child->{$this->_myId}, $ret[$key]['children']);
      }
    }
    return $ret;
  }
}

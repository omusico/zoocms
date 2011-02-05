<?php
/**
 * Connector_Service_Link
 * @package Connector
 * @subpackage Service
 */

/**
 * @package    Connector
 * @subpackage Service
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Connector_Service_Link extends Zoo_Service
{
	protected $factory = null;
	/**
     *
     * @staticvar Content_Node_Factory $factory
     * @return Content_Node_Factory
     */
    public function getFactory() {
        if (!$this->factory) {
        	$this->factory = new Connector_Link_Factory();
        }
        return $this->factory;
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
     * Add elements to a form for adding a link to a content node
     *
     * @param Zend_Form $form
     * @param string $label translated text
     * @param string $type link type
     */
    function addLinkFormElement(Zend_Form $form, $label, $type = "link") {
        // Build form element for linking, complete with AJAX code for lookup
        $element = new Zend_Form_Element_Text('connector_link_'.$type.'_txt');
        $element->setLabel($label);

        $hidden = new Zend_Form_Element_Hidden('connector_link_'.$type);

        $form->addElement($element, 'connector_link_'.$type.'_txt');
        $form->addElement($hidden, 'connector_link_'.$type);
    }

    /**
     * Count how many links one or more nodes have of a given type
     *
     * @param array|int $nids
     * @param string $type
     *
     * @return array
     */
    function countLinksByNode($nids, $type = 'link') {
        $ret = array();
        $nids = (array) $nids;

        $factory = $this->getFactory();
        $select = $factory->select()->from($factory, array('nid', 'COUNT(*) as count'))
                            ->where('type = ?', $type)
                            ->where('nid IN (?)', $nids)
                            ->group('nid');
        $ret = $factory->fetchAll($select);

        return $ret;
    }

    /**
     * Make a connection from one node to another
     * @param int $from
     * @param int $to
     * @param string $type
     * @return bool
     */
    function connect($from, $to, $type = 'link') {
    	$item = $this->find($from, $to, $type);
    	if ($item->current()) {
    		return false;
    	}
    	
    	$weight = $this->getNextWeight($from, $type);

        $gnode = $this->createRow();
        $gnode->nid = $from;
        $gnode->tonid = $to;
        $gnode->type = $type;
        $gnode->weight = $weight;
        return $gnode->save();
    }
    
    /**
     * Remove a connection between a node and another
     * 
     * @param int $from
     * @param int $to
     * @param int $type
     * @return bool
     */
    function remove($from, $to, $type = 'link') {
    	$factory = $this->getFactory();
    	$where = array();
    	$where[] = $factory->getAdapter()->quoteInto('type = ?', $type);
    	$where[] = $factory->getAdapter()->quoteInto('nid = ?', $from);
    	if ($to) {
    	    $where[] = $factory->getAdapter()->quoteInto('tonid = ?', $to);
    	}
    	
    	return $factory->delete($where);
    }
    
    /**
     * Update a linked node's position relative to another linked node
     * @param string $type
     * @param Zoo_Content_Interface $node
     * @param int $movedId
     * @param int $targetId
     * @param int $position
     * @return bool
     */
    function update($type = 'link', $node, $movedId, $targetId, $position) {
    	$factory = $this->getFactory();
		$moved = $factory->fetchAll($factory->select()
        									->where('type = ?', $type)
											->where('nid = ?', $node->id)
        									->where('tonid = ?', $movedId))->current();
		
        $target = $factory->fetchAll($factory->select()
        									->where('type = ?', $type)
        									->where('nid = ?', $node->id)
        									->where('tonid = ?', $targetId))->current();
        									
        $direction = $target->weight - $moved->weight;
        $oldweight = $moved->weight;
        if ($direction > 0) {
			// Item moved forwards
			$newweight = $position < 0 ? $target->weight - 1 : $target->weight;
			// Decrease weight for all items in between item's original weight and new weight
			$set = "weight = weight-1 WHERE type = " . $factory->getAdapter ()->quote ( $type ) . " 
        								AND nid = " . $node->id . " 
        								AND weight > " . $moved->weight . " 
        								AND weight <= " . $newweight ;
		}
		else {
			// Item moved backwards
			switch ($position) {
				case -1:
				case 3:
					$newweight = $target->weight;
					break;
					
				case 1:
				case 2:
					$newweight = $target->weight + 1;
					break;
			}
			// Increase weight for all items in between new weight and item's original weight
			$set = "weight = weight+1 WHERE type = " . $factory->getAdapter ()->quote ( $type ) . " 
        									AND nid = " . $node->id . " 
        									AND weight >= " . $newweight . " 
        									AND weight < " . $moved->weight;
		}
		if ($newweight == $oldweight) {
			// No change
			return true;
		}
        Zoo::getService('db')->getDb('master')->query("UPDATE ".$factory->info(Zend_Db_Table_Abstract::NAME)." SET ".$set);
        $moved->weight = $newweight;
        return $moved->save();
    }
}
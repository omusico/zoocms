<?php
/**
 * Content linking DAO
 * @package    Connector
 * @subpackage Link
 */

/**
 * @package    Connector
 * @subpackage Link
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Connector_Link_Factory extends Zoo_Db_Table {
	/**
	 * Get nodes linked to a node
	 * @param Zoo_Content_Interface $node
	 * @param string $type
	 * @param string $order
	 * @param int $limit
	 * @return array
	 */
	function getLinkedNodes($node, $type = 'link', $order = "weight", $limit = 0) {
		try {
            $content_factory = Zoo::getService('content')->getFactory();
            $select = $content_factory->select()->from(array('c' => $content_factory->info(self::NAME)));
            $select->join(array('cl' => $this->info(self::NAME)), 'cl.tonid = c.id', array());
            $select->where('cl.nid = ?', $node->id);
            $select->where('cl.type = ?', $type);
            $select->order($order);
            if ($limit > 0) {
            	$select->limit($limit);
            }
            $nodes = $content_factory->fetchAll($select);
            $ret = array();
            foreach ($nodes as $node) {
                $ret[] = Zoo::getService('content')->loadFromNode($node, 'List');
            }
            return $ret;
        }
        catch (Exception $e) {
            // Return empty array
            /**
             * @todo Log to error service - must be problem with database connection/table
             */
            return array();
        }
	}
	
	/**
	 * Get the next item - if any - of a given type
	 * @param Zoo_Content_Interface $item
	 * @param string $type
	 * @return Zoo_Content_Interface|false
	 */
	public function getNext($nid, $tonid, $type = "link") {
	    $select = $this->select();
    	$select->where('nid = ?', $nid);
    	$select->where('tonid = ?', $tonid);
    	$select->where('type = ?', $type);
		$row = $this->fetchRow($select);
		
	    $select = $this->select();
	    $select->where('nid = ?', $nid);
    	$select->where('type = ?', $type);
    	$select->where('weight > ?', $row->weight);
    	$select->order('weight ASC');
    	$row = $this->fetchRow($select);
    	if ($row) {
    	    return Zoo::getService('content')->load($row->tonid, 'List');
    	}
    	return false;
	}
	
	/**
	 * Get the previous item - if any - of a given type
	 * @param Zoo_Content_Interface $item
	 * @param string $type
	 * @return Zoo_Content_Interface|false
	 */
	public function getPrevious($nid, $tonid, $type = "link") {
	    $select = $this->select();
	    $select->where('nid = ?', $nid);
    	$select->where('tonid = ?', $tonid);
    	$select->where('type = ?', $type);
		$row = $this->fetchRow($select);

		$select = $this->select();
		$select->where('nid = ?', $nid);
    	$select->where('type = ?', $type);
    	$select->where('weight < ?', $row->weight);
    	$select->order('weight DESC');
    	$row = $this->fetchRow($select);
    	if ($row) {
    	    return Zoo::getService('content')->load($row->tonid, 'List');
    	}
    	return false;
	}
	
/**
     * Get next weight for a linked node
     * @note NOT concurrency protected
     * 
     * @param int $itemId
     * @param string $type 
     * @return int
     */
    public function getNextWeight($itemId, $type = 'link') {
    	$select = $this->select();
    	$select->from($this,array('MAX(weight) as weight'));
    	$select->where('nid = ?', $itemId);
    	$select->where('type = ?', $type);
    	$select->group('nid');
		$row = $this->fetchRow($select);
		return $row ? $row->weight+1 : 1;
    }
}
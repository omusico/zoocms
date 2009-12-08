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
	 * @return Zend_Db_Table_Rowset
	 */
	function getLinkedNodes($node, $type = 'link', $order = "weight", $limit = 0) {
		try {
            $content_factory = Zoo::getService('content')->getFactory();
            $select = $content_factory->select()->from(array('c' => $content_factory->info(Zend_Db_Table_Abstract::NAME)));
            $select->join(array('cl' => $this->info(Zend_Db_Table_Abstract::NAME)), 'cl.tonid = c.id', array());
            $select->where('cl.nid = ?', $node->id);
            $select->where('cl.type = ?', $type);
            $select->order($order);
            if ($limit > 0) {
            	$select->limit($limit);
            }
            $nodes = $content_factory->fetchAll($select);
            // Call hooks for items
            /*
             * @todo this shouldn't be here, I think... but there is currently not a "Get THESE content items" on the
             * content service
             */
            try {
                Zoo::getService("hook")->trigger("Node", "List", $nodes);
            }
            catch (Zoo_Exception_Service $e) {
                // Hook service not available - log? Better not, some people may live happily without a hook service
            }
            return $nodes;
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
     * Get next weight for a linked node
     * @note NOT concurrency protected
     * 
     * @param int $itemId
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
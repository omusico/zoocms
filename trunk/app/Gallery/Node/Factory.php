<?php
/**
 * @package Gallery
 * @subpackage Node
 */

/**
 * @package Gallery
 * @subpackage Node
 */
class Gallery_Node_Factory_deprecated extends Zoo_Db_Table {
    /**
     * Retrieve images for a given gallery
     *
     * @param Zoo_Content_Interface $item
     * @return Zend_Db_Table_Rowset
     */
    public function getGalleryImages(Zoo_Content_Interface $item) {
        try {
            $content_factory = Zoo::getService('content')->getFactory();
            $select = $content_factory->select()->from(array('c' => $content_factory->info(Zend_Db_Table_Abstract::NAME)));
            $select->join(array('gn' => $this->info(Zend_Db_Table_Abstract::NAME)), 'gn.fid = c.id', array());
            $select->where('gn.nid = ?', $item->id);
            $select->order('gn.weight');
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
            return array();
        }
    }
    
    /**
     * Get subgalleries to selected item
     * 
     * @param Zoo_Content_Interface $item
     * @return array
     */
    public function getSubGalleries($item) {
    	return Zoo::getService('content')->getContent(array('nodetype' => 'gallery_node', 'parent' => $item->id, 'render' => true), 0, 0);
    }
    
    /**
     * Get next weight for a gallery image
     * @note NOT concurrency protected
     * 
     * @param int $itemId
     * @return int
     */
    public function getNextWeight($itemId) {
    	$select = $this->select();
    	$select = $select->from($this,array('MAX(weight) as weight'));
    	$select = $select->where('nid = ?', $itemId);
    	$select = $select->group('nid');
		$row = $this->fetchRow($select);
		return $row->weight+1;
    }
}
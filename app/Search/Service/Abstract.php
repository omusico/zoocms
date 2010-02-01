<?php
/**
 * @package    Search
 * @subpackage Service
 */

/**
 * Search_Service_Abstract
 *
 * @package    Search
 * @subpackage Service
 * @copyright  Copyright (c) 2009 ZooCMS
 * @version    1.0
 */
abstract class Search_Service_Abstract extends Zoo_Service {
    /**
     * The search index
     *
     * @var Zend_Search_*
     */
    protected $index;

    /**
     * Add/Update $items in the search index
     *
     * @param Zend_Db_Table_Rowset|Content_Node_Interface $items Rowset of Zoo_Content_Interface objects
     */
    function index($items) {
        set_time_limit(3600);
        ini_set('memory_limit', '512M');

        if ($items instanceof Zend_Db_Table_Rowset ) {
            $docs = array();
            foreach ($items as $item) {
                $docs[] = $this->_build($item);
            }
            $this->index->addDocuments($docs);
        }
        $this->index->commit();
        return $this;
    }

    /**
     * Optimize the index
     *
     * @return Search_Service_Lucene
     */
    function optimize() {
        ini_set('memory_limit', '512M');
        $this->index->optimize();
        return $this;
    }
}
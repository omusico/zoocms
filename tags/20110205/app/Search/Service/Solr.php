<?php
/**
 * @package    Search
 * @subpackage Service
 */

/**
 * Search_Service_Solr
 *
 * @package    Search
 * @subpackage Service
 * @copyright  Copyright (c) 2009 ZooCMS
 * @version    1.0
 */
class Search_Service_Solr extends Search_Service_Abstract {
    /**
     * Get an instance of the service
     *
     * @param Zend_Config $config
     * @return Search_Service_Abstract
     */
    public function &getService(Zend_Config $config) {
        if (!$this->index) {
            $this->index = new Apache_Solr_Service($config->host, $config->port, $config->path);
            if (!$this->index->ping()) {
                throw new Zoo_Exception_Service("Solr server unavailable");
            }
        }
        return $this;
    }

    /**
     * Build Apache_Solr_Document from Zoo_Content_Interface object
     *
     * @param Zoo_Content_Interface $item
     * @return Apache_Solr_Document
     */
    protected function _build(Zoo_Content_Interface $item) {
        // (Re-)Index document
        $document = new Apache_Solr_Document();
  		$document->nid = $item->id;
  		$document->title = $item->title;
  		list($content) = Zoo::getService('content')->getRenderedContent($item->id, 'Display');
  		$document->contents = strip_tags($content);
  		$document->link = $item->url();
  		$document->type = $item->type;
  		$document->uid = $item->uid;

  		// Index wants a W3C canonical timestamp in GMT _WITH_ Z at the end
  		$date = new Zend_Date($item->published, Zend_Date::TIMESTAMP);
  		$date->setTimezone('gmt');
  		$document->published = str_replace("+00:00", "Z", $date->get(Zend_Date::W3C));

  		return $document;
    }

    /**
     * Search the index for $term
     *
     * @param string $term search string
     * @param int $limit max number of returned items
     * @param int $offset offset to start search from
     * @param array $params additional parameters for search
     *
     * @return array
     */
    function search($term, $limit = 10, $offset = 0, $params = array()) {
        $result = $this->index->search($term, $offset, $limit, $params);

        $hitcount = $result->response->numFound;
        $docs = $result->response->docs;


        return array('count' => $hitcount, 'results' => $docs);
    }
}
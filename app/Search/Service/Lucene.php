<?php
/**
 * @package    Search
 * @subpackage Service
 */

/**
 * Search_Service_Lucene
 *
 * @package    Search
 * @subpackage Service
 * @copyright  Copyright (c) 2009 ZooCMS
 * @version    1.0
 */
class Search_Service_Lucene extends Search_Service_Abstract {
    /**
     * Get an instance of the service
     *
     * @param Zend_Config $config
     * @return Search_Service_Lucene
     */
    public function &getService(Zend_Config $config) {
        if (!$this->index) {
            //create the index object
            try {
                $this->index = new Zend_Search_Lucene(ZfApplication::$_data_path.$config->path);
            }
            catch (Zend_Search_Lucene_Exception $e) {
                // Index doesn't exist
                $this->index = new Zend_Search_Lucene(ZfApplication::$_data_path.$config->path, true);
            }

            $this->index->setMaxBufferedDocs($config->bufferedDocs);
            $this->index->setMergeFactor($config->mergeFactor);
        }
        return $this;
    }

    /**
     * Add node to index
     *
     * @param Zoo_Content_Interface $item
     */
    protected function _build(Zoo_Content_Interface $item) {
        // Delete existing document, if exists
        $hits = $this->index->find('nid:' . $item->id);
        foreach ($hits as $hit) {
            $this->index->delete($hit->id);
        }

        // (Re-)Index document
        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::text('nid', $item->id));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('link', $item->url()));
        $doc->addField(Zend_Search_Lucene_Field::unStored('title', $item->title));
        $doc->addField(Zend_Search_Lucene_Field::unStored('type', $item->type));
        $doc->addField(Zend_Search_Lucene_Field::unStored('published', $item->published));
        $doc->addField(Zend_Search_Lucene_Field::unStored('uid', $item->uid));
        list($content) = Zoo::getService('content')->getRenderedContent($item->id, 'Display');
        $doc->addField(Zend_Search_Lucene_Field::unStored('contents', strip_tags($content)));

        return $doc;
    }

    /**
     * Search the index for $term
     *
     * @todo Investigate offset - doesn't seem to be implemented in Zend_Search_Lucene
     *
     * @param string $term
     * @param int $limit max number of returned items
     * @param int $offset offset to start search from
     * @param array $params additional parameters for search
     *
     * @return array
     */
    function search($term, $limit = 10, $offset = 0, $params = array()) {
        $this->index->setResultSetLimit($limit);
        $hits = $this->index->find(strtolower($term));

        $count = $this->getTotalResults($term);

        return array('count' => $count, 'results' => $hits);
    }

    /**
     * Get number of matched results in search
     *
     * Unfortunately this duplicates the search, since Zend_Search_Lucene does not return the match count with the result
     *
     * @param string $query
     * @return int
     */
    private function getTotalResults($query) {
        if (is_string($query)) {
            $query = Zend_Search_Lucene_Search_QueryParser::parse($query);
        }

        if (!$query instanceof Zend_Search_Lucene_Search_Query) {
            throw new Zend_Search_Lucene_Exception('Query must be a string or Zend_Search_Lucene_Search_Query object');
        }

        $this->index->commit();
        $query = $query->rewrite($this->index)->optimize($this->index);
        $query->execute($this->index);

        return count($query->matchedDocs());
    }
}
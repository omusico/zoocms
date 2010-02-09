<?php
/**
 * @package Search
 * @subpackage Controllers
 *
 */
/**
 * @package Search
 * @subpackage Controllers
 *
 */

class Search_SearchController extends Zoo_Controller_Action
{
    /**
     * Perfrom search
     *
     */
    function indexAction() {
        $res = Zoo::getService('search')->search($this->_request->getParam('term'), 20);
        $hits = $items = array();
        foreach ($res['results'] as $hit) {
            $hits[] = $hit->nid;
        }
        if (count($hits) > 0) {
            try {
                $items = Zoo::getService('content')->getRenderedContent(array_unique($hits));
            }
            catch (Zoo_Service_Exception $e) {
                $items = array();
            }
        }
        $this->view->assign('items', $items);
        $this->view->assign('hitcount', $res['count']);
    }
}
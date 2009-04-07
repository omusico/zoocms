<?php
/**
 * @package Estate
 * @subpackage Controllers
 *
 */

/**
 * @package Estate
 * @subpackage Controllers
 *
 */
class Estate_IndexController extends Zoo_Controller_Action
{
    /**
     * Display list of estate nodes
     *
     */
    public function indexAction()
    {
        $method = __METHOD__;
        $cacheid = str_replace("::", "_", $method);

        $content = $this->checkCache($cacheid);
        if (!$content) {
            $items = Zoo::getService('content')->getContent(array('active' => true,
                                                                  'nodetype' => 'estate_node',
                                                                  'viewtype' => 'list',
                                                                  'render' => true));
            $this->view->assign('items', $items);
            $content = $this->getContent();
            $this->cache($content, $cacheid, array('nodelist', 'node_estate_node'), 60); //60 Seconds set - should be dynamic? Should it invalidate, whenever a node is saved?
        }
        $this->renderContent($content);
    }
}

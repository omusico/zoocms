<?php
/**
 * @package  Filemanager
 * @subpackage Controllers
 *
 */

/**
 * @package  Filemanager
 * @subpackage Controllers
 *
 */
class Filemanager_IndexController extends Zoo_Controller_Action
{
    /**
     * Display list of file nodes
     *
     */
    public function indexAction()
    {
        $method = __METHOD__;
        $cacheid = str_replace("::", "_", $method);

        $content = $this->checkCache($cacheid);
        if (!$content) {
            $items = Zoo::getService('content')->getContent(array('active' => true,
                                                                  'nodetype' => 'filemanager_file',
                                                                  'render' => false,
            													  'order' => "published DESC",
            													  'limit' => 20));
            $this->view->assign('items', $items);
            $content = $this->getContent();
            $this->cache($content, $cacheid, array('nodelist', 'node_filemanager_file'), 60); //60 Seconds set - should be dynamic? Should it invalidate, whenever a node is saved?
        }
        $this->renderContent($content);
    }

}

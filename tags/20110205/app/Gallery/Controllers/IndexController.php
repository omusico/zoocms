<?php
/**
 * @package Gallery
 * @subpackage Controllers
 */

/**
 * Gallery front page
 *
 * @package Gallery
 * @subpackage Controllers
 * @copyright  Copyright (c) 2009 ZooCMS
 * @version    1.0
 * @author ZooCMS
 */
class Gallery_IndexController extends Zoo_Controller_Action {
    /**
     * Display gallery front page
     *
     */
    public function indexAction()
    {
        $method = __METHOD__;
        $cacheid = str_replace("::", "_", $method);

        $content = $this->checkCache($cacheid);
        if (!$content) {
            $items = Zoo::getService('content')->getContent(array('active' => true,
                                                                  'nodetype' => 'gallery_node',
                                                                  'render' => false));
            $this->view->assign('items', $items);
            $content = $this->getContent();
            $this->cache($content, $cacheid, array('nodelist', 'node_gallery_node'), 60); //60 Seconds set - should be dynamic? Should it invalidate, whenever a node is saved?
        }
        $this->renderContent($content);
    }
}
<?php
/**
 * @package Content
 * @subpackage Controllers
 *
 */

/**
 * @package Content
 * @subpackage Controllers
 *
 */
class Content_IndexController extends Zoo_Controller_Action
{
    /**
     * Display list of content nodes
     *
     */
    public function indexAction()
    {
        $method = __METHOD__;
        $cacheid = str_replace("::", "_", $method);

        $content = $this->checkCache($cacheid);
        if (!$content) {
            $items = Zoo::getService('content')->getContent(array('active' => true,
                                                                  'group' => 'content',
                                                                  'render' => true));
            $this->view->assign('items', $items);


            $content = $this->getContent();
            $this->cache($content, $cacheid, array('nodelist'), 60); //60 Seconds set - should it be dynamic?
        }
        $this->renderContent($content);
    }
}

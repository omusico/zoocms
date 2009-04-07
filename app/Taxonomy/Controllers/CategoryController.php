<?php
/**
 * @package Taxonomy
 * @subpackage Controllers
 *
 */

/**
 * @package Taxonomy
 * @subpackage Controllers
 *
 */
class Taxonomy_CategoryController extends Zoo_Controller_Action
{
    /**
     * Display content - not comments and categories
     *
     */
    public function indexAction()
    {
        $method = __METHOD__;
        $cacheid = str_replace("::", "_", $method).intval($this->getRequest()->getParam('id'));;

        $content = $this->checkCache($cacheid);
        if (!$content) {
            $found = Zoo::getService('content')->find($this->_request->getParam('id'));
            if ($found->count() == 0) {
                throw new Zend_Controller_Action_Exception(Zoo::_("Category does not exist"), 404);
            }
            $category = $found->current();
            $items = Zoo::getService('content')->getContent(array('active' => true,
                                                                  'group' => 'content',
                                                                  'parent' => $category->id,
                                                                  'render' => true));
            $this->view->assign('items', $items);
            $this->view->assign('category', $category);


            $content = $this->getContent();
            $this->cache($content, $cacheid, array('nodelist'), 60); //60 Seconds set - should be dynamic? Should it invalidate, whenever any node is saved?
        }
        $this->renderContent($content);
    }
}

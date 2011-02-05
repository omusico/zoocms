<?php
/**
 * @package User
 * @subpackage Controllers
 */

/**
 * User profile viewer
 *
 * @package User
 * @subpackage Controllers
 * @copyright  Copyright (c) 2009 ZooCMS
 * @version    1.0
 * @author ZooCMS
 */
class User_ProfileController extends Zoo_Controller_Action {
    function indexAction() {
        $method = __METHOD__;
        $cacheid = str_replace("::", "_", $method);

        $content = $this->checkCache($cacheid);
        if (!$content) {
            $id = $this->_request->getParam('id');
            $profile = Zoo::getService('profile')->getProfile($id);
            $content = Zoo::getService('content')->getContent(array('active' => true,
                                                                  'group' => 'content',
                                                                  'render' => true,
                                                                  'author' => $id));
            $comments = Zoo::getService('content')->getContent(array('active' => true,
                                                                  'group' => 'comment',
                                                                  'render' => true,
                                                                  'author' => $id));
            $this->view->assign('profile', $profile);
            $this->view->assign('content', $content);
            $this->view->assign('comments', $comments);

            $content = $this->getContent();
            $this->cache($content, $cacheid, array(), 60);
            //60 Seconds set - should it be dynamic? Invalidate, when user creates new content?
        }
        $this->renderContent($content);
    }
}
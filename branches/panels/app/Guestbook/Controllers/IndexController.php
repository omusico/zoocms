<?php
/**
 * @package Guestbook
 * @subpackage Controllers
 *
 */

/**
 * IndexController
 * 
 * @package Guestbook
 * @subpackage Controllers
 *
 */

class Guestbook_IndexController extends Zoo_Controller_Action {
	/**
	 * The default action - show the guestbook entries
	 */
	public function indexAction() {
		$method = __METHOD__;
        $cacheid = str_replace("::", "_", $method).intval($this->getRequest()->getParam('page', 1));
        
        $can_edit = false;
        if (Zoo::getService('acl')->checkAccess('edit')) {
        	$cacheid .= "_edit";
        	$can_edit = true;
        }

        $content = $this->checkCache($cacheid);
        if (!$content) {
        	$limit = 20;
        	// Offset = items per page multiplied by the page number minus 1
			$offset = ($this->getRequest()->getParam('page', 1) - 1) * $limit;
        	$options = array('active' => true,
        					'nodetype' => 'guestbook_entry',
        					'order' => 'created DESC',
        					'render' => true);
            $select = Zoo::getService('content')->getContentSelect($options, $offset, $limit);
            
            $this->view->items = Zoo::getService('content')->getContent($options, $offset, $limit);
		
			// Pagination
			Zend_Paginator::setDefaultScrollingStyle('Elastic');
			Zend_View_Helper_PaginationControl::setDefaultViewPartial(array('pagination_control.phtml', 'zoo'));
			
			$adapter = new Zend_Paginator_Adapter_DbSelect ( $select );
			$paginator = new Zend_Paginator ( $adapter );
			$paginator->setItemCountPerPage($limit);
			$paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
			$paginator->setView($this->view);
			$this->view->assign('paginator', $paginator);
			
			$this->view->can_edit = $can_edit;

            $content = $this->getContent();
            $this->cache($content, $cacheid, array('nodelist', 'guestbook_list'));
        }
        $this->view->pagetitle = Zoo::_('Guestbook');
        $this->renderContent($content);
	}
}

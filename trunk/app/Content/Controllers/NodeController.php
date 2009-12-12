<?php
/**
 * @package Content
 * @subpackage Controllers
 */

/**
 * @package Content
 * @subpackage Controllers
 */
class Content_NodeController extends Zoo_Controller_Action
{
	public function init() {
		$ajaxContext = $this->_helper->getHelper ( 'AjaxContext' );
		$ajaxContext->addActionContext ( 'edit', 'html' )
					->initContext ();
	}
    /**
     * Display a node
     *
     */
    function indexAction() {
        $id = intval($this->getRequest()->getParam('id'));
        /**
         * @todo more generic sanitation than intval??
         */
        Zend_Registry::set('content_id', $id);

        $found = Zoo::getService('content')->find($id);
        if ($found->count() == 0) {
            throw new Zend_Controller_Action_Exception(Zoo::_("Content not found"), 404);
        }
        $item = $found->current();

        $can_edit = false;
        try {
	        if (!(Zoo::getService('acl')->checkItemAccess($item))) {
	            throw new Exception(Zoo::_("Access denied - insufficient privileges"), 403);
	        }
	        $can_edit = Zoo::getService('acl')->checkItemAccess($item, 'edit');
        }
        catch (Zoo_Exception_Service $e) {
        	// No acl service installed
        }
        
        $cacheid = "Content_nodeDisplay_".$id.($can_edit ? "_edit" : "");
        $content = $this->checkCache($cacheid);
        if (!$content) {
            try {
                Zoo::getService("hook")->trigger("Node", "Display", $item);
            }
            catch (Zoo_Exception_Service $e) {
                // Hook service not available - log? Better not, some people may live happily without a hook service
            }

            $this->view->assign('can_edit', $can_edit);
            $this->view->assign('item', $item);

            // Emulate
            $module = substr($item->type, 0, strpos($item->type, "_"));
            $this->emulateModule($module);

            $content = $this->getContent();
            $this->cache($content, $cacheid, array('node', 'node_'.$item->type, 'node_'.$item->id));
        }
        
        $this->view->assign('pagetitle', $item->title);
        $this->renderContent($content);
    }

    /**
     * Add a node
     *
     */
    public function addAction()
    {
        $item = Zoo::getService('content')->createRow();
        if ($type = $this->getRequest()->getParam("type")) {
            $item->type = $type;
        }
        else {
            $item->type = "content_node";
        }

        try {
	        if (!Zoo::getService('acl')->checkItemAccess($item, 'add')) {
	            throw new Exception(Zoo::_("Access denied - insufficient privileges"), 403);
	        }
        }
    	catch (Zoo_Exception_Service $e) {
        	// No acl service installed
        }
        $this->view->item = $item;
        $this->view->type = Zoo::getService('content')->getType($item->type);
        $this->view->form = $item->getForm($this->_helper->getHelper('url')
                                                    ->direct('save', 'node', 'Content'));
        $this->render("form");
    }

    /**
     * Edit a node
     *
     */
    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $item = Zoo::getService('content')->find($id)->current();
        if ($item) {
        	try {
	            if (!Zoo::getService('acl')->checkItemAccess($item, 'edit') && !Zoo::getService('acl')->checkItemAccess($item, 'editown')) {
	                throw new Exception(Zoo::_("Access denied - insufficient privileges"), 403);
	            }
        	}
	        catch (Zoo_Exception_Service $e) {
	        	// No acl service installed
	        }
            $this->view->item = $item;
            $this->view->type = Zoo::getService('content')->getType($item->type);
            $this->view->form = $item->getForm($this->_helper->getHelper('url')
                                                    ->direct('save', 'node', 'Content'));
			if ($this->getRequest()->isXmlHttpRequest()) {
				$this->view->form->setAttrib('onsubmit', 'submitForm(this);return false;');
			}
            $this->render("form");
        }
        else {
            $this->_forward('add');
        }
    }

    /**
     * Save a node
     *
     */
    public function saveAction()
    {
        if (@$_REQUEST['id'] > 0) {
            $item = Zoo::getService('content')->find($_REQUEST['id'])->current();
            try {
	            if (!Zoo::getService('acl')->checkItemAccess($item, 'edit') && !Zoo::getService('acl')->checkItemAccess($item, 'editown')) {
	                throw new Exception(Zoo::_("Access denied - insufficient privileges"), 403);
	            }
            }
	        catch (Zoo_Exception_Service $e) {
	        	// No acl service installed
	        }
        }
        else {
            $item = Zoo::getService('content')->createRow();
            $item->type = isset($_REQUEST['type']) && $_REQUEST['type'] != "" ? $_REQUEST['type'] : 'content_node';
            try {
	            if (!Zoo::getService('acl')->checkItemAccess($item, 'add')) {
	                throw new Exception(Zoo::_("Access denied - insufficient privileges"), 403);
	            }
            }
	        catch (Zoo_Exception_Service $e) {
	        	// No acl service installed
	        }
        }

        $form = $item->getForm($this->_helper->getHelper('url')
                                                    ->direct('save', 'node', 'Content'));

        if ($form->isValid($_REQUEST)) {
            $values = $form->getValues();
            $item->title = $values['title'];
            $item->content = $values['content'];

            /**
             * @todo Add hour and minute to publish timestamp
             * @todo Add permissions and permission check for publishtime and status modification
             */
            $content_type = Zoo::getService('content')->getType($item->type);
            if ($content_type->has_publishdate_select) {
            	$item->status = $values['status'];
            	$item->published = strtotime($values['published']);
			} else {
				$item->status = 1;
				if (! $item->id) {
					$item->published = time ();
				}
			}

            $item->save();

            /**
             * @todo Save revision
             */

            try {
                Zoo::getService("hook")->trigger("Node", "Save", $form, $item);
            }
            catch (Zoo_Exception_Service $e) {
                // Hook service not available - log? Better not, some people may live happily without a hook service
            }

            /**
             * Invalidate cache
             */
            Zoo::getService('cache')->remove(get_class($item)."_".$item->id);
            Zoo::getService('cache')->remove(get_class($item)."_".$item->id."_edit");
            Zoo::getService('cache')->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('node_'.$item->id));

            if ($item->pid > 0) {
                // Clear cache for immediate parent
                $parent = Zoo::getService('content')->find($item->pid)->current();
                Zoo::getService('cache')->remove(get_class($parent)."_".$item->pid);
                Zoo::getService('cache')->remove(get_class($parent)."_".$item->pid."_edit");
                Zoo::getService('cache')->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('node_'.$item->pid));
            }
            if ($this->getRequest()->isXmlHttpRequest()) {
            	exit;
            }
            $this->_helper->redirector->gotoRoute(array('id' => $item->id), $item->type);
        }
        $this->view->form = $form;
        $this->view->form->populate($_REQUEST);
        $this->view->type = Zoo::getService('content')->getType($item->type);
        $this->render('form');
    }

    /**
     * Loads view script paths and translations for another module
     *
     * @param string $module
     */
    function emulateModule($module) {
        $module = ucfirst($module);
        if (strcasecmp($module, "content") != 0) {
            $layout = Zend_Layout::getMvcInstance();
            // Add module paths to view scripts
            $this->view->addBasePath(ZfApplication::$_base_path."/app/$module/Views", $module."_View");
            $this->view->addScriptPath($layout->getLayoutPath()."default/templates/$module/");
            $this->view->addScriptPath($layout->getLayoutPath().$layout->getLayout()."/templates/$module/");

            // Add translation for this module
            try {
                Zoo::getService("translator")->addTranslation(
                ZfApplication::$_base_path."/app/".$module."/Language",
                null,
                array('scan' => Zend_Translate::LOCALE_FILENAME ));
            }
            catch (Zend_Translate_Exception $e) {
                // Translation doesn't exist
            }
        }
    }
}
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
    function init() {

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

        if (!$this->checkAccess($item)) {
            throw new Exception(Zoo::_("Access denied - insufficient privileges"), 403);
        }

        $can_edit = $this->checkAccess($item, 'edit');
        $cacheid = get_class($this)."_".$id.($can_edit ? "_edit" : "");
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

        if (!$this->checkAccess($item, 'add')) {
            throw new Exception(Zoo::_("Access denied - insufficient privileges"), 403);
        }
        $this->view->item = $item;
        $this->view->type = Zoo::getService('content')->getType($item->type);
        $this->view->form = $item->getForm($this->_helper->getHelper('url')
                                                    ->direct('save', 'node', 'content'));
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
            if (!$this->checkAccess($item, 'edit') && !$this->checkAccess($item, 'editown')) {
                throw new Exception(Zoo::_("Access denied - insufficient privileges"), 403);
            }
            $this->view->item = $item;
            $this->view->type = Zoo::getService('content')->getType($item->type);
            $this->view->form = $item->getForm($this->_helper->getHelper('url')
                                                    ->direct('save', 'node', 'content'));
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
            if (!$this->checkAccess($item, 'edit') && !$this->checkAccess($item, 'editown')) {
                throw new Exception(Zoo::_("Access denied - insufficient privileges"), 403);
            }
        }
        else {
            $item = Zoo::getService('content')->createRow();
            $item->type = isset($_REQUEST['type']) && $_REQUEST['type'] != "" ? $_REQUEST['type'] : 'content_node';
            if (!$this->checkAccess($item, 'add')) {
                throw new Exception(Zoo::_("Access denied - insufficient privileges"), 403);
            }
        }

        $form = $item->getForm($this->_helper->getHelper('url')
                                                    ->direct('save', 'node', 'content'));

        if ($form->isValid($_REQUEST)) {
            $values = $form->getValues();
            $item->title = $values['title'];
            $item->content = $values['content'];

            /**
             * @todo Change how status and published time is set
             */
            $item->status = 1;
            if (!$item->id) {
                $item->published = time();
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
            Zoo::getService('cache')->remove(get_class($this)."_".$item->id);
            Zoo::getService('cache')->remove(get_class($this)."_".$item->id."_edit");
            Zoo::getService('cache')->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('node_'.$item->id));

            if ($item->pid > 0) {
                // Clear cache for immediate parent
                Zoo::getService('cache')->remove(get_class($this)."_".$item->pid);
                Zoo::getService('cache')->remove(get_class($this)."_".$item->pid."_edit");
                Zoo::getService('cache')->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('node_'.$item->pid));
            }

            $this->_helper->redirector->gotoRoute(array('id' => $item->id), $item->type);
        }
        $this->view->form = $form;
        $this->view->form->populate($_REQUEST);
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

    /**
     * Check access to performing actions on content
     *
     * @param Content_Node $item
     * @param string $privilege
     * @return bool
     */
    private function checkAccess($item, $privilege = "index") {
        try {
            if ($privilege == "editown") {
                if ($item->uid != Zoo::getService('user')->getCurrentUser()->id) {
                    return false;
                }
            }
            /**
             * Should this be shortened?
             * Decision made not to, since this is more transparent to the developer
             */
            $roles = Zoo::getService('user')->getCurrentUser()->getGroups();

            $acl = Zoo::getService('acl');
            foreach ($roles as $role) {
                if ($acl->isAllowed($role, 'content.node', $privilege) ||
                    $acl->isAllowed($role, 'content.node', $privilege.'.'.$item->type)) {
                    return true;
                }
            }
        }
        catch (Zoo_Exception_Service $e) {
            // No acl service installed... allow or disallow?
            // Allow chosen because if site admin did want access restrictions, (s)he would install an ACL and user service
            return true;
        }
        catch (Zend_Acl_Exception $e) {
            // Most likely reason: Resource doesn't exist - should we do something? It will deny access...

            // Log?
        }
        return false;
    }
}
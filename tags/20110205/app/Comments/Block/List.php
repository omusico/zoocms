<?php
/**
 * Block with list of comments
 * @package    Comments
 * @subpackage Block
 */

/**
 * @package    Comments
 * @subpackage Block
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Comments_Block_List extends Zoo_Block_Abstract  {
    /**
     * Returns a unique ID for this block
     * Can be overridden in subclasses to depend on e.g. current page or other factors affecting content
     *
     *
     * @return string
     */
    function getCacheId() {
        try {
            $roles = Zoo::getService('user')->getCurrentUser()->getGroups();
            $groups = "_".implode('_',array_keys($roles));
        }
        catch (Zoo_Exception_Service $e) {
            $groups = "";
        }
        if (Zend_Registry::isRegistered('content_id')) {
            return get_class($this)."_".Zend_Registry::get('content_id')."_".$groups;
        }
        return get_class($this)."_".$this->id;
    }

    /**
     * Return an array of vars to be assigned to the Zend_View_Abstract object for use in the block's template
     *
     * @return array
     */
    function getTemplateVars() {
        $items = $uids = $authors = array();
        $id = 0;
        if (Zend_Registry::isRegistered('content_id')) {
            $id = Zend_Registry::get('content_id');

            // Fetch comments for current content
            $items = Zoo::getService('content')->getContent(array('active' => true,
                                                                  'group' => 'comment',
                                                                  'parent' => $id,
                                                                  'render' => false));
            if ($items->count()) {
                // Fetch comment authors
                foreach ($items as $item) {
                    $uids[] = $item->uid;
                }
                try {
                    $authors = Zoo::getService('profile')->getProfiles($uids);
                }
                catch (Zoo_Exception_Service $e) {
                    // Do nothing, no profile service installed, should it be a module requirement?
                }
            }
        }

        $comment = Zoo::getService('content')->createRow(array('pid' => $id, 'type' => 'comments_node'));
        return array('comments' => $items, 'authors' => $authors, 'form' => $comment->getForm("/content/node/save"));
    }
}
<?php
/**
 * @package Gallery
 * @subpackage Block
 */

/**
 * @package    Gallery
 * @subpackage Block
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Gallery_Block_List extends Zoo_Block_Abstract  {
    /**
     * Get cache ID - although this also benefits from the fact that this code is never cached, so HEAD javascript can be added
     * @see library/Zoo/Block/Zoo_Block_Abstract#getCacheId()
     */
    function getCacheId() {
    	$view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
    	$view->jQuery()->enable()->uiEnable();
		$view->jQuery()->addJavascriptFile('/js/jquery/treeview/jquery.treeview.js', 'text/javascript');
		$view->jQuery()->addStylesheet('/js/jquery/treeview/jquery.treeview.css');
		
		$js = ZendX_JQuery_View_Helper_JQuery::getJQueryHandler().'("#treeview").treeview({collapsed: true, persist: "location"});';
		$view->jQuery()->addOnLoad($js);
		
		return parent::getCacheId();
    }
    
    /**
     * Retrieve galleries listing
     * 
     * @return array
     */
    function getTemplateVars() {
        $galleries = Zoo::getService('content')->getContent(
                                                    array('active' => true,
                                                    	  'nodetype' => 'gallery_node',
                                                    	  'viewtype' => "Short",
                                                          'order' => 'published'),
                                                    0,
                                                    0);
        $tree = new Zoo_Object_Tree($galleries, 'id', 'pid');
        return array('galleries' => $tree->toArray());
    }
}
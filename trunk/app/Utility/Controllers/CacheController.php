<?php
/**
 * @package  Utility
 * @subpackage Controllers
 *
 */

/**
 * @package  Utility
 * @subpackage Controllers
 *
 */
class Utility_CacheController extends Zend_Controller_Action
{
    /**
     * Does nothing
     *
     */
    function indexAction() {
         try {
             $i = Zoo::getService('cache')->getIds();
         }
         catch (Exception $e) {
             $i = $e;
         }
         $this->view->ids = $i;
         try {
             $t = Zoo::getService('cache')->getTags();
         }
         catch (Exception $e) {
             $t = $e;
         }
         $this->view->tags = $t;
    }

    
    /**
     * Clears all cache
     *
     */
    function cleanAction() {
    	if ($this->getRequest()->getParam('tag')) {
    		Zoo::getService('cache')->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($this->getRequest()->getParam('tag')));
    	}
    	else {
        	Zoo::getService('cache')->clean();
        	Zoo::getService('cache')->getCache('metadata')->clean();
        	Zoo::getService('cache')->getCache('acl')->clean();
    	}
    }

    function apcAction() {
        define("USE_AUTHENTICATION", 0);
        
        Zend_Controller_Front::getInstance()->getResponse()->clearHeaders();
        //$this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        unset($_SERVER['PHP_SELF']);
        include dirname(__FILE__)."/extra/apc.php";
    }
    
    function memcacheAction() {
        Zend_Controller_Front::getInstance()->getResponse()->clearHeaders();
        //$this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        unset($_SERVER['PHP_SELF']);
        include dirname(__FILE__)."/extra/memcache.php";
    }
}

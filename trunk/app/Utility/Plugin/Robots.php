<?php
/**
 * @package Utility
 * @subpackage Plugin
 */

/**
 * @package Utility
 * @subpackage Plugin
 */
class Utility_Plugin_Robots extends Zend_Controller_Plugin_Abstract {
    /**
     * Add headers to avoid search engine indexing of entire site
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function dispatchLoopStartup($request = null) {
    	header("X-Robots-Tag: noindex,nofollow,noarchive");
    	Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view->headMeta()->appendName('robots', 'noindex,nofollow,noarchive');    
    }
}
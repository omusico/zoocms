<?php
/**
 * @package Rewrite
 * @subpackage Plugin
 *
 */

/**
 * @package Rewrite
 * @subpackage Plugin
 *
 */
class Rewrite_Plugin_Path extends Zend_Controller_Plugin_Abstract {
	/**
	 * Locate path alias node
	 * @see library/Zend/Controller/Plugin/Zend_Controller_Plugin_Abstract#routeStartup($request)
	 * 
	 * @param Zend_Controller_Request_Abstract $request
	 * @return void
	 */
	public function routeShutdown(Zend_Controller_Request_Abstract $request) {
    	if ($request->getRequestUri() != "/" && $path = Zoo::getService('path')->findPath($request->getRequestUri())) {
	    	$content_service = Zoo::getService ( 'content' );
			$request->setActionName($content_service->action);
			$request->setControllerName($content_service->controller);
			$request->setModuleName($content_service->module);
			$request->setParam('id', $path->nid);
		}
	}
}

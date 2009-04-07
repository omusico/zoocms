<?php
/**
 * @package    Zoo
 * @subpackage Controller
 */
/**
 * Zoo_Controller_Action
 *
 * @package    Zoo
 * @subpackage Controller
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */

class Zoo_Controller_Action extends Zend_Controller_Action {

    /**
     * Put content in cache
     *
     * @param string $content Content to cache
     * @param string $cacheid ID to cache it under
     * @param array $tags Tags to put on the cache
     * @param int $lifetime cache time
     */
    function cache($content, $cacheid, $tags = array(), $lifetime = null) {
        if ($_SERVER['REQUEST_METHOD'] != "GET") {
            // Only cache GET requests
            return;
        }
        try {
            Zoo::getService('cache')->save($content,
                                           $cacheid,
                                           $tags,
                                           $lifetime);
        }
        catch (Zoo_Exception_Service $e) {
            // Cache service not available, do nothing
        }
    }

    /**
     * Check the cache for a cache ID - returns cached content if any
     *
     * @param string $cacheid
     * @return string
     */
    function checkCache($cacheid) {
        try {
            $content = Zoo::getService("cache")->load($cacheid);
        }
        catch (Zoo_Exception_Service $e) {
            // Cache unavailable, set content to empty string
            $content = "";
        }
        return $content;
    }
    /**
     * Get controller action content
     *
     * @return string
     */
    function getContent($action = null) {
        if (null !== $action) {
            $this->_helper->viewRenderer->setRender($action);
        }
        $path = $this->_helper->viewRenderer->getViewScript();
        return $this->_helper->viewRenderer->view->render($path);
    }

    /**
     * Append content to response
     *
     * @param string $content Content to append
     * @param string $name Named segment to append to
     */
    function renderContent($content, $name = null) {
        if (null === $name) {
            $name = $this->_helper->viewRenderer->getResponseSegment();
        }

        $this->getResponse()->appendBody(
            $content,
            $name
        );

        $this->_helper->viewRenderer->setNoRender();
    }
}
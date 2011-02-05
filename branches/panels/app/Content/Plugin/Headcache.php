<?php
/**
 * @package    Content
 * @subpackage Plugin
 */
/**
 * Content_Plugin_Headcache
 *
 * @package    Content
 * @subpackage Plugin
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Content_Plugin_Headcache extends Zend_Controller_Plugin_Abstract {

    /**
     * Cache headers / Send cached header information to view
     *
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $tag = Zend_Registry::isRegistered('content_id')
                ? "node_".Zend_Registry::get('content_id')
                : $request->getModuleName()."_".$request->getControllerName();
        //$this->manageHeaders('headScript', $tag);
        //$this->manageHeaders('headLink', $tag);
        //$this->manageHeaders('headMeta', $tag);
        //$this->manageHeaders('headStyle', $tag);
        $this->addHead($tag);
        $this->addJquery();
    }

    /**
     * Retrieve head links, styles, metas etc. from cache add them to page's <head> section
     * Update cache if necessary
     *
     * @param string $type - headScript, headLink, headMeta etc.
     * @param string $tag
     */
    private function manageHeaders($type, $tag) {
        $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
        /* @var $view Zend_View_Abstract */
        $cacheid = $type."_".md5($_SERVER['REQUEST_URI']);

        $update_cache = false;
        $headscript = $view->$type();
        $to_cache = array();
        if ($headscript) {
        	foreach ($headscript as $item) {
	            $to_cache[] = $item;
	            $update_cache = true;
        	}
        }

        $from_cache = Zoo::getService('cache')->load($cacheid);
        if ($from_cache) {
            foreach ($from_cache as $item) {
                if (!in_array($item, $to_cache)) {
                	$view->$type()->append($item);
                	$to_cache[] = $item;
                }
            }
        }

        if ($update_cache) {
            Zoo::getService('cache')->save($to_cache,
                                            $cacheid,
                                            array($type, $tag),
                                            null);
        }
    }
/**
     * Retrieve head links, styles, metas etc. from cache add them to page's <head> section
     * Update cache if necessary
     *
     * @param string $type - headScript, headLink, headMeta etc.
     * @param string $tag
     */
    private function addHead($tag) {
        $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
        /* @var $view Zend_View_Abstract */
        $cacheid = "Head_".md5($_SERVER['REQUEST_URI']);

        $update_cache = false;
        foreach ( array ('headScript', 'headStyle', 'headLink', 'headMeta' ) as $type ) {
            $headscript = $view->$type ();
            $to_cache = array ();
            if ($headscript) {
                foreach ( $headscript as $item ) {
                    $to_cache [] = $item;
                    $update_cache = true;
                }
            }
        }
            
        $from_cache = Zoo::getService ( 'cache' )->load ( $cacheid );
        if ($from_cache) {
            foreach ( $from_cache as $item ) {
                if (! in_array ( $item, $to_cache )) {
                    $view->$type ()->append ( $item );
                    $to_cache [] = $item;
                }
            }
        }

        if ($update_cache) {
            Zoo::getService('cache')->save($to_cache,
                                            $cacheid,
                                            array($tag),
                                            null);
        }
    }
    
    private function addJquery() {
    	$view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
        /* @var $view Zend_View_Abstract */
        $cacheid = "jquery_".md5($_SERVER['REQUEST_URI']);
    	$update_cache = false;
    	$add = array();
    	if ($view->jQuery ()->isEnabled ()) {
	        $add['scripts'] = $view->jQuery()->getJavascriptFiles();
	        $add['css'] = $view->jQuery()->getStylesheets();
	        $add['onLoad'] = $view->jQuery()->getOnLoadActions();
			$to_cache = array ();
			if (isset($add['scripts'])) {
				foreach ( $add['scripts'] as $item ) {
					$to_cache['scripts'] [] = $item;
					$update_cache = true;
				}
			}
    		if (isset($add['css'])) {
				foreach ( $add['css'] as $item ) {
					$to_cache['css'] [] = $item;
					$update_cache = true;
				}
			}
    		if (isset($add['onLoad'])) {
				foreach ( $add['onLoad'] as $item ) {
					$to_cache['onLoad'] [] = $item;
					$update_cache = true;
				}
			}
    	}

        $from_cache = Zoo::getService('cache')->load($cacheid);
        if ($from_cache) {
            foreach ($from_cache as $item) {
            	if (isset($from_cache['scripts'])) {
            		foreach ($from_cache['scripts'] as $item) {
                		$view->jQuery()->addJavascriptFile($item);
                		$to_cache['scripts'][] = $item;
            		}
            	}
            	if (isset($from_cache['css'])) {
            		foreach ($from_cache['css'] as $item) {
                		$view->jQuery()->addStylesheet($item);
                		$to_cache['css'][] = $item;
            		}
            	}
            	if (isset($from_cache['onLoad'])) {
            		foreach ($from_cache['onLoad'] as $item) {
                		$view->jQuery()->addOnLoad($item);
                		$to_cache['onLoad'][] = $item;
            		}
            	}
            }
        }

        if ($update_cache) {
            Zoo::getService('cache')->save($to_cache,
                                            $cacheid,
                                            array(),
                                            null);
        }
    }
}
<?php
/**
 * @package Zoo
 * @subpackage Hook
 */
/**
 * @package    Zoo
 * @subpackage Hook
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
abstract class Zoo_Hook_Abstract {
    /**
     * View object reference
     *
     * @var Zend_View_Abstract
     */
    protected $view;

    /**
     * Constructor - populates view attribute
     *
     */
    function __construct() {
        $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
        /* @var $view Zend_View_Abstract */

        $this->view = clone $view;
        
        try {
            $module = substr(get_class($this), 0, strpos(get_class($this), "_"));
            if (file_exists(ZfApplication::$_base_path."/app/".$module."/Language")) {
	        Zoo::getService('translator')->addTranslation(
	            	    ZfApplication::$_base_path."/app/".$module."/Language",
	                	null,
	                	array('scan' => Zend_Translate::LOCALE_FILENAME ));
            }
        }
        catch (Zoo_Exception_Service $e) {
        	// No translation service - doesn't matter
        }
    }

    /**
     * Render a hook's content
     *
     * @param string $name template to use
     * @param string $module module to fetch template from
     * @param string $controller controller to fetch template from, defaults to 'hooks'
     *
     * @return string Rendered content
     *
     */
    public function render($name, $module, $controller = "hooks") {
        $layout = Zend_Layout::getMvcInstance();
        // Reset view script paths
        $this->view->setScriptPath(null);

        // Build new ones for hooks
        $this->view->addBasePath(ZfApplication::$_base_path."/app/$module/views", $module."_View");
        //$this->view->addScriptPath(ZfApplication::$_base_path."/app/$module/Views/");
        $this->view->addScriptPath($layout->getLayoutPath()."default/templates/$module");
        $this->view->addScriptPath($layout->getLayoutPath().$layout->getLayout()."/templates/$module");

        return $this->view->render($controller."/".$name);
    }

    /**
     * Assembles a URL based on a given route
     *
     * This method will typically be used for more complex operations, as it
     * ties into the route objects registered with the router.
     *
     * @param  array   $urlOptions Options passed to the assemble method of the Route object.
     * @param  mixed   $name       The name of a Route to use. If null it will use the current Route
     * @param  boolean $reset
     * @param  boolean $encode
     * @return string Url for the link href attribute.
     */
    public function url($urlOptions = array(), $name = null, $reset = false, $encode = true) {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        return $router->assemble($urlOptions, $name, $reset, $encode);
    }
}
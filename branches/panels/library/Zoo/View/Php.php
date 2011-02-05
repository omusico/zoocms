<?php
/**
 * @package    ZooLib
 * @subpackage View
 */
/**
 * @package    ZooLib
 * @subpackage View
 * @copyright  Copyright (c) 2008 ZooCMS
 */
class Zoo_View_Php extends Zend_View_Abstract {

    /**
     * Constructor
     *
     * @see Zend_View::__construct
     * @throws Exception
     */
    public function init()
    {
        $layout = Zend_Layout::getMvcInstance();
        $module = ucfirst(Zend_Controller_Front::getInstance()->getRequest()->getModuleName());
        $controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
        if ($layout) {

            $pagetitle = $module != "Zoo" ? ucwords($module." ".$controller) : ($controller != "index" ? ucfirst($controller) : "" );

            $sitename = Zend_Registry::isRegistered('config') ? Zend_Registry::get('config')->site->sitename : "ZooCMS";
            $this->assign( array(
            'base_rootpath' => ZfApplication::$_base_path,
            'themepath' => $layout->getLayoutPath().$layout->getLayout(),
            'themeurl' => Zend_Controller_Front::getInstance()->getBaseUrl()."/themes/".$layout->getLayout(),
            //			'langcode' => _LANGCODE,
            'pagetitle' => $pagetitle,
            'sitename' => $sitename,
            ) );

            if (Zend_Registry::isRegistered('config')) {
                $this->assign(Zend_Registry::get("config")->site->toArray() );
            }

            $this->addBasePath(ZfApplication::$_base_path."/app/$module/views", $module."_View");
            $this->addScriptPath($layout->getLayoutPath()."default/templates/$module/");
            $this->addScriptPath($layout->getLayoutPath().$layout->getLayout()."/templates/$module/");
        }
        else {
            $this->addBasePath(ZfApplication::$_base_path."/app/$module/views", $module."_View");
        }
    }

    /**
     * fetch a template, echos the result,
     *
     * @see Zend_View_Abstract::render()
     * @param string $name the template
     * @return void
     */
    protected function _run()
    {
        $filename = func_get_arg(0);

        //process the template
        try {
            include $filename;
        }
        catch (Zend_Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * check a template for existance
     * useful, if you have a generic content template
     * but want to check for a specific one first
     *
     * @param string $name template name
     * @return bool
     * @uses Zend_View_Abstract::_script()
     */
    public function templateExists($name) {
        try {
            // _script() will throw an exception if the template cannot be found
            $this->_script($name);
            $ret = true;
        }
        catch (Zend_View_Exception $e) {
            $ret = false;
        }
        return $ret;
    }

    /**
     * Finds a view script from the available directories.
     *
     * @param $name string The base name of the script.
     * @return void
     */
    protected function _script($name) {
        if (strpos($name, ".") === false) {
            $name .= ".phtml";
        }
        return parent::_script(strtolower($name));
    }
}
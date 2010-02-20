<?php
/**
 * @package Default
 * @subpackage Helpers

 */
/**
 * @package Default
 * @subpackage Helpers
 *
 * Basic helper for example purposes
 */
class Zend_View_Helper_GetTypeImage {
    /**
     * View object
     *
     * @var Zend_View
     */
    public $view;

    /**
     * Set view property
     *
     * @param Zend_View_Interface $view
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    /**
     * Get full script path
     *
     * @param string $script
     * @return string
     */
    public function scriptPath($script)
    {
        return $this->view->getScriptPath($script);
    }

    /**
     * Returns an image for a mimetype
     *
     * @return string
     */
    public function getTypeImage($mimetype) {
        $urlOptions = array('module' => 'filemanager',
                            'controller' => 'file',
                            'action' => 'getTypeImage',
                            'mimetype' => str_replace("/", "_", $mimetype));
        return Zend_Controller_Front::getInstance()->getRouter()->assemble($urlOptions, 'default');
    }
}
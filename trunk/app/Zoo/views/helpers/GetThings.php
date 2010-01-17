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
class Zend_View_Helper_GetThings {
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
     * Returns getThings([currentdate])
     *
     * @return string
     */
    public function getThings() {
        return "getThings(".date('d-m-Y H:i:s').")";
    }
}
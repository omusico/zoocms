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
class Zend_View_Helper_Filter {
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
     * Returns content escaped using the View's escape function with more parameters
     *
     * @return string
     */
    public function filter($item, $field = "content", $length = 0) {
        try {
            return Zoo::getService("filter")->filter($item, $field, $length);
        }
        catch (Zoo_Exception_Service $e) {
            return $this->view->escape($item);
        }
    }
}
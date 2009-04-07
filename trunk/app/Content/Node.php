<?php
/**
 * @package  Content
 * @subpackage Node
 */

/**
 * @package  Content
 * @subpackage Node
 */
class Content_Node extends Zend_Db_Table_Row_Abstract implements Zoo_Content_Interface {
    /**
     * Hook content
     *
     * @var array
     */
    public $hooks = array();
    /**
     * Get the form for adding content nodes
     *
     * @param string $action
     *
     * @return Content_Node_Form
     */
    public function getForm($action) {
        return new Content_Node_Form($this, $action);
    }

    /**
     * Returns the URL for the content node
     *
     * @return string Url for the link href attribute.
     */
    public function url()
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        return $router->assemble(array('id' => $this->id), $this->type);
    }
}
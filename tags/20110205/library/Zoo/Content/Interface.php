<?php
/**
 * @package    Zoo
 * @subpackage Interface
 */
/**
 * Zoo_Content_Interface
 *
 * @package    Zoo
 * @subpackage Interface
 * @copyright  Copyright (c) 2009 ZooCMS
 * @version    1.0
 */
interface Zoo_Content_Interface {
    /**
     * Get the form for adding content nodes
     *
     * @param string $action
     * @return Zend_Form
     */
    public function getForm($action);

    /**
     * Returns the URL for the content node
     *
     * @return string Url for the link href attribute.
     */
    public function url();
}
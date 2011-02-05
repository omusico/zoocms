<?php
/**
 * @package    Navigate
 * @subpackage Hook
 */

/**
 * @package	   Navigate
 * @subpackage Hook
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Navigate_Hook_Node extends Zoo_Hook_Abstract {
    /**
     * Hook for node form - if type is Estate Node, add extra fields
     *
     * @param Zend_Form $form
     * @param array $arguments
     */
    public function nodeForm(Zend_Form &$form, &$arguments) {
        $item =& array_shift($arguments);
        
    }

    /**
     * Hook for node save
     *
     * @param Zend_Form $form
     * @param array $arguments
     */
    public function nodeSave(Zend_Form $form, &$arguments) {
        $item = array_shift($arguments);
        
    }
}
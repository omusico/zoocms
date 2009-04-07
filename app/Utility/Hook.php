<?php
/**
 * @package Utility
 * @subpackage Hook
 */

/**
 * Utility_Hook
 *
 * @package   Utility
 * @subpackage Hook
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Utility_Hook extends Zend_Db_Table_Row_Abstract  {

    /**
     * Get the form for adding content nodes
     *
     * @return Utility_Hook_Form
     */
    public function getForm($existing_hooks = null) {
        return new Utility_Hook_Form($this, null, $existing_hooks);
    }
}
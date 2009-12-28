<?php
/**
 * @package Guestbook
 * @subpackage Node
 */

/**
 * @package  Guestbook
 * @subpackage Node
 */
class Guestbook_Node extends Zend_Db_Table_Row_Abstract {
	/**
     * Get string representation of object
     *
     * @return string
     */
    function __toString() {
        $ret = array();
        foreach (array_keys($this->_data) as $key ) {
            $ret[] = $key." : ".$this->$key;
        }
        return implode(' ', $ret);
    }
}
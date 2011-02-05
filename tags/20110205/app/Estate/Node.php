<?php
/**
 * @package Estate
 * @subpackage Node
 */

/**
 * @package  Estate
 * @subpackage Node
 */
class Estate_Node extends Zend_Db_Table_Row_Abstract {

    /**
     * Get string representation of object
     *
     * @return string
     */
    function __toString() {
        $ret = array();
        foreach ($this->_data as $key => $value) {
            $ret[] = $key." : ".$value;
        }
        return implode(' ', $ret);
    }
}
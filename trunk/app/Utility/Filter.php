<?php
/**
 *
 * @package    Utility
 * @subpackage Filter
 */

/**
 * Utility_Filter
 *
 * @package    Utility
 * @subpackage Filter
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Utility_Filter extends Zend_Db_Table_Row_Abstract {
    public $optional = false;
    public $default = 1;
    /**
     *
     * @param string $text text to parse
     * @return string
     */
    public function filter($text) {
        $className = $this->class;
        $class = new $className();
        return $class->filter($text);
    }
}
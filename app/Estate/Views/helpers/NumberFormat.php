<?php
/**
 *
 * @package Estate
 * @subpackage Helpers
 */

/**
 * @package Estate
 * @subpackage Helpers
 *
 * Helper that formats numbers according to European notation
 */
class Zend_View_Helper_NumberFormat {
    /**
     * Format a number with thousands separator (European notation)
     *
     * @param int $number
     * @return string
     */
    public function NumberFormat($number) {
        return number_format($number, 0, ",", ".");
    }
}
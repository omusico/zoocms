<?php
/**
 * @package Utility
 * @subpackage Filter
 */

/**
 * @package Utility
 * @subpackage Filter
 */

class Utility_Filter_SafeHTML {
    /**
     * Remove unwanted HTML through the XML_SafeHTML class
     *
     * @param string $text
     * @return string
     */
    public function filter($text) {
        $safe = new XML_SafeHTML();
        return $safe->parse($text);
    }
}
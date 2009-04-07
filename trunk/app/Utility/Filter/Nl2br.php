<?php
/**
 * @package Utility
 * @subpackage Filter
 * 
 */


/**
 * @package Utility
 * @subpackage Filter
 */

class Utility_Filter_Nl2br {
    /**
	 * Convert linebreaks to <br /> tags
     *
     * @param	string  $text
     *
     * @return	string
	 */
	function filter($text)
	{
		return preg_replace("/(\015\012)|(\015)|(\012)/","<br />",$text);
	}
}
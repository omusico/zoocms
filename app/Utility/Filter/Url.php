<?php
/**
 * @package Utility
 * @subpackage Filter
 */

/**
 * @package Utility
 * @subpackage Filter
 */

class Utility_Filter_Url {

    /**
	 * Make links in the text clickable
	 *
	 * @param   string  $text
	 * @return  string
	 **/
	function filter($text)
	{
		$patterns = array("/(^|[^]_a-z0-9-=\"'\/])([a-z]+?):\/\/([^, \r\n\"\(\)'<>]+)/i", "/(^|[^]_a-z0-9-=\"'\/])www\.([a-z0-9\-]+)\.([^, \r\n\"\(\)'<>]+)/i", "/(^|[^]_a-z0-9-=\"'\/])ftp\.([a-z0-9\-]+)\.([^, \r\n\"\(\)'<>]+)/i", "/(^|[^]_a-z0-9-=\"'\/:\.])([a-z0-9\-_\.]+?)@([^, \r\n\"\(\)'<>\[\]]+)/i");
		$replacements = array("\\1<a href=\"\\2://\\3\" target=\"_blank\">\\2://\\3</a>", "\\1<a href=\"http://www.\\2.\\3\" target=\"_blank\">www.\\2.\\3</a>", "\\1<a href=\"ftp://ftp.\\2.\\3\" target=\"_blank\">ftp.\\2.\\3</a>", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>");
		return preg_replace($patterns, $replacements, $text);
	}
}
<?php
/**
 * @package Utility
 * @subpackage Filter
 */

/**
 * @package Utility
 * @subpackage Filter
 */

class Utility_Filter_Bbcode {
    /**
	 * Replace BBCode with their equivalent HTML formatting
	 *
	 * @param   string  $text
	 * @return  string
	 **/
	function filter($text)
	{
		$patterns = array();
		$replacements = array();
		
		$patterns[] = "/\[siteurl=(['\"]?)([^\"'<>]*)\\1](.*)\[\/siteurl\]/sU";
		$replacements[] = '<a href="'.Zend_Controller_Front::getInstance()->getBaseUrl().'/\\2">\\3</a>';
		$patterns[] = "/\[url=(['\"]?)(http[s]?:\/\/[^\"'<>]*)\\1](.*)\[\/url\]/sU";
		$replacements[] = '<a href="\\2" target="_blank">\\3</a>';
		$patterns[] = "/\[url=(['\"]?)(ftp?:\/\/[^\"'<>]*)\\1](.*)\[\/url\]/sU";
		$replacements[] = '<a href="\\2" target="_blank">\\3</a>';
		$patterns[] = "/\[url=(['\"]?)([^\"'<>]*)\\1](.*)\[\/url\]/sU";
		$replacements[] = '<a href="http://\\2" target="_blank">\\3</a>';
		
		$patterns[] = "/\[email]([^;<>\*\(\)\"']*)\[\/email\]/sU";
		$replacements[] = '<a href="mailto:\\1">\\1</a>';
		$patterns[] = "/\[b](.*)\[\/b\]/sU";
		$replacements[] = '<strong>\\1</strong>';
		$patterns[] = "/\[i](.*)\[\/i\]/sU";
		$replacements[] = '<em>\\1</em>';
		$patterns[] = "/\[u](.*)\[\/u\]/sU";
		$replacements[] = '<u>\\1</u>';
		$patterns[] = "/\[d](.*)\[\/d\]/sU";
		$replacements[] = '<del>\\1</del>';

		$patterns[] = "/\[quote]/sU";
		$replacements[] = '<div class="quote"><blockquote>';
		$patterns[] = "/\[\/quote]/sU";
		$replacements[] = '</blockquote></div>';

		$text = str_replace( "\x00", "", $text );
		$c = "[\x01-\x1f]*";
		$patterns[] = "/j{$c}a{$c}v{$c}a{$c}s{$c}c{$c}r{$c}i{$c}p{$c}t{$c}:/si";
		$replacements[] = "(script removed)";
		$patterns[] = "/a{$c}b{$c}o{$c}u{$c}t{$c}:/si";
		$replacements[] = "about :";

		$ret = preg_replace($patterns, $replacements, $text);

        // Remove BBCode start tag where end tag is truncated
        $last_bracket = strrpos($ret, "[");
        if ($last_bracket > 0) {
            $ret = substr($txt, 0, $last_bracket);
        }
        return $ret;
	}
}
<?php
/**
 * @category    Zoo
 * @package     View
 * @subpackage  Helper
 */

/**
 * jQuery Autocomplete View Helper
 *
 * @package    View
 * @subpackage Helper
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_ZooAutocomplete extends ZendX_JQuery_View_Helper_AutoComplete
{
	/**
	 * Render a Autocompleting FormText field.
	 *
	 * @param  string $id
	 * @param  string $value
	 * @param  array  $params
	 * @param  array  $attribs
	 * @return string
	 */
    public function zooAutocomplete($id, $value='', array $params=array(), array $attribs=array())
    {
    	// Add autocomplete JS
		$this->jquery->addJavascriptFile('/js/jquery/autocomplete/jquery.autocomplete.js', 'text/javascript');
		$this->jquery->addStylesheet('/js/jquery/autocomplete/jquery.autocomplete.css');

	    return parent::autoComplete($id, $value, $params, $attribs);
    }
}
<?php
/**
 * @category    Zoo
 * @package     View
 * @subpackage  Helper
 */

/**
 * jQuery Color Picker View Helper
 *
 * @uses 	   Zend_View_Helper_FormText
 * @package    ZendX_JQuery
 * @subpackage View
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_ZooColorPicker extends ZendX_JQuery_View_Helper_UiWidget
{
	/**
	 * Render a Color Picker in an FormText field.
	 *
	 * @link   http://docs.jquery.com/UI/ColorPicker
	 * @param  string $id
	 * @param  string $value
	 * @param  array  $params
	 * @param  array  $attribs
	 * @return string
	 */
    public function zooColorPicker($id, $value='', array $params=array(), array $attribs=array())
    {
    	$this->jquery->addJavascriptFile(Zend_Controller_Front::getInstance()->getBaseUrl().'/js/jquery/colorpicker/js/colorpicker.js', 'text/javascript');
        $this->jquery->addStylesheet(Zend_Controller_Front::getInstance()->getBaseUrl()."/js/jquery/colorpicker/css/colorpicker.css");
    	
	    $attribs = $this->_prepareAttributes($id, $value, $attribs);

	    if(strlen($value) >= 6) {
	        $params['color'] = $value;
	    }
	    if(count($params) > 0) {
            $params = ZendX_JQuery::encodeJson($params);
	    } else {
	        $params = "{}";
	    }

        $js = sprintf('%s("#%s").ColorPicker({onSubmit: function(hsb, hex, rgb, el) {
											%s(el).val(hex);
											%s(el).ColorPickerHide();
										},
										onBeforeShow: function () {
											%s(this).ColorPickerSetColor(this.value);
										}
									})
									.bind("keyup", function(){
										%s(this).ColorPickerSetColor(this.value);
									});',
            ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
            $attribs['id'],
            ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
            ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
            ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
            ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
            $params
        );

        $this->jquery->addOnLoad($js);

	    return $this->view->formText($id, $value, $attribs);
    }
}
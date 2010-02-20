<?php
/**
 * @category    Zoo
 * @package     View
 * @subpackage  Helper
 */

/**
 *
 * @uses 	   Zend_View_Helper_FormText
 * @package    Zoo
 * @subpackage View
 */
class Zend_View_Helper_FileBrowser extends ZendX_JQuery_View_Helper_UiWidget
{
	/**
	 * Render a File browser 
	 *
	 * @param  string $id
	 * @param  string $value
	 * @param  array  $params
	 * @param  array  $attribs
	 * @return string
	 */
    public function fileBrowser($id, $value='', array $params=array(), array $attribs=array())
    {
	    $attribs = $this->_prepareAttributes($id, $value, $attribs);

	    if(strlen($value) >= 6) {
	        $params['color'] = $value;
	    }
	    if(count($params) > 0) {
            $params = ZendX_JQuery::encodeJson($params);
	    } else {
	        $params = "{}";
	    }

        $js = sprintf('%s("#%s").click(function(){window.open("%s", "%s", "location=0,status=1,scrollbars=1,width=800,height=500");});',
            ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
            $attribs['id']."_image",
            "/filemanager/file/browse?elementid=".$attribs['id'],
            $attribs['id']."_window",
            $params
        );

        $this->jquery->addOnLoad($js);
        
        $js2 = sprintf("function callFunction(id, url, element_id) {
        	element_id = '#' + element_id
        	var image_id = element_id + '_image';
        	%s(image_id).attr('src', url);
        	%s(element_id).attr('value', id);
	        }",
	        ZendX_JQuery_View_Helper_JQuery::getJQueryHandler(),
	        ZendX_JQuery_View_Helper_JQuery::getJQueryHandler()
	        );
        
        $this->view->headScript()->appendScript($js2);
        
    	// XHTML or HTML end tag?
        $endTag = ' />';
        if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
            $endTag= '>';
        }
        
        if ($value && $file = Zoo::getService('filemanager')->find($value)->current()) {
        	$xhtml = '<img id="'.$attribs['id'].'_image" src="'.($file->getUrl(150,150)).'" '.$endTag;
        }
        else {
        	$xhtml = '<img id="'.$attribs['id'].'_image" src="/images/crystal_project/128x128/mimetypes/ascii.png" '.$endTag;
        }
        $xhtml .= $this->view->formHidden($id, $value, $attribs);
	    return $xhtml;
    }
}
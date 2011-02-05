<?php
/**
 * Helper to generate a "wysiwyg" textarea element
 *
 * @category   Zoo
 * @package    View
 * @subpackage Helper
 * @copyright  Copyright (c) 2009 ZooCMS
 */
class Zend_View_Helper_FormWysiwyg extends Zend_View_Helper_FormTextarea
{
    /**
     * How many instances of the wysiwyg editor is set in the form
     * 
     * @var int
     */
    static $instances = 0;

    /**
     * Generates a 'wysiwyg' textarea element.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param mixed $value The element value.
     *
     * @param array $attribs Attributes for the element tag.
     *
     * @return string The element XHTML.
     */
    public function formWysiwyg($name, $value = null, $attribs = null)
    {
    	$info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable
        
        $xhtml = $this->formTextarea($name, $value, $attribs);
        /**
         * @todo Change to query file manager service for URLs
         */
        $xhtml .= '<script type="text/javascript">
        				CKEDITOR.replace( "'. $this->view->escape($id) .'", {
        					filebrowserBrowseUrl : "/filemanager/file/browse",
        					filebrowserImageBrowseUrl : "/filemanager/file/browse?type=Images"
    						} );
        			</script>';
        $this->instances += 1;
        if ($this->instances == 1) {
        	$this->view->headScript()->appendFile( $this->view->baseUrl().'/js/ckeditor/ckeditor.js');
        }
        return $xhtml;
    }
}

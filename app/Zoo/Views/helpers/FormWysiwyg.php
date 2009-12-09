<?php
/**
 * Helper to generate a "wysiwyg" textare element
 *
 * @category   Zoo
 * @package    View
 * @subpackage Helper
 * @copyright  Copyright (c) 2009 ZooCMS
 */
class Zend_View_Helper_FormWysiwyg extends Zend_View_Helper_FormTextarea
{
    /**
     * The default number of rows for a textarea.
     *
     * @access public
     *
     * @var int
     */
    public $rows = 24;

    /**
     * The default number of columns for a textarea.
     *
     * @access public
     *
     * @var int
     */
    public $cols = 80;
    
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
        $xhtml .= '<script type="text/javascript">
        				CKEDITOR.replace( "'. $this->view->escape($id) .'" );
        			</script>';
        $this->instances += 1;
        if ($this->instances == 1) {
        	$this->view->headScript()->appendFile( $this->view->baseUrl().'/js/ckeditor/ckeditor.js');
        }
        return $xhtml;
    }
}

<?php
/**
 * @package    Zoo
 * @subpackage Block
 */

/**
 * Zoo_Block_Abstract
 *
 * @package    Zoo
 * @subpackage Block
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
abstract class Zoo_Block_Abstract {
    /**
     * Block output template
     *
     * @var string
     */
    public $template;
    /**
     * Block title
     *
     * @var string
     */
    public $title;
    /**
    /**
     * Block name
     *
     * @var string
     */
    public $name;
    /**
     * Block weight for ordering blocks relative to each other
     *
     * @var int
     */
    public $weight = 50;
    /**
     * Name of the block's module
     *
     * @var string
     */
    public $module;
    /**
     * Time to cache the module in seconds
     *
     * @var int
     */
    public $cache_time = 2;
    /**
     * Block configuration options
     *
     * @var array
     */
    public $options = array();
    
    /**
     * Block ID
     *
     * @var int
     */
    public $id;

    /**
     * Apply general options
     *
     * @param array $options
     */
    function __construct($options = array()) {
        if (isset($options['id'])) {
            $this->id = $options['id'];
        }
        if (isset($options['title'])) {
            $this->title = $options['title'];
        }
        if (isset($options['name'])) {
            $this->name = $options['name'];
        }
        if (isset($options['options'])) {
        	if (is_string($options['options'])) {
        		$this->options = unserialize($options['options']);
        	}
        	elseif (is_array($options['options'])) {
        		$this->options = $options['options'];
        	}
        }
        if (isset($options['cache_time'])) {
            $this->cache_time = $options['cache_time'];
        }
        $this->module = substr(get_class($this), 0, strpos(get_class($this), "_"));
        $this->setTemplate(strtolower(substr(get_class($this), strrpos(get_class($this), "_")+1)));
    }

    /**
     * Returns a unique ID for this block
     * Can be overridden in subclasses to depend on e.g. current page or other factors affecting content
     *
     *
     * @return string
     */
    function getCacheId() {
        return get_class($this)."_".$this->id;
    }
    
    /**
     * Get cache tags for the block's content
     * @return array
     */
    function getCacheTags() {
        return array();
    }

    /**
     * Return an array of vars to be assigned to the Zend_View_Abstract object for use in the block's template
     *
     * @return array
     */
    function getTemplateVars() {
        return array();
    }

    /**
     * Get template for block
     * Subclasses can either just set the template class member or do calculations in an overriding method to determine it
     *
     * @return string
     */
    function setTemplate($template) {
        $this->template = $template;
    }
}
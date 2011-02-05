<?php
/**
 *
 * @package    Flex
 * @subpackage Block
 */

/**
 * Flex_Block
 *
 * @package    Flex
 * @subpackage Block
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Flex_Block extends Zend_Db_Table_Row_Abstract {
    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init()
    {
        //@todo find a better solution to serialized array data types? 
        if (is_string($this->_data['options'])) {
            if ($this->_data['options'] != "") {
                $this->options = unserialize($this->_data['options']);
            }
            else {
                $this->options = array();
            }
        }
    }
    
    protected function _insert() {
        $this->options = serialize($this->options);
    }
    
    protected function _postInsert() {
        $this->options = unserialize($this->options);
    }
    
    protected function _update() {
        $this->options = serialize($this->options);
    }
    
    protected function _postUpdate() {
        $this->options = unserialize($this->options);
    }
}
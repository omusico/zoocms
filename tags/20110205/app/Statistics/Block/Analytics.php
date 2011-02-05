<?php
/**
 * @package Statistics
 * @subpackage Block
 */

/**
 * @package    Statistics
 * @subpackage Block
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Statistics_Block_Analytics extends Zoo_Block_Abstract  {
    public $template = "analytics";
    
    /**
     * Retrieve Google Analytics tracking code from block configuration
     * 
     * @return array
     */
    function getTemplateVars() {
    	/**
    	 * @todo change to dynamic input of tracking code through block administration
    	 */
    	$this->options['tracking'] = "UA-11868277-1";
        return array('tracking' => $this->options['tracking']);
    }
}
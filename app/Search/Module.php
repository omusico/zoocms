<?php
/**
 * Module definitions
 * @package Search
 */

/**
 * @package    Search
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Search_Module extends Zoo_Module {
    /**
     * Services the module depends on
     *
     * @var array
     */
    protected $dependencies = array('content');
    /**
     * Services provided by the module
     *
     * @todo find a solution to multiple services of same category in one module
     *
     * @var array
     */
    protected $services = array('search' => 'Search_Service_Lucene', 'search' => 'Search_Service_Lucene');
}
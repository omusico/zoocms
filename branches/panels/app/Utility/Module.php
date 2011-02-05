<?php
/**
 * @package Utility
 */
/**
 * Utility_Module
 *
 * @package    Utility
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */

class Utility_Module extends Zoo_Module {
    /**
     * Services provided by the module
     *
     * @var array
     */
    protected $services = array('hook' => 'Utility_Service_Hook', 
                                'translator' => 'Utility_Service_Translator',
                                'cache' => 'Utility_Service_Cache');
}
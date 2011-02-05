<?php
/**
 * Module definitions
 *
 * @package   Connector
 * @copyright  Copyright (c) 2008 ZooCMS
 */

/**
 * @package   Connector
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Connector_Module extends Zoo_Module {
    /**
     * Services necessary for this module
     *
     * @var array
     */
    protected $dependencies = array('db','content');
    /**
     * Services provided by this module
     *
     * @var array
     */
    protected $services = array('link' => 'Connector_Service_Link');
}
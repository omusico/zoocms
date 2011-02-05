<?php
/**
 * @package Zoo
 */

/**
 * Zoo_Module
 *
 * @package    Zoo
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Zoo_Module {
    /**
     * Services the module depends on
     *
     * @var array
     */
    protected $dependencies = array();
    /**
     * Services provided by the module
     *
     * @var array
     */
    protected $services = array();
    /**
     * Plugins provided by the module
     *
     * @var array
     */
    protected $plugins = array();

    /**
     * Object instantiation
     *
     */
    function __construct() {

    }

    /**
     * Accessor method
     *
     * @return array
     */
    function getDependencies() {
        return $this->dependencies;
    }

    /**
     * Accessor method
     *
     * @return array
     */
    function getServices() {
        return $this->services;
    }

    /**
     * Accessor method
     *
     * @return array
     */
    function getPlugins() {
        return $this->plugins;
    }

    /**
     * Defines basic method for module installation
     * To be overridden in subclasses if special processing is needed on module installation
     *
     * @return bool
     */
    function install() {
        return true;
    }


    /**
     * Defines basic method for module update
     * To be overridden in subclasses if special processing is needed on module update
     *
     * @return bool
     */
    function update() {
        return true;
    }

    /**
     * Defines basic method for module uninstallation
     * To be overridden in subclasses if special processing is needed on module uninstallation
     *
     * @return bool
     */
    function uninstall() {
        return true;
    }
}
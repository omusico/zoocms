<?php
/**
 * Comments module definitions
 * @package    Comments
 */

/**
 * @package    Comments
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Comments_Module extends Zoo_Module {
    /**
     * Services necessary for this module
     *
     * @var array
     */
    protected $dependencies = array('db', 'content', 'link');
    /**
     * Services provided by this module
     *
     * @var array
     */
    protected $services = array('comment' => 'Comments_Service_Comment');
}
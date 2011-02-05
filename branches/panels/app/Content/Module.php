<?php
/**
 * @package   Content
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */

/**
 * @package   Content
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Content_Module extends Zoo_Module {
    /**
     * Services necessary for this module
     *
     * @var array
     */
    protected $dependencies = array('db');
    /**
    /**
     * Services provided by this module
     *
     * @var array
     */
    protected $services = array('content' => 'Content_Service_Content');
    /**
     * Routes provided by this module
     *
     * @var array
     */
    protected $routes = array('content_node' =>array('route'    => "article/:id",
                                                     'defaults' => array('module'       => "content",
                                                                         'controller'   => "node",
                                                                         'action'       => "index"),
                                                     'reqs'     => array('id' => "\d+")
                                                    )
                             );
}
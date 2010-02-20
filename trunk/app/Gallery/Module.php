<?php
/**
 * @package    Gallery
 */

/**
 * @package    Gallery
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Gallery_Module extends Zoo_Module {
    /**
     * Services the module depends on
     *
     * @var array
     */
    protected $dependencies = array('db', 'content', 'link');
    /**
     * Routes supplied by the module
     *
     * @var array
     */
    protected $routes = array('gallery_node' =>array('route'    => "galleri/:id",
                                                     'defaults' => array('module'       => "content",
                                                                         'controller'   => "node",
                                                                         'action'       => "index"),
                                                     'reqs'     => array('id' => "\d+")
                                                   ),
                              'gallery_list' => array('route' => "galleri",
                                                       'defaults' => array('module'     => "gallery",
                                                                           'controller' => "index",
                                                                           'action'     => "index"))
                             );
}
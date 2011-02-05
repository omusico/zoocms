<?php
/**
 * @package    Estate
 */

/**
 * @package    Estate
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Estate_Module extends Zoo_Module {
    /**
     * Services the module depends on
     *
     * @var array
     */
    protected $dependencies = array('db', 'content');
    /**
     * Routes supplied by the module
     *
     * @var array
     */
    protected $routes = array('estate_node' =>array('route'    => "bolig/:id",
                                                     'defaults' => array('module'       => "content",
                                                                         'controller'   => "node",
                                                                         'action'       => "index"),
                                                     'reqs'     => array('id' => "\d+")
                                                   ),
                              'estate_search' => array('route' => "bolig",
                                                       'defaults' => array('module'     => "estate",
                                                                           'controller' => "index",
                                                                           'action'     => "index"))
                             );
}
<?php
/**
 * Module definitions
 * @package    Acl
 */

/**
 *
 * @package    Acl
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Acl_Module extends Zoo_Module {
    protected $dependencies = array('db');
    protected $services = array('user' => 'Acl_Service_User');

//    function install() {
//        $directory = new DirectoryIterator(ZfApplication::$_base_path."/app");
//        $resource_factory = new Acl_Resource_Factory();
//        foreach ($directory as $dir) {
//            if (!$dir->isDot() && $dir->isDir()) {
//                if (file_exists($dir->getPathname()."/Controllers")) {
//                    $appdir = new DirectoryIterator($dir->getPathname()."/Controllers");
//                    $module = strtolower($dir->getFilename());
//
//                    $controller_resource = $resource_factory->createRow(array('module' => $module, 'resource' => ''));
//                    $controller_resource->save();
//
//                    foreach ($appdir as $controller) {
//                        if (!$controller->isDot() && !$controller->isDir()) {
//                            $name = substr($controller->getFilename(), 0, strpos($controller->getFilename(), "."));
//                            $classname = ucfirst($module."_".$name);
//
//                            include_once($controller->getPathname());
//
//                            $resource_obj = $resource_factory->createRow(array('module' => $module, 'resource' => strtolower(substr($name, 0, strrpos($name, 'Controller'))), 'parent' => $controller_resource->id));
//                            $resource_obj->save();
//                        }
//                    }
//                }
//            }
//        }
//    }
}
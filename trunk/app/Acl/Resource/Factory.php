<?php

/**
 * Resource DAO class
 * @package    Acl
 * @subpackage Resource
 */

/**
 * Acl_Resource_Factory
 *
 * @package    Acl
 * @subpackage Resource
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Acl_Resource_Factory extends Zoo_Db_Table {
/**
 *      * What:
     * module e.g. content
     * resource e.g. node
     * privilege e.g. access/edit/editown/create/delete
     * item id e.g. 28 (or 0 for no specific item)
     * content type e.g. estate_node
     *
     * module.resource.id - privilege.content_type
     * content.node.28 - access.estate_node
     *
     * Permission:
     * role ID
     * privilege ID
     */
}
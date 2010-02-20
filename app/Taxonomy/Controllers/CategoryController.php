<?php
/**
 * @package Taxonomy
 * @subpackage Controllers
 *
 */

/**
 * @package Taxonomy
 * @subpackage Controllers
 *
 */
class Taxonomy_CategoryController extends Zoo_Controller_Action
{
    /**
     * Display content - not comments and categories
     *
     */
    public function indexAction()
    {
        $select = Zoo::getService('db')->getDb()->select()->from('nonexisting_table');
        var_dump(Zoo::getService('db')->getDb()->fetchAll($select));
    }
}

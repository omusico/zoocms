<?php
/**
 * @package Search
 * @subpackage Controllers
 *
 */

/**
 * @package Search
 * @subpackage Controllers
 *
 */
class Search_IndexController extends Zoo_Controller_Action
{
    /**
     * Index all content nodes
     *
     * @todo change to display search form
     *
     */
    public function indexAction()
    {
        $items = Zoo::getService('content')->fetchAll();
        Zoo::getService('search')->index($items)->optimize();
    }
}

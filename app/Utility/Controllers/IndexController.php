<?php
/**
 * @package  Utility
 * @subpackage Controllers
 *
 */

/**
 * @package  Utility
 * @subpackage Controllers
 *
 */
class Utility_IndexController extends Zend_Controller_Action
{
    /**
     * Display informative PHP settings
     *
     */
    public function indexAction()
    {
        phpinfo();
    }
}

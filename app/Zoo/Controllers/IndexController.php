<?php
/**
 * @package Default
 * @subpackage Controllers
 *
 */

/**
 * @package Default
 * @subpackage Controllers
 *
 */
class IndexController extends Zend_Controller_Action
{
    /**
     * Check if system is installed - if not, redirect to install module
     *
     */
    public function indexAction()
    {
        if (!Zend_Registry::isRegistered('config')) {
            $this->_forward('index', 'Install');
        }

        // Do absolutely nothing more - content should come through block plugin
    }
}

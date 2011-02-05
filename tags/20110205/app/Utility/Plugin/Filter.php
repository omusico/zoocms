<?php
/**
 * @package    Utility
 * @subpackage Plugin
 */
/**
 * Utility_Plugin_Filter
 *
 * @package    Utility
 * @subpackage Plugin
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Utility_Plugin_Filter extends Zend_Controller_Plugin_Abstract {

    /**
     * Set view's escape() method to use the filter helper
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
        $view->setEscape(array('Utility_Service_Filter', 'filter'));
    }
}
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
class Utility_CacheController extends Zend_Controller_Action
{
    /**
     * Does nothing
     *
     */
    function indexAction() {
         try {
             $i = Zoo::getService('cache')->getIds();
         }
         catch (Exception $e) {
             $i = $e;
         }
         $this->view->ids = $i;
         try {
             $t = Zoo::getService('cache')->getTags();
         }
         catch (Exception $e) {
             $t = $e;
         }
         $this->view->tags = $t;
    }

    
    /**
     * Clears all cache
     *
     */
    function cleanAction() {
        Zoo::getService('cache')->clean();
    }


}

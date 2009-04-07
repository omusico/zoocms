<?php
/**
 * @package  Filemanager
 * @subpackage Controllers
 *
 */

/**
 * @package  Filemanager
 * @subpackage Controllers
 *
 */
class Filemanager_FileController extends Zend_Controller_Action
{
    /**
     * Echo file contents
     *
     */
    public function showAction()
    {
        $factory = new Filemanager_File_Factory();
        if ( !($file = $factory->find($this->getRequest()->getParam("id"))->current())) {
            $file = $factory->createRow();
        }
        /**
         * @todo Add last-modified, check access permissions etc.
         */
//        header("Last-Modified: ".Wed, 15 Nov 1995 04:58:08 GMT);
        header("Content-Type: ".$file->mimetype);
        echo file_get_contents($file->getPath());
        die;
//        $this->getHelper('layout')->disableLayout();
//        $this->getHelper('viewRenderer')->setNoRender();
    }
}

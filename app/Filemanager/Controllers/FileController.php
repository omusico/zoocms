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
        header("Last-Modified: ".date('r', filemtime($file->getPath())));
        header("Expires: ".date('r', strtotime("+ 1 year")));

        if (!$file->isImage()) {
            header("Content-Type: ".$file->mimetype);
            echo file_get_contents($file->getPath());
        }
        else {
            if ($this->getRequest()->getParam("width") > 0 || $this->getRequest()->getParam("height") > 0) {
                $w = $this->getRequest()->getParam("width") > 0 ? $this->getRequest()->getParam("width") : 1024;
                $h = $this->getRequest()->getParam("height") > 0 ? $this->getRequest()->getParam("height") : 1024;
                $max = (bool) $this->getRequest()->getParam("max") or true;

                // Get new dimensions
                list($width_orig, $height_orig) = getimagesize($file->getPath());

                $ratio_orig = $width_orig/$height_orig;

                if ($w/$h > $ratio_orig) {
                    $w = $h*$ratio_orig;
                } else {
                    $h = $w/$ratio_orig;
                }
                
                /*
                 * @todo Check for existing file
                 */
                $thumbpath = ZfApplication::$_data_path.DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."thumb".DIRECTORY_SEPARATOR.$file->nid."_".intval($w)."_".intval($h).($max ? "_max" : "");
                if (!file_exists($thumbpath)) {
                    // Resample
                    $image_p = imagecreatetruecolor($w, $h);
                    switch ($file->mimetype) {
                        case "image/jpeg":
                            $image = imagecreatefromjpeg($file->getPath());
                            break;

                        case "image/png":
                            $image = imagecreatefrompng($file->getPath());
                            break;

                        case "image/wbmp":
                            imagecreatefromwbmp($file->getPath());
                            break;

                        case "image/gif":
                            imagecreatefromgif($file->getPath());
                            break;
                    }
                    imagecopyresampled($image_p, $image, 0, 0, 0, 0, $w, $h, $width_orig, $height_orig);

                    // Write to file
                    imagepng($image_p, $thumbpath, 0);
                }
                // Content type
                header('Content-type: image/png');
                //Output
                echo file_get_contents($thumbpath);
            }
            else {
                // Content type
                header('Content-type: image/png');
                //Output
                echo file_get_contents($file->getPath());
            }
        }
//        $this->getHelper('layout')->disableLayout();
//        $this->getHelper('viewRenderer')->setNoRender();
        die;
    }

    public function gettypeimageAction() {
        $type = str_replace("_", "/", $this->getRequest()->getParam("mimetype"));
        switch ($type) {
            case "application_pdf":
                // Content type
                header('Content-type: image/jpeg');
                //Output
                echo file_get_contents(ZfApplication::$_base_path.DIRECTORY_SEPARATOR."app/Filemanager/images/pdf.jpg");
                break;

            default:
                // Content type
                header('Content-type: image/jpeg');
                //Output
                echo file_get_contents(ZfApplication::$_base_path.DIRECTORY_SEPARATOR."app/Filemanager/images/pdf.jpg");
                
                break;
        }
        die;
    }
}

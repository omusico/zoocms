<?php
/**
 * @package    Filemanager
 * @subpackage File
 */

/**
 * @package    Filemanager
 * @subpackage File
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Filemanager_File extends Zend_Db_Table_Row_Abstract {
    /**
     * nid
     * mimetype
     * size
     * exif
     */

    /**
     * Get URL for this file
     *
     * @return string
     */
    function getUrl($width = 0, $height = 0, $max = true) {
        $urlOptions = array('module' => 'Filemanager',
                            'controller' => 'file',
                            'action' => 'show',
                            'id' => $this->nid,
                            'width' => $width,
                            'height' => $height,
                            'max' => intval($max),
                            'r' => filemtime($this->getPath()));
        return Zend_Controller_Front::getInstance()->getRouter()->assemble($urlOptions, 'default');
    }

    /**
     * Get physical path for this file
     *
     * @return string
     */
    function getPath() {
        return ZfApplication::$_data_path.DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR.$this->nid;
    }

    /**
     * Is this file an image file?
     *
     * @return bool
     */
    function isImage() {
        return substr($this->mimetype, 0, 5) === "image";
    }
}
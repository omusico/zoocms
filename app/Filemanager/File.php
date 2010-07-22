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
        $urlOptions = array('module' => 'filemanager',
                            'controller' => 'file',
                            'action' => 'show',
                            'id' => $this->nid,
                            'width' => $width,
                            'height' => $height,
                            'max' => intval($max),
                            'r' => filemtime($this->getPath()));
        return Zend_Controller_Front::getInstance()->getRouter()->assemble($urlOptions, 'default', true);
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
    
    /**
     * Get path to a thumbnail for an image
     * Creates thumbnail if it does not exist, maintaining aspect ratio
     * 
     * @param int $h height of thumbnail
     * @param int $w width of thumbnail
     * @param bool $max whether to use the height and width as maximum values, otherwise it will be minimum
     * @return string path to physical file
     */
    function getThumbnail($w = 1024, $h = 1024, $max = true) {
    	if (!$this->isImage()) {
    		return $this->getPath();
    	}
		// Get new dimensions
		list ( $width_orig, $height_orig ) = getimagesize ( $this->getPath () );
		
		if ($w == 0) {
			$w = $width_orig;
		}
		if ($h == 0) {
			$h = $height_orig;
		}
		
		if ($w > $width_orig && $h > $height_orig) {
			// Requested image is larger than the original - serve the original
			return $this->getPath();
		}
		
		$ratio_orig = $width_orig / $height_orig;
		
		if ($max) {
			if ($w / $h > $ratio_orig) {
				$w = $h * $ratio_orig;
			} else {
				$h = $w / $ratio_orig;
			}
		} else {
			if ($w / $h > $ratio_orig) {
				$h = $h / $ratio_orig;
			} else {
				$w = $w * $ratio_orig;
			}
		}
		
		$thumbpath = ZfApplication::$_data_path . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "thumb" . DIRECTORY_SEPARATOR . $this->nid . "_" . intval ( $w ) . "_" . intval ( $h ) . ($max ? "_max" : "");
		if (! file_exists ( $thumbpath )) {
			// Resample
			$image_p = imagecreatetruecolor ( $w, $h );
			switch ($this->mimetype) {
				case "image/jpeg" :
					$image = imagecreatefromjpeg ( $this->getPath () );
					break;
				
				case "image/png" :
					$image = imagecreatefrompng ( $this->getPath () );
					break;
				
				case "image/wbmp" :
					$image = imagecreatefromwbmp ( $this->getPath () );
					break;
				
				case "image/gif" :
					$image = imagecreatefromgif ( $this->getPath () );
					break;
			}
			imagecopyresampled ( $image_p, $image, 0, 0, 0, 0, $w, $h, $width_orig, $height_orig );
			
			// Write to file
			imagepng ( $image_p, $thumbpath, 3 );
		}
		return $thumbpath;
	}
}
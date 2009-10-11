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
class Filemanager_FileController extends Zend_Controller_Action {
	/**
	 * Echo file contents
	 *
	 */
	public function showAction() {
		$factory = new Filemanager_File_Factory ( );
		if (! ($file = $factory->find ( $this->getRequest ()->getParam ( "id" ) )->current ())) {
			$file = $factory->createRow ();
		}
		/**
		 * @todo Check access permissions etc.
		 */
		header ( "Last-Modified: " . date ( 'r', filemtime ( $file->getPath () ) ) );
		header ( "Expires: " . date ( 'r', strtotime ( "+ 1 year" ) ) );
		
		if (! $file->isImage ()) {
			header ( "Content-Type: " . $file->mimetype );
			echo file_get_contents ( $file->getPath () );
		} else {
			if ($this->getRequest ()->getParam ( "width" ) > 0 || $this->getRequest ()->getParam ( "height" ) > 0) {
				$w = $this->getRequest ()->getParam ( "width" ) > 0 ? $this->getRequest ()->getParam ( "width" ) : 1024;
				$h = $this->getRequest ()->getParam ( "height" ) > 0 ? $this->getRequest ()->getParam ( "height" ) : 1024;
				$max = ( bool ) $this->getRequest ()->getParam ( "max" ) or true;
				
				// Get new dimensions
				list ( $width_orig, $height_orig ) = getimagesize ( $file->getPath () );
				
				$ratio_orig = $width_orig / $height_orig;
				
				if ($w / $h > $ratio_orig) {
					$w = $h * $ratio_orig;
				} else {
					$h = $w / $ratio_orig;
				}
				
				$thumbpath = ZfApplication::$_data_path . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "thumb" . DIRECTORY_SEPARATOR . $file->nid . "_" . intval ( $w ) . "_" . intval ( $h ) . ($max ? "_max" : "");
				if (! file_exists ( $thumbpath )) {
					// Resample
					$image_p = imagecreatetruecolor ( $w, $h );
					switch ($file->mimetype) {
						case "image/jpeg" :
							$image = imagecreatefromjpeg ( $file->getPath () );
							break;
						
						case "image/png" :
							$image = imagecreatefrompng ( $file->getPath () );
							break;
						
						case "image/wbmp" :
							imagecreatefromwbmp ( $file->getPath () );
							break;
						
						case "image/gif" :
							imagecreatefromgif ( $file->getPath () );
							break;
					}
					imagecopyresampled ( $image_p, $image, 0, 0, 0, 0, $w, $h, $width_orig, $height_orig );
					
					// Write to file
					imagepng ( $image_p, $thumbpath, 0 );
				}
				// Content type
				header ( 'Content-type: image/png' );
				//Output
				echo file_get_contents ( $thumbpath );
			} else {
				// Content type
				header ( 'Content-type: image/png' );
				//Output
				echo file_get_contents ( $file->getPath () );
			}
		}
		//        $this->getHelper('layout')->disableLayout();
		//        $this->getHelper('viewRenderer')->setNoRender();
		die ();
	}
	
	/**
	 * Get representation image for a file type 
	 * @return void
	 */
	public function gettypeimageAction() {
		$type = str_replace ( "_", "/", $this->getRequest ()->getParam ( "mimetype" ) );
		switch ($type) {
			case "application_pdf" :
				// Content type
				header ( 'Content-type: image/jpeg' );
				//Output
				echo file_get_contents ( ZfApplication::$_base_path . DIRECTORY_SEPARATOR . "app/Filemanager/images/pdf.jpg" );
				break;
			
			default :
				// Content type
				header ( 'Content-type: image/jpeg' );
				//Output
				echo file_get_contents ( ZfApplication::$_base_path . DIRECTORY_SEPARATOR . "app/Filemanager/images/pdf.jpg" );
				
				break;
		}
		die ();
	}
	
	/**
	 * Upload zip file and extract contents, creating content nodes for each file 
	 */
	public function zipuploadAction() {
		$form = new FilemanagerZipForm ( );
		if ($form->zipfile->isUploaded ()) {
			if ($form->zipfile->receive ()) {
				// Update physical file
				$location = $form->zipfile->getFileName ();
				
				$zip = new ZipArchive ( );
				$res = $zip->open ( $location );
				if ($res === true) {
					// loop through all the files in the archive
					for($i = 0; $i < $zip->numFiles; $i ++) {
						$entry = $zip->statIndex ( $i );
						var_dump($entry);
						die;
						if ($entry ['size'] > 0) {
							$file = $zip->getFromIndex ( $i );
							if ($file) {
								// Create Content + File objects
								
								// Write file to data path
								file_put_contents($file_obj->getPath(), $file);
								$file_obj->size = $entry['size'];
								$file_obj->mimetype = $entry['type']; // Exists?
								$file_obj->save();
							}
						}
					}
					$zip->close ();
				} else {
				
				}
				unlink($location);
			
			} else {
				print "Error receiving the file";
			/**
			 * @todo: Change this to exception/debug message
			 */
			}
		}
	}
	
	public function uploadAction() {
		$this->view->headScript()->appendFile('http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js', 'text/javascript');
		$this->view->headScript()->appendFile('/js/infusion/framework/core/js/ProgressiveEnhancement.js', 'text/javascript');
		$this->view->headScript()->appendFile('/js/infusion/InfusionAll.js', 'text/javascript');

		$this->view->headLink()->appendStylesheet('/js/infusion/components/uploader/css/Uploader.css', 'text/javascript');
		$this->view->headLink()->appendStylesheet('/js/infusion/framework/fss/css/fss-layout.css', 'text/javascript');
		
	}
	
	public function performuploadAction() {
		
	}
}

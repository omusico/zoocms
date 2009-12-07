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
class Filemanager_FileController extends Zoo_Controller_Action {
	public function init() {
		$ajaxContext = $this->_helper->getHelper ( 'AjaxContext' );
		$ajaxContext->addActionContext ( 'categories', 'html' )
					->addActionContext ( 'list', 'html')
					->initContext ();
	}
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
		Zend_Controller_Front::getInstance()->getResponse()->clearHeaders();
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
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
		Zend_Controller_Front::getInstance()->getResponse()->clearHeaders();
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
	}
	
	public function uploadAction() {
		$this->view->headScript()->appendFile('/js/infusion/InfusionAll.js', 'text/javascript');
		$this->view->headScript()->appendFile('/js/infusion/framework/core/js/ProgressiveEnhancement.js', 'text/javascript');

		//$this->view->headLink()->appendStylesheet('/js/infusion/framework/fss/css/fss-reset.css' );
		$this->view->headLink()->appendStylesheet('/js/infusion/framework/fss/css/fss-layout.css');
		$this->view->headLink()->appendStylesheet('/js/infusion/components/uploader/css/Uploader.css');
	}
	
	public function performuploadAction() {
		Zend_Controller_Front::getInstance()->getResponse()->clearHeaders();
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
		/*$writer = new Zend_Log_Writer_Stream ( ZfApplication::$_data_path.DIRECTORY_SEPARATOR."upload.txt" );
		$logger = new Zend_Log ( $writer );
		
		$logger->log ( serialize($_REQUEST), Zend_Log::INFO );*/
		
		$image = Zoo::getService ( 'content' )->createRow ();
		$image->type = 'filemanager_file';
		$image->title = substr ( $this->getRequest ()->getParam ( 'Filename' ), 0, strrpos ( $this->getRequest ()->getParam ( 'Filename' ), '.' ));
		$image->status = 1;
		$image->published = time ();
		$image->pid = $this->getRequest ()->getParam ( 'parent' );
		$image->save ();

		/**
		 * A little bit of hard-coding, since the normal file uploading through Zend_Form_Element_File is not used with flash/AJAX uploader 
		 */
		$factory = new Filemanager_File_Factory();
		$file = $factory->createRow ();
		$file->nid = $image->id;
		$file->mimetype = $factory->getMimetype ( $_FILES ['Filedata'] ['name'] );
		
		$file->size = $_FILES ['Filedata'] ['size'];
		$file->save ();
		
		rename ( $_FILES ['Filedata'] ['tmp_name'], $file->getPath () );

		// Clear listing cache
		Zoo::getService('cache')->remove("Filemanager_FileController_listAction".$image->pid);
		
		try {
			// Trigger node save hooks
			$form = $image->getForm($this->_helper->getHelper('url')
                                                    ->direct('save', 'node', 'Content'));
            Zoo::getService("hook")->trigger("Node", "Save", $form, $image);
        }
        catch (Zoo_Exception_Service $e) {
            // Hook service not available - log? Better not, some people may live happily without a hook service
        }

        // Report back the URL for the file
        $factory = new Filemanager_File_Factory ( );
        $file = $factory->find($image->id)->current();
        if ($file) {
			echo $file->getUrl(200,200);
        }
        else {
        	// Error during upload, delete the file object
        	$image->delete();
        	echo Zoo::_("An error occurred during file upload, please try again");
        }
	}
	
	/**
	 * List categories - should be in another controller, but which?
	 * @todo move to another controller
	 */
	public function categoriesAction() {
		$categories = Zoo::getService('content')->getContent(
                                                    array('group' => 'category',
                                                          'order' => 'title'),
                                                    0,
                                                    0);
        $tree = new Zoo_Object_Tree($categories, 'id', 'pid');
        $this->view->assign('tree', $tree->toArray());
	}
	
	/**
	 * List files in a category - should be in another controller, but which? Probably Filemanager/IndexController
	 * @todo move to another controller
	 */
	public function listAction() {
		$method = __METHOD__;
        $cacheid = str_replace("::", "_", $method).intval($this->getRequest()->getParam('id'));
        
        $content = $this->checkCache($cacheid);
        if (!$content) {
            $found = Zoo::getService('content')->find($this->_request->getParam('id'));
            if ($found->count() == 0) {
                throw new Zend_Controller_Action_Exception(Zoo::_("Category does not exist"), 404);
            }
            $category = $found->current();
            $items = Zoo::getService('content')->getContent(array('active' => true,
                                                                  'nodetype' => 'filemanager_file',
                                                                  'parent' => $category->id,
                                                                  'render' => false));
            $this->view->assign('items', $items);
            $this->view->assign('category', $category);


            $content = $this->getContent();
            $this->cache($content, $cacheid, array('nodelist'), 60); //60 Seconds set - should be dynamic? Should it invalidate, whenever any node is saved?
        }
        $this->renderContent($content);
	}
}
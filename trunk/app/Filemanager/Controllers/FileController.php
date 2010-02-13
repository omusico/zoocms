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
	 * Combines four images into one, with a little overlap for appearance's sake
	 */
	public function combineAction() {
		$ids = array($this->getRequest()->getParam('id1'),
					$this->getRequest()->getParam('id2'),
					$this->getRequest()->getParam('id3'),
					$this->getRequest()->getParam('id4')
					);
		$ids = array_map('intval', $ids);
		
		$w = $this->getRequest ()->getParam ( "width", 1024 );
		$h = $this->getRequest ()->getParam ( "height", 1024 );
		
		$thumbpath = ZfApplication::$_data_path . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "thumb" . DIRECTORY_SEPARATOR . "combine_" . implode ( '_', $ids ) . "_" . intval ( $w ) . "_" . intval ( $h );
		if (! file_exists ( $thumbpath )) {
			$factory = new Filemanager_File_Factory ( );
			$files = $factory->find ( $ids );
			$imgBuf = array ();
			foreach ( $files as $file ) {
				//$url = "http://".$_SERVER['HTTP_HOST'].$file->getUrl(ceil($w/2)+5, ceil($h/2)+5, false);
				$iTmp = imagecreatefrompng($file->getThumbnail(ceil($w/2)+5, ceil($h/2)+5, false));
				array_push ( $imgBuf, $iTmp );
			}
			$iOut = imagecreatetruecolor ( $w, $h );
			imagecopy ( $iOut, $imgBuf [0], 0, 0, 0, 0, imagesx ( $imgBuf [0] ), imagesy ( $imgBuf [0] ) );
			imagedestroy ( $imgBuf [0] );
			imagecopy ( $iOut, $imgBuf [2], 0, ceil($w/2)+5, 0, 0, imagesx ( $imgBuf [2] ), imagesy ( $imgBuf [2] ) );
			imagedestroy ( $imgBuf [2] );
			imagecopy ( $iOut, $imgBuf [1], ceil($h/2)-5, 0, 0, 0, imagesx ( $imgBuf [1] ), imagesy ( $imgBuf [1] ) );
			imagedestroy ( $imgBuf [1] );
			imagecopy ( $iOut, $imgBuf [3], ceil($h/2)+5, ceil($w/2)-5, 0, 0, imagesx ( $imgBuf [3] ), imagesy ( $imgBuf [3] ) );
			imagedestroy ( $imgBuf [3] );
			// Write to file
			imagepng ( $iOut, $thumbpath, 0 );
		}
		// Disable layout and template rendering
		Zend_Controller_Front::getInstance ()->getResponse ()->clearHeaders ();
		$this->getHelper ( 'layout' )->disableLayout ();
		$this->getHelper ( 'viewRenderer' )->setNoRender ();
				
		$last = filemtime ( $thumbpath );
		$expires = date ( 'r', strtotime ( "+ 1 year" ) );
		header ( "Cache-Control: max-age=".(86400*365));
		header ( "Pragma: public");
		header ( "Last-Modified: " . date ( 'r', $last ) );
		header ( "Expires: " . $expires );
		
		// Content type
		header ( 'Content-type: image/png' );

		$cond = isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : 0;
		if ($cond && $cond >= $last && $this->getRequest()->isGet() ) {
			header('HTTP/1.0 304 Not Modified');
			exit;
		}
		//Output
		header ( "Content-Length: ".filesize($thumbpath));
		echo file_get_contents ( $thumbpath );
	}
	
	/**
	 * Echo file contents
	 *
	 */
	public function showAction() {
		Zend_Controller_Front::getInstance()->getResponse()->clearHeaders();
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        
		$factory = new Filemanager_File_Factory ( );
		if (! ($file = $factory->find ( $this->getRequest ()->getParam ( "id" ) )->current ())) {
			$file = $factory->createRow ();
		}
		if (! $file->isImage ()) {
			header ( "Content-Type: " . $file->mimetype );
			$path = $file->getPath ();
		} else {
			if ($this->getRequest ()->getParam ( "width" ) > 0 || $this->getRequest ()->getParam ( "height" ) > 0) {
				// Content type
				header ( 'Content-type: image/png' );
				//Output
				$path = $file->getThumbnail($this->getRequest()->getParam('width', 1024), $this->getRequest()->getParam('height', 1024), $this->getRequest()->getParam('max', 1));
			} else {
				// Content type
				header ( 'Content-type: '.$file->mimetype );
				//Output
				$path = $file->getPath ();
			}
		}
		
		/**
		 * @todo Check access permissions etc.
		 */
		$last = filemtime ( $path );
		$expires = date ( 'r', strtotime ( "+ 1 year" ) );
		header ( "Cache-Control: max-age=".(86400*365));
		header ( "Pragma: public");
		header ( "Last-Modified: " . date ( 'r', $last ) );
		header ( "Expires: " . $expires );
		$cond = isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : 0;
		
		if ($cond && $cond >= $last && $this->getRequest()->isGet() ) {
			header('HTTP/1.0 304 Not Modified');
			exit;
		}
		header ( "Content-Length: ".filesize($path));
		echo file_get_contents ( $path );
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
	
	/**
	 * File browser action
	 */
	public function browseAction() {
		ini_set('memory_limit', '64M');
		$this->view->jQuery()->enable()->uiEnable();
		$this->view->jQuery()->addJavascriptFile('/js/jquery/contextmenu/jquery.contextmenu.js', 'text/javascript');
		$this->view->headLink()->appendStylesheet('/js/jquery/contextmenu/jquery.contextmenu.css');
		$this->view->jQuery()->addJavascriptFile('/js/jquery/treeview/jquery.treeview.js', 'text/javascript');
		$this->view->headLink()->appendStylesheet('/js/jquery/treeview/jquery.treeview.css');
		
		$js = ZendX_JQuery_View_Helper_JQuery::getJQueryHandler().'(document).ready(function(){
				'.ZendX_JQuery_View_Helper_JQuery::getJQueryHandler().'("#treeview").treeview({collapsed: true});
  			   });';
		$this->view->jQuery()->addOnLoad($js);
		
		/**
		 * @todo Find another way to implement changing layout template in a controller
		 */
		Zend_Layout::getMvcInstance()->setInflectorTarget(':script/popup.:suffix');
		
		$categories = Zoo::getService('content')->getContent(
                                                    array('group' => 'category',
                                                          'order' => 'title',
                                                    	  'hooks' => false),
                                                    0,
                                                    0);
        $tree = new Zoo_Object_Tree($categories, 'id', 'pid');
        $this->view->assign('tree', $tree->toArray());
        
        if ($this->getRequest()->getParam('connectTo')) {
        	$this->selectedAction(true);
        }
        else {
        	$this->view->connectTo = 0;
        }
        
        $configure_form = new Zend_Form(array('action' => "#", 'id' => "configure-form", 'method' => 'get'));
		$order_element = new Zend_Form_Element_Select('order');
		$order_element->setLabel('Order');
		$order_element->setAttrib('onchange', 'selectCategory();');
		$order_element->addMultiOptions(array('created DESC' => 'created DESC',
												'created ASC' => 'created ASC',
												'title DESC' => 'title DESC',
												'title ASC' => 'title ASC'));
		
		$limit_element = new Zend_Form_Element_Select('limit');
		$limit_element->setLabel('Per page');
		$limit_element->addMultiOptions(array(20 => 20,50 => 50,100 => 100,200 => 200,500 => 500));
		$limit_element->setAttrib('onchange', 'selectCategory();');
		
		$configure_form->addElements(array($order_element, $limit_element));
		$this->view->configureform = $configure_form;
	}
	
	/**
	 * Connect a file to a node
	 * @return void
	 */
	public function connectAction() {
		Zend_Controller_Front::getInstance()->getResponse()->clearHeaders();
        $this->getHelper('layout')->disableLayout();
			
		if ($this->getRequest()->isPost() ) {
			$item = Zoo::getService('content')->load($this->getRequest()->getParam('image'), 'List');
			// Connect image to gallery_node
	        if (Zoo::getService('link')->connect($this->getRequest()->getParam('id'), $item->id, $this->getRequest()->getParam('type'))) {
                $this->view->image = $item;
                $this->render ( "sel-item" );
                try {
                    Zoo::getService ( 'cache' )->remove ( "Content_nodeDisplay_" . $item->id );
                    Zoo::getService ( 'cache' )->remove ( "Content_nodeDisplay_" . $item->id . "_edit" );
                }
                catch ( Zoo_Exception_Service $e ) {
                    // No caching service installed, nothing to remove
                }
	        }
	        else {
	        	$this->getHelper('viewRenderer')->setNoRender();
	        }
		}
	}
	
	/**
	 * Remove a connection between a file and a node
	 * @return void
	 */
	public function removeAction() {
		$item = Zoo::getService('content')->find($this->getRequest()->getParam('id'))->current();
    	if ($this->getRequest()->isPost()) {
    		Zoo::getService('link')->remove($item->id, intval($this->getRequest()->getParam('image')), $this->getRequest()->getParam('type'));
    		echo Zoo::_("Image removed");
    	}
    	else {
    		/**
    		 * @todo change "gallery" to name of content type
    		 */
    		echo sprintf(Zoo::_("Are you sure, you want to remove %s from the gallery?"), $item->title);
    	}
    	$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
    }
	
	/**
	 * Show browse/upload page
	 */
	public function uploadAction() {
		$this->view->headScript()->appendFile('/js/infusion/InfusionAll.js', 'text/javascript');
		$this->view->headScript()->appendFile('/js/infusion/framework/core/js/ProgressiveEnhancement.js', 'text/javascript');

		//$this->view->headLink()->appendStylesheet('/js/infusion/framework/fss/css/fss-reset.css' );
		$this->view->headLink()->appendStylesheet('/js/infusion/framework/fss/css/fss-layout.css');
		$this->view->headLink()->appendStylesheet('/js/infusion/components/uploader/css/Uploader.css');
		
		/**
		 * @todo Find another way to implement changing layout template in a controller
		 */
		Zend_Layout::getMvcInstance()->setInflectorTarget(':script/popup.:suffix');
		
		$this->view->assign('catId', intval($this->getRequest()->getParam('catid')));
		$this->view->assign('connectTo', intval($this->getRequest()->getParam('connectTo', 0)));
	}
	
	/**
	 * Perform upload of a file
	 */
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
		$image->published = time();
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
	 * List categories
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
	 * List files in a category
	 */
	public function listAction() {
		$found = Zoo::getService ( 'content' )->find ( $this->_request->getParam ( 'id' ) );
		if ($found->count () == 0) {
			throw new Zend_Controller_Action_Exception ( Zoo::_ ( "Category does not exist" ), 404 );
		}
		$category = $found->current ();
		$this->view->assign ( 'category', $category );

		$limit = $this->getRequest()->getParam('limit', 20);
		// Offset = items per page multiplied by the page number minus 1
		$offset = ($this->getRequest()->getParam('page', 1) - 1) * $limit;
		$order = $this->getRequest()->getParam('order', "created DESC");
		$nodetype = $this->getRequest()->getParam('nodetype', "filemanager_file");
		
		$options =  array ( 'active' => true, 
							'nodetype' => $nodetype, 
							'parent' => $category->id,
							'order' => $order, 
							'render' => false );
		
		$select = Zoo::getService ( 'content' )->getContentSelect (	$options, 
																	$offset, 
																	$limit );

		$this->view->items = Zoo::getService('content')->getContent($options, $offset, $limit);
		// Pagination
		Zend_Paginator::setDefaultScrollingStyle('Elastic');
		Zend_View_Helper_PaginationControl::setDefaultViewPartial('file/pagination_control.phtml');
		
		$adapter = new Zend_Paginator_Adapter_DbSelect ( $select );
		$paginator = new Zend_Paginator ( $adapter );
		$paginator->setItemCountPerPage($limit);
		$paginator->setCurrentPageNumber($this->getRequest()->getParam('page', 1));
		$paginator->setView($this->view);
		$this->view->assign('paginator', $paginator);
	}
	
	/**
	 * Reorder files connected to a node
	 * 
	 * @return void
	 */
	public function performreorderAction() {
        // Reorder files
        $item = Zoo::getService('content')->find($this->getRequest()->getParam('id'))->current();
        //Target is on the form selected-file-list-image-[ID]
        $targetId = intval(substr($this->getRequest()->getParam('target'), strrpos($this->getRequest()->getParam('target'), "-")+1));
        // -1 = before, 1 = after
        $position = $this->getRequest()->getParam('position');
        Zoo::getService('link')->update($this->getRequest()->getParam('type'), $item, $this->getRequest()->getParam('movedId'), $targetId, $position);
        
        try {
        	Zoo::getService('cache')->remove("Content_nodeDisplay_".$item->id);
        	Zoo::getService('cache')->remove("Content_nodeDisplay_".$item->id."_edit");
        }
        catch (Zoo_Exception_Service $e) {
        	// No caching service installed, nothing to remove
        }
    	
        $this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
    }
    
    /**
     * Show nodes connected to a given node with a given connection type
     * @param bool $included whether the content is included in another request or is a stand-alone action. If not included, layout will be disabled and the list rendered
     */
    public function selectedAction($included = false) {
    	$this->view->headScript()->appendFile('/js/infusion/InfusionAll.js', 'text/javascript');
		$this->view->headLink()->appendStylesheet('/js/infusion/framework/fss/css/fss-layout.css');
		$this->view->headLink()->appendStylesheet('/js/infusion/components/reorderer/css/Reorderer.css');
		$this->view->headLink()->appendStylesheet('/js/infusion/components/reorderer/css/ImageReorderer.css');
		$this->view->headLink()->appendStylesheet('http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/smoothness/jquery-ui.css');
		
        $found = Zoo::getService('content')->find($this->getRequest()->getParam('connectTo'));
        if ($found->count() == 0) {
            throw new Zend_Controller_Action_Exception(Zoo::_("Content not found"), 404);
        }
        $item = $found->current();
        
        try {
	    	if (!Zoo::getService('acl')->checkItemAccess($item, 'edit')) {
	        	throw new Exception(Zoo::_("Access denied - insufficient privileges"), 403);
	        }
        }
    	catch (Zoo_Exception_Service $e) {
        	// No acl service installed
        }
        
        // Find files connected to the item
		$item->hooks ['connected_nodes'] = Zoo::getService('link')->getLinkedNodes($item, $this->getRequest()->getParam('type'));

        $this->view->assign('item', $item);
        $this->view->assign('connectTo', $item->id);
        $this->view->assign('type', $this->getRequest()->getParam('type'));
        
        if (!$included) {
        	$this->getHelper('layout')->disableLayout();
			$this->render("sel-list");
        }
    }
}

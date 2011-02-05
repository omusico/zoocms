<?php
/**
 * @package Gallery
 * @subpackage Controllers
 */

/**
 * Gallery display
 *
 * @package Gallery
 * @subpackage Controllers
 * @copyright  Copyright (c) 2009 ZooCMS
 * @version    1.0
 * @author ZooCMS
 */
class Gallery_ImportController extends Zoo_Controller_Action {
	
	public function importAction() {
		if (Zoo::getService('user')->getCurrentUser()->id == 0) {
    		throw new Exception("No access");
    	}
        ini_set('memory_limit', '1024M');
        $url = "http://www.123hjemmeside.dk/".$this->getRequest()->getParam('account');
        $urls = $this->getUrls($url);
        $this->view->urls = $urls;
        $this->view->account = $this->getRequest()->getParam('account');
        
        $this->view->jQuery()->enable();
	}
	
	public function octimportAction() {
		if (Zoo::getService('user')->getCurrentUser()->id == 0) {
    		throw new Exception("No access");
    	}
    	Zend_Controller_Front::getInstance()->getResponse()->clearHeaders();
        $this->getHelper('layout')->disableLayout();
        //$this->getHelper('viewRenderer')->setNoRender();
        
        ini_set('memory_limit', '1024M');
        $urls = array(0 => array('href' => 'http://www.123hjemmeside.dk/Ravinski/21381099', 'title' => 'Firenze'));
        
        header("Content-Type: text/html; charset=utf-8");
        
        foreach ($urls as $i => $url) {
            set_time_limit(60);
            $html = file_get_contents($url['href']);
            $doc = new DomDocument();
            @$doc->loadHTML($html);

            $divs = $doc->getElementsByTagName('div');
            foreach ($divs as $div) {
                if (strstr($div->getAttribute('class'), "twoLineSpace")) {
                    $Document = new DOMDocument();
                    $Document->appendChild($Document->importNode($div,true));
                    $urls[$i]['text'] = trim(strip_tags($Document->saveHTML(), "<br>"));
                    break;
                }
            }

            $tags = $doc->getElementsByTagName('table');
            foreach ($tags as $tag) {
                if ($tag->getAttribute('class') == "albumphoto") {
                    $cnodes = $tag->getElementsByTagName('a');
                    foreach ($cnodes as $cnode) {
                        $imgurls[$i] = array('href' => $cnode->getAttribute('href'));
                    }
                    break;
                }
            }
            unset($doc);
        }

        $this->getImages($imgurls, $urls);

        $this->createGalleries($urls);
        $this->view->urls = $urls;
        $this->render('doimport');
	}
	
	public function doimportAction() {
		if (Zoo::getService('user')->getCurrentUser()->id == 0) {
    		throw new Exception("No access");
    	}
    	Zend_Controller_Front::getInstance()->getResponse()->clearHeaders();
        $this->getHelper('layout')->disableLayout();
        //$this->getHelper('viewRenderer')->setNoRender();
        
        ini_set('memory_limit', '1024M');
        $url = "http://www.123hjemmeside.dk/".$this->getRequest()->getParam('account');
        $urls = $this->getUrls($url);
        
        $urls = array_slice($urls, $this->getRequest()->getParam('offset'), 1);
                
        header("Content-Type: text/html; charset=utf-8");
        
        foreach ($urls as $i => $url) {
            set_time_limit(60);
            $html = file_get_contents($url['href']);
            $doc = new DomDocument();
            @$doc->loadHTML($html);

            $divs = $doc->getElementsByTagName('div');
            foreach ($divs as $div) {
                if (strstr($div->getAttribute('class'), "twoLineSpace")) {
                    $Document = new DOMDocument();
                    $Document->appendChild($Document->importNode($div,true));
                    $urls[$i]['text'] = trim(strip_tags($Document->saveHTML(), "<br>"));
                    break;
                }
            }

            $tags = $doc->getElementsByTagName('table');
            foreach ($tags as $tag) {
                if ($tag->getAttribute('class') == "albumphoto") {
                    $cnodes = $tag->getElementsByTagName('a');
                    foreach ($cnodes as $cnode) {
                        $imgurls[$i] = array('href' => $cnode->getAttribute('href'));
                    }
                    break;
                }
            }
            unset($doc);
        }

        $this->getImages($imgurls, $urls);

        $this->createGalleries($urls);
        $this->view->urls = $urls;
	}
    
    private function getUrls($url) {
    	$urls = array();
    	$html = file_get_contents($url);
        $doc = new DomDocument();
        @$doc->loadHTML($html);

        $tags2 = $doc->getElementsByTagName('div');
        foreach ($tags2 as $tag) {
            if ($tag->getAttribute('class') == "menuitem_photoalbum") {
                $nodes = $tag->childNodes;
                foreach ($nodes as $node) {
                    $href = $node->getAttribute('href');
                    $cnodes = $tag->childNodes;
                    $text = "";
                    foreach ($cnodes as $cnode) {
                        $cnodes = $tag->childNodes;
                        foreach ($cnodes as $cnode) {
                            $text = $cnode->nodeValue;
                        }
                    }
                    $urls[] = array('href' => $href, 'title' => $text);
                }
            }
        }
        unset($doc);
        return $urls;
    }
    
    private function getImages($imgurls, &$urls) {
    	foreach ($imgurls as $i => $url) {
            $html = file_get_contents($url['href']);
            $doc = new DomDocument();
            @$doc->loadHTML($html);
            $tags = $doc->getElementsByTagName('input');

            foreach ($tags as $tag) {
                $v = $tag->getAttribute('value');
                if (strstr($v, "#*#")) {
                    if ($tag->getAttribute('class') == "slideshowlist") {
                        $urls[$i]['ids'] = explode('#*#', $v);
                    }
                    elseif ($tag->getAttribute('class') == "slideshowtitleslist") {
                        $urls[$i]['texts'] = explode('#*#', $v);
                    }
                }
            }
            unset($doc);
        }
    }
    
    private function createGalleries($urls) {
    	$factory = new Filemanager_File_Factory();
        $link_service = Zoo::getService('link');
        foreach ($urls as $i => $array) {
            set_time_limit(60);

            // Create gallery_node content item
            $gallery = Zoo::getService('content')->createRow();
            $gallery->type = 'gallery_node';
            $gallery->title = $array['title'];
            $gallery->content = $array['text'];
            $gallery->status = 1;
            $gallery->published = time();
            $gallery->save();
            $fp = fopen(ZfApplication::$_data_path.DIRECTORY_SEPARATOR.$this->getRequest()->getParam('account')."_".$this->getRequest()->getParam('offset').".txt", 'w');
        	fwrite($fp, serialize(array('id' => $gallery->id, 'total' => count($array['ids']))));
        	fclose($fp);
            Zoo::getService('content')->setFilter($gallery, 3, 1);
            Zoo::getService('content')->setFilter($gallery, 6, 1);

            foreach ($array['ids'] as $k => $id) {
                set_time_limit(60);
                
                // Insert image with $id and $array['texts'][$k]
                $image = Zoo::getService('content')->createRow();
                $image->type = 'filemanager_file';
                $image->title = $id;
                if ($array['texts'][$k]) {
                    $image->content = $array['texts'][$k];
                }
                $image->status = 1;
                $image->published = time();
                $image->pid = $gallery->id;
                $image->save();

                Zoo::getService('content')->setFilter($image, 3, 1);
                Zoo::getService('content')->setFilter($image, 6, 1);

                $file = $factory->createRow();
                $file->nid = $image->id;
                $file->mimetype = "image/jpeg";

                // Fetch image from 123hjemmeside.dk
                $url = "http://www.123hjemmeside.dk/picture.aspx?id=".$id;
                $jpg = file_get_contents($url);
                file_put_contents($file->getPath(), $jpg);

                $file->size = filesize($file->getPath());
                $file->save();

                // Connect image to gallery_node
                $gnode = $link_service->createRow();
                $gnode->type = 'gallery_image';
                $gnode->nid = $gallery->id;
                $gnode->tonid = $image->id;
                $gnode->weight = $k;
                $gnode->save();

                unset($image, $file, $jpg, $gnode);
            }
            unset($gallery);
        }
    }
}
<?php
/**
 * @package  Navigate
 * @subpackage Menu
 *
 */

/**
 * @package  Navigate
 * @subpackage Menu
 *
 */
class Navigate_Service_Menu extends Zoo_Service {
    protected $container = null;
    
    /**
     * Returns content menu container object
     * @return Zend_Navigation_Container
     */
    function getContentMenu() {
        if (! $this->container) {
            $cacheid = "navigate_content_menu";
            try {
                $this->container = Zoo::getService ( "cache" )->load ( $cacheid );
            }
            catch ( Zoo_Exception_Service $e ) {
                // Cache unavailable
            }
            if (! $this->container) {
                // menu not loaded from cache
                $items = Zoo::getService ( 'content' )->getContent ( array ('group' => 'category', 'active' => true, 'order' => 'published', 'render' => false ), 0, 0 );
                $tree = new Zoo_Object_Tree ( $items, 'id', 'pid' );
                $this->container = $this->getRootPage ();
                $this->treeToPageContainer ( $tree, $this->container );
                $tags = array ('menu' );
                try {
                    Zoo::getService ( 'cache' )->save ( $this->container, $cacheid, $tags );
                }
                catch ( Zoo_Exception_Service $e ) {
                    // Cache service not available, do nothing
                }
            }
        }
        return $this->container;
    }
    
    /**
     * Recursively add pages to the container
     * 
     * @param Zoo_Object_Tree $tree
     * @param Zend_Navigation_Container $container
     * @param int $key
     * @return void
     */
    function treeToPageContainer(&$tree, Zend_Navigation_Page $container, $key = 0) {
        $children = $tree->getFirstChild ( $key );
        foreach ( $children as $child ) {
            $page = $this->nodeToPage ( $child );
            $container->addPage ( $page );
            $this->treeToPageContainer ( $tree, $page, $child->id );
        }
    }
    
    /**
     * Get a breadcrumb navigation menu from the node and up to the root element (Recursive)
     * 
     * @param Zoo_Content_Service $node
     * @param array $array
     * 
     * @return Zend_Navigation_Page
     */
    function getBreadcrumbsFromNode($node) {
        $array = array_reverse ( $this->getNodeParentPath($node));
        $i = 0;
        $container = $this->getRootPage ();
        $current = $container;
        foreach ( $array as $item ) {
            $current->addPage ( $item );
            $current = $item;
            $i ++;
        }
        return $container;
    }
    
    /**
     * Recursive method to get an array of all parent nodes as Zend_Navigation_Page objects
     *
     * @param Zoo_Content_Interface $node
     * @param array $array
     * @return array 
     */
    function getNodeParentPath($node, $array = array()) {
        $array [] = $this->nodeToPage ( $node, true );
        if ($parent = $node->getParent ()) {
            return $this->getNodeParentPath ( $parent, $array );
        }
        return $array;
    }
    
    /**
     * Transform a node into a page
     * @param $node
     * @return Zend_Navigation_Page_Uri
     */
    function nodeToPage(Zoo_Content_Interface $node, $active = false) {
        return new Zend_Navigation_Page_Uri ( array ('uri' => $node->url (), 'title' => $node->title, 'label' => $node->title, 'id' => 'node_' . $node->id, 'resource' => 'content.node', 'privilege' => 'index.' . $node->type, 'active' => $active ) );
    }
    
    /**
     * Get a root container for a menu
     * @return Zend_Navigation_Page_Uri
     */
    protected function getRootPage() {
        return new Zend_Navigation_Page_Uri ( array ('uri' => '/', 'title' => Zoo::_ ( 'Front page' ), 'label' => Zoo::_ ( 'Front page' ), 'id' => 'root' ) );
    }
}
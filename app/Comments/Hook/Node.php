<?php
/**
 * Node hooks
 * @package    Comments
 * @subpackage Hook
 */

/**
 * @package    Comments
 * @subpackage Hook
 * @copyright  Copyright (c) 2009 ZooCMS
 * @version    1.0
 */

class Comments_Hook_Node extends Zoo_Hook_Abstract {
    /**
     * Hook for node listing - fetches comment count
     *
     * @param Content_Node|array $items
     *
     * @return void
     *
     */
    public function nodeList(&$items) {
    	if (count($items) == 0) {
    		return;
    	}
        $nids = array();
        foreach ($items as $item) {
            $nids[] = $item->id;
            $url[$item->id] = $this->url(array('id' => $item['id']), $item['type']);
        }
        // Count comments per node
        $comments = Zoo::getService('content')->countChildren($nids, 'comment');
        foreach ($comments as $comment_count) {
            $counts[$comment_count->pid] = $comment_count->comment;
        }
        foreach ($items as $item) {
            $count = isset($counts[$item->id]) ? ($counts[$item->id]) : 0;
            if ($count > 0) {
            	$this->view->url = $url[$item->id];
            	$this->view->count = $count;
            	/**
	             * Render HTML
	             */
            	$item->hooks['comments'] = $this->render('nodelist', 'Comments');
            }
        }
    }

    /**
     * Hook for node save - if type is Comments Node, save extra fields
     *
     * @param Zend_Form $form
     * @param array $arguments
     */
    public function nodeSave(&$form, &$arguments) {
        $item = array_shift($arguments);
        $arguments = $form->getValues();
        if ($item->type == "comments_node") {
            $item->pid = $arguments['pid'];
            $item->save();

            Zoo::getService('cache')->remove("Comments_Block_List_".$item->pid);
        }
    }
}
<?php
/**
 * @package Flex
 * @subpackage Plugin
 */

/**
 * Flex_Plugin_Block
 *
 * @package    Flex
 * @subpackage Plugin
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Flex_Plugin_Block extends Zend_Controller_Plugin_Abstract {

    /**
     * Fetch and assign block content to view
     *
     */
    public function dispatchLoopShutdown()
    {
    	if (!Zend_Layout::getMvcInstance()->isEnabled()) {
    		// No layout, no blocks
    		return;
    	}
    	if (Zend_Layout::getMvcInstance()->getInflectorTarget() == ':script/popup.:suffix') {
    		// If using popups, no block
    		/**
    		 * @todo change this to be more generic - something like ZfApplication::hasBlocks()... but where should this method be?
    		 */
    		return;
    	}
        // Retrieve blocks to be shown on this page
        $factory = new Flex_Block_Factory();
        $blocks = $factory->getBlocks();
        if (!$blocks) {
        	// No blocks to show
            return;
        }

        $rendered_blocks = array();
		
        $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
        /* @var $view Zend_View_Abstract */
                        
        foreach (array_keys($blocks) as $position) {
            foreach ($blocks[$position] as $block) {
                $cacheid = $block->getCacheId();
                try {
                    $content = Zoo::getService("cache")->load($cacheid);
                }
                catch (Zoo_Exception_Service $e) {
                    // Cache unavailable, set content to empty string
                    $content = "";
                }
                if (!$content) {
                    if (!isset($blockview)) {
                        // Don't clone the view until it is needed
                        $blockview = clone $view;
                    }

                    $blockview->clearVars();
                    $this->resetViewScripts($blockview, $block);
                    $this->addLanguage($block->module);

                    $blockview->assign($block->getTemplateVars());
                    $content .= $blockview->render($block->template);

                    try {
                        Zoo::getService('cache')->save($content,
                                                       $cacheid,
                                                       array('block', 'block_'.$block->id),
                                                       $block->cache_time);
                    }
                    catch (Zoo_Exception_Service $e) {
                        // Cache service not available, do nothing
                    }
                }

                if (!$content) {
                    // Still no content to show, skip to next block
                    continue;
                }
                $block_arr['content'] = $content;
                $block_arr['title'] = $block->title;
                $block_arr['block'] = $block;

                $rendered_blocks[$position][] = $block_arr;
                unset($block_arr);
            }
        }
        $view->assign('blocks', $rendered_blocks);

        return;
    }

    /**
     * Reset the view's script paths and set new ones for use in the block
     *
     * @param Zend_View_Abstract $view
     * @param Zoo_Block_Abstract $block
     */
    private function resetViewScripts(Zend_View_Abstract $view, Zoo_Block_Abstract $block) {
        $layout = Zend_Layout::getMvcInstance();
        // Reset view script paths
        $view->setScriptPath(null);

        $module = ucfirst($block->module);
        // Build new ones for blocks
        $view->addBasePath(ZfApplication::$_base_path."/app/$module/Views", $module."_View");
        $view->addScriptPath(ZfApplication::$_base_path."/app/$module/Views/scripts/blocks");
        $view->addScriptPath($layout->getLayoutPath()."default/templates/$module/blocks");
        $view->addScriptPath($layout->getLayoutPath().$layout->getLayout()."/templates/$module/blocks");
    }

    /**
     * Add language from block's module
     * @todo Only add if not already loaded?
     *
     * @param string $module
     */
    function addLanguage($module) {
        try {
            Zoo::getService("translator")->addTranslation(
                                                ZfApplication::$_base_path."/app/".ucfirst($module)."/Language",
                                                null,
                                                array('scan' => Zend_Translate::LOCALE_FILENAME ));
        }
        catch (Zend_Translate_Exception $e) {
            // Translation doesn't exist, no biggie, do nothing
        }
    }
}
<?php
/**
 * @package Default
 * @subpackage Helpers

 */
/**
 * @package Default
 * @subpackage Helpers
 *
 * Basic helper for example purposes
 */
class Utility_Filter_Helper {
    /**
     * Filter a content item's content
     *
     * @return string
     */
    static function filter($item) {
        $nodefilters = array();
        if (is_a($item, 'Zoo_Content_Interface') ) {
            $txt = $item->content;
            $nodefilters = Zoo::getService('content')->getFilters($item);
        }
        else {
            $txt = $item;
        }
        if (count($nodefilters)) {
            $ids = array();
            foreach ($nodefilters as $nodefilter) {
                $ids[] = $nodefilter->filter_id;
            }
            $filters = Zoo::getService('filter')->getFilters($ids);
            foreach ($filters as $filter) {
                $txt = $filter->filter($txt);
            }
            if (extension_loaded('tidy')) {
                $config = array('indent' => TRUE,
                            'show-body-only' => TRUE,
                            'output-xhtml' => TRUE,
                            'wrap' => 0);
                $tidy = tidy_parse_string($txt, $config, 'UTF8');
                $tidy->cleanRepair();
                $txt = tidy_get_output( $tidy );
            }
        }
        else {
            $txt = htmlspecialchars($txt);
        }
        return $txt;
    }
}
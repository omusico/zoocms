<?php
/**
 * @package    Utility
 * @subpackage Plugin
 */
/**
 * Utility_Plugin_Debugger
 *
 * @package    Utility
 * @subpackage Plugin
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Utility_Plugin_Debugger extends Zend_Controller_Plugin_Abstract {

    /**
     * Send debug information to view
     *
     */
    public function dispatchLoopShutdown()
    {
        $profiler = Zoo::getService("db")->getDb()->getProfiler();
        if ($profiler->getEnabled() && !($profiler instanceof Zend_Db_Profiler_Firebug) && ($queryCount = $profiler->getTotalNumQueries()) > 0) {
            $longestTime  = 0;
            $longestQuery = null;
            $totalTime    = $profiler->getTotalElapsedSecs();

            $queries = array();
            foreach ($profiler->getQueryProfiles(null, true) as $query) {
                /* @var $query Zend_Db_Profiler_Query */
                if ($query->getQueryType() != Zend_Db_Profiler::CONNECT && $query->getElapsedSecs() > $longestTime) {
                    $longestTime  = round($query->getElapsedSecs(), 4);
                    $longestQuery = $query->getQuery();
                }
                $queries[] = $query->getQuery()." (".round($query->getElapsedSecs(), 6).")";
            }

            $debug = '<div class="query-execution-time">Executed ' . $queryCount . ' queries in ' . round($totalTime, 4) . ' seconds' . "</div>\n" ;
            $debug .=  '<div class="query-avg-time">Average query length: ' . round($totalTime / $queryCount, 6) . ' seconds' . "</div>\n" ;
            $debug .=  '<div class="queries-per-second">Queries per second: ' . round($queryCount / $totalTime, 2) . "</div>\n" ;
            $debug .=  '<div class="query-long-length">Longest query length: ' . round($longestTime, 6) . "</div>\n" ;
            $debug .=  '<div class="query-long">Longest query: ' . $longestQuery . "</div>\n" ;
            $debug .=  implode("<br />\n", $queries);

            $view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
            /* @var $view Zend_View_Abstract */
            $view->assign('debug', $debug."\n<div class='debugger'>".get_class($profiler)."</div>");
        }

    }
}
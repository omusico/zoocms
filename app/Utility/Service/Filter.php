<?php
/**
 * @package Utility
 * @subpackage Service
 */
/**
 * Utility_Service_Filter
 *
 * @package    Utility
 * @subpackage Service
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Utility_Service_Filter extends Zoo_Service {
    /**
     * Get available filters
     *
     * @param $ids array
     * @return array
     * @throws Zend_Db_Exception if trouble with database or tables
     */
    public function getFilters($ids = array()) {
        $ret = array();
        $factory = new Utility_Filter_Factory();
        $select = $factory->select();
        if ($ids != array()) {
            $select = $select->where('id IN (?)', $ids);
        }
        return $factory->fetchAll($select);
    }

    /**
     *
     * Add a filter
     *
     * @param string $name
     * @param string $class
     * @return bool
     */
    public function addFilter($name, $class) {
        $factory = new Utility_Filter_Factory();
        $filter = $factory->createRow(array('name' => $name, 'class' => $class));
        return $filter->save();
    }

    /**
     *
     * Remove a filter
     *
     * @param int $filter_id
     * @return bool true if filter is not found - since the purpose is to remove it from the database
     * mission objectives are accomplished as long as it is not there after this method has run
     */
    public function removeFilter($id) {
        $factory = new Utility_Filter_Factory();
        $filter= $factory->find($id);
        return $filter->count() > 0 ? $filter->current()->delete() : true;
    }


    /**
     * Get filters for a given user
     *
     * @param int $uid
     * @return false|Zend_Db_Table_Rowset
     * @throws Zend_Db_Exception if trouble with database or tables
     */
    public function getFiltersByUser($uid) {
        $factory = new Utility_Filter_User_Factory();
        $userfilters = $factory->fetchAll(
                             array('uid = ?' => $uid)
                        );
        if ($userfilters->count() > 0) {
            $filterids = array();
            $optional = $defaults = array();
            foreach ($userfilters as $userfilter) {
                $filterids[] = $userfilter->filter_id;
                if ($userfilter->optional) {
                    $optional[] = $userfilter->filter_id;
                }
                $defaults[$userfilter->filter_id] = $userfilter->default;
            }
            $factory = new Utility_Filter_Factory();
            $filters = $factory->fetchAll($factory->select()
                                                        ->where('id IN (?)', $filterids)
                                                        ->order('weight'));
            if (count($filters)) {
                foreach ($filters as $filter) {
                    $filter->optional = in_array($filter->id, $optional);
                    $filter->default = isset($defaults[$filter->id]) ? $defaults[$filter->id] == 1 : true;
                }
                return $filters;
            }
        }
        return false;
    }

    /**
     *
     * Add a filter to a user
     *
     * @param int $filter_id
     * @param int $uid
     * @return bool
     */
    public function addFilterToUser($filter_id, $uid) {
        $factory = new Utility_Filter_User_Factory();
        $userfilter = $factory->createRow(array('uid' => $uid, 'filter_id' => $filter_id));
        return $userfilter->save();
    }

    /**
     *
     * Remove a filter from a user
     *
     * @param int $filter_id
     * @param int $uid
     * @return bool
     */
    public function removeFilterFromUser($filter_id, $uid) {
        $factory = new Utility_Filter_User_Factory();
        $userfilter = $factory->fetchRow(
                            $factory->select()
                                ->where('uid = ?', $uid)
                                ->where('filter_id = ?', $filter_id));
        return $userfilter ? $userfilter->delete() : true;
    }
}
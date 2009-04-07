<?php
/**
 * @package Content
 * @subpackage Node
 */

/**
 * @package Content
 * @subpackage Node
 */
class Content_Node_Factory extends Zoo_Db_Table {

    /**
     * Insert a new row
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */
    public function insert(array $data ) {
        $data['created'] = time();
        $data['createdby'] = 0;
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $identity = Zend_Auth::getInstance()->getIdentity();
            $data['createdby'] = $identity->id;
        }
        return parent::insert($data);
    }

    /**
     * Updates existing rows.
     *
     * @param  array        $data  Column-value pairs.
     * @param  array|string $where An SQL WHERE clause, or an array of SQL WHERE clauses.
     * @return int          The number of rows updated.
     */
    public function update(array $data, $where)
    {
        // add a timestamp
        $data['updated'] = time();
        $data['updatedby'] = 0;
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $identity = Zend_Auth::getInstance()->getIdentity();
            $data['updatedby'] = $identity->id;
        }
        return parent::update($data, $where);
    }
}
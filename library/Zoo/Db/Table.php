<?php
/**
 * @package    Zoo
 * @subpackage Db
 */

/**
 * @package    Zoo
 * @subpackage Db
 * @copyright  Copyright (c) 2008 ZooCMS
 *
 * Adds default functionality to db tables, guessing a rowclass from the table class if none is given
 * Also guesses the table name from the rowclass - can be overridden in subclasses
 */
class Zoo_Db_Table extends Zend_Db_Table_Abstract {
    /**
     * Name of class
     *
     * @todo investigate what - if anything - this is used for
     *
     * @var string
     */
    protected $className = "";

    /**
     * Constructor.
     *
     * Supported params for $config are:
     * - db              = user-supplied instance of database connector,
     *                     or key name of registry instance.
     * - name            = table name.
     * - primary         = string or array of primary key(s).
     * - rowClass        = row class name.
     * - rowsetClass     = rowset class name.
     * - referenceMap    = array structure to declare relationship
     *                     to parent tables.
     * - dependentTables = array of child tables.
     * - metadataCache   = cache for information from adapter describeTable().
     *
     * @param  mixed $config Array of user-specified config options, or just the Db Adapter.
     * @return void
     */
    public function __construct($config = array())
    {
        if (!isset($config['rowClass'])) {
            // Set a classname to be the name of the table class minus the last part
            // e.g. Content_Node_Factory becomes Content_Node
            $rowclass = substr(get_class($this), 0, strrpos(get_class($this), "_"));
            if (class_exists($rowclass)) {
                $config['rowClass'] = $rowclass;
            }
        }
        parent::__construct($config);
    }

    /**
     * Set name of table
     *
     */
    protected function _setupTableName()
    {
        if ($this->getRowClass()) {
            $this->_name = $this->getRowClass();
        }
        parent::_setupTableName();
    }
    
    /**
     * Inserts a new row.
     * Switches to master db connection 
     *
     * @param  array  $data  Column-value pairs.
     * @return mixed         The primary key of the row inserted.
     */
    public function insert(array $data)
    {
        $this->_setAdapter(Zoo::getService('db')->getDb('master'));
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
        $this->_setAdapter(Zoo::getService('db')->getDb('master'));
        return parent::update($data, $where);
    }
    
	/**
     * Deletes existing rows.
     *
     * @param  array|string $where SQL WHERE clause(s).
     * @return int          The number of rows deleted.
     */
    public function delete($where)
    {
        $this->_setAdapter(Zoo::getService('db')->getDb('master'));
        return parent::delete($where);
    }
}
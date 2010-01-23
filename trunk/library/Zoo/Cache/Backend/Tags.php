<?php
/**
 * Zend_Cache Tags emulator for backends not supporting them natively
 *
 * It'll use a Zend_Db_Table_Abstract object to persist the mapping
 * between cache keys and its tags.
 *
 * @author      ZooCMS Crew
 * @credits     Iván -DrSlump- Montes <drslump AT pollinimini DOT net>
 * @version     1.0
 * @license     BSD
 */


class Zoo_Cache_Backend_Tags extends Zend_Cache_Backend implements Zend_Cache_Backend_Interface
{
    /**
     * Available options
     *
     * =====> (Zend_Cache_Core) backend:
     * A Zend_Cache_Backend object or an associative array declaring its configuration:
     * 'class' => (string) The full class name of the backend
     * 'options' => (array) The array of options passed to the class constructor
     *
     * =====> (array) table:
     * An associative array with the Zend_Db_Table_Abstract class data:
     * 'class'  => string) The full class name of the backend
     * 'options' => (array) additional configuration parameters to use with Zend_Db_Table_Abstract::__construct()
     * It is assumed that the table will have an 'cacheid' and a 'tag' column
     *
     * =====> (book) track_untagged:
     * If true it will keep track of untagged items so they can be purged by the NOT_MATCHING_TAG mode
     *
     * =====> (book) duplicates_ok:
     * If true it won't make sure that there are no duplicate entries in the database.
     *
     * @var array available options
     */
    protected $_options = array(
        'backend'       => null,
        'table'         => null,
        'track_untagged'=> false,
        'duplicates_ok' => false
    );

    /**
     * Zend_Cache_Core object
     *
     * @var Zend_Cache_Core object
     */
    private $_backend = null;

    /**
     * Table object
     *
     * @var Zend_Db_Table_Abstract object
     */
    private $_table = null;

    /**
     * Constructor
     *
     * @param array $options associative array of options
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        if ( is_array($options['backend']) ) {

            $backendClass = $options['backend']['class'];

            $this->_backend = new $backendClass($options['backend']['options']);

        } else if ( $options['backend'] instanceof Zend_Cache_Backend_Interface ) {

            $this->_backend = $options['backend'];

        } else {
            Zend_Cache::throwException('The backend option is not correctly set!');
        }

        if (!is_array($options['table'])) {
            Zend_Cache::throwException('The table option is not correctly set!');
        }
    }

    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * @param  string  $id                     Cache id
     * @param  boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @return string|false cached datas
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        return $this->_backend->load( $id, $doNotTestCacheValidity );

        // If not in cache make sure to delete the tag mapping to handle cache expiration
//        if ( $result === false && !$this->_options['duplicates_ok'] ) {
//            $where = $this->getTable()->getAdapter()->quoteInto('cacheid = ?', $id);
//            $this->getTable()->delete($where);
//        }
//
//        return $result;
    }

    /**
     * Get Zend_Db_Table_Abstract object for persistence
     */
    protected function getTable() {
        if (!$this->_table) {
            $tableClass = $this->_options['table']['class'];
            $this->_table = new $tableClass($this->_options['table']['options']);
        }
        return $this->_table;
    }

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param  string $id Cache id
     * @return mixed|false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    public function test($id) {
        return $this->_backend->test($id);
    }

    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param  string $data             Datas to cache
     * @param  string $id               Cache id
     * @param  array  $tags             Array of strings, the cache record will be tagged by each string entry
     * @param  int    $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @return boolean True if no problem
     */
    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        $result = $this->_backend->save( $data, $id, array(), $specificLifetime );

        if ( $result ) {
            // Makes sure to always store the key for notMatchingTag cleaning mode
            if ( $this->_options['track_untagged'] && !count($tags) ) {
                $tags = array('');
            }

            if ( count($tags) ) {
                foreach ( $tags as $tag ) {
                    $result = $this->getTable()->fetchAll($this->getTable()->select()
                                                                ->where('cacheid = ?', $id)
                                                                ->where('tag = ?', $tag));
                    if (!$result->count()) {
                        $row = $this->getTable()->createRow(array('cacheid' => $id, 'tag' => $tag));
                        $row->save();
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Remove a cache record
     *
     * @param  string $id Cache id
     * @return boolean True if no problem
     */
    public function remove($id)
    {
        if ( !$this->_options['duplicates_ok'] ) {
            $where = $this->getTable()->getAdapter()->quoteInto('cacheid = ?', $id);
            $this->getTable()->delete($where);
        }

        return $this->_backend->remove($id);
    }

    /**
     * Clean some cache records
     *
     * Available modes are :
     * 'all' (default)  => remove all cache entries ($tags is not used)
     * 'old'            => remove too old cache entries ($tags is not used)
     * 'matchingTag'    => remove cache entries matching all given tags
     *                     ($tags can be an array of strings or a single string)
     * 'notMatchingTag' => remove cache entries not matching one of the given tags
     *                     ($tags can be an array of strings or a single string)
     *
     * @param  string $mode Clean mode
     * @param  array  $tags Array of tags
     * @return boolean True if no problem
     */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array())
    {
        if ( $mode == Zend_Cache::CLEANING_MODE_MATCHING_TAG ||
                $mode == Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG ) {

            if ( !is_array($tags) ) {
                $tags = array($tags);
            }

            // Build the query for the desired matching mode
            if ( $mode == Zend_Cache::CLEANING_MODE_MATCHING_TAG ) {
                $select = $this->getTable()->select()->where('tag IN (?)', $tags);
            } else {
                $select = $this->getTable()->select()->where('tag NOT IN (?)', $tags);
            }
            
            $items = $this->getTable()->fetchAll($select);
            
            foreach ( $items as $item ) {
                $this->remove( $item->cacheid );
            }

            return true;

        } else {
            return $this->_backend->clean($mode, $tags);

        }
    }


    /**
     * Magic call handler to forward to the real backend the method calls
     *
     * @param string    $method     The method called
     * @param array     $args       The arguments used packed as an array
     * @return mixed
     */
    public function __call( $method, $args )
    {
        return call_user_method_array( $method, $this->_backend, $args );
    }
}
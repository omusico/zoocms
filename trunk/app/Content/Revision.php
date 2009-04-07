<?php
/**
 * @package Content
 * @subpackage Node
 */

/**
 * @package Content
 * @subpackage Node
 */
class Content_Revision extends Zend_Db_Table_Row_Abstract {
    /**
     * Node ID
     *
     * @var int
     */
    public $nid;

    /**
     * Version ID
     *
     * @var int
     */
    public $vid;

    /**
     * User ID of version creator
     *
     * @var int
     */
    public $uid;

    /**
     * Version title
     *
     * @var string
     */
    public $title;

    /**
     * Body text
     *
     * @var string
     */
    public $body;

    /**
     * Teaser/Introduction text
     *
     * @var string
     */
    public $teaser;

    /**
     * Revision log message
     *
     * @var string
     */
    public $log;

    /**
     * Creation timestamp
     *
     * @var int
     */
    public $created;
}
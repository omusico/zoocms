<?php
/**
 * @package  Rewrite
 * @subpackage Path
 *
 */

/**
 * @package  Rewrite
 * @subpackage Path
 *
 */
class Rewrite_Path_Factory extends Zoo_Db_Table {
	public function findPath($path) {
		return $this->fetchRow($this->select()->where('path = ?', $path));
	}
}
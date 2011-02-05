<?php
/**
 * @package  Filemanager
 * @subpackage File
 *
 */

/**
 * @package  Filemanager
 * @subpackage File
 *
 */
class Filemanager_File_Factory extends Zoo_Db_Table {
	public function getMimetype($filename) {
		$fileext = substr ( strrchr ( $filename, '.' ), 1 );
		if (empty ( $fileext ))
			return (false);
		$regex = "/^([\w\+\-\.\/]+)\s+(\w+\s)*($fileext\s)/i";
		$lines = file ( ZfApplication::$_base_path.DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."Filemanager".DIRECTORY_SEPARATOR."mime.types", FILE_IGNORE_NEW_LINES );
		foreach ( $lines as $line ) {
			if (substr ( $line, 0, 1 ) == '#')
				continue; // skip comments
			if (! preg_match ( $regex, $line, $matches ))
				continue; // no match to the extension
			return ($matches [1]);
		}
		return (false); // no match at all
	}
}
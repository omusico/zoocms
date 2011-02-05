<?php
/**
 * @package Utility
 * @subpackage Filter
 */

/**
 * @package Utility
 * @subpackage Filter
 */

class Utility_Filter_Smiley {
    protected $smileys = array();
    /**
     * Replace emoticons in the message with smiley images
     *
     * @param	string  $message
     *
     * @return	string
     */
    function filter($message)
	{

		if (count($this->smileys) == 0) {
			$this->loadSmileys();
		}
		if (is_array($this->smileys)) {
			foreach ($this->smileys as $smile) {
                $replace = '<img src="'.XOOPS_UPLOAD_URL.'/'.htmlspecialchars($smile['smile_url']).'" alt="'.$smile['code'].'" />';
				$message = str_replace($smile['code'], $replace, $message);
			}
		}
		return $message;
	}

    /**
     * Load smileys from data source
     */
    protected function loadSmileys() {

    }
}
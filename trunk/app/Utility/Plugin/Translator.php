<?php
/**
 * @package Utility
 * @subpackage Plugin
 */

/**
 * @package Utility
 * @subpackage Plugin
 */
class Utility_Plugin_Translator extends Zend_Controller_Plugin_Abstract {
    /**
     * Set default adapter and cache for Zend_Db_Table classes
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function dispatchLoopStartup($request = null) {
         // Add translator
        $translate = Zoo::getService("translator");
        Zend_Registry::set("Zend_Translate", $translate);
        // Language
        try {
            $translate->addTranslation(ZfApplication::$_base_path."/app/".$request->getModuleName()."/Language",
                                       null,
                                       array('scan' => Zend_Translate::LOCALE_FILENAME ));
        }
        catch (Zend_Translate_Exception $e) {
            // Translation doesn't exist.
        }
    }
}
<?php
/**
 * @package Utility
 * @subpackage Service
 */

/**
 * Utility_Service_Translator
 *
 * @package    Utility
 * @subpackage Service
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Utility_Service_Translator extends Zoo_Service
{
    /**
     * Private service object instance
     *
     * @var Zend_Translate
     */
    private $service;

    /**
     * Retrieve service object instance
     *
     * @param Zend_Config $config
     * @return Zend_Translate
     */
    public function &getService($config)
    {
        if (!$this->service) {
            Zend_Translate::setCache(Zoo::getService('cache')->getCache('translate'));

            /*
             * @todo Re-enable this with configuration options instead of hardcoding
            $writer = new Zend_Log_Writer_Firebug();
			$logger = new Zend_Log($writer);
            $this->service = new Zend_Translate('gettext',
                                                ZfApplication::$_base_path."/app/Zoo/Language",
                                                null,
                                                array(
                                                      'scan' => Zend_Translate::LOCALE_FILENAME,
                                                      'disableNotices' => true, 
                                                	  'log' => $logger,
                                                	  'logUntranslated' => true)
                                                );
            */
            $this->service = new Zend_Translate('gettext',
                                                ZfApplication::$_base_path."/app/Zoo/Language",
                                                null,
                                                array(
                                                      'scan' => Zend_Translate::LOCALE_FILENAME,
                                                      'disableNotices' => true)
                                                );
            if ($config->language->default) {
            	$this->service->setLocale($config->language->default);
            	Zend_Registry::set("Zend_Locale", new Zend_Locale($config->language->default));
            }
			elseif (!$this->service->isAvailable(Zend_Registry::get('Zend_Locale')->getLanguage())) {
                // when user requests a not available language reroute to default
                $this->service->setLocale('en');
            }
        }
        return $this->service;
    }
}
?>
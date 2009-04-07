<?php
/**
 * @package Default
 * @subpackage Form
 *
 */

/**
 * @package Default
 * @subpackage Form
 *
 */
class Default_Form_Settings extends Zend_Form_SubForm {
    /**
     * Create system settings configuration form
     *
     * @param array|Zend_Config $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->setName('settings');
        $this->setLegend('Settings');

        $theme = new Zend_Form_Element_Select('theme', array('label' => "Default theme"));
        $mod_dir = ZfApplication::$_doc_root."/themes";
        $iterator = new DirectoryIterator($mod_dir);
        foreach ($iterator as $file) {
            if ($file->isDir() && $file->getFilename() != "." && $file->getFilename() != "..") {
                $options[$file->getFilename()] = $file->getFilename();
            }
        }
        $theme->addMultiOptions($options);

        $this->addElement(new Zend_Form_Element_Text('sitename', array('label' => "Site name")));


        $this->addElement($theme, 'theme');
    }

}
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
class Zoo_Form_Settings extends Zend_Form_Subform {
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
            if ($file->isDir() && $file->getFilename() != "." 
                && $file->getFilename() != ".."
                && substr($file->getFilename(), 0, 1) != ".") {
                $options[$file->getFilename()] = $file->getFilename();
            }
        }
        $theme->addMultiOptions($options);

        $sitename = new Zend_Form_Element_Text('sitename', array('label' => "Site name",) );
        $sitename->addValidator('stringLength', false, array(6, 20));
        $sitename->setRequired();
        $this->addElement($sitename );


        $this->addElement($theme, 'theme');
    }

}
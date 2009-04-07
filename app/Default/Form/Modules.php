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
class Default_Form_Modules extends Zend_Form_SubForm {
    /**
     * Create a module selection form for system installation
     *
     * @param array|Zend_Config $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->setName('modules');
        $this->setLegend('Modules');

        $mod_dir = ZfApplication::$_base_path."/app";
        $iterator = new DirectoryIterator($mod_dir);
        foreach ($iterator as $file) {
            if ($file->isDir() && $file->getFilename() != "." && $file->getFilename() != "..") {
                $module = new Zend_Form_Element_Checkbox($file->getFilename(), array('value' => 1));
                $module->setAttrib('id', 'modules_'.$file->getFilename())->setLabel($file->getFilename());

                $this->addElement($module);
            }
        }
    }
}
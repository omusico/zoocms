<?php
/**
 * @package  Utility
 * @subpackage Controllers
 *
 */

/**
 * @package  Utility
 * @subpackage Controllers
 *
 */
class Utility_ModuleController extends Zend_Controller_Action
{
    /**
     * Display module list
     *
     */
    function indexAction() {
        $this->writeModulesIni();
    }

    /**
     * Update module(s)
     *
     */
    public function updateAction() {
        
    }

    /**
     * Install module(s)
     *
     */
    public function installAction()
    {
        
    }

    /**
     * Uninstall module(s)
     *
     */
    public function uninstallAction()
    {

    }

    private function writeModulesIni() {
        // Load all sections from an existing config file, while skipping the extends.
        $filename = ZfApplication::$_data_path . '/etc/modules.ini';
        $config = new Zend_Config_Ini($filename, null, array('skipExtends' => true,
                                                             'allowModifications' => true));

        $config->production = array();
        $config->setExtend('staging', 'production');
        $config->setExtend('cli', 'production');
        // Modify a value
        $directory = new DirectoryIterator(ZfApplication::$_base_path."/app");
        $config->production->modules = array();
        foreach ($directory as $dir) {
            $name = strtolower($dir->getFilename());
            if (!$dir->isDot() && $dir->isDir() && !in_array($name{0},array(".","_") ) ) {
                $config->production->modules->$name = '/app/'.$dir->getFilename().'/Controllers';
            }
        }

        // Write the config file
        $writer = new Zend_Config_Writer_Ini();
        $writer->write($filename, $config);
    }
}

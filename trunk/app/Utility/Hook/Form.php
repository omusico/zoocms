<?php
/**
 * @package Utility
 * @subpackage Hook
 */

/**
 * @package Utility
 * @subpackage Hook
 */
class Utility_Hook_Form extends Zend_Form {
    /**
     * Object added/edited by the form
     *
     * @var Utility_Hook
     */
    private $target;
    /**
     * Internal cache of hooks
     *
     * @var array
     */
    protected $hooks = array();

    /**
     * Set form elements for a Content_Node object
     *
     * @param Utility_Hook $target
     * @param array $options
     */
    function __construct(Utility_Hook $target, $options = array(), $existing_hooks = null) {
        $this->target = $target;
        if ($existing_hooks) {
            foreach ($existing_hooks as $hook) {
                $this->hooks[$hook->type][$hook->action][$hook->class] = 1;
            }
        }
        parent::__construct($options);
     }

     /**
      * Get list of available hooks
      *
      * @return array
      */
    function getHooks() {
        $ret = array();
        $directory = new DirectoryIterator(ZfApplication::$_base_path."/app");
        foreach ($directory as $dir) {
            if (!$dir->isDot() && $dir->isDir() && $dir->getFilename() != "Zoo" && $dir->getFilename() != "Utility") {
                if (file_exists($dir->getPathname()."/Hook")) {
                    $appdir = new DirectoryIterator($dir->getPathname()."/Hook");
                    foreach ($appdir as $hook) {
                        if (!$hook->isDot() && !$hook->isDir()) {
                            $name = substr($hook->getFilename(), 0, strpos($hook->getFilename(), "."));
                            $classname = $dir->getFilename()."_Hook_".$name;
                            $hookobject = new $classname();

                            foreach (get_class_methods($hookobject) as $action) {
                                if (strstr(strtolower($action), strtolower($name)) != false &&
                                    !isset($this->hooks[$name][substr($action, strlen($name))][$dir->getFilename()])) {

                                    $ret[$name][substr($action, strlen($name))][] = $dir->getFilename();
                                }
                            }
                        }
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * Form initialization
     *
     * @return void
     */
    public function init()
    {
        $url = Zend_Controller_Front::getInstance()->getRouter()
                                                    ->assemble(array('module' => "utility",
                                                                     'controller' => 'hook',
                                                                     'action' => 'save'),
                                                               'default');
        $this->setAction($url)->setMethod('post');

        $type = new Zend_Form_Element_Text('type');
        $type->setLabel('Type');
        $type->setRequired(true)->addValidator(new Zend_Validate_StringLength(1,255));

        $action = new Zend_Form_Element_Text('action');
        $action->setLabel('Action');
        $action->setRequired(true)->addValidator(new Zend_Validate_StringLength(1,255));

        $type = new Zend_Form_Element_Select('type');
        $type->setLabel('Hook');

        $hooks = $this->getHooks();
        foreach ($hooks as $name => $hook) {
            $values = array();
            ksort($hook);
            foreach ($hook as $value => $modulelist) {
                foreach ($modulelist as $module) {
                    $values[$name."_".$value."_".$module] = $value." ".$module;
//                    $modules[$module] = $module;
                }
            }
            $type->addMultiOption($name, $values);
        }

        $weight = new Zend_Form_Element_Text('weight');
        $weight->setLabel('Weight')->setAttrib('size',2);
        $weight->setRequired(true)->addValidator(new Zend_Validate_Int());

        $submit = new Zend_Form_Element_Submit('save');
        $submit->setLabel('save');

        $this->addElements(array($type, $weight));

        $legend = $this->target->id > 0 ? Zoo::_("Edit item") : Zoo::_("Add item");
        $this->addDisplayGroup(array('type', 'action', 'weight'), 'hook_form', array('legend' => $legend ));

        $this->addElement($submit);
        if ($this->target->id > 0) {
            $id_ele = new Zend_Form_Element_Hidden('id');
            $id_ele->setValue(intval($target->id));
            $this->addElement($id_ele);
        }
        $this->populate($this->target->toArray());

    }
}
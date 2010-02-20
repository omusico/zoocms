<?php
/**
 * @package    Content
 * @subpackage Hook
 */

/**
 * @package   Content
 * @subpackage Hook
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Content_Hook_Node extends Zoo_Hook_Abstract {
    public function nodeMenu($item) {
        $item->hooks['menu'][] = new Zend_Navigation_Page_Mvc(array('label' => Zoo::_('Edit'),
        															'route' => 'default',
        															'module' => 'content', 
                                                                    'controller' => 'node',
                                                                    'action' => 'edit',
                                                                    'reset_params' => true,
                                                                    'resource' => 'content.node', 
                                                                    'privilege' => 'edit.' . $item->type,
                                                                    'params' => array('id' => $item->id)));
        $item->hooks['menu'][] = new Zend_Navigation_Page_Mvc(array('label' => Zoo::_('Delete'),
                                                                    'route' => 'default',
        															'module' => 'content', 
                                                                    'controller' => 'node',
                                                                    'action' => 'delete',
                                                                    'reset_params' => true,
                                                                    'resource' => 'content.node', 
                                                                    'privilege' => 'edit.' . $item->type,
                                                                    'params' => array('id' => $item->id)));
    }
    /**
     * Hook for node form - if type is Estate Node, add extra fields
     *
     * @param Zend_Form $form
     * @param array $arguments
     */
    public function nodeForm(Zend_Form &$form, &$arguments) {
        $item =& array_shift($arguments);
        
        $title = new Zend_Form_Element_Text('title', array('class' => 'content_title'));
        $title->setLabel('Title');
        $title->setRequired(true)->addValidator(new Zend_Validate_StringLength(2,255));

        $content = new Zoo_Form_Element_Wysiwyg('content');
        $content->setRequired(false)->setLabel('Content')->setAttrib('cols', 50);
        
        $form->addElements(array($title, $content));
        
        $form->addDisplayGroup(array('title', 'content'), 'content_add', array('legend' => Zoo::_('Content')));
        
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $identity = Zend_Auth::getInstance()->getIdentity();
            $uid = $identity->id;
        }
        else {
            $uid = 0;
        }
        $filters = Zoo::getService('filter')->getFiltersByUser($uid);

        if ($filters && $filters->count() > 0) {
            foreach ($filters as $filter) {
                $options = array();
                if (!$filter->optional) {
                    $options = array('disabled' => 'disabled',
                                     'value' => 1);
                }
                elseif ($item->id == 0) {
                    $options['value'] = $filter->default;
                }

                $ele = new Zend_Form_Element_Checkbox("filter_".$filter->name, $options);
                $ele->setLabel($filter->name);
                $form->addElement($ele);
                $elements[] = "filter_".$filter->name;
                $userfilters[$filter->id] = $filter;
            }
            $options = array('legend' => Zoo::_("Filters"));
            $form->addDisplayGroup($elements, 'filter_set', $options);
            if ($item->id > 0) {
                // Fetch set filters
                $filters = Zoo::getService('content')->getFilters($item);
                $populate = array();
                foreach ($filters as $filter) {
                    $populate['filter_'.$userfilters[$filter->filter_id]->name] = 1;
                }
                $form->populate($populate);
            }
        }
    }

    /**
     * Hook for node save
     *
     * @param Zend_Form $form
     * @param array $arguments
     */
    public function nodeSave(Zend_Form $form, &$arguments) {
        $item = array_shift($arguments);
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $identity = Zend_Auth::getInstance()->getIdentity();
            $uid = $identity->id;
        }
        else {
            $uid = 0;
        }
        $filters = Zoo::getService('filter')->getFiltersByUser($uid);
        if ($filters && $filters->count() > 0) {
            foreach ($filters as $filter) {
                $value = $filter->optional ? $form->getValue('filter_'.$filter->name) : 1;
                Zoo::getService('content')->setFilter($item, $filter->id, $value);
            }
        }
    }
}
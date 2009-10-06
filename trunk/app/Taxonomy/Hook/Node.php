<?php
/**
 * @package Taxonomy
 * @subpackage Hook
 */

/**
 * @package    Taxonomy
 * @subpackage Hook
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Taxonomy_Hook_Node extends Zoo_Hook_Abstract {

    /**
     * Hook for node form, add extra fields
     *
     * @param Zend_Form $form
     * @param array $arguments
     */
    public function nodeForm(Zend_Form &$form, &$arguments) {
        $item =& array_shift($arguments);

        $type = Zoo::getService('content')->getType($item->type);
        if ($type->group == "comment") {
            return;
        }

        $category = new Zend_Form_Element_Select('category');
        if ($item->type == "taxonomy_category"){
            $category->setLabel('Parent category');
            $category->addMultiOption(0, Zoo::_('Root category'));
        }
        else {
            $category->setLabel('Category');
        }

        /**
         * @todo: Change to use content service
         * @todo: Change to not create select directly
         */
        $categories = Zoo::getService('content')->getContent(
                                                    array('group' => 'category',
                                                          'order' => 'title'),
                                                    0,
                                                    0);

        $tree = new Zoo_Object_Tree($categories, 'id', 'pid');

        $category->addMultiOptions($tree->getIndentedArray(
                                                    'title',
                                                    0,
                                                    " - ",
                                                    (($item->type == "taxonomy_category" && $item->id > 0) ? $item->id : null)));

        $form->addElement($category);

        $options = array('legend' => Zoo::_("Category"));
        $form->addDisplayGroup(array('category'), 'category_select', $options);

        $form->populate(array('category' => $item->pid));
    }
    /**
     * Hook for node save - save parent (category)
     *
     * @param Zend_Form $form
     * @param array $arguments
     */
    public function nodeSave(&$form, &$arguments) {
        $item = array_shift($arguments);
        $arguments = $form->getValues();
        if (isset($arguments['category'])) {
            $item->pid = $arguments['category'];
            $item->save();
        }
    }
}
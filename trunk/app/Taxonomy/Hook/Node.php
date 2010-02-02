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
     * Hook for node display - fetches items in the category
     *
     * @param Zoo_Content_Interface $item
     *
     * @return void
     */
	public function nodeDisplay(&$item) {
		if ($item->type == "taxonomy_category") {
			$items = Zoo::getService ( 'content' )->getContent ( array ('active' => true, 
																		'group' => 'content', 
																		'parent' => $item->id, 
																		'render' => true ) );
			$item->hooks['taxonomy_items'] = $items;
		}
	}

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
        $content_type = Zoo::getService('content')->getType($item->type);
        if ($content_type->group == "category"){
        	/**
        	 * @todo change to look at the content type's group instead of hard-coded types
        	 */
            $category->setLabel('Parent');
            $category->addMultiOption(0, Zoo::_('Root'));
        }
        else {
            $category->setLabel('Category');
        }

        $categories = Zoo::getService('content')->getContent(
                                                    array('group' => 'category',
                                                          'order' => 'title',
                                                          'viewtype' => 'Short',
                                                    	  'hooks' => false),
                                                    0,
                                                    0);

        $tree = new Zoo_Object_Tree($categories, 'id', 'pid');

        $category->addMultiOptions($tree->getIndentedArray(
                                                    'title',
                                                    0,
                                                    " - ",
                                                    (($item->type == "taxonomy_category" && $item->id > 0) ? $item->id : null)));

        $form->addElement($category);
        
        //$parent = new Zoo_Form_Element_Autocomplete('autocompleteparent');
        //$parent->setJQueryParam('source', "/Content/node/autocomplete")
        //       ->setLabel('Parent')->setAttrib('style', 'width: 400px;');
        //$form->addElement($parent);
        

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
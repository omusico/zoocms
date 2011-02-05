<?php
/**
 * @package    Estate
 * @subpackage Hook
 */

/**
 * @package   Estate
 * @subpackage Hook
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Estate_Hook_Node extends Zoo_Hook_Abstract {
    /**
     * Hook for node display - fetches Estate Node information, if the node is an Estate Node
     *
     * @param Zoo_Content_Interface $item
     *
     * @return void
     */
    public function nodeDisplay(&$item) {
        if ($item->type == "estate_node") {
            // Find Estate node extra information
            $factory = new Estate_Node_Factory();
            $estate = $factory->find($item->id)->current();
            $item->hooks['estate_node'] = $estate;
        }
    }

    /**
     * Hook for node listing - fetches Estate Node information
     *
     * @param Zoo_Content_Interface $item
     *
     * @return void
     *
     * @todo Change to fetch all information for all estate nodes in one go
     */
    public function nodeList(&$item) {
        $this->nodeDisplay($item);
    }

    /**
     * Hook for node form - if type is Estate Node, add extra fields
     *
     * @param Zend_Form $form
     * @param array $arguments
     */
    public function nodeForm(Zend_Form &$form, &$arguments) {
        $item =& array_shift($arguments);
        if ($item->type == "estate_node") {
            // Add estate fields
            $price = new Zend_Form_Element_Text('estate_price', array('size' => 10));
            $price->setLabel('Price');
            $price->setRequired(true)->addValidator(new Zend_Validate_Int());

            $area = new Zend_Form_Element_Text('estate_area', array('size' => 5));
            $area->setLabel('Area');
            $area->setRequired(true)->addValidator(new Zend_Validate_StringLength(1,5))->addValidator(new Zend_Validate_Int());

            $rooms = new Zend_Form_Element_Text('estate_rooms', array('size' => 5));
            $rooms->setLabel('Rooms');
            $rooms->setRequired(true)->addValidator(new Zend_Validate_StringLength(1,5))->addValidator(new Zend_Validate_Int());

            $floors = new Zend_Form_Element_Select('estate_floors');
            $floors->setLabel('Floors');
            $floors->addMultiOptions(array(1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '>5'));

            $year = new Zend_Form_Element_Text('estate_year');
            $year->setLabel('Built Year');
            $year->setRequired(true)->addValidator(new Zend_Validate_GreaterThan(1500))->addValidator(new Zend_Validate_Int());

            $ground = new Zend_Form_Element_Text('estate_ground');
            $ground->setLabel('Ground area');
            $ground->setRequired(true)->addValidator(new Zend_Validate_Int());

            $form->addElements(array($price, $area, $rooms, $floors, $year, $ground));

            $options = array('legend' => Zoo::_("Real estate information"));
            $form->addDisplayGroup(array('estate_price', 'estate_area', 'estate_rooms', 'estate_floors', 'estate_year', 'estate_ground'), 'estate_add', $options);

            if ($item->id > 0) {
                // Fetch estate object
                $factory = new Estate_Node_Factory();
                $estate = $factory->find($item->id)->current();
                if (!$estate) {
                    $estate = $factory->createRow();
                }
                $values = $estate->toArray();
                $populate = array();
                foreach ($values as $key => $value) {
                    $populate['estate_'.$key] = $value;
                }
                $form->populate($populate);
            }
        }
    }

    /**
     * Hook for node save - if type is Estate Node, save extra fields
     *
     * @param Zend_Form $form
     * @param array $arguments
     */
    public function nodeSave(Zend_Form $form, &$arguments) {
        $item = array_shift($arguments);
        if ($item->type == "estate_node") {
            $factory = new Estate_Node_Factory();
            // Save estate fields
            $estate = false;
            if ($item->id > 0) {
                // Fetch estate object
                $estate = $factory->find($item->id)->current();
            }
            if (!$estate) {
                $estate = $factory->createRow();
            }

            $arguments = $form->getValues();
            $estate->nid = $item->id;
            $estate->price = $arguments['estate_price'];
            $estate->area = $arguments['estate_area'];
            $estate->rooms = $arguments['estate_rooms'];
            $estate->floors = $arguments['estate_floors'];
            $estate->year = $arguments['estate_year'];
            $estate->ground = $arguments['estate_ground'];
            $estate->save();
        }
    }
}
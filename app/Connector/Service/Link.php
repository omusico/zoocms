<?php
/**
 * Connector_Service_Link
 * @package Connector
 * @subpackage Service
 */

/**
 * @package    Connector
 * @subpackage Service
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Connector_Service_Link extends Zoo_Service
{
    /**
     * Add elements to a form for adding a link to a content node
     *
     * @param Zend_Form $form
     * @param string $label translated text
     * @param string $type link type
     */
    function addLinkFormElement(Zend_Form $form, $label, $type = "link") {
        // Build form element for linking, complete with AJAX code for lookup
        $element = new Zend_Form_Element_Text('connector_link_'.$type.'_txt');
        $element->setLabel($label);

        $hidden = new Zend_Form_Element_Hidden('connector_link_'.$type);

        $form->addElement($element, 'connector_link_'.$type.'_txt');
        $form->addElement($hidden, 'connector_link_'.$type);
    }

    /**
     * Count how many links one or more nodes have of a given type
     *
     * @param array|int $nids
     * @param string $type
     *
     * @return array
     */
    function countLinksByNode($nids, $type = 'link') {
        $ret = array();
        $nids = (array) $nids;

        $factory = new Connector_Link_Factory();
        $select = $factory->select()->from($factory, array('nid', 'COUNT(*)'))
                            ->where('type = ?', $type)
                            ->group('nid');
        $ret = $factory->fetchAll($select);

        return $ret;
    }
}
?>
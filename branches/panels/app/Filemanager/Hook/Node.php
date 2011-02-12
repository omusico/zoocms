<?php

/**
 * @package Filemanager
 * @subpackage Hook
 */

/**
 * @package    Filemanager
 * @subpackage Hook
 * @copyright  Copyright (c) 2008 ZooCMS
 * @version    1.0
 */
class Filemanager_Hook_Node extends Zoo_Hook_Abstract {

  /**
   * Hook for node display - fetches Filemanager Node information, if the node is a Filemanager Node
   *
   * @param Zoo_Content_Interface $item
   *
   * @return void
   */
  public function nodeDisplay(&$item) {
    if ($item->type == "filemanager_file") {
      // Find Filemanager node extra information
      $factory = new Filemanager_File_Factory();
      $file = $factory->find($item->id)->current();
      $item->hooks['filemanager_file'] = $file;
    }
  }

  /**
   * Hook for node listing - fetches Filemanager Node information
   *
   * @param Zoo_Content_Interface $item
   *
   * @return void
   *
   */
  public function nodeList(&$item) {
    $this->nodeDisplay($item);
  }

  /**
   * Hook for short node listing - fetches Filemanager Node information
   *
   * @param Zoo_Content_Interface $item
   *
   * @return void
   *
   */
  public function nodeShort(&$item) {
    $this->nodeDisplay($item);
  }

  /**
   * Hook for node form - if type is Filemanager Node, add extra fields
   *
   * @param Zend_Form $form
   * @param array $arguments
   */
  public function nodeForm(Zend_Form &$form, &$arguments) {
    $item = & array_shift($arguments);
    if (!Zend_Controller_Front::getInstance()->getRequest()->isXmlHttpRequest() && $item->type == "filemanager_file") {
      // Add Filemanager fields
      $form->setAttrib('enctype', 'multipart/form-data');
      $file = new Zend_Form_Element_File('filemanager_file');
      $file->setLabel("Select file")
              ->setDestination(ZfApplication::$_data_path . DIRECTORY_SEPARATOR . "files")
              ->setRequired(false);
      $form->addElement($file);

      $fields[] = 'filemanager_file';

      if ($item->id > 0) {
        // Fetch Filemanager object
        $factory = new Filemanager_File_Factory();
        $file_item = $factory->find($item->id)->current();
        if ($file_item) {
          $existing = new Zend_Form_Element_Image('filemanager_image');
          if (substr($file_item->mimetype, 0, 5) == "image") {
            $imageid = $file_item->nid;
          } else {
            $imageid = 0;
          }
          $urlOptions = array('module' => 'filemanager',
              'controller' => 'file',
              'action' => 'show',
              'id' => $imageid);
          $existing->setImage(Zend_Controller_Front::getInstance()
                          ->getRouter()
                          ->assemble($urlOptions, 'default')
          );
          $fields[] = 'filemanager_image';
        }
      }

      $options = array('legend' => Zoo::_("File upload"));
      $form->addDisplayGroup($fields, 'filemanager_fileupload', $options);
    } else {
      // Add content node image selector
    }
  }

  /**
   * Hook for node save - if type is Filemanager Node, save extra fields
   *
   * @param Zend_Form $form
   * @param array $arguments
   */
  public function nodeSave(&$form, &$arguments) {
    $item = array_shift($arguments);
    $arguments = array_shift($arguments);
    if ($item->type == "filemanager_file" && $form->filemanager_file) {
      $factory = new Filemanager_File_Factory();

      // Save Filemanager fields
      if ($item->id > 0) {
        // Fetch Filemanager object
        $file = $factory->find($item->id)->current();
        if (!$file) {
          $file = $factory->createRow();
        }
      } else {
        $file = $factory->createRow();
      }

      if ($form->filemanager_file->isUploaded()) {
        if ($form->filemanager_file->receive()) {
          $file->nid = $item->id;

          // Update physical file
          $location = $form->filemanager_file->getFileName();
          $existing = $file->getPath();
          if (file_exists($existing)) {
            unlink($existing);
          }
          rename($location, $existing);

          // Update database
          $file->mimetype = $factory->getMimetype($_FILES ['filemanager_file'] ['name']);

          $file->size = filesize($file->getPath());
          //                if (substr($file->mimetype, 0, 5) == "image") {
          //                    $file->exif = "";
          /**
           * @todo: Implement exif data read
           */
          //                }
          $file->save();
        } else {
          print "Error receiving the file";
          /**
           * @todo: Change this to exception/debug message
           */
        }
      }
    }
  }

}
<?php
/**
 * @package Default
 * @subpackage Controllers
 *
 */

/**
 * @package Default
 * @subpackage Controllers
 *
 */
class ErrorController extends Zend_Controller_Action
{
    /**
     * Set page title
     *
     */
    public function init() {
        $this->view->assign("pagetitle", Zoo::_("Error"));
    }

    /**
     * Default action - 404 not found
     *
     */
    public function indexAction()
    {
        $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');

        $messages = $this->_helper->getHelper('FlashMessenger')->getMessages();
        $this->view->assign("errormessages", $messages);
    }

    /**
     * Default error page action - show an error message
     *
     */
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        $messages = array();

        switch ((string)$errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');

                $messages[] = Zoo::_("The page you requested was not found.");
                if (ZfApplication::getEnvironment() == "development" || ZfApplication::getEnvironment() == "staging") {
                    $messages[] = $errors->exception->getMessage();
                }
                break;

            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER:
            case 0:
                // application error
                //$messages[] = Zoo::_("An unexpected error occurred with your request. Please try again later.");
                $messages[] = $errors->exception->getMessage();
                if (ZfApplication::getEnvironment() == "development" || ZfApplication::getEnvironment() == "staging") {
                    $trace = $errors->exception->getTrace();
                    foreach (array_keys($trace) as $i) {
                        if ($trace[$i]['args']) {
                            foreach ($trace[$i]['args'] as $index => $arg) {
                                if (is_object($arg)) {
                                    $trace[$i]['args'][$index] = get_class($arg);
                                }
                                elseif (is_array($arg)) {
                                    $trace[$i]['args'][$index] = "array";
                                }
                            }
                        }
                        $trace[$i]['file_short'] = "..".substr($trace[$i]['file'], strrpos(str_replace("\\", DIRECTORY_SEPARATOR, $trace[$i]['file']), DIRECTORY_SEPARATOR));
                    }
                    $this->view->assign('trace', $trace);
                }
                break;

            default:
                // application error
                $this->getResponse()->setRawHeader('HTTP/1.1 '.$errors->type);
                $messages[] = $errors->exception->getMessage();
                break;
        }

        // Clear previous content
        $this->getResponse()->clearBody();

        $this->view->assign('errormessages', $messages);
    }

    /**
     * Set default rendering template
     *
     */
    public function postDispatch() {
        $this->render('error');
    }
}
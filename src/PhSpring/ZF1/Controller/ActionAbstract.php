<?php

namespace PhSpring\ZF1\Controller;

use PhSpring\Annotations\ExceptionHandler;
use PhSpring\Engine\Exceptions\UnAuthorizedException;
use PhSpring\Engine\MethodInvoker;

/**
 * @author lobiferi
 */
abstract class ActionAbstract extends Zend_Controller_Action {

    private $returnedValue;

    public function init() {
        Zend_Controller_Front::getInstance()->setBaseUrl('/');
    }

    /**
     * add the XmlHttpRequest handling to the response
     * @override
     */
    public function postDispatch() {
        if ($this->_helper->viewRenderer->getNoRender()) {
            $this->_response->setBody(Zend_Json::encode($this->returnedValue));
            return true;
        }
        return false;
    }

    /**
     * Called from \Zend_Controller_Action
     * If it can not find the [action]Action method, will be found by action name
     * The extended behaviour will be used by XmlHttpRequest
     * @param type $methodName action method name
     * @param type $args arguments from the context
     * @override
     */
    public function __call($methodName, $args) {
        $method = null;
        if (preg_match('/^(.*?)Action$/', $methodName, $method)) {
            try {
                $this->returnedValue = MethodInvoker::invoke($this, $method[1], $args);
            } catch (BadMethodCallException $exc) {
                parent::__call($methodName, $args);
            } catch (UnAuthorizedException $e) {
                $this->unAuthorizedExceptionHandler($e);
            }
        } else {
            parent::__call($methodName, $args);
        }
    }

    /**
     * @ExceptionHandler("PhSpring\Engine\Exceptions\UnAuthorizedException")
     */
    public function unAuthorizedExceptionHandler() {
        
    }

    public function setReturnedValue($returnedValue) {
        $this->returnedValue = $returnedValue;
    }

}

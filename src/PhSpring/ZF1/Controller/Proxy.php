<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PhSpring\ZF1\Controller;

use BadMethodCallException;
use PhSpring\Engine\Exceptions\UnAuthorizedException;
use PhSpring\Engine\MethodInvoker;
use PhSpring\Engine\RequestMappingHelper;
use Zend_Controller_Request_Abstract;
use Zend_Controller_Response_Abstract;

/**
 * Description of Proxy
 *
 * @author lobiferi
 */
class Proxy extends ActionAbstract {

    private $controller;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array(), $controller = null) {
        parent::__construct($request, $response, $invokeArgs);
        $this->controller = $controller;
    }

    public function dispatch($action) {
        // Notify helpers of action preDispatch state
        $this->_helper->notifyPreDispatch();

        $this->preDispatch();
        if ($this->getRequest()->isDispatched()) {
            if (null === $this->_classMethods) {
                $this->_classMethods = get_class_methods($this);
            }

            // If pre-dispatch hooks introduced a redirect then stop dispatch
            // @see ZF-7496
            if (!($this->getResponse()->isRedirect())) {
                $this->callMethod($action);
            }
            $this->postDispatch();
        }

        // whats actually important here is that this action controller is
        // shutting down, regardless of dispatching; notify the helpers of this
        // state
        $this->_helper->notifyPostDispatch();
    }

    /**
     * Called from \Zend_Controller_Action
     * If it can not find the [action]Action method, will be found by action name
     * The extended behaviour will be used by XmlHttpRequest
     * @param type $methodName action method name
     * @param type $args arguments from the context
     * @override
     */
    public function callMethod($methodName, $args = array()) {
        $method = null;
        preg_match('/^(.*?)Action$/', $methodName, $method);
        try {
            $annotatedMethod = RequestMappingHelper::getMatchingMethod($this->controller);
            if ($annotatedMethod !== null) {
                $this->setReturnedValue(MethodInvoker::invoke($this->controller, $annotatedMethod, $args));
            } else if (method_exists($this->controller, $methodName)) {
                $this->setReturnedValue(MethodInvoker::invoke($this->controller, $methodName, $args));
            } else {
                $this->setReturnedValue(MethodInvoker::invoke($this->controller, $method[1], $args));
            }
        } catch (BadMethodCallException $exc) {
            return parent::__call($methodName, $args);
        } catch (UnAuthorizedException $exc) {
            (new UnAuthorizedExceptionHandler())->run($this, $exc);
        }
    }

}

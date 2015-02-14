<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PhSpring\ZF1\Controller;

use PhSpring\Annotations\Controller;
use PhSpring\Engine\ClassInvoker;
use PhSpring\Reflection\ReflectionClass;
use Zend_Controller_Action;
use Zend_Controller_Action_Interface;
use Zend_Controller_Dispatcher_Exception;
use Zend_Controller_Dispatcher_Standard;
use Zend_Controller_Request_Abstract;
use Zend_Controller_Response_Abstract;

/**
 * Description of Dispatcher
 *
 * @author lobiferi
 */
class Dispatcher extends Zend_Controller_Dispatcher_Standard {

    public function formatClassName($moduleName, $className) {
        return $this->formatModuleName($moduleName) . '\\' . $className;
    }

    public function dispatch(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response) {
        $this->setResponse($response);

        /**
         * Get controller class
         */
        if (!$this->isDispatchable($request)) {
            $controller = $request->getControllerName();
            if (!$this->getParam('useDefaultControllerAlways') && !empty($controller)) {
                require_once 'Zend/Controller/Dispatcher/Exception.php';
                throw new Zend_Controller_Dispatcher_Exception('Invalid controller specified (' . $request->getControllerName() . ')');
            }

            $className = $this->getDefaultControllerClass($request);
        } else {
            $className = $this->getControllerClass($request);
            if (!$className) {
                $className = $this->getDefaultControllerClass($request);
            }
        }

        /**
         * Load the controller class file
         */
        $className = $this->loadClass($className);

        /**
         * Instantiate controller with request, response, and invocation
         * arguments; throw exception if it's not an action controller
         */
        $controller = ClassInvoker::getNewInstance($className, array($request, $this->getResponse(), $this->getParams()));
        if (!($controller instanceof Zend_Controller_Action_Interface) &&
                !($controller instanceof Zend_Controller_Action) && !(new ReflectionClass($className))->hasAnnotation(Controller::class)) {
            throw new Zend_Controller_Dispatcher_Exception(
            'Controller "' . $className . '" is not an instance of Zend_Controller_Action_Interface'
            );
        }

        /**
         * Retrieve the action name
         */
        $action = $this->getActionMethod($request);

        /**
         * Dispatch the method call 
         */
        $request->setDispatched(true);

        // by default, buffer output
        $disableOb = $this->getParam('disableOutputBuffering');
        $obLevel = ob_get_level();
        if (empty($disableOb)) {
            ob_start();
        }

        try {
            if (!($controller instanceof Zend_Controller_Action_Interface) &&
                    !($controller instanceof Zend_Controller_Action)) {
                (new Proxy($request, $this->getResponse(), $this->getParams(), $controller))->dispatch($action);
            } else {
                $controller->dispatch($action);
            }
        } catch (Exception $e) {
            // Clean output buffer on error
            $curObLevel = ob_get_level();
            if ($curObLevel > $obLevel) {
                do {
                    ob_get_clean();
                    $curObLevel = ob_get_level();
                } while ($curObLevel > $obLevel);
            }
            throw $e;
        }

        if (empty($disableOb)) {
            $content = ob_get_clean();
            $response->appendBody($content);
        }

        // Destroy the page controller instance and reflection objects
        $controller = null;
    }

}

<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PhSpring\ZF1\Application;

use PhSpring\Engine\InvokerConfig;
use PhSpring\ZF1\Application\Resource\ServiceLoader;
use PhSpring\ZF1\Controller\Dispatcher;
use PhSpring\ZF1\Controller\Request;
use PhSpring\ZF1\Controller\Response;
use Zend_Config;
use Zend_Controller_Action_HelperBroker;
use Zend_Controller_Front;
use Zend_Registry;

/**
 * Description of Bootstrap
 *
 * @author lobiferi
 */
class Bootstrap extends \Zend_Application_Bootstrap_Bootstrap {

    public function __construct($application) {
        parent::__construct($application);
        Zend_Controller_Front::getInstance()->setDispatcher(new Dispatcher());
        $this->config = new Zend_Config($this->getOptions());
        Zend_Registry::set('Zend_Config', $this->config);
        Zend_Registry::set('config', $this->config);
        new ServiceLoader($this->getOptions());
    }

    protected function _initInvokerConfig() {
        $request = new Request();
        Zend_Controller_Front::getInstance()->setRequest($request);
        InvokerConfig::setRequestHelper($request);
        $response = new Response(Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer'));
        InvokerConfig::setResponseHelper($response);
        Zend_Controller_Front::getInstance()->setResponse($response);
    }

}

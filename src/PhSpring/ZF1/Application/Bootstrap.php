<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PhSpring\ZF1\Application;

/**
 * Description of Bootstrap
 *
 * @author lobiferi
 */
class Bootstrap {

    protected function _initInvokerConfig() {
        $request = new Request();
        Zend_Controller_Front::getInstance()->setRequest($request);
        InvokerConfig::setRequestHelper($request);
        $response = new Response(Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer'));
        InvokerConfig::setResponseHelper($response);
        Zend_Controller_Front::getInstance()->setResponse($response);
    }

}

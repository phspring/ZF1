<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PhSpring\ZF1\Application\Module;

use Zend_Application_Module_Bootstrap;
use Zend_Controller_Action_HelperBroker;
use Zend_Loader_Autoloader;
use Zend_View_Smarty;

/**
 * Description of Bootstrap
 *
 * @author lobiferi
 */
class Bootstrap extends Zend_Application_Module_Bootstrap {

    protected function _initView() {
        /* @var $view Zend_View_Smarty */
        $view = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer')->view;
        $view->addScriptPath(APPLICATION_PATH . '/layouts/' . strtolower((string) $view->getAssignedVars('THEME')) . '/' . strtolower($this->getModuleName()));
    }

    protected function _initAutoloader() {
        $loader = function($className) {
            $className = str_replace('\\', '_', $className);
            Zend_Loader_Autoloader::autoload($className);
        };

        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->pushAutoloader($loader, preg_split('/_/', get_class($this))[0] . '\\');
    }

}

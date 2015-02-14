<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PhSpring\ZF1\Controller;

use PhSpring\Engine\IResponseHelper;
use Zend_Controller_Action_Helper_ViewRenderer;
use Zend_Controller_Response_Http;
use Zend_Layout;

/**
 * Description of Response
 *
 * @author lobiferi
 */
class Response extends Zend_Controller_Response_Http implements IResponseHelper {

    private $render;

    public function __construct(Zend_Controller_Action_Helper_ViewRenderer $render) {
        $this->render = $render;
    }

    public function setHeader($name, $value, $replace = true) {
        parent::setHeader($name, $value, $replace);
        return $this;
    }

    public function setNoRender() {
        $this->render->setNoRender();
        Zend_Layout::getMvcInstance()->disableLayout();
    }

}

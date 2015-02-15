<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PhSpring\ZF1\Config;

use PhSpring\Annotations\Component;
use PhSpring\Config\IConfig;
use Zend_Registry;

/**
 * Description of ZendConfigAdapter
 *
 * @author lobiferi
 * @Component
 */
class ConfigAdapter implements IConfig{
    private $resource;

    public function __construct() {
        $this->resource = Zend_Registry::get('config');
    }
    public function __get($name) {
        if(is_array($this->resource)){
            return $this->resource[$name];
        }
        return $this->resource->{$name};
    }
    
}

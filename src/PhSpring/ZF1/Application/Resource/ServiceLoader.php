<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PhSpring\ZF1\Application\Resource;

use Doctrine\Common\Annotations\AnnotationReader;
use PhSpring\Annotation\Helper;
use PhSpring\Engine\BeanFactory;
use PhSpring\Engine\Config;
use PhSpring\Engine\ExtendedCachedReader;
use ReflectionClass;
use RuntimeException;

/**
 * Description of Service
 *
 * @author lobiferi
 */
class ServiceLoader {

    /**
     *
     * @var BeanFactory
     */
    private $beanFactory;

    public function __construct($options) {
        $this->beanFactory = BeanFactory::getInstance();
        if (array_key_exists('beanFactory', $options) && array_key_exists('autoload', $options['beanFactory'])) {
            BeanFactory::setAutoLoadSupport(!!$options['beanFactory']['autoload']);
        }
        if (array_key_exists('annotations', $options)) {
            $this->setAnnotationSupport($options['annotations']);
        }
        if (array_key_exists('services', $options)) {
            $this->addServices($options['services']);
        }
    }

    private function setAnnotationSupport($options) {
        if (array_key_exists('namespaces', $options)) {
            $this->setAnnotationNamespaces($options['namespaces']);
        }
        if (array_key_exists('reader', $options)) {
            $this->setAnnotationReader($options['reader']);
        }
    }

    private function setAnnotationNamespaces($options) {
        foreach ($options as $namespace) {
            if (is_string($namespace)) {
                $namespace = array('ns' => $namespace);
            }
            $namespace = array_merge(array('path' => LIB_PATH), $namespace);
            Config::addAnnotationNamespace($namespace['ns'], $namespace['path']);
            Helper::addAnnotationHandlerNamespace($namespace['ns'] . '\Handler');
        }
    }

    private function setAnnotationReader($options) {
        $reader = new AnnotationReader();
        if (array_key_exists('cache', $options)) {
            $reader = $this->getCachedAnnotationNamespaceReader($options['cache'], $reader);
        }
        Helper::setHelper($reader);
    }

    private function getCachedAnnotationNamespaceReader(array $options, AnnotationReader $reader) {
        $debug = array_key_exists('debug', $options) ? !!$options['debug'] : false;
        return new ExtendedCachedReader(
                $reader, $this->getCacheInstance($options), $debug
        );
    }

    private function getCacheInstance($options) {
        $cacheClass = $options['type'];
        if (!class_exists($cacheClass)) {
            $cacheClass = 'Doctrine\Common\Cache\\' . $cacheClass;
            if (!class_exists($cacheClass)) {
                throw new RuntimeException('The cache driver is not found - ' . $options['type']);
            }
        }

        $reflClass = new ReflectionClass($cacheClass);
        $constructor = $reflClass->getConstructor();
        $invokeParams = array();
        if ($constructor) {
            foreach ($constructor->getParameters() as $param) {
                if (array_key_exists($param->getName(), $options)) {
                    $invokeParams[$param->getName()] = $options[$param->getName()];
                }
            }
        }
        return $reflClass->newInstanceArgs($invokeParams);
    }

    private function addServices($services) {
        foreach ($services as $service) {
            $name = null;
            if (is_array($service)) {
                $name = $service['name'];
                $service = $service['class'];
            }
            try {
                $this->beanFactory->addBeanClass($service, $name);
            } catch (\Exception $e) {
                var_dump($service, $name);
                echo $e->getTraceAsString();
                die();
            }
        }
    }

}

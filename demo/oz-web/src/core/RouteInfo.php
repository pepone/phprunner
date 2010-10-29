<?php

// *****************************************************************************
//
// Copyright (c) 2010 José Manuel, Gutiérrez de la Concha. All rights reserved.
//
// This copy of oz-web is licensed to you under the terms described
// in the LICENSE file included in this distribution.
//
// email: pepone.onrez@gmail.com
//
// *****************************************************************************

/**
 * Esta excepcion la utilizamos para indicar que el recurso al que
 * se trata de acceder no existe.
 */
class ResourceNotExistsException extends SystemException
{
    public $name;

    function __construct($name)
    {
        parent::__construct("Resource: `" . $name . "' doesn't exists on this system.");
        $this->name = $name;
    }
}

/**
 *
 * Esta clase se utilizara para mapear las urls a controladores en
 * funcion del recurso al que se accede.
 *
 * Las reglas de este mapeado son muy sencillas
 *
 * Cada recurso tiene un controlador y una acion
 * El recurso se especifica con el parametro "r" de la ulr.
 *
 * Ejemplo:
 *
 * Url: http://localhost/index.php?r=default/index
 *
 * El nombre del controlador es "default" esto intenta cargar
 * la clase "DefaultPageController" desde el fichero
 * "src/controller/public/DefaultPageController.php"
 * 
 * El nombre de la acion es "index" y se mapea al metodo "indexAction"
 * de la clase "DefaultPageController"
 */
class RouteInfo
{
    private $_controller;
    private $_action;

    public function __construct($controller, $action)
    {
        $this->_controller = $controller;
        $this->_action = $action;
    }

    public function controller()
    {
        return $this->_controller;
    }

    public function action()
    {
        return $this->_action;
    }

    public function resource()
    {
        return RouteInfo::r($this->_controller, $this->_action);
    }

    /**
     *
     * @param string $resource el recurso, se corresponde con el parametro "r"
     * de la URL, debe ser del tipo <controller-name>/<action-name>
     * Ejemplo: "default/index"
     *
     * @return RouteInfo devuelve un objeto RouteInfo con la informacion de la
     * ruta del recurso.
     */
    public static function parse($resource)
    {
        // Ensure resource name has not '.' to avoid relative
        // name issues in the controller path.

        if(strpos($resource, '.') === true)
        {
            throw new ResourceNotExistsException($resource);
        }
        $tokens = explode('/', $resource);
        if(count($tokens) < 2)
        {
            throw new ResourceNotExistsException($resource);
        }
        $controller_action =  array_pop($tokens);
        $controller_route = implode('/', $tokens);
        return new RouteInfo($controller_route, $controller_action);
    }

    private static function r($controller, $action)
    {
        return $controller . '/' . $action;
    }
}

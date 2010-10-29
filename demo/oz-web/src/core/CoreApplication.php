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

require_once(dirname(__FILE__) . '/../../Init.php');
require_once(OZ_PATH. '/src/core/Exceptions.php');
require_once(OZ_PATH. '/src/core/Request.php');
require_once(OZ_PATH. '/src/core/Response.php');
require_once(OZ_PATH. '/src/core/RouteInfo.php');
require_once(OZ_PATH. '/src/core/View.php');
require_once(OZ_PATH. '/src/core/Html.php');

class CoreApplication
{
    private static $_app;

    protected $_ice;
    protected $_runner;
    protected $_lastException;
    protected $_route;
    protected $_contentType;
    protected $_requestController;
    protected $_controllerBasePath;
    protected $_defaultRoute;
    protected $_resourceNotExistsRoute;
    protected $_authDeniedRoute;
    protected $_controllers;
    protected $_loginRedirectURL;
    protected $_logoutRedirectURL;

    function __construct($config = array())
    {
        self::$_app = $this;
        $this->_lastException = null;
        $this->_route = null;
        $this->_contentType = "text/html";
        $this->_requestController = null;

        $this->_controllerBasePath = ArrayUtil::getWithDefault($config, 'controllerBasePath', 'public/');
        $this->_defaultRoute = ArrayUtil::getWithDefault($config, 'defaultRoute', 'default/index');
        $this->_resourceNotExistsRoute = ArrayUtil::getWithDefault($config, 'resourceNotExistsRoute');
        $this->_authDeniedRoute = ArrayUtil::getWithDefault($config, 'authDeniedRoute');
        
        $this->_controllers = ArrayUtil::getWithDefault($config, 'controllers', array());
        $this->_loginRedirectURL = ArrayUtil::getWithDefault($config, 'loginRedirectURL', '/');
        $this->_logoutRedirectURL = ArrayUtil::getWithDefault($config, 'logoutRedirectURL', '/');

        $this->_templatePath = ArrayUtil::getWithDefault($config, 'templatePath', '/src/template');
    }

    static public function app()
    {
        if(!self::$_app)
        {
            self::$_app = new CoreApplication();
        }
        return self::$_app;
    }

    public function contentType()
    {
        return $this->_contentType;
    }

    public function  setContentType($contentType)
    {
        $this->_contentType = $contentType;
    }

    public function route()
    {
        return $this->_route;
    }

    public function resource()
    {
        return $this->_route->resource();
    }

    public function requestController()
    {
        return $this->_requestController;
    }

    public function setLastException($ex)
    {
        $this->_exception = $ex;
    }

    public function lastException()
    {
        return $this->_exception;
    }

    public function templatePath()
    {
        return OZ_PATH . $this->_templatePath;
    }

    public function createUrl($resource = '', $args = array(), $anchor = '', $relative = true)
    {
        if($resource == '')
        {
            $resource = $this->_defaultRoute;
        }
        
        $url = '';
        if(!$relative)
        {
            $url .= "http://" . Request::hostName();
            if(Request::port() != 80)
            {
                $url .= ":" . Request::port();
            }
        }
        
        $url .= '/index.php';

        if($resource != '')
        {
            $url .= '?r=' . $resource;
        }
        if(count($args) > 0)
        {
            foreach($args as $key => $value)
            {
                $url .= '&' . $key . '=' . $value;
            }
        }
        if($anchor != '')
        {
            $url .= $anchor;
        }
        return $url;
    }

    public function createFriendlyUrl($resource = '/', $args = array(), $relative = true)
    {
        $url = '';
        if(!$relative)
        {
            $url .= "http://" . Request::hostName();
            if(Request::port() != 80)
            {
                $url .= ":" . Request::port();
            }
        }

        if(!StringUtil::beginsWith($resource, '/'))
        {
            $url .= '/';
        }
        $url .= $resource;

        if(count($args) > 0)
        {
            $i = 0;
            foreach($args as $key => $value)
            {
                if($i == 0)
                {
                    $url .= '?';
                }
                else
                {
                    $url .= '&';
                }
                $url .= $key . '=' . $value;
                $i++;
            }
        }
        return $url;
    }

    public function executeController($controllerName = 'site', $actionName = 'index', $controllerPrefix = '')
    {
        $tokens = explode('/', $controllerName);
        $controllerRoute = '';
        if(count($tokens) > 1)
        {
            $controllerClassName =  array_pop($tokens);
            $controllerRoute = implode('/', $tokens);
        }
        else
        {
            $controllerClassName =  $controllerName;
        }
        $controllerClassName = ucfirst($controllerClassName);

        if(!in_array($controllerClassName, $this->_controllers))
        {
            error_log('Class Name not found: ' . $controllerClassName);
            throw new ResourceNotExistsException($this->route()->resource());
        }
        // TODO: Move the Classname to core/RouteInfo

        $file = OZ_PATH . '/src/controller/' . $controllerPrefix;
        if($controllerPrefix != '')
        {
            $file .= $controllerRoute . '/';
        }
        $file .= $controllerClassName . '.php';

        if(!file_exists($file) || !is_file($file))
        {
            throw new ResourceNotExistsException('/controller/' . $controllerPrefix . $controllerName . '.php');
        }

        require_once($file);

        $controller = new  $controllerClassName();

        if(!method_exists($controller, $actionName))
        {
            throw new ResourceNotExistsException($this->route()->resource());
        }

        $this->_requestController = $controller;
        $controller->beforeExecute();
        $response = $controller->processRequest($actionName);
        return $controller->afterExecute($response);
    }

    /**
     *
     * Punto de entrada de la aplicacion.
     *
     **/
    public function main()
    {
        try
        {
            try
            {
                if(method_exists($this, 'beforeExecuteController'))
                {
                    $this->beforeExecuteController();
                }
                $uri = Request::uri();

                $this->_route = RouteInfo::parse(Request::get('r', $this->_defaultRoute));

                $response = $this->executeController($this->_route->controller() . 'PageController',
                                                     $this->_route->action() . 'Action',
                                                     $this->_controllerBasePath);
                header('HTTP/1.1 200 OK');
                header('Content-type: ' . $this->contentType() . '; charset=utf-8');
                header('Content-Length: ' . strlen($response));
                echo $response;
            }
            catch(ResourceNotExistsException $ex)
            {
                $this->setLastException($ex);
                $route = RouteInfo::parse($this->_resourceNotExistsRoute);
                $response = $this->executeController($route->controller() . 'PageController',
                    $route->action() . 'Action', $this->_controllerBasePath);
                
                header('HTTP/1.1 404 Not Found');
                header('Content-type: text/html; charset=utf-8');
                header('Content-Length: ' . strlen($response));
                echo $response;
            }
            catch(AuthDeniedException $ex)
            {
                $this->setLastException($ex);
                $route = RouteInfo::parse($this->_authDeniedRoute);
                $response = $this->executeController($route->controller() . 'PageController',
                                                     $route->action() . 'Action',
                                                     $this->_controllerBasePath);
                header('HTTP/1.1 403 Forbidden');
                header('Content-type: text/html; charset=utf-8');
                header('Content-Length: ' . strlen($response));
                echo $response;
            }
        }
        catch(Exception $ex)
        {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-type: text/html; charset=utf-8');
            echo '<h2>Internal Server Error</h2>';
            error_log($ex->getMessage());
        }
        exit(0);
    }
}

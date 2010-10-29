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

class SystemException extends Exception
{
    public $args;

    function __construct($message, $args = array())
    {
        $this->message = $message;
    }
}

class ArgumentException extends SystemException
{
    function __construct($msg)
    {
        parent::__construct($msg);
    }
}

class AuthDeniedException extends SystemException
{
    function __construct()
    {
        parent::__construct("Auth denied.");
    }
}

class ClassNotExistsException extends SystemException
{
    public $name;

    function __construct($name)
    {
        parent::__construct("class: `" . $name . "' not exists.");
        $this->name = $name;
    }
}

class ControllerActionNotExistsException extends SystemException
{
    public $controller;
    public $action;

    function __construct($controller, $action)
    {
        parent::__construct("Controller: `" . $controller . "' hasn't action: `" . $action . "'");
        $this->controller = $controller;
        $this->action = $action;
    }
}


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

class Controller
{
    protected $_model;
    
    public function __construct($model = array())
    {
        $this->_model = $model;
    }

    public function model()
    {
        return $this->_model;
    }
    
    public function beforeExecute()
    {
    }

    public function afterExecute($response)
    {
        return $response;
    }

    public function processRequest($action)
    {
        return $this->$action();
    }
}


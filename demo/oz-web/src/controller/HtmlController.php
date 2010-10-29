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

require_once (OZ_PATH . '/src/controller/Controller.php');
require_once (OZ_PATH . '/src/core/HtmlView.php');
require_once (OZ_PATH . '/src/core/HtmlModel.php');

class HtmlController extends Controller
{
    public function __construct()
    {
        parent::__construct(new HtmlModel());
        $this->model()->setTitle("");
    }

    public function processRequest($action)
    {
        if(!$this->model())
        {
            throw new ArgumentException('model should not be null');
        }
        $this->model()->setMainBody(parent::processRequest($action));
        $view = new HtmlView($this->model());
        return $view->render();
    }
}

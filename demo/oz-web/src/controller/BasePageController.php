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

require_once (OZ_PATH . '/src/controller/HtmlController.php');

class BasePageController extends HtmlController
{
    protected $_mainView;

    public function  __construct()
    {
        parent::__construct();
        $this->_mainView = null;
    }
}

class BaseAdminPageController extends BasePageController
{
    public function  __construct() {
        parent::__construct();
    }
    
    public function  beforeExecute() {
        $this->checkIsAdmin();
        parent::beforeExecute();
    }
}

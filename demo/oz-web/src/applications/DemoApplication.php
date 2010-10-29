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

require_once(OZ_PATH. '/src/core/CoreApplication.php');
require_once(OZ_PATH. '/src/core/CoreSession.php');

class DemoApplication extends CoreApplication
{
    protected $_session = null;
    
    public function  __construct()
    {
        parent::__construct(array(
            'controllers' => array(
                'PhpRunnerPageController',
                'DefaultPageController'
            ),
            'controllerBasePath' => 'public/',
            'defaultRoute' => 'default/index',
            'authDeniedRoute' => 'default/authDenied',
            'resourceNotExistsRoute' => 'default/resourceNotExists',
            'templatePath' => '/src/template/demo/'));
    }
}

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

require_once (OZ_PATH . '/src/controller/BasePageController.php');

class DefaultPageController extends BasePageController
{
    public function  __construct()
    {
        parent::__construct();
    }

    public function indexAction()
    {
        $app = CoreApplication::app();
        $model = $app->requestController()->model();
        $model->setTitle('PhpRunner DEMO');

        $model->registerCss("/resources/css/reset.css");
        $model->registerCss("/resources/css/table.css");
        $model->registerCss("/resources/themes/redmon/jquery-ui-1.8.4.custom.css");
        $model->registerCss('/resources/css/layout.css');
        
        $model->registerScript("/resources/js/jquery/jquery.min.js");
        $model->registerScript("/resources/js/jsclass/min/core.js");
        $model->registerScript("/resources/js/jsclass/min/hash.js");
        $model->registerScript("/resources/js/jsclass/min/enumerable.js");
        $model->registerScript("/resources/js/jsclass/min/package.js");
        $model->registerScript("/resources/js/jquery-ui-1.8.4.custom.min.js");
        $model->registerScript('/resources/js/jquery.uuid.js');
        $model->registerScript('/resources/js/jquery.timers.js');
        $model->registerScript("/resources/js/Proxy.js");
        $model->registerScript("/resources/js/Table.js");
        $model->registerScript("/resources/js/ObjectDialog.js");
        

        $mainView = new View('DefaultPageLayout.php');
        $phpRunner = new View('PhpRunnerView.php');
        $mainView->addChild('FrameMid', $phpRunner);
        
        return  $mainView->render();
    }

    public function resourceNotExistsAction()
    {
        $mainView = new View('DefaultPageLayout.php');
        $app = CoreApplication::app();

        $name = $app->lastException()->name;
        $model = $app->requestController()->model();
        $model->setTitle('Pagina No Encontrada "' . $name);

        $notFoundView = new View('PageNotFound.php');
        $notFoundView->set('pagename', $name);
        $mainView->addChild('FrameMid', $notFoundView);
        return  $mainView->render();
    }

    public function authDeniedAction()
    {
        $mainView = new View('DefaultPageLayout.php');
        $app = CoreApplication::app();

        $model = $app->requestController()->model();
        $model->setTitle('Acceso Denegado');

        $accessDeniedView = new View('AccessDenied.php');

        $mainView->addChild('FrameMid', $accessDeniedView);
        return  $mainView->render();
    }

    public function logoutAction()
    {
        CoreApplication::app()->session()->logout();
    }
}

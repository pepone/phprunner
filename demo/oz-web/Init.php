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

//
// oz-web sources path, should point to oz-web root directory
//
DEFINE ('OZ_PATH', dirname(__FILE__));

//
// Web server document root.
//
DEFINE ('ONLINE_PATH', dirname(__FILE__) . '/online');

//
// Ice PHP include path.
//
DEFINE ('ICE_PATH', '/opt/Ice-3.4.1/php');


//
// PhpRunner Ice generated path
//
DEFINE ('PHP_RUNNER_SLICE_PATH', OZ_PATH . '/../../src/slice');

//
// PhpRunner Server Endpoints
//
DEFINE ('PHP_RUNNER_ENDPOINTS', 'PhpRunner:tcp -h 127.0.0.1 -p 10001 -t 2000');

//
// TODO move to specific controller so that Ice sources are only included
// when required.
//
$includePath = get_include_path();
if($includePath != '')
{
    $includePath .= PATH_SEPARATOR;
}
$includePath .= ICE_PATH;

set_include_path($includePath);

require_once('Ice.php');
require_once(PHP_RUNNER_SLICE_PATH . '/PhpRunner.php');

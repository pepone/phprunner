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

class Response
{
    public static function setCookie($name, $value, $expired, $hostname, $secure)
    {
        setcookie($name, $value, $expired, $hostname, $secure);
    }

    public static function redirect($location, $code = '302 Found', $exit = true)
    {
        header("HTTP/1.1 " . $code);
        header("Location: " . $location);
        if($exit)
        {
            exit(0);
        }
    }

    public static function setCookieAndRedirect($url, $name, $value, $expired, $hostname, $secure)
    {
        Response::setCookie($name, $value, $expired, $hostname, $secure);
        Response::redirect($url);
    }
}

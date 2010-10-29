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

require_once(OZ_PATH. '/src/core/ArrayUtil.php');

class Request
{
    static public function dumpPOST()
    {
        ob_start();
        print_r($_POST);
        return ob_get_clean();
    }
    static public function get($var, $default = '')
    {
        return Request::cleanInput(ArrayUtil::getWithDefault($_GET, $var, $default));
    }
    
    static public function getAsArray($var)
    {
        $val = Request::get($var);
        $val = trim($val);
        if($val == '')
        {
            return array();
        }
        if(!StringUtil::beginsWith($val, '('))
        {
            return array();
        }
        $val = substr($val, 1, strlen($val) - 1); // left (

        if(!StringUtil::endsWith($val, ')'))
        {
            return array();
        }
        $val = substr($val, 0, strlen($val) - 1); // right )

        $values = array();
        $tokens = explode(',', $val);

        foreach($tokens as $token)
        {
            $token = trim($token);
            if($token == '')
            {
                continue;
            }
            $values[] = $token;
        }
        return $values;
    }

    static public function tmpFile($name = 'file')
    {
        if(!isset($_FILES[$name]))
        {
            return '';
        }
        return ArrayUtil::getWithDefault($_FILES[$name], 'tmp_name');
    }

    static public function filename($name = 'file')
    {
        if(!isset($_FILES[$name]))
        {
            return '';
        }
        return ArrayUtil::getWithDefault($_FILES[$name], 'name');
    }

    static public function post($var, $default = '')
    {
        return Request::cleanInput(ArrayUtil::getWithDefault($_POST, $var, $default));
    }

    static public function cookie($name)
    {
        return ArrayUtil::getWithDefault($_COOKIE, $name);
    }

    private static function cleanInput($input)
    {
        if(is_array($input))
        {
            $items = array();
            foreach($input as $key => $value)
            {
                $items[stripslashes($key)] = Request::cleanInput($value);
            }
            return $items;
        }
        return stripslashes($input);
    }

    public static function remoteAddress()
    {
        return ArrayUtil::getWithDefault($_SERVER, 'REMOTE_ADDR');
    }

    public static function userAgent()
    {
        return ArrayUtil::getWithDefault($_SERVER, 'HTTP_USER_AGENT');
    }

    public static function referer()
    {
        return ArrayUtil::getWithDefault($_SERVER, 'HTTP_REFERER');
    }

    public static function methodName()
    {
        return ArrayUtil::getWithDefault($_SERVER, 'REQUEST_METHOD');
    }

    public static function port()
    {
        return ArrayUtil::getWithDefault($_SERVER, 'SERVER_PORT');
    }

    public static function hostName($level = 0)
    {
        $hostname = ArrayUtil::getWithDefault($_SERVER, 'HTTP_HOST');
        if($level <= 0)
        {
            return $hostname;
        }
        $tokens = array_reverse(explode('.', $hostname));
        $i = 0;
        $hostname = '';
        foreach($tokens as $token)
        {
            if($i >= $level)
            {
                break;
            }
            if($i > 0)
            {
                $hostname = $token . '.' . $hostname;
            }
            else
            {
                $hostname = $token;
            }
            $i++;
        }
        return $hostname;
    }

    public static function protocol()
    {
        return ArrayUtil::getWithDefault($_SERVER, 'SERVER_PROTOCOL');
    }

    public static function scriptName()
    {
        return ArrayUtil::getWithDefault($_SERVER, 'SCRIPT_NAME');
    }

    public static function isPOST()
    {
        return Request::methodName() == 'POST';
    }

    public static function isGET()
    {
        return Request::methodName() == 'GET';
    }

    public static function isHEAD()
    {
        return Request::methodName() == 'HEAD';
    }

    public static function isPUT()
    {
        return Request::methodName() == 'PUT';
    }

    public static function isDELETE()
    {
        return Request::methodName() == 'DELETE';
    }

    public static function protocolName()
    {
        $protocol = Request::protocol();
        return substr($protocol, 0, strpos($protocol, '/', 0));
    }

    public static function protocolVersion()
    {
        $protocol = Request::protocol();
        $pos = strpos($protocol, '/', 0);
        return substr($protocol, $pos + 1, strlen($protocol) - $pos - 1);
    }

    public static function uri()
    {
        $uri = ArrayUtil::getWithDefault($_SERVER, 'REQUEST_URI');
        $pos =  strpos($uri, '?', 0);
        if($pos > 0)
        {
            $uri = substr($uri, 0, $pos);
        }
        return $uri;
    }

    public static function queryString()
    {
        $uri = ArrayUtil::getWithDefault($_SERVER, 'REQUEST_URI');
        $pos =  strpos($uri, '?', 0);
        if(!$pos)
        {
            return "";
        }
        return substr($uri, $pos + 1, strlen($uri) - $pos - 1);
    }
    
    public static function url($relative = true)
    {
        $url =  '';
        if(!$relative)
        {
            $url .= strtolower(Request::protocolName()) . "://" . Request::hostName();
        }
        $url .= Request::uri();
        $queryString = Request::queryString();
        if(strlen($queryString) > 0)
        {
            $url .= "?" . $queryString;
        }
        return $url;
    }
}

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

class StringUtil
{
    public static function beginsWith($string, $findme)
    {
        return strpos($string, $findme) === 0;
    }

    public static function lowerFirst($str, $n = 1)
    {
        if(strlen($str) <= $n)
        {
            return strtolower($str);
        }
        return strtolower(substr($str, 0, $n)) . substr($str, $n, strlen($str) - $n);
    }

    public static function endsWith($string, $findme)
    {
        return (strlen($string) >= strlen($findme) &&
                $findme == substr($string, strlen($string) - strlen($findme)));
    }

    public static function hasToken($str, $delimiter, $findme)
    {
        $tokens = explode($delimiter, $string);
        return in_array($findme, $tokens);
    }

    public static function isUpperCase($str)
    {
        return strtoupper($str) == $str;
    }

    public static function isLowerCase($str)
    {
        return strtolower($str) == $str;
    }

    public static function csvArray($values, $delimiter = ",")
    {
        if(!is_array($values))
        {
            throw new ArgumentException('$values should be an array');
        }
        $csv = "";
        $size = count($values);
        $i = 0;
        foreach($values as $value)
        {
            $csv .= $value;
            if($i < ($size - 1))
            {
                $csv .= $delimiter;
            }
            $i++;
        }
        return $csv;
    }
}
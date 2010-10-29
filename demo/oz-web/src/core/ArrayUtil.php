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

class ArrayUtil
{
    public static function getWithDefault($array, $key, $default = '')
    {
        if(!is_array($array))
        {
            throw new ArgumentException('$array should be an array.');
        }
        if(!is_string($key))
        {
            throw new ArgumentException('$key should be a string.');
        }
        if(isset($array[$key]))
        {
            return $array[$key];
        }
        return $default;
    }
}

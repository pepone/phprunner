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

class Model
{
    protected $_attributes;

    public function __construct($attributes = array())
    {
        $this->_attributes = $attributes;
    }

    public function attributes()
    {
        return $this->_attributes;
    }

    public function setAttribute($name, $value)
    {
        $this->_attributes[$name] = $value;
    }

    public function attribute($name, $default = '')
    {
        if(array_key_exists($name, $this->_attributes))
        {
            return $this->_attributes[$name];
        }
        return $default;
    }

    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->_attributes);
    }
}
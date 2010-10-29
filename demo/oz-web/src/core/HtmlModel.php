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

class HtmlModel
{
    protected $_shortcutIcon;
    protected $_title;
    protected $_description;
    protected $_keywords;
    protected $_scriptIncludes;
    protected $_cssIncludes;
    protected $_scripts;
    protected $_mainBody;
    protected $_openGraphProperties;

    public function __construct()
    {
        $this->_shortcutIcon = '';
        $this->_title = 'Set page title';
        $this->_description = '';
        $this->_keywords = array();
        $this->_scripts = array();
        $this->_scriptIncludes = array();
        $this->_cssIncludes = array();
        $this->_mainBody = '';
        $this->_metaTags = array();
        $this->_openGraphProperties = array();
    }

    public function addMetaTag($name, $value)
    {
        $this->_metaTags[$name] = $value;
    }

    public function metaTags()
    {
        return $this->_metaTags;
    }

    public function addOpenGraphProperty($name, $value)
    {
        $this->_openGraphProperties[$name] = $value;
    }

    public function openGraphProperties()
    {
        return $this->_openGraphProperties;
    }

    public function setShortcutIcon($shortcutIcon)
    {
        $this->_shortcutIcon = $shortcutIcon;
    }

    public function shortcutIcon()
    {
        return $this->_shortcutIcon;
    }
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    public function title()
    {
        return $this->_title;
    }

    public function setDescription($description)
    {
        $this->_description = $description;
    }

    public function description()
    {
        return $this->_description;
    }

    public function setKeywords($keywords)
    {
        $tokens = explode(',', $keywords);
        $this->_keywords = array();
        $this->addKeywords($tokens);
    }

    public function addKeyword($k)
    {
        if(!in_array($k, $this->_keywords))
        {
            $this->_keywords[] = $k;
        }
    }

    public function addKeywords($keywords)
    {
        foreach($keywords as $k)
        {
            $this->addKeyword($k);
        }
    }

    public function keywords()
    {
        return $this->_keywords;
    }

    public function registerScript($name, $value = '')
    {
        $value = trim($value);
        if($value != '')
        {
            if(!array_key_exists($name, $this->_scripts))
            {

                $this->_scripts[$name] = $value;
            }
        }
        else
        {
            if(!in_array($name, $this->_scriptIncludes))
            {
                $this->_scriptIncludes[] = $name;
            }
        }
    }

    public function scripts()
    {
        return $this->_scripts;
    }

    public function scriptIncludes()
    {
        return $this->_scriptIncludes;
    }

    public function registerCss($href)
    {
        if(!in_array($href, $this->_cssIncludes))
        {
            $this->_cssIncludes[] = $href;
        }
    }

    public function cssIncludes()
    {
        return $this->_cssIncludes;
    }

    public function setMainBody($mainBody)
    {
        $this->_mainBody = $mainBody;
    }

    public function mainBody()
    {
        return $this->_mainBody;
    }
}
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

require_once(OZ_PATH . '/src/core/Template.php');

class View
{
    protected $id;
    protected $_children;
    protected $_template;
    protected $_templateName;
    protected $_templatePath;
    protected $_model;

   
    public function __construct($templateName = '', $model = null)
    {
        $this->_id = uniqid();
        $this->_children = array();
        $this->_template = null;
        $this->_templateName = $templateName;
        if(!$model)
        {
            $model = array();
        }
        $this->_model = $model;
    }

    public function setTemplatePath($path)
    {
        $this->template()->setPath($path);
    }
    
    public function templatePath()
    {
        if($this->_template == null)
        {
            return CoreApplication::app()->templatePath();
        }
        return $this->_template->path();
    }

    public function model()
    {
        return $this->_model;
    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function id()
    {
        return $this->_id;
    }

    public function setTemplateName($name)
    {
        $this->_templateName = $name;
    }

    public function templateName()
    {
        return $this->_templateName;
    }

    public function template()
    {
        if($this->_template == null)
        {
            $this->_template = new Template($this->templatePath());
        }
        return $this->_template;
    }

    public function addChild($box, $view)
    {
        if(!isset($this->_children[$box]))
        {
            $this->_children[$box] = array();
        }
        array_push($this->_children[$box], $view);
    }

    public function renderChildren()
    {
        $boxes = array();
        foreach($this->_children as $box => $views)
        {
            $out = '';
            foreach($views as $view)
            {
               $out .= $view->render();
            }
            $boxes[$box] = $out;
        }
        return $boxes;
    }

    public function children()
    {
        return $this->_children;
    }

    public function set($name, $value)
    {
        $this->template()->set($name, $value);
    }

    public function render()
    {
        $this->set('model', $this->model());
        $this->set('boxes', $this->renderChildren());
        $this->set('app', CoreApplication::app());
        return $this->template()->render($this->templateName());
    }
}

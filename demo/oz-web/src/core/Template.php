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

require_once(OZ_PATH . '/src/core/Exceptions.php');

class TemplateNotExistsException extends SystemException
{
    public $path;

    function __construct($path)
    {
        parent::__construct("Template: `" . $path . "' not exists.");
        $this->path = $path;
    }
}

class TemplateDirectoryNotExistsException extends SystemException
{
    public $path;

    function __construct($path)
    {
        parent::__construct("Template directory: `" . $path . "' not exists.");
        $this->path = $path;
    }
}

class Template
{
    protected $_vars;
    protected $_path;

    /**
     * Constructor
     *
     * @param string $path the template directory path, the application
     * template path is used as default.
     *
     * @see Application::templatePath
     *
     * @return void
     **/
    public function Template($path = null)
    {
        if($path == null)
        {
            $path = Application::app()->templatePath();
        }
        $this->setPath($path);
        $this->_vars = array();
    }

    /**
     *
     * @return string $path the template directory path.
     *
     */
    public function path()
    {
        return $this->_path;
    }

    /**
     * Set the path to the template files.
     *
     * @param string $path path to template files
     *
     * @return void
     *
     * @throws TemplateDirectoryNotExistsException - The template directory doesn't exists.
     **/
    public function setPath($path)
    {
        if(!is_dir($path))
        {
            throw new TemplateDirectoryNotExistsException($path);
        }
        $this->_path = $path;
    }

    /**
     * Set a template variable.
     *
     * @param string $name name of the variable to set
     * @param mixed $value the value of the variable
     *
     * @return void
     **/
    public function set($name, $value)
    {
        $this->_vars[$name] = $value;
    }

    /**
     * Set a bunch of variables at once using an associative array.
     *
     * @param array $vars array of vars to set
     * @param bool $clear whether to completely overwrite the existing vars
     *
     * @return void
     **/
    public function setVars($vars, $clear = false)
    {
        if($clear)
        {
            $this->_vars = $vars;
        }
        else
        {
            if(is_array($vars)) $this->_vars = array_merge($this->_vars, $vars);
        }
    }

    /**
     * Render the template file.
     *
     * @param string string the template file name
     *
     * @return string The rendered data
     *
     * @throws TemplateNotExistsException - The template file doesn't exists.
     **/
    public function render($file)
    {
        $path = $this->_path . $file;
        if(!file_exists($path) && is_file($path))
        {
            throw new TemplateNotExistsException($path);
        }
        extract($this->_vars);          // Extract the vars to local namespace
        ob_start();                     // Start output buffering
        include($path);  // Include the file
        $contents = ob_get_contents();  // Get the contents of the buffer
        ob_end_clean();                 // End buffering and discard
        return $contents;               // Return the contents
    }
}

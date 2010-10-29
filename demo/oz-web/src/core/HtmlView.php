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

require_once (OZ_PATH . '/src/core/XillaTag.php');
require_once (OZ_PATH . '/src/core/View.php');
require_once (OZ_PATH . '/src/core/StringUtil.php');

class HtmlView extends View
{
    public function __construct($model)
    {
        parent::__construct('', $model);
    }

    public function render()
    {
        $body = $this->renderBody();
        $head = $this->renderHead();
        $out = doctype_xhtml() . "\n";
        $out .= render(html(array("xmlns" => "http://www.w3.org/1999/xhtml", "xml:lang" => "en", "lang" => "en"),
                            array($head, $body)));
        return $out;
    }
    

    private function renderHead()
    {
        $head = array();

        $objects = $this->model()->cssIncludes();
        foreach($objects as $css)
        {
            $head[] = hlink(array('href' => $css, 'type' => 'text/css', 'rel' => 'stylesheet'));
        }

        $head[] = hlink(array('rel' => 'shortcut icon', 'href' => $this->model()->shortcutIcon()));

        $objects = $this->model()->scriptIncludes();
        foreach($objects as $js)
        {
            $head[] = script(array('src' => $js, 'type' => 'text/javascript'));
        }

        $keywords = StringUtil::csvArray($this->model()->keywords());
        if($keywords != '')
        {
            $head[] = meta(array('name' => 'keywords', 'content' => $keywords));
        }

        $description = $this->model()->description();
        if($description != '')
        {
            $head[] = meta(array('name' => 'description', 'content' => $description));
        }
        
        $head[] = meta( array('http-equiv' => 'Content-Type', 'content' => 'text/html; charset=UTF-8'));


        $head[] = title("", $this->model()->title());

        $metaTags = $this->model()->metaTags();
        foreach($metaTags as $name => $content)
        {
            $head[] = meta(array('name' => $name, 'content' => $content));
        }

        $openGraphProperties = $this->model()->openGraphProperties();
        foreach($openGraphProperties as $property => $content)
        {
            $head[] = meta(array('property' => $property, 'content' => $content));
        }

        return head("", $head);
    }

    private function renderBody()
    {
        $body = array();
        $model = $this->model();
        $body[] = $model->mainBody();
        
        $objects = $this->model()->scripts();
        foreach($objects as $name => $js)
        {
            $body[] = script(array('type' => 'text/javascript'), $js);
        }            
        return body("", $body);
    }
}
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

class Html
{
    static public function renderBox($boxes, $name)
    {
        if(!is_array($boxes))
        {
            return;
        }
        if(!is_string($name))
        {
            return;
        }
        if(!isset($boxes[$name]))
        {
            return;
        }
        echo $boxes[$name];
    }

    static public function i18n($name)
    {
        echo '[['.$name.']]';
    }

    static public function writeSuccessOrFailure($value)
    {
        if($value == true)
        {
            return "Success";
        }
        return "Failure";
    }

    static public function attributes($attributes)
    {
        $out = '';
        foreach($attributes as $key => $value)
        {
            $out .= $key . '="' . $value . '" ';
        }
        return $out;
    }
    
    static public function formBegin($model)
    {
        $out = "<form ";
        $out .= Html::attributes($model->attributes());
        $out .= ">\n";
        echo $out;
        Html::input($model, 'FORM-POST-KEY');
    }

    static public function formEnd($model)
    {
        $out = "</form>\n";
        echo $out;
    }

    static public function label($model, $field, $decorator = null)
    {
        $out = "<label ";
        $out .= Html::attributes($model->field($field)->label()->attributes());
        $out .= ">\n";
        if($decorator)
        {
            $out .= $decorator->render($model, $field);
        }
        else
        {
            $out .= $model->field($field)->label()->attribute('value');
        }
        $out .= "</label>\n";
        echo $out;
    }

    static public function input($model, $field)
    {
        $fieldModel = $model->field($field);
        $hasErrors = $fieldModel->hasErrors();
        $attributes = $fieldModel->attributes();
        $class = '';
        if(isset($attributes['class']))
        {
            $class = $attributes['class'];
        }
        if($hasErrors)
        {
            if($class != '')
            {
                $class .= ' ';
            }
            $attributes['class'] = $class . 'error';
        }

        echo "<input " . Html::attributes($attributes) . "/>\n";


        if($fieldModel->hasErrors())
        {
            echo '<span class="error">';
            Html::errorSummary($fieldModel);
            echo '</span>';
        }
    }

    static public function inputHelp($model, $field)
    {
        echo $model->field($field)->attribute("help");
    }

    static public function writeTextArea($model, $name)
    {
        $field = $model->field($name);
        $out = "<textarea " . Html::attributes($field->attributes()) . ">\n";

        $out .= $field->value();
        $out .= "</textarea>\n";
        echo $out;
    }

    static public function selectList($model, $field)
    {
        $list = $model->field($field);
        $options = $list->options();
        $selected = $list->selectedValues();
        
        $out = "<select " . Html::attributes($list->attributes()) . "'>\n";
        foreach($options as $option)
        {
            $out .= '<option ';

            if(in_array($option->value, $selected))
            {
                error_log('OPTION ' . $option->name . ' IS SELECTED value: ' . $option->value);
                $out .= 'selected ';
            }
            $out .= 'value="' . $option->value . '">' . $option->name . "</option>\n";
        }
        $out .= "</select>\n";
        echo $out;
    }

    static public function radioInput($model, $field)
    {
        $attributes = $model->field($field)->attributes();
        $list = $model->field($field);
        $options = $list->options();
        $selected = $list->selectedValues();
        foreach($options as $option)
        {
            $args = array();
            $args['type'] = 'radio';
            $args['value'] = $option->value;
            $args['name'] = $attributes['name'];
            if(isset($attributes['class']))
            {
                $args['class'] = $attributes['class'];
            }
            $args['id'] = $attributes['id'] . "-" . $option->value;

            if(in_array($option->value, $selected))
            {
                $args['checked'] = 'yes';
            }
            echo "<p>" . $option->name . "<input " . Html::attributes($args) . "/></p>\n";
        }
    }

    static public function listInput($model, $field)
    {
        $attributes = $model->field($field)->attributes();
        $list = $model->field($field);
        $options = $list->options();
        $selected = $list->selectedValues();
        foreach($options as $option)
        {
            $args = array();
            $args['type'] = $attributes['type'];
            $args['value'] = $option->value;
            $args['name'] = $attributes['name'];
            if(isset($attributes['class']))
            {
                $args['class'] = $attributes['class'];
            }
            $args['id'] = $attributes['id'] . "-" . $option->value;

            if(in_array($option->value, $selected))
            {
                $args['checked'] = 'yes';
            }
            echo "<p>" . $option->name . "<input " . Html::attributes($args) . "/></p>\n";
        }
    }

    static public function submit($model, $field)
    {
        $fieldModel = $model->field($field);
        $attributes = array('id' => uniqid(),
                            'name' => $fieldModel->attribute('name'),
                            'type' => 'submit',
                            'value' => $fieldModel->attribute('value'),
                            'class' => $fieldModel->attribute('class'));
        $out = "<input " . Html::attributes($attributes) . "/>\n";
        echo $out;
    }

    static public function errorSummary($model)
    {
        if($model->hasErrors())
        {
            $out = '<ul class="error">';
            $errors = $model->errors();
            foreach($errors as $e)
            {
                $out .= "<li>" . $e . "</li>\n";
            }
            $out .= "</ul>\n";
            echo $out;
        }
    }
}

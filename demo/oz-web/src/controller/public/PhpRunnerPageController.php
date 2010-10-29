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

require_once (OZ_PATH . '/src/controller/AjaxBaseController.php');

class PhpRunnerPageController extends AjaxBaseController
{
    private $_ice;

    public function  __construct()
    {
        $this->_ice = Ice_initialize();
    }

    public function createAction()
    {
        $runner = $this->runner();
        $duration = Request::post('duration', 240);

        if(!is_numeric($duration) || $duration < 240 || $duration > 600)
        {
            throw new ArgumentException('duaration should be a numeric value betwen 240 and 600.');
        }
        $id = $runner->execute("DemoScript.php", array("--duration", $duration));
        return json_encode(new JSONResponse('OK', $id));
    }

    public function listAction()
    {
        $runner = $this->runner();

        $processInfoList = $runner->processList();

        $tasks = array();

        foreach($processInfoList as $process)
        {
            $tasks[] = array("attributes" => 
                array("id" => $process->id, 
                    "script" => $process->script,
                    "args" => join(' ', $process->args),
                    "date" => date("d/F/Y H:i:s.", $process->timestamp / 1000)));
        }
        $total = count($tasks);
        return json_encode(new JSONResponse('OK', new ResultSet($tasks, 0, $total, $total)));
    }

    public function killAction()
    {
        $runner = $this->runner();
        $identities= Request::post('identities', array());

        foreach($identities as $id)
        {
            $runner->stop($id);
        }
        return json_encode(new JSONResponse('OK', ''));
    }

    protected function runner()
    {
        $proxy = $this->_ice->stringToProxy(PHP_RUNNER_ENDPOINTS);
        return oz_php_CommandRunnerPrxHelper::uncheckedCast($proxy);
    }
}

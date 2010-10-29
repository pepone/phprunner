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

require_once (OZ_PATH . '/src/controller/Controller.php');

class ResultSet
{
    public $objects;
    public $page;
    public $limit;
    public $count;

    public function  __construct($objects, $page, $limit, $count) {
        
        $this->objects = $objects;
        $this->page = $page;
        $this->limit = $limit;
        $this->count = $count;
    }
}

class JSONResponse
{
    public $status;
    public $data;

    public function  __construct($status, $data)
    {
        $this->status = $status;
        $this->data = $data;
    }
}

class JSONException
{
    public $name;
    public $message;

    public function  __construct($ex)
    {
        if(is_object($ex))
        {
            $this->name = get_class($ex);
            $this->message = $ex->getMessage();
        }
        else
        {
            $this->name = 'Unknow Error';
            $this->message = $ex;
        }
        error_log($this->name . " " . $this->message);
    }
}

class AjaxBaseController extends Controller
{
    public function __construct()
    {
        parent::__construct(new Model());
        Application::app()->setContentType('application/json');
    }

    public function processRequest($action)
    {
        $response = '';
        try
        {
            $response = $this->$action();
        }
        catch(Exception $ex)
        {
            $response = json_encode(new JSONResponse('UserException', new JSONException($ex)));
        }
        return $response;
    }
}

class AjaxAdminController extends AjaxBaseController
{
    public function  __construct()
    {
        parent::__construct();
    }

    public function processRequest($action)
    {
        try
        {
            $this->checkIsAdmin();
        }
        catch(AuthDeniedException $ex)
        {
            return json_encode(new JSONResponse('UserException', new JSONException($ex)));
        }
        return parent::processRequest($action);
    }
}

class AjaxObjectController extends AjaxAdminController
{
    public $dao;

    public function  __construct($dao)
    {
        parent::__construct();
        $this->dao = $dao;
    }

    public function create($args)
    {
        return json_encode(new JSONResponse('OK', $this->dao->create($args)));
    }

    public function update($args)
    {
        $this->dao->update($args);
        return json_encode(new JSONResponse('OK', ''));
    }

    public function updateI18n($args)
    {
        $this->dao->updateI18n($args);
        return json_encode(new JSONResponse('OK', ''));
    }

    public function findI18n()
    {
        $id = Request::post('id');
        $language = Request::post('language');
        $args = array('values' => array($id, $language));
        $object = $this->dao->findI18n($args);
        return json_encode(new JSONResponse('OK', $object));
    }

    public function findAction()
    {
        $args = array('values' => array(Request::post('id')));
        $object = $this->dao->find($args);
        return json_encode(new JSONResponse('OK', $object));
    }

    public function removeAction()
    {
        $args = array('values' => array(Request::post('id')));
        $this->dao->remove($args);
        return json_encode(new JSONResponse('OK', ''));
    }

    public function removeInAction()
    {
        $identities = Request::post('identities', array());
        $args = array('values' => array($identities));
        $this->dao->removeIn($args);
        return json_encode(new JSONResponse('OK', ''));
    }

    public function listAction()
    {
        $sort = Request::post('sort', array());
        $limit = Request::post('limit', 20);
        $offset = Request::post('offset', 0);
        $page = ceil($offset / $limit);
        $count = $this->dao->countObjects();
        $lastPage = ceil($count / $limit) - 1;
        if($lastPage < 0)
        {
            $lastPage = 0;
        }
        if($page > $lastPage)
        {
            $page = $lastPage;
            $offset = $limit * $page;
        }

        $args = array('sort' => $sort, 'limit' => $limit, 'offset' => $offset);
        $objects = $this->dao->listObjects($args);
        return json_encode(new JSONResponse('OK', new ResultSet($objects, $page, $limit, $count)));
    }

    public function listAllAction()
    {
        $sort = Request::post('sort', array());
        $args = array('sort' => $sort);
        $objects = $this->dao->listObjects($args);
        return json_encode(new JSONResponse('OK', $objects));
    }

    public function searchAction()
    {
        $query = Request::post('query');
        $sort = Request::post('sort', array());
        $limit = Request::post('limit', 20);
        $offset = Request::post('offset', 0);
        $page = ceil($offset / $limit);
        $count = $this->dao->searchCount(array('values' => array($query)));
        $lastPage = ceil($count / $limit) - 1;
        if($lastPage < 0)
        {
            $lastPage = 0;
        }
        if($page > $lastPage)
        {
            $page = $lastPage;
            $offset = $limit * $page;
        }

        $args = array('values' => array($query),'sort' => $sort, 'limit' => $limit, 'offset' => $offset);
        $objects = $this->dao->search($args);

        return json_encode(new JSONResponse('OK', new ResultSet($objects, $page, $limit, $count)));
    }
}

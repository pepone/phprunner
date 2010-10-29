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

//
// Disable session cookie.
//
ini_set("session.use_cookies", false);

//
// Disable transid in urls.
//
ini_set("session.session.use_transid", false);


class CoreSession
{
    protected $_id;
    protected $_email;
    protected $_loginRedirectURL;
    protected $_logoutRedirectURL;
    
    public function __construct($loginRedirectURL, $logoutRedirectURL)
    {
        $this->_id = '';
        $this->_email = 'nobody';
        $this->_loginRedirectURL = $loginRedirectURL;
        $this->_logoutRedirectURL = $logoutRedirectURL;

        $id = Request::cookie('session-id');
        if($id == '')
        {
            $id = Request::post('session-id');
        }
        if($id == '')
        {
            $id = Request::get('session-id');
        }
        if($id != '')
        {
            $this->_id = $id;
        }
        session_id($this->_id);

        //
        // Start the session, but do not report any PHP error to the client if it fails.
        // There are exploits that use errors to get knowledge of web server backend internals
        // and to search for other system vulnerabilities.
        //
        @session_start();
        if(!isset($_SESSION)) // Check that the session started OK
        {
            throw new Exception('Error starting session');
        }
        $this->_email = ArrayUtil::getWithDefault($_SESSION, 'email', 'nobody');
    }

    public function id()
    {
        return $this->_id;
    }

    public function email()
    {
        return $this->_email;
    }

    public function setEmail($email)
    {
        $this->_email = $email;
        $_SESSION['email'] = $email;
    }

    public function logout()
    {
        $this->setEmail('nobody');
        Response::redirect($this->_logoutRedirectURL);
    }
    public function login($email, $password)
    {
        $passwordsFile = $this->passwordsFile();
        if(!file_exists($passwordsFile))
        {
            throw new FileNotExistsException($passwordsFile);
        }
        $passwords = file_get_contents($passwordsFile);
        $lines = explode("\n", $passwords);
        $auth = false;
        foreach($lines as $line)
        {
            $tokens = explode(':', $line);
            if(count($tokens) != 2)
            {
                continue;
            }
            if($tokens[0] == $email && $tokens[1] == $password)
            {
                $auth = true;
                break;
            }
        }
        if($auth)
        {
            $this->setEmail($email);
            Response::setCookieAndRedirect($this->_loginRedirectURL, "session-id", session_id(),
                time() + 86400, "/", false);
        }
    }

    protected function passwordsFile()
    {
        return ONLINE_PATH . '/../config/passwords';
    }
}
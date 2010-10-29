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

var SystemException = new JS.Class({

    initialize: function(name, message)
    {
        this.name = name;
        this.message = message;
    }
});

var ResourceNotExistsException = new JS.Class({

    initialize: function(controller, action)
    {
        this.name = 'Recurso No Econtrado';
        this.message = 'El recurso "' + controller + '/' + action + '" no existe.';
    }
});

var Proxy = new JS.Class({
    initialize: function(controller){
        this.controller = controller;
    },
    
    execute: function(action, args, successCallback, errorCallback)
    {
        var url = '/index.php?r=' + this.controller + '/' + action;
        $.ajax({
            url: url,
            type: "POST",
            data: args,
            success: function(response)
            {
                if(response === null || typeof response == 'undefined')
                {
                    errorCallback(new SystemException('Invalid Response', 'Invalid Response "' + response + '"'));
                    return;
                }
                
                if(typeof response == 'string')
                {
                    try
                    {
                        response = JSON.parse(response);
                    }
                    catch(ex)
                    {
                        alert(response);
                        if(ex instanceof SyntaxError)
                        {
                            errorCallback(new SystemException('InvalidJSON', 'Invalid JSON'));
                        }
                        else
                        {
                            errorCallback(new SystemException('InvalidJSON', 'Unexpected exception parsing JSON'));
                        }
                        return;
                    }
                }
                
                if(response.status === null || typeof response.status == 'undefined')
                {
                    errorCallback(new SystemException('Invalid Response', 
                        'Invalid Response Status: "' + response.status + '"'));
                }
                
                if(response.status == 'UserException')
                {
                    errorCallback(response.data);
                    return;
                }
                successCallback(response.data);
            },
            error: function(request, message, error)
            {
                if(request.status == 404)
                {
                    errorCallback(new ResourceNotExistsException(this.controller, action));
                }
                else
                {
                    errorCallback(new SystemException('Error Desconocido', 'Error desconocido ' +
                        request.status + ' message: "' + message + '" error: "' + error + '"'));
                }
            }});
    }
});

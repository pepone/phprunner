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

var ObjectDialog = new JS.Class({

    initialize: function(config)
    {
        this._id = config.id;
        this._buttons = new JS.Hash();
        this._loadForeign = 0;
        this._commonSelector = '#' + this._id;
        this._tabs = [];
        this._fieldTabs = new JS.Hash();
        

        $('body').append('<div id="' + this._id + '"></div>');
        if(typeof config.className != 'undefined')
        {
            $('#' + this._id).addClass(config.className);
        }

        var i = null;
        
        if(typeof config.tabs != 'undefined')
        {
            this._commonSelector = '#' + this._id + '-common';
            $('#' + this._id).append('<ul class="tabs"></ul>');
            //
            // Setup dialog tab structure
            //

            $('#' + this._id + ' ul').append('<li><a href="#' + this._id + '-common">General</a></li>');
            $('#' + this._id).append('<div id="' + this._id + '-common"></div>');
            var tab = null;
            for(i = 0; i < config.tabs.length; i++)
            {
                tab = config.tabs[i];
                $('#' + this._id + ' > ul.tabs').append('<li><a href="#' + tab.id + '">' + tab.name + '</a></li>');
                $('#' + this._id).append('<div id="' + tab.id + '"></div>');
                this._tabs[i] = tab.view('#' + tab.id);
            }
        }
        
        if(typeof config.objectType != 'undefined')
        {
            this._objectType = config.objectType;
            
            this._objectProxy = config.proxy;

            this._objectAttributeId = 'id';
            if(typeof config.attributeId != 'undefined')
            {
                this._objectAttributeId = config.attributeId;
            }

            this._objectAttributeName = 'name';
            if(typeof config.attributeName!= 'undefined')
            {
                this._objectAttributeName = config.attributeName;
            }
        }

        var current = this;
        config.dialogOptions['close'] = function(){
            current.clear();
        };

        if(typeof config.tabs != 'undefined')
        {
            config.dialogOptions['open'] = function(){
                if(typeof current._object != 'undefined')
                {
                    current.toggleUpload($('#' + current._id).data('tabs').panels[
                        $('#' + current._id).tabs('option', 'selected')].id);
                }
            };
        }
        
        this._dialog = $('#' + this._id).dialog(config.dialogOptions);
        $('#' + this._id).resize(function(e){
            current.center();
            if(typeof config.tabs != 'undefined' && typeof current._object != 'undefined')
            {
                current.toggleUpload($('#' + current._id).data('tabs').panels[
                    $('#' + current._id).tabs('option', 'selected')].id);
            }
        });


        if(typeof config.message != 'undefined')
        {
            this.setMessage(config.message);
        }

        this._fields = new JS.Hash();
        if(typeof config.fields != 'undefined')
        {
            var field = null;
            
            $(this._commonSelector).append('<fieldset class="general"></fieldset>');

            for(i = 0; i < config.fields.length; i++)
            {
                field = config.fields[i];

                if(typeof field.tab == 'undefined')
                {
                    $(this._commonSelector + ' fieldset.general').append('<p class="field-' + field.name +
                        '"></p>');
                }
                else
                {
                    this._fieldTabs.put(field.tab.name, field.tab);
                    if($(this._commonSelector + ' fieldset.general div#' + field.tab.name).length == 0)
                    {
                        $(this._commonSelector + ' fieldset.general').append('<div id="' + field.tab.name +
                            '"><ul></ul></div>');
                    }
                    $(this._commonSelector + ' fieldset.general div#' + field.tab.name + ' ul').append(
                        '<li><a href="#' + field.tab.id + '">' + field.tab.title + '</a></li>');
                    $(this._commonSelector + ' fieldset.general div#' + field.tab.name).append(
                        '<div id="' + field.tab.id + '"></div>');
                    $(this._commonSelector + ' fieldset.general div#' + field.tab.id).append(
                        '<p class="field-' + field.name + '"></p>');
                }

                this._fields.put(field.name, field);
                if(field.type != 'hidden')
                {
                    this.drawLabel(field);
                }
                switch(field.type)
                {
                    case 'text':
                    case 'password':
                    case 'checkbox':
                    case 'radio':
                    case 'hidden':
                    {
                        this.drawInput(field);
                        break;
                    }
                    case 'select':
                    {
                        this.drawSelect(field);
                        break;
                    }
                    case 'textarea':
                    {
                        this.drawTextArea(field);
                        break;
                    }
                    default:
                    {
                        break;
                    }
                }
            }
        }
        
        if(typeof config.actions != 'undefined')
        {
            this._actions = config.actions;
            $(this._commonSelector).append('<div class="actions"></div>');
            for(i = 0; i < this._actions.length; i++)
            {
                this.drawAction(this._actions[i]);
            }
        }
        if(typeof config.tabs != 'undefined')
        {
            $('#' + this._id).tabs({
                show: function(event, ui){
                    current.center();
                    current.toggleUpload($(ui.panel).attr('id')); }
            });
        }

        var fieldTabs = this._fieldTabs.keys();
        for(i = 0; i < fieldTabs.length; i++)
        {
            $('#' + fieldTabs[i]).tabs();
        }
    },

    setTitle: function(title)
    {
        this._dialog.dialog('option', 'title', title);
    },

    setMessage: function(message)
    {
        $(this._commonSelector + ' > div.message').remove();
        $(this._commonSelector).prepend('<div class="message">' + message + '</div>');
    },

    drawAction: function(action)
    {
        $(this._commonSelector + ' .actions').append('<a href="#' + action.name + '">' +
            action.text + '</a>');

        var button = $(this._commonSelector + ' .actions a[href="#' + action.name + '"]').button(
            {icons: {primary: action.icon}, text:false});
        this._buttons.put(action.name, button);
        this.bindAction(action);
    },

    bindAction: function(action)
    {
        var current = this;
        var button = this._buttons.get(action.name);
        $(this._commonSelector + ' .actions a[href="#' + action.name + '"]').unbind('click');
        
        $(this._commonSelector + ' .actions a[href="#' + action.name + '"]').bind('click',
            function(){
                if(button.button("option", "disabled"))
                {
                    return false;
                }
                action.callback(current);
                return false;
            });
    },

    drawInput: function(field)
    {
        $(this._commonSelector + ' p.field-' + field.name).append('<input type="' + field.type +
            '" name="' + field.name + '"/>');
    },

    drawTextArea: function(field)
    {
        $(this._commonSelector + ' p.field-' + field.name).append('<textarea cols="' + field.cols +
            '" rows="' +  field.rows + '" ' + 'name="' + field.name + '"></textarea>');
    },

    drawSelect: function(field)
    {
        $(this._commonSelector + ' p.field-' + field.name).append('<select name="' + field.name +
            '"></select>');
    },

    drawLabel: function(field)
    {
        if(field.label !== null && typeof field.label != "undefined")
        {
            $(this._commonSelector + ' p.field-' + field.name).append('<label>' + field.label + '</label>');
        }
    },

    clear: function()
    {
        var fields = this._fields.values();
        for(var i = 0; i < fields.length; i++)
        {
            this.clearField(fields[i]);
        }
    },

    clearField: function(field)
    {
        switch(field.type)
        {
            case 'text':
            case 'password':
            case 'checkbox':
            case 'radio':
            case 'hidden':
            {
                $(this._commonSelector + ' input[name="' + field.name + '"]').val('');
                break;
            }
            case 'select':
            {
                $(this._commonSelector + ' select[name="' + field.name + '"]').val('');
                break;
            }
            case 'textarea':
            {
                $(this._commonSelector + ' textarea[name="' + field.name + '"]').val('');
                break;
            }
            default:
            {
                break;
            }
        }
    },

    load: function(object)
    {
        this._object = object;
        
        for(var key in object)
        {
            this.loadField(this._fields.get(key), object[key]);
        }

        var tabView = null;
        for(var i = 0; i < this._tabs.length; i++)
        {
            tabView = this._tabs[i];
            tabView.load(object);
        }
    },

    loadField: function(field, value)
    {
        if(field !== null)
        {
            switch(field.type)
            {
                case 'text':
                case 'password':
                case 'radio':
                case 'hidden':
                {
                    $(this._commonSelector + ' input[name="' + field.name + '"]').val(value);
                    break;
                }
                case 'checkbox':
                {
                    $(this._commonSelector + ' input[name="' + field.name + '"]').attr('checked', value);
                    break;
                }
                case 'select':
                {
                    $(this._commonSelector + ' select[name="' + field.name + '"]').val(value);
                    break;
                }
                case 'textarea':
                {
                    $(this._commonSelector + ' textarea[name="' + field.name + '"]').val(value);
                    break;
                }
                default:
                {
                    break;
                }
            }
        }
    },

    open: function()
    {
        var fields = this._fields.values();
        var field = null;
        for(var i = 0; i < fields.length; i++)
        {
            field = fields[i];
            if(field.type == 'select' && typeof field.selectOptions != 'undefined')
            {
                $(this._commonSelector + ' select[name="' + field.name + '"]').children().remove();

                var option = null;
                for(var j = 0; j < field.selectOptions.length; j++)
                {
                    option = field.selectOptions[j];
                    $(this._commonSelector + ' select[name="' + field.name +'"]').append(
                        '<option value="' + option.value + '">' + option.name + '</option>');
                }
            }
            if(jQuery.isFunction(field.defaultValue))
            {
                this.loadField(field, field.defaultValue());
            }
        }
        this.center();
        this._dialog.dialog('open');
    },

    center: function()
    {
        this._dialog.dialog( "option", "position", "center");
    },

    close: function()
    {
        this._dialog.dialog('close');
    },

    fieldValues: function()
    {
        var object = {};
        var fields = this._fields.values();
        var field = null;
        for(var i = 0; i < fields.length; i++)
        {
            field = fields[i];
            object[field.name] = this.fieldValue(field.name);
        }
        return object;
    },

    fieldValue: function(name)
    {
        var field = this._fields.get(name);
        var value = null;
        switch(field.type)
        {
            case 'text':
            case 'password':
            case 'radio':
            case 'hidden':
            {
                value = $(this._commonSelector + ' input[name="' + field.name + '"]').val();
                break;
            }
            case 'checkbox':
            {
                value = $(this._commonSelector + ' input[name="' + field.name + '"]').attr('checked');
                break;
            }
            case 'select':
            {
                value = $(this._commonSelector + ' select[name="' + field.name + '"] option:selected').val();
                break;
            }
            case 'textarea':
            {
                value = $(this._commonSelector + ' textarea[name="' + field.name + '"]').val();
                break;
            }
            default:
            {
                break;
            }
        }
        return value;
    },

    showUpload: function()
    {
        if(typeof this._object != 'undefined')
        {
            var top = $('#' + this._id).parent().css('top');
            top = top.substr(0, top.length - 2);
            top = parseInt(top) + 115;
            var left = $('#' + this._id).parent().css('left');
            left = left.substr(0, left.length - 2);
            left = parseInt(left) + 40;

            $('#upload-button-' + this._object.id + ' object').css('width', '24px');
            $('#upload-button-' + this._object.id + ' object').css('height', '24px');
            $('#upload-button-' + this._object.id).css('top',  top + 'px');
            $('#upload-button-' + this._object.id).css('left', left + 'px');
        }
    },

    hideUpload: function()
    {
        if(typeof this._object != 'undefined')
        {
            $('#upload-button-' + this._object.id).css('top',  '0px');
            $('#upload-button-' + this._object.id).css('left', '0px');
            $('#upload-button-' + this._object.id + ' object').css('width', '0px');
            $('#upload-button-' + this._object.id + ' object').css('height', '0px');
        }
    },

    toggleUpload: function(id)
    {
        if(id.endsWith('-images'))
        {
            this.showUpload();
        }
        else
        {
            this.hideUpload();
        }
    }
});

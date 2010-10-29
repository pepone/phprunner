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

function arrayFind(container, eq)
{
    var element = null;
    for(var i = 0; i < container.length; i++)
    {
        if(eq(container[i]))
        {
            element = container[i];
            break;
        }
    }
    return element;
}

var Field = new JS.Class({

    initialize: function(index, id, name, sortable, visible, decorator)
    {
        this.index = index;
        this.id = id;
        this.name = name;
        this.sortable = sortable;
        this.visible = visible
        if(typeof decorator == "undefined")
        {
            this.decorator = null;
        }
        else
        {
            this.decorator = decorator;
        }

    }
});

var SortField = new JS.Class({

    initialize: function(field, asc)
    {
        this.field = field;
        this.asc = asc;
    }
});

var Action = new JS.Class({

    initialize: function(index, text, icon, needSelection, callback)
    {
        this._index = index;
        this._text = text;
        this._icon = icon;
        this._needSelection = needSelection;
        this._callback = callback;
        this._button = null;
    },

    run: function(table)
    {
        if(this._button === null)
        {
            return;
        }
        if(this._button.button("option", "disabled"))
        {
            return;
        }
        this._callback(table);
        return;
    }
});

var Table = new JS.Class({

    initialize: function(options)
    {
        this._id = options.id;
        this._rowAttributeId = options.rowAttributeId;
        if(this._rowAttributeId == '' || this._rowAttributeId === null ||
           typeof this._rowAttributeId == "undefined")
        {
            this._rowAttributeId = 'id';
        }
        this._attributeName = options.attributeName;
        if(this._attributeName == '' || this._attributeName === null ||
           typeof this._attributeName == "undefined")
        {
            this._attributeName = 'name';
        }
        this._objects = new JS.Hash();
        this._lastPage = 0;
        this._totalPages = 0;
        this._currentPage = 0;
        this._totalObjects = 0;
        this._itemsPerPage = options.itemsPerPage;
        this._maxHeight = options.maxHeight;
        
        this._query = '';
        
        if(this._id == '' || this._id === null || typeof this._id == "undefined")
        {
            this._id = jQuery.uuid();
        }
        this._fields = new JS.Hash();
        for(var i = 0; i < options.fields.length; i++)
        {
            var f = options.fields[i];
            this._fields.put(i, new Field(i, f.id, f.name, f.sortable, f.visible, f.decorator));
        }
        this._actions = new JS.Hash();
        if(typeof options.actions !== "undefined")
        {
            for(var j = 0; j < options.actions.length; j++)
            {
                var a = options.actions[j];
                this._actions.put(j, new Action(j, a.text, a.icon, a.needSelection, a.callback));
            }
        }
        
        this._sort = [];

        this._loadPage = options.loadPage;
        this._search = options.search;
        this._edit = options.edit;
    },

    getSort: function()
    {
        var sort = [];
        for(var i = 0; i < this._sort.length; i++)
        {
            var f = this._sort[i];

            var obj = {};
            obj[f.field.id] = f.asc;
            sort[sort.length] = obj;
        }
        return sort;
    },
    
    //
    // Draw the table
    //
    draw: function(selector)
    {
        this.selector = selector;
        
        $(selector).append('<div class="tablewrapper"></div>');

        $(selector + ' .tablewrapper').append('<div class="toolbar"></div>');

        $(selector + ' .tablewrapper .toolbar').addClass(
            'ui-toolbar ui-widget-header ui-corner-tl ui-corner-tr ui-helper-clearfix');

        $(selector + ' .tablewrapper .toolbar').append(
            '<div class="ui-buttonset ui-buttonset-multi"><ul></ul></div>');

        var actions = this._actions.values();
        for(var i = 0; i < actions.length; i++)
        {
            this.drawAction(actions[i]);
        }

        if(typeof this._search !== 'undefined')
        {
            this.drawSearchForm();
        }
        
        $(selector + ' .tablewrapper').append('<table id="' + this._id + '"></table>');

        $('#' + this._id).append('<thead><tr></tr></thead>');
        var fields = this._fields.values();
        for(var j = 0; j < fields.length; j++)
        {
            this.drawFieldHeader(j, fields[j]);
        }
        $('#' + this._id).append('<tbody></tbody>');
        $(selector + ' .tablewrapper').append(
            '<div class="bottom-toolbar ui-widget-header ui-corner-bl ui-corner-br ui-helper-clearfix"></div>');

        this.drawNavigation();
    },

    drawSearchForm: function()
    {
        var prefix = this.selector + ' .tablewrapper .toolbar';
        $(prefix).append('<div class="searchform ui-buttonset ui-buttonset-multi"></div>');
        prefix += ' .searchform';
        $(prefix).append('<input type="text" name="query" size="15"/>');

        $(prefix).append('<a href="#search">Buscar</a>');
        this._btnSearch = $(prefix + ' a[href="#search"]').button({icons: {primary: 'ui-icon-search'},
            text:false});

        var current = this;
        $(prefix + ' a[href="#search"]').bind('click', 
            function(){
                
                if(current._btnSearch.button( "option", "disabled" ))
                {
                    return false;
                }
                current._query = $(prefix + ' input[name="query"]').val();
                if(current._query == '')
                {
                    return false;
                }
                current.search(0);
                return false;
            });

        $(prefix).append('<a href="#reset-search">Ver Todos</a>');
        $(prefix + ' a[href="#reset-search"]').button({icons: {primary: 'ui-icon-circle-close'},
            text:false});

        this._btnResetSearch = $(prefix + ' a[href="#reset-search"]').bind('click',
            function(){
                if(current._btnResetSearch.button( "option", "disabled" ))
                {
                    return false;
                }
                current.resetSearch();
                return false;
            });
        this._btnResetSearch.button('disable');
    },

    drawNavigation: function()
    {
        $(this.selector + ' .tablewrapper .bottom-toolbar').append('<ul></ul>');

        $(this.selector + ' .tablewrapper .bottom-toolbar ul').append(
            '<li><a href="#first">First</a></li>');

        $(this.selector + ' .tablewrapper .bottom-toolbar ul').append(
            '<li><a href="#previous">Previous</a></li>');

        $(this.selector + ' .tablewrapper .bottom-toolbar ul').append(
            '<li>Page: <input name="page" size="5" type="text" value="' + this._currentPage + '"/> ' +
            'de <span class="total">' + this._totalPages + '</span></li>');
            
        $(this.selector + ' .tablewrapper .bottom-toolbar ul').append(
            '<li><a href="#next">Next</a></li>');
        $(this.selector + ' .tablewrapper .bottom-toolbar ul').append(
            '<li><a href="#last">Last</a></li>');

        var prefix = this.selector + ' .tablewrapper .bottom-toolbar ul li';

        var current = this;
        this._btnSeekFirst = $(prefix + ' a[href="#first"]').button(
            {icons: {primary: 'ui-icon-seek-first'}, text:false});
        $(prefix + ' a[href="#first"]').bind('click', function(){
            if(current._btnSeekFirst.button( "option", "disabled" ))
            {
                return false;
            }
            current.seekFirst();s
            return false;});

        this._btnSeekPrevious = $(prefix + ' a[href="#previous"]').button(
            {icons: {primary: 'ui-icon-seek-prev'}, text:false});
            
        $(prefix + ' a[href="#previous"]').bind('click', function(){
            if(current._btnSeekPrevious.button( "option", "disabled" ))
            {
                return false;
            }
            current.seekPrevious();
            return false;});

        this._btnSeekNext = $(prefix + ' a[href="#next"]').button(
            {icons: {primary: 'ui-icon-seek-next'}, text:false});

        $(prefix + ' a[href="#next"]').bind('click', function(){
            if(current._btnSeekNext.button( "option", "disabled" ))
            {
                return false;
            }
            current.seekNext();
            return false;});

        this._btnSeekLast = $(prefix + ' a[href="#last"]').button(
            {icons: {primary: 'ui-icon-seek-end'}, text:false});

        $(prefix + ' a[href="#last"]').bind('click', function(){
            if(current._btnSeekLast.button( "option", "disabled" ))
            {
                return false;
            }
            current.seekLast();
            return false;});
    },

    drawAction: function(action)
    {
        var prefix = this.selector + ' .tablewrapper .toolbar div ul';
        $(prefix).append('<li><a href="#">' + action._text + '</a></li>');
        prefix +=  ' li:eq(' + action._index + ') a';
        action._button = $(prefix).button({icons: {primary: action._icon}, text:false});

        if(action._needSelection)
        {
            action._button.button('disable');
        }
        var current = this;
        $(prefix).bind('click', function(){
            action.run(current);
            return false;
        });

    },
    
    drawFieldHeader: function(index, field)
    {
        var current = this;
        $('#' + this._id + ' thead tr').append('<th></th>');

        
        $('#' + this._id + ' thead tr th:eq(' + index + ')').addClass('ui-state-default');
        

        $('#' + this._id + ' thead tr th:eq(' + index + ')').append(
            '<div class="field_value">' + field.name + '</div>');

        if(field.sortable)
        {
            $('#' + this._id + ' thead tr th:eq(' + index + ') div.field_value').append(
                '<span class="sortable classcss_right ui-icon ui-icon-carat-2-n-s"></span>');

            $('#' + this._id + ' thead tr th:eq(' + index + ') div.field_value').bind('click',
                function(event){current.sortField(event, index, field)});
        }
            
        if(!field.visible)
        {
            $('#' + this._id + ' thead tr th:eq(' + index + ')').hide();
        }

        $('#' + this._id + ' thead tr th').disableSelection();
    },

    sortField: function(event, index, field)
    {
        if(!event.shiftKey)
        {
            this.singleFieldSorting(index, field);
        }
        else
        {
            this.multipleFieldSorting(index, field);
        }
        this.seekFirst();
    },

    singleFieldSorting: function(index, field)
    {
       
        this.cleanSortIcons();
        var asc = true;

        var f = arrayFind(this._sort, function(e){
            if(e.field.id == field.id)
            {
                return true;
            }
            return false;
        });

        if(this._sort.length == 0)
        {
            $('#' + this._id + ' thead tr th:eq(' + index + ') div.field_value span.sortable').removeClass(
                'ui-icon-carat-2-n-s');
        }
        this._sort = [];
        if(f !== null)
        {
            asc = !f.asc;
            this._sort [0] = new SortField(field, !f.asc);
        }
        else
        {
            this._sort [0] = new SortField(field, true);
        }

        var sortClass = 'ui-icon-carat-1-n';
        var removeClass =  'ui-icon-carat-1-s';
        
        if(!asc)
        {
            sortClass = 'ui-icon-carat-1-s';
            removeClass =  'ui-icon-carat-1-n';
        }

        var e = $('#' + this._id + ' thead tr th:eq(' + index + ') div.field_value > span.sortable');
        e.removeClass('ui-icon-carat-2-n-s');
        e.addClass(sortClass);
        e.removeClass(removeClass);
    },

    multipleFieldSorting: function(index, field)
    {
        var f = arrayFind(this._sort, function(e){
            if(e.field.id == field.id)
            {
                return true;
            }
            return false;
        });

        var e = null;
        if(f === null)
        {
            e = $('#' + this._id + ' thead tr th:eq(' + index + ') div.field_value > span.sortable');
            e.removeClass('ui-icon-carat-2-n-s');
            e.addClass('ui-icon-carat-1-n');
            this._sort [this._sort.length] = new SortField(field, true);
            return;
        }
        else
        {
            if(f.asc)
            {
                f.asc = false;
                e = $('#' + this._id + ' thead tr th:eq(' + index + ') div.field_value > span.sortable');
                e.removeClass('ui-icon-carat-1-n');
                e.addClass('ui-icon-carat-1-s');
            }
            else
            {
                var j = 0;
                for(; j < this._sort.length; j++)
                {
                    if(this._sort[j] == f)
                    {
                        break;
                    }
                }
                this._sort.splice(j, 1);
                e = $('#' + this._id + ' thead tr th:eq(' + index + ') div.field_value > span.sortable');
                e.removeClass('ui-icon-carat-1-s');
                e.addClass('ui-icon-carat-2-n-s');
            }
        }
    },

    cleanSortIcons: function()
    {
        for(var i = 0; i < this._sort.length; i++)
        {
            var f = this._sort[i].field;
            var e = $('#' + this._id + ' thead tr th:eq(' + f.index + ') div.field_value > span.sortable');
            e.removeClass('ui-icon-carat-1-n');
            e.removeClass('ui-icon-carat-1-s');
            e.addClass('ui-icon-carat-2-n-s')
        }
    },

    loadPage: function(page)
    {
        this.createLoadingOverlay();
        if(!this._loadPage(this, page * this._itemsPerPage))
        {
            this.removeLoadingOverlay();
        }
    },

    createLoadingOverlay: function()
    {
        this.removeLoadingOverlay();
        $(this.selector + ' .tablewrapper').append(
            '<div class="loadingwrapper"><div class="loading">' +
                '<div><img src="/resources/css/img/16x16/node-loading.gif"/>Cargando</div>' +
            '</div></div>');

        this.loadingOverlayPosition();

        var current = this;

        $(this.selector + ' .tablewrapper').resize(function(e){
            current.loadingOverlayPosition();
            current.setupScrollBar();
        });
    },

    setupScrollBar: function()
    {
        if(typeof this._maxHeight != 'undefined')
        {
            var h = $('#' + this._id + ' tbody').height();
            
            if(h >= this._maxHeight)
            {
                $('#' + this._id + ' tbody').css('height', this._maxHeight + 'px');
                $('#' + this._id + ' tbody').css('overflow-y', 'auto');
                $('#' + this._id + ' tbody').css('overflow-x', 'hidden');
            }
        }
    },
    
    loadingOverlayPosition: function()
    {
        var h = $(this.selector + ' .tablewrapper').height();
        $(this.selector + ' .tablewrapper > div.loadingwrapper').height(h);
        $(this.selector + ' .tablewrapper > div.loadingwrapper > div.loading').height(h);

        var w = $(this.selector + ' .tablewrapper').width();
        $(this.selector + ' .tablewrapper > div.loadingwrapper').width(w);
        $(this.selector + ' .tablewrapper > div.loadingwrapper > div.loading').width(w);

        var position = $(this.selector + ' .tablewrapper').position();
        $(this.selector + ' .tablewrapper > div.loadingwrapper').css('top', position.top);
        $(this.selector + ' .tablewrapper > div.loadingwrapper').css('left', position.left);
    },

    removeLoadingOverlay: function()
    {
        $(this.selector + ' .tablewrapper div.loadingwrapper').remove();
    },

    loadObjects: function(resultset)
    {
        $('#' + this._id + ' tbody').removeAttr('style');
        
        this._currentPage = parseInt(resultset.page);

        if(resultset.limit <= 0)
        {
            this._lastPage = 0;
        }
        else
        {
            this._lastPage = Math.ceil(parseInt(resultset.count) / parseInt(resultset.limit)) - 1;
        }
        
        if(this._lastPage < 0)
        {
            this._lastPage = 0;
        }

        if(this._currentPage == this._lastPage)
        {
            this._btnSeekNext.button('disable');
            this._btnSeekLast.button('disable');
        }
        else
        {
            this._btnSeekNext.button('enable');
            this._btnSeekLast.button('enable');
        }

        if(this._currentPage == 0)
        {
            this._btnSeekFirst.button('disable');
            this._btnSeekPrevious.button('disable');
        }
        else
        {
            this._btnSeekFirst.button('enable');
            this._btnSeekPrevious.button('enable');
        }

        $(this.selector + ' .bottom-toolbar input[name="page"]').val(this._currentPage + 1);
        $(this.selector + ' .bottom-toolbar span.total').text(this._lastPage + 1);

        $('#' + this._id + ' tbody').children().remove();
        this._objects.clear();

        for(var i = 0; i < resultset.objects.length; i++)
        {
            this.drawRow(i, resultset.objects[i].attributes);
        }
        $('#' + this._id + ' tbody tr td').disableSelection();
        this.refreshActions();
        this.removeLoadingOverlay();
        this.setupScrollBar();
    },

    reload: function()
    {
        if(this._query == '')
        {
            this.loadPage(this._currentPage);
        }
        else
        {
            this.search(this._currentPage);
        }
    },
    
    seekFirst: function()
    {
        if(this._query == '')
        {
            this.loadPage(0);
        }
        else
        {
            this.search(0);
        }
    },

    seekPrevious: function()
    {
        if(this._query == '')
        {
            this.loadPage(this._currentPage - 1);
        }
        else
        {
            this.search(this._currentPage - 1);
        }
    },

    seekNext: function()
    {
        if(this._query == '')
        {
            this.loadPage(this._currentPage + 1);
        }
        else
        {
            this.search(this._currentPage + 1);
        }
    },

    seekLast: function()
    {
        if(this._query == '')
        {
            this.loadPage(this._lastPage);
        }
        else
        {
            this.search(this._lastPage);
        }
    },

    search: function(page)
    {
        this.createLoadingOverlay();
        this._btnResetSearch.button('enable');
        if(!this._search(this, this._query, page * this._itemsPerPage))
        {
            this.removeLoadingOverlay();
        }
    },

    resetSearch: function()
    {
        this._query = '';
        this._btnResetSearch.button('disable');
        $('.searchform input[name="query"]').val('');
        this.loadPage(0);
    },

    drawRow: function(index, data)
    {
        this._objects.put(data[this._rowAttributeId], data);
        $('#' + this._id + ' tbody').append('<tr id="row-' + data[this._rowAttributeId] + '"></tr>');
        if(index % 2 == 0)
        {
            $('#' + this._id + ' tbody tr:eq(' + index + ')').addClass('even');
        }
        else
        {
            $('#' + this._id + ' tbody tr:eq(' + index + ')').addClass('odd');
        }
        
        var fields = this._fields.values();
        for(var j = 0; j < fields.length; j++)
        {
            var f = fields[j];
            if(f.decorator === null)
            {
                $('#' + this._id + ' tbody tr:eq(' + index + ')').append('<td>' +
                    data[f.id] + '</td>');
            }
            else
            {
                $('#' + this._id + ' tbody tr:eq(' + index + ')').append('<td>' +
                    f.decorator(data[f.id]) + '</td>');
            }

            if(!f.visible)
            {
                $('#' + this._id + ' tbody tr:eq(' + index + ') td:eq(' + j + ')').hide();
            }

        }
        var current = this;
        $('#' + this._id + ' tbody tr:eq(' + index + ')').unbind('click');
        $('#' + this._id + ' tbody tr:eq(' + index + ')').bind('click', function(e){
            return current.clickRow(index, e, data);
        });
    },

    clickRow: function(index, event, data)
    {
        var row = '#' + this._id + ' tbody tr:eq(' + index + ')';
        if(event.shiftKey)
        {
            //
            // Multiple selection
            //
            if($(row).hasClass('row_selected'))
            {
                $(row).removeClass('row_selected');
            }
            else
            {
                $(row).addClass('row_selected');
            }
        }
        else
        {
            //
            // TODO Simple selection
            //
            if($(row).hasClass('row_selected'))
            {
                $('#' + this._id + ' tbody tr').removeClass('row_selected');
            }
            else
            {
                $('#' + this._id + ' tbody tr').removeClass('row_selected');
                $(row).addClass('row_selected');
            }
        }
        
        this.refreshActions();
        return false;
    },

    selectedRows: function()
    {
        var selected = [];
        $('#' + this._id + ' tbody tr.row_selected').each(function(index, e){
            selected[index] = $(this).attr('id');
        });
        return selected;
    },

    selectedObjects: function()
    {
        var objects = [];
        var selected = this.selectedRows();
        var id = null;
        var object = null;
        for(var i = 0; i < selected.length; i++)
        {
            id = selected[i].substring(4);
            object = this._objects.get(id);
            if(object == null)
            {
                continue;
            }
            objects[objects.length] = object;
        }
        return objects;
    },
    
    refreshActions: function()
    {
        var cmd = null;
        if($('#' + this._id + ' tbody tr.row_selected').length > 0)
        {
            cmd = 'enable';
        }
        else
        {
            cmd = 'disable';
        }

        var actions = this._actions.values();
        for(var j = 0; j < actions.length; j++)
        {
            var action = actions[j];
            if(action._needSelection)
            {
                action._button.button(cmd);
            }
        }
    }
});
<div class="phprunner"></div>

<script type="text/javascript">

$(document).ready(function(){
    var PhpRunnerPrx = new JS.Class(Proxy, {
        
        initialize: function(){
            this.callSuper('phpRunner');
        },
        create: function(duration, onSuccess, onError){
            this.execute('create', {duration: duration}, onSuccess, onError);
        },
        list: function(onSuccess, onError){
            this.execute('list', {}, onSuccess, onError);
        },
        kill: function(identities, onSuccess, onError){
            this.execute('kill', {identities: identities}, onSuccess, onError);
        },
        restore: function(file, onSuccess, onError){
            this.execute('restore', {file: file}, onSuccess, onError);
        }
    });

    var runnerPrx = new PhpRunnerPrx();

    var removeTaskDialog = new ObjectDialog({
        id: 'removetaskdialog',
        application: this._application,
        className: 'admindialog',
        actions: [{
            name: 'remove', text: 'Remove ', icon: 'ui-icon-check',
            callback: function(dialog){
                var objects = table.selectedObjects();
                var ids = [];
                for(var i = 0; i < objects.length; i++)
                {
                    var object = objects[i];
                    ids[i] = object.id;
                }
                runnerPrx.kill(ids,
                    function(){
                        table.reload();
                        dialog.close();
                    },
                    function(ex){
                        alert(ex.name + ' ' + ex.message);
                    }
                );
            }},
            {name: 'cancel', text: 'Cancel', icon: 'ui-icon-circle-close',
             callback: function(dialog){dialog.close();}}],

        dialogOptions: {autoOpen: false, title: 'Kill Task', position:'center', modal: true,
            width:460, draggable: false, resizable: false, closeOnEscape:false}});

    var createTaskDialog = new ObjectDialog({
        id: 'createtaskdialog',
        className: 'admindialog',
        fields: [
            {label: 'Duration', name:'duration', type:'select',
             selectOptions:[{value: '240', name: '4 minutes'},
                            {value: '300', name: '5 minutes'},
                            {value: '360', name: '6 minutes'},
                            {value: '420', name: '7 minutes'},
                            {value: '480', name: '8 minutes'},
                            {value: '540', name: '9 minutes'},
                            {value: '600', name: '10 minutes'}]}
        ],
        actions: [{
            name: 'remove', text: 'Remove ', icon: 'ui-icon-check',
            callback: function(dialog){
                
                runnerPrx.create(dialog.fieldValue('duration'),
                    function(){
                        dialog.close();
                        table.reload();                        
                    },
                    function(ex){
                        alert(ex.name + ' ' + ex.message);
                    }
                );
            }},
            {name: 'cancel', text: 'Cancel', icon: 'ui-icon-circle-close',
             callback: function(dialog){dialog.close();}}],
        dialogOptions: {autoOpen: false, title: 'Create Task', position:'center', modal: true,
            width:460, draggable: false, resizable: false, closeOnEscape:false}
    });

    var table = new Table({
        attributeName: 'file',
        fields: [
            {id: 'id', name: 'Id', visible:false, sortable: false},
            {id: 'script', name: 'Script', visible:true, sortable: false},
            {id: 'args', name: 'Args', visible:true, sortable: false},
            {id: 'date', name: 'Date', visible:true, sortable: false}],
        actions: [
            {text: 'Create Tasks', icon: 'ui-icon-circle-plus',
                 needSelection: false,
                 callback: function(table){
                   
                    createTaskDialog.setMessage(
                        '<div>Do you want to create a new task of type <strong>DemoScript.php</strong></div>');
                    createTaskDialog.open();
                 }},
            {text: 'Kill Selected Tasks', icon: 'ui-icon-circle-minus',
                 needSelection: true,
                 callback: function(table){
                    var objects = table.selectedObjects();
                    var items = '';
                    for(var i = 0; i < objects.length; i++)
                    {
                        var object = objects[i];
                        items += '<li>' + object.id + '</li>';
                    }
                    removeTaskDialog.setMessage(
                        '<div>Do you want to kill the following tasks:' +
                        '<ul>' + items + '</ul></div>');
                    removeTaskDialog.open();
                 }},
            {text: 'Refresh', icon: 'ui-icon-refresh', needSelection: false,
                callback: function(table){table.reload();}}
        ],
        loadPage: function(table, offset){
            runnerPrx.list(
                function(resultset){
                    table.loadObjects(resultset);
                },
                function(ex){
                    alert(ex.name + " " + ex.message);
                    table.removeLoadingOverlay();
                });
            return true;
        }
        });
    
    table.draw('.phprunner');
    table.loadPage(0);
    $('.phprunner').everyTime(10000, 'listTasks', function(){
        table.loadPage(0);
    });
});
</script>
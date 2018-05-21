<!-- Pharmacy POS: Copyright (c) 2018 Magnum Digital <joenyugoh@gmail.com> <https://www.gnu.org/licenses/lgpl.html> -->
<div class="page-header">
    <h1 style="margin-right: 20px; display: inline-block;">
        Expenses
    </h1>
    <button onclick="$('#addexpensesdialog').dialog('open');" id="addbtn" class="btn btn-primary btn-sm pull-right"><i class="icon-pencil align-top bigger-125"></i>Add</button>
</div><!-- /.page-header -->

<div class="row">
    <div class="col-xs-12">
        <!-- PAGE CONTENT BEGINS -->

        <div class="row">
            <div class="col-xs-12">

                <div class="table-header">
                    Manage your business expenses
                </div>

                <table id="expensestable" class="table table-striped table-bordered table-hover dt-responsive" style="width: 100%;">
                    <thead>
                    <tr>
                        <th data-priority="0" class="center noexport">
                            <label>
                                <input type="checkbox" class="ace" />
                                <span class="lbl"></span>
                            </label>
                        </th>
                        <th data-priority="4">ID</th>
                        <th data-priority="2">Name</th>
                        <th data-priority="3">Total</th>
                        <th data-priority="1" class="noexport">Actions</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div><!-- PAGE CONTENT ENDS -->
</div><!-- /.col -->
<div id="editexpensedialog" class="hide">
    <table>
        <tr>
            <td style="text-align: right;"><label>Name:&nbsp;</label></td>
            <td>
                <input class="form-control" id="expensename" type="text"/>
                <input id="expenseid" type="hidden"/>
            </td>
        </tr>
    </table>
</div>
<div id="addexpensesdialog" class="hide">
    <table>
        <tr>
            <td style="text-align: right;">
                <label>Name:&nbsp;</label>
            </td>
            <td>
                <input id="newexpensename" class="form-control" type="text"/><br/>
            </td>
        </tr>
    </table>
</div>
<div id="addexpensedialog" class="hide">
    <table>
        <input type="hidden" id="expenseidadd">
        <h3>Expense :: <span id="expensenameadd"></span></h3>
        <tr>
            <td style="text-align: right;">
                <label for="expenseamountadd">Amount: </label>
            </td>
            <td>
                <input type="text" class="form-control" id="expenseamountadd">
            </td>
        </tr>
        <tr>
            <td style="text-align: right;">
                <label for="expensedateadd">Date: </label>
            </td>
            <td>
                <input type="text" style="width: 100%;" class="form-control" id="expensedateadd" onclick="$(this).blur();"/>
            </td>
        </tr>
        <tr>
            <td style="text-align: right;">
                <label for="expensenoteadd">Note: </label>
            </td>
            <td>
                <textarea name="note" id="expensenoteadd" cols="30" rows="3" class="form-control"></textarea>
            </td>
        </tr>
    </table>
</div>
<div id="expenseshistdialog" class="hide">
    <div style="width: 100%; overflow-x: auto;">
        <table class="table table-responsive table-stripped">
            <thead>
            <tr>
                <th>Expense</th>
                <th>Location</th>
                <th>User</th>
                <th>Amount</th>
                <th>Note</th>
                <th>DT</th>
            </tr>
            </thead>
            <tbody id="expenseshisttable"></tbody>
        </table>
    </div>
</div>
<!-- page specific plugin scripts; migrated to index.php due to heavy use -->

<!-- inline scripts related to this page -->
<script type="text/javascript">
    var expenses = null;
    var datatable;
    $(function() {
        expenses = WPOS.getJsonData("expenses/get");
        var suparray = [];
        var supitem;
        for (var key in expenses){
            supitem = expenses[key];
            suparray.push(supitem);
        }
        datatable = $('#expensestable').dataTable({
            "bProcessing": true,
            "aaData": suparray,
            "aaSorting": [[ 2, "asc" ]],
            "aoColumns": [
                { mData:null, sDefaultContent:'<div style="text-align: center"><label><input class="ace dt-select-cb" type="checkbox"><span class="lbl"></span></label><div>', bSortable: false, sClass:"noexport" },
                { "mData":"id" },
                { "mData":"name" },
                { "mData": "total"},
                { mData:null, sDefaultContent:'<div class="action-buttons"><a class="green" onclick="openeditexpensedialog($(this).closest(\'tr\').find(\'td\').eq(1).text());"><i class="icon-pencil bigger-130"></i></a><a class="blue" onclick="openaddexpensedialog($(this).closest(\'tr\').find(\'td\').eq(1).text());"><i class="icon-plus bigger-130"></i></a><a class="green" onclick="openexpensehistorydialog($(this).closest(\'tr\').find(\'td\').eq(1).text());"><i class="icon-time bigger-130"></i></a><a class="red" onclick="removeExpense($(this).closest(\'tr\').find(\'td\').eq(1).text())"><i class="icon-trash bigger-130"></i></a></div>', "bSortable": false, sClass: "noexport" }
            ],
            "columns": [
                {},
                {type: "numeric"},
                {type: "string"},
                {type: "numeric"},
                {}
            ],
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                // Add selected row count to footer
                var selected = this.api().rows('.selected').count();
                return sPre+(selected>0 ? '<br/>'+selected+' row(s) selected <span class="action-buttons"><a class="red" onclick="removeSelectedCategories();"><i class="icon-trash bigger-130"></i></a></span>':'');
            }
        });

        datatable.find("tbody").on('click', '.dt-select-cb', function(e){
            var row = $(this).parents().eq(3);
            if (row.hasClass('selected')) {
                row.removeClass('selected');
            } else {
                row.addClass('selected');
            }
            datatable.api().draw(false);
            e.stopPropagation();
        });

        $('table.dataTable th input:checkbox').on('change' , function(){
            var that = this;
            $(this).closest('table.dataTable').find('tr > td:first-child input:checkbox')
                .each(function(){
                    var row = $(this).parents().eq(3);
                    if ($(that).is(":checked")) {
                        row.addClass('selected');
                        $(this).prop('checked', true);
                    } else {
                        row.removeClass('selected');
                        $(this).prop('checked', false);
                    }
                });
            datatable.api().draw(false);
        });

        // dialogs
        $( "#addexpensesdialog" ).removeClass('hide').dialog({
            resizable: false,
            width: 'auto',
            modal: true,
            autoOpen: false,
            title: "Add Expense",
            title_html: true,
            buttons: [
                {
                    html: "<i class='icon-save bigger-110'></i>&nbsp; Save",
                    "class" : "btn btn-success btn-xs",
                    click: function() {
                        saveExpense(true);
                    }
                }
                ,
                {
                    html: "<i class='icon-remove bigger-110'></i>&nbsp; Cancel",
                    "class" : "btn btn-xs",
                    click: function() {
                        $( this ).dialog( "close" );
                    }
                }
            ],
            create: function( event, ui ) {
                // Set maxWidth
                $(this).css("maxWidth", "375px");
            }
        });
        $( "#editexpensedialog" ).removeClass('hide').dialog({
            resizable: false,
            width: 'auto',
            modal: true,
            autoOpen: false,
            title: "Edit Expense",
            title_html: true,
            buttons: [
                {
                    html: "<i class='icon-save bigger-110'></i>&nbsp; Update",
                    "class" : "btn btn-success btn-xs",
                    click: function() {
                        saveExpense(false);
                    }
                }
                ,
                {
                    html: "<i class='icon-remove bigger-110'></i>&nbsp; Cancel",
                    "class" : "btn btn-xs",
                    click: function() {
                        $( this ).dialog( "close" );
                    }
                }
            ],
            create: function( event, ui ) {
                // Set maxWidth
                $(this).css("maxWidth", "375px");
            }
        });
        $( "#addexpensedialog" ).removeClass('hide').dialog({
            resizable: false,
            width: 'auto',
            modal: true,
            autoOpen: false,
            title: "Add an expense",
            title_html: true,
            buttons: [
                {
                    html: "<i class='icon-save bigger-110'></i>&nbsp; Add",
                    "class" : "btn btn-success btn-xs",
                    click: function() {
                        addExpense(false);
                    }
                }
                ,
                {
                    html: "<i class='icon-remove bigger-110'></i>&nbsp; Cancel",
                    "class" : "btn btn-xs",
                    click: function() {
                        $( this ).dialog( "close" );
                    }
                }
            ],
            create: function( event, ui ) {
                // Set maxWidth
                $(this).css("maxWidth", "375px");
            }
        });
        $( "#expenseshistdialog" ).removeClass('hide').dialog({
            resizable: false,
            width: 'auto',
            maxWidth: '700px',
            modal: true,
            autoOpen: false,
            title: "Expenses History",
            title_html: true,
            buttons: [
                {
                    html: "<i class='icon-remove bigger-110'></i>&nbsp; Close",
                    "class" : "btn btn-xs",
                    click: function() {
                        $( this ).dialog( "close" );
                    }
                }
            ],
            create: function( event, ui ) {
                // Set maxWidth
                $(this).css("maxWidth", "700px");
            }
        });

        // hide loader
        WPOS.util.hideLoader();
    });
    // updating records
    function openeditexpensedialog(id){
        var item = expenses[id];
        $("#expenseid").val(item.id);
        $("#expensename").val(item.name);
        $("#editexpensedialog").dialog("open");
    }
    // add specific expenses
    function openaddexpensedialog(id){
        var item = expenses[id];
        $("#expenseidadd").val(item.id);
        $("#expensenameadd").text(item.name);
        // Add expense datepickers
        $("#expensedateadd").datepicker({dateFormat:"dd/mm/yy"});
        $("#expensedateadd").datepicker('setDate', new Date());
        $("#addexpensedialog").dialog("open");
    }
    // show expenses
    function openexpensehistorydialog(id){
        WPOS.util.showLoader();
        var expenseitems = WPOS.sendJsonData("expenses/history", JSON.stringify({expenseid: id, locationid: JSON.parse(localStorage.getItem('wpos_config')).locationid}));
        // populate expenses dialog with list
        $("#expenseshisttable").html("");
        var expense;
        for (var i in expenseitems){
            expense = expenseitems[i];
            $("#expenseshisttable").append('<tr><td>'+expense.expense+'</td><td>'+WPOS.locations[expense.locationid].name+'</td><td>'+WPOS.users[expense.userid].username+'</td><td>'+expense.amount+'</td><td>'+expense.notes+'</td><td>'+(moment(parseInt(expense.dt)).format('DD/MM/YYYY H:mm:ss'))+'</td></tr>');
        }
        WPOS.util.hideLoader();
        $("#expenseshistdialog").dialog('open');
    }

    function addExpense() {
        // show loader
        WPOS.util.showLoader();
        var item = {}, result;
        var processdt = $("#expensedateadd").datepicker("getDate");
        processdt.setHours(new Date().getHours());
        processdt.setMinutes(new Date().getMinutes());
        processdt.setSeconds(new Date().getSeconds());
        processdt.setMilliseconds(new Date().getMilliseconds());
        item.dt = processdt.getTime();
        item.ref = (new Date()).getTime()+"-1-"+Math.floor((Math.random() * 10000) + 1);
        item.expenseid = $('#expenseidadd').val();
        item.amount = $('#expenseamountadd').val();
        item.locationid = JSON.parse(localStorage.getItem('wpos_config')).locationid;
        item.userid = WPOS.loggeduser.id;
        item.notes = $('#expensenoteadd').val();
        item.status = 1;
        result = WPOS.sendJsonData("expenses/item/add", JSON.stringify(item));
        if (result!==false){
            expenses[result.id] = result;
            reloadTable();
            $('#expenseamountadd').val('');
            $("#addexpensedialog").dialog("close");
        }
        //Hide loader
        WPOS.util.hideLoader();
    }

    function saveExpense(isnewitem){
        // show loader
        WPOS.util.showLoader();
        var item = {}, result;
        if (isnewitem){
            // adding a new category
            var name_field = $("#newexpensename");
            item.name = name_field.val();
            result = WPOS.sendJsonData("expenses/add", JSON.stringify(item));
            if (result!==false){
                expenses[result.id] = result;
                reloadTable();
                name_field.val('');
                $("#addexpensesdialog").dialog("close");
            }
        } else {
            // updating an item
            item.id = $("#expenseid").val();
            item.name = $("#expensename").val();
            result = WPOS.sendJsonData("expenses/edit", JSON.stringify(item));
            if (result!==false){
                expenses[result.id] = result;
                reloadTable();
                $("#editexpensedialog").dialog("close");
            }
        }
        // hide loader
        WPOS.util.hideLoader();
    }
    function removeExpense(id){

        var answer = confirm("Are you sure you want to delete this expense?");


        if (answer){
            // show loader
            WPOS.util.hideLoader();
            if (WPOS.sendJsonData("expenses/delete", '{"id":'+id+'}')){
                delete expenses[id];
                reloadTable();
            }
            // hide loader
            WPOS.util.hideLoader();
        }
    }

    function removeSelectedCategories(){
        var ids = datatable.api().rows('.selected').data().map(function(row){ return row.id });

        var answer = confirm("Are you sure you want to delete "+ids.length+" selected items?");
        if (answer){
            // show loader
            WPOS.util.hideLoader();
            if (WPOS.sendJsonData("expenses/delete", '{"id":"'+ids.join(",")+'"}')){
                for (var i=0; i<ids.length; i++){
                    delete expenses[ids[i]];
                }
                reloadTable();
            }
            // hide loader
            WPOS.util.hideLoader();
        }
    }

    function reloadData(){
        expenses = WPOS.getJsonData("expenses/get");
        reloadTable();
    }
    function reloadTable(){
        var suparray = [];
        var tempsup;
        for (var key in expenses){
            tempsup = expenses[key];
            suparray.push(tempsup);
        }
        datatable.fnClearTable(false);
        datatable.fnAddData(suparray, false);
        datatable.api().draw(false);
    }
</script>
<style type="text/css">
    #expensestable_processing {
        display: none;
    }
</style>
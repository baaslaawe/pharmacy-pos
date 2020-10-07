<!-- WallacePOS: Copyright (c) 2014 WallaceIT <micwallace@gmx.com> <https://www.gnu.org/licenses/lgpl.html> -->
<div class="page-header">
    <h1 class="inline">
        Reports
    </h1>
    <select id="reptype" onchange="generateReport();" style="vertical-align: middle; margin-right: 20px; margin-bottom: 5px;">
        <option value="stats/general">Summary</option>
        <option value="stats/accounting">Mini-Accounting</option>
        <option value="stats/takings">Cash Count</option>
        <option value="stats/itemselling">Item Sales</option>
        <option value="stats/categoryselling">Category Sales</option>
        <option value="stats/supplyselling">Supplier Sales</option>
        <option value="stats/expenses">Expenses</option>
        <option value="stats/bills">Bills</option>
        <option value="stats/stock">Current Stock</option>
        <option value="stats/daapatients">DAA List</option>
        <option value="stats/order">Purchase Order</option>
        <option value="stats/expired">Expired Items</option>
        <option value="stats/devices">Device Cash</option>
        <option value="stats/locations">Location Cash</option>
        <option value="stats/users">User Cash</option>
        <option value="stats/tax">Tax Breakdown</option>
    </select>
    <div style="display: inline-block; vertical-align:middle; margin-right: 20px;">
        <label>Transactions
        <select id="reptranstype" onchange="generateReport();" style="vertical-align: middle; margin-right: 20px; margin-bottom: 5px;">
            <option value="all">All Sales</option>
            <option value="sale">POS Sales</option>
            <option value="invoice">Invoices</option>
        </select>
        </label>
    </div>
    <div style="display: inline-block; vertical-align:middle; margin-right: 20px;">
        <label>Range: <input type="text" style="width: 85px;" id="repstime" onclick="$(this).blur();" /></label>
        <label>to <input type="text" style="width: 85px;" id="repetime" onclick="$(this).blur();" /></label>
    </div>
    <div style="display: inline-block; vertical-align: top;">
        <button onclick="printCurrentReport();" class="btn btn-primary btn-sm"><i class="icon-print align-top bigger-125"></i>Print</button>&nbsp;
        <button class="btn btn-success btn-sm" onclick="exportCurrentReport();"><i class="icon-cloud-download align-top bigger-125"></i>Export CSV</button>
    </div>
</div><!-- /.page-header -->
<div class="row">
    <div class="col-xs-12">
        <!-- PAGE CONTENT BEGINS -->
        <div style="overflow-x: auto; padding: 10px;">
            <div id="reportcontain">

            </div>
        </div>
    </div><!-- PAGE CONTENT ENDS -->
</div><!-- /.col -->
<script type="text/javascript">
    var repdata;
    var etime;
    var stime;

    // Generate report
    function generateReport(){
        // show loader
        WPOS.util.showLoader();
        var type = $("#reptype").val();
        // load the data
        var rtype = $("#reptranstype").val();
        if(type === 'stats/expenses')
            rtype= 'expense';
        if(type === 'stats/bills')
            rtype= 'bill';
        repdata = WPOS.sendJsonData(type, JSON.stringify({"stime":stime, "etime":etime, "type":rtype}));
        // populate the report using the correct function
        switch (type){
            case "stats/general":
                populateSummary();
                break;
            case "stats/accounting":
                populateAccounting();
                break;
            case "stats/takings":
                populateTakings("Cash Count", "Method");
                break;
            case "stats/itemselling":
                populateItems("Item Sales");
                break;
            case "stats/categoryselling":
                populateSelling("Category Sales");
                break;
            case "stats/supplyselling":
                populateSelling("Supplier Sales");
                break;
            case "stats/stock":
                populateStock();
                break;
            case "stats/daapatients":
                populateDAA();
                break;
            case "stats/order":
                populateOrder();
                break;
            case "stats/expired":
              populateExpired();
              break;
            case "stats/costs":
              populateCost();
              break;
            case "stats/devices":
                populateTakings("Device Cash", "Device Name");
                break;
            case "stats/locations":
                populateTakings("Location Cash", "Location Name");
                break;
            case "stats/users":
                populateTakings("User Cash", "User Name");
                break;
            case "stats/bills":
                populateBills("Bills");
                break;
            case "stats/expenses":
                populateExpenses("Expenses");
                break;
            case "stats/tax":
                populateTax();
        }
        // hide loader
        WPOS.util.hideLoader();
    }

    // REPORT GEN FUNCTIONS
    function getReportHeader(heading){
        return "<div id='#repheader' style='text-align: center; margin-bottom: 5px;'><h3>"+heading+"</h3><h5>"+$("#repstime").val()+" - "+$("#repetime").val()+"</h5></div>";
    }

    function getCurrentReportHeader(heading){
        var timestamp = new Date();
        timestamp = timestamp.getTime();
        return "<div id='#repheader' style='text-align: center; margin-bottom: 5px;'><h3>"+heading+"</h3><h5>"+WPOS.util.getDateFromTimestamp(timestamp)+"</h5>";
    }

    function populateSummary(){
        var rtype = $("#reptranstype").val();
        var html = getReportHeader("Summary");
        var all = rtype === 'all';
        var sale = rtype === 'sale';
        var invoice = rtype === 'invoice';
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td></td><td>#</td><td>Total</td></tr></thead><tbody>";
        all ? html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+repdata.salerefs+'\');">Sales & Invoices</a></td><td>'+repdata.salenum+'</td><td>'+WPOS.util.currencyFormat(repdata.saletotal)+'</td></tr>': '';
        sale ? html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+repdata.salerefs+'\');">Sales</a></td><td>'+repdata.salenum+'</td><td>'+WPOS.util.currencyFormat(repdata.saletotal)+'</td></tr>': '';
        invoice ? html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+repdata.invoicerefs+'\');">Invoices</a></td><td>'+repdata.invoicenum+'</td><td>'+WPOS.util.currencyFormat(repdata.invoicetotal)+'</td></tr>': '';
        html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+repdata.refundrefs+'\');">Refunds</a></td><td>'+repdata.refundnum+'</td><td>'+WPOS.util.currencyFormat(repdata.refundtotal)+'</td></tr>';
        html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+repdata.voidrefs+'\');">Voids</a></td><td>'+repdata.voidnum+'</td><td>'+WPOS.util.currencyFormat(repdata.voidtotal)+'</td></tr>';
        all ? html += '<tr><td><a onclick="WPOS.transactions.openExpensesList(\''+repdata.expensesrefs+'\');">Expenses</a></td><td>'+repdata.expensesnum+'</td><td>'+WPOS.util.currencyFormat(repdata.expenses)+'</td></tr>': '';
        all ? html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+repdata.refs+'\');">Revenue(Total money collected)</a></td><td>'+repdata.salenum+'</td><td>'+WPOS.util.currencyFormat(repdata.totaltakings)+'</td></tr>': '';
        invoice ? html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+repdata.invoicerefs+'\');">Paid Invoices</a></td><td>'+repdata.invoicenum+'</td><td>'+WPOS.util.currencyFormat(parseFloat(repdata.invoicetotal)- parseFloat(repdata.invoicebalance))+'</td></tr>': '';
        html += '<tr><td>Discounts</td><td>'+repdata.salenum+'</td><td>'+WPOS.util.currencyFormat(repdata.discounts)+'</td></tr>';
        html += '<tr><td>Cost</td><td>'+repdata.salenum+'</td><td>'+WPOS.util.currencyFormat(repdata.cost)+'</td></tr>';
        all ? html += '<tr><td>Gross profit</td><td>'+repdata.salenum+'</td><td>'+WPOS.util.currencyFormat(repdata.profit)+'</td></tr>': html += '<tr><td>Profit</td><td>'+repdata.salenum+'</td><td>'+WPOS.util.currencyFormat(repdata.profit)+'</td></tr>';
        all ? html += '<tr><td>Net profit</td><td>'+repdata.salenum+'</td><td>'+WPOS.util.currencyFormat(repdata.netprofit)+'</td></tr>': '';
        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }
    function populateAccounting(){
        var html = getReportHeader("Mini-Accounting");
        var value = repdata.stockvalue + repdata.revenue - repdata.bills - repdata.expenses;
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td></td><td>#</td><td>Total</td></tr></thead><tbody>";
        html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+repdata.refs+'\');">Stock value b4 margin</a></td><td>'+repdata.stocktotal+'</td><td>'+WPOS.util.currencyFormat(repdata.stockvalueBeforeMargin)+'</td></tr>'                                                                                                                                                               ;
        html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+repdata.refs+'\');">Stock value</a></td><td>'+repdata.stocktotal+'</td><td>'+WPOS.util.currencyFormat(repdata.stockvalue)+'</td></tr>'                                                                                                                                                               ;
        html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+repdata.refs+'\');">Revenue</a></td><td>'+repdata.stocktotal+'</td><td>'+WPOS.util.currencyFormat(repdata.revenue)+'</td></tr>';
        html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+repdata.billsrefs+'\');">Bills</a></td><td>'+repdata.billsnum+'</td><td>'+WPOS.util.currencyFormat(repdata.bills)+'</td></tr>';
        html += '<tr><td><a onclick="WPOS.transactions.openExpensesList(\''+repdata.expensesrefs+'\');">Expenses</a></td><td>'+repdata.expensesnum+'</td><td>'+WPOS.util.currencyFormat(repdata.expenses)+'</td></tr>';
        html += '<tr><td><a onclick="WPOS.transactions.openExpensesList(\''+repdata.expensesrefs+'\');">Business value</a></td><td>  </td><td>'+WPOS.util.currencyFormat(value)+'</td></tr>';
        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }

    function populateTakings(repname, colname){
        var html = getReportHeader(repname);
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>"+colname+"</td><td># Sales</td><td>Cash</td><td># Refunds</td><td>Refunds</td><td>Balance</td></tr></thead><tbody>";
        var rowdata;
        for (var i in repdata){
            rowdata = repdata[i];
            html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+rowdata.refs+'\');">'+(rowdata.hasOwnProperty('name')?rowdata.name:i)+'</a></td><td>'+rowdata.salenum+'</td><td>'+WPOS.util.currencyFormat(rowdata.saletotal)+'</td><td><a onclick="WPOS.transactions.openTransactionList(\''+rowdata.refundrefs+'\');">'+rowdata.refundnum+'</a></td><td>'+WPOS.util.currencyFormat(rowdata.refundtotal)+'</td><td>'+WPOS.util.currencyFormat(rowdata.balance)+'</td></tr>';
        }

        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }

    function populateSelling(title){
        var html = getReportHeader(title);
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Name</td><td># Sold</td><td>Total</td><td># Refunded</td><td>Total</td><td>Balance</td></tr></thead><tbody>";
        var rowdata;
        for (var i in repdata){
            rowdata = repdata[i];
            html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+rowdata.refs+'\');">'+rowdata.name+'</a></td><td>'+rowdata.soldqty+'</td><td>'+WPOS.util.currencyFormat(rowdata.soldtotal)+'</td><td>'+rowdata.refundqty+'</td><td>'+WPOS.util.currencyFormat(rowdata.refundtotal)+'</td><td>'+WPOS.util.currencyFormat(rowdata.balance)+'</td></tr>';
        }

        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }

    function populateExpenses(title){
        var html = getReportHeader(title);
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Name</td><td># </td><td>Total</td></tr></thead><tbody>";
        var rowdata;
        for (var i in repdata){
            rowdata = repdata[i];
            html += '<tr><td><a onclick="WPOS.transactions.openExpensesList(\''+rowdata.refs+'\');">'+rowdata.name+'</a></td><td>'+rowdata.enum+'</td><td>'+WPOS.util.currencyFormat(rowdata.total)+'</td></tr>';
        }

        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }

    function populateBills(title){
        var html = getReportHeader(title);
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Name</td><td># </td><td>Total</td></tr></thead><tbody>";
        var rowdata;
        for (var i in repdata){
            rowdata = repdata[i];
            html += '<tr><td><a onclick="WPOS.transactions.openExpensesList(\''+rowdata.refs+'\');">'+rowdata.name+'</a></td><td>'+rowdata.enum+'</td><td>'+WPOS.util.currencyFormat(rowdata.total)+'</td></tr>';
        }

        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }

    function populateItems(title){
        var html = getReportHeader(title);
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Name</td><td># Sold</td><td>Discounts</td><td>Tax</td><td>Total</td><td># Refunded</td><td>Total</td><td>Balance</td></tr></thead><tbody>";
        var items = repdata;
      var names = [];
      for (var i in items) {
        names[items[i].name] = [];
        names[items[i].name].name = items[i].name;
        names[items[i].name].refs = items[i].refs;
        names[items[i].name].discounttotal = 0;
        names[items[i].name].refundqty = 0;
        names[items[i].name].refundtotal = 0;
        names[items[i].name].balance = 0;
        names[items[i].name].taxtotal = 0;
        names[items[i].name].soldqty = 0;
        names[items[i].name].netqty = 0;
        names[items[i].name].soldtotal = 0;
      }
      for (var i in items) {
        names[items[i].name].netqty += parseInt(items[i].netqty);// Sum all the qty from same item name
        names[items[i].name].soldtotal += parseFloat(items[i].soldtotal);// Sum all the qty from same item name
        names[items[i].name].soldqty += parseInt(items[i].soldqty);// Sum all the qty from same item name
        names[items[i].name].taxtotal += parseInt(items[i].taxtotal);// Sum all the qty from same item name
        names[items[i].name].balance += parseFloat(items[i].balance);// Sum all the qty from same item name
        names[items[i].name].refundtotal += parseFloat(items[i].refundtotal);// Sum all the qty from same item name
        names[items[i].name].refundqty += parseFloat(items[i].refundqty);// Sum all the qty from same item name
        names[items[i].name].discounttotal += parseFloat(items[i].discounttotal);// Sum all the qty from same item name
      }
      var filteredItems = [];
      for (var i in items) {
        filteredItems.push(items[i].name);// get all names
      }
      var uniqueItems = [...new Set(filteredItems)]; //get only unque names
      var list = [];
      for(var i in names) {
        if (uniqueItems.indexOf(names[i].name) !== -1) {
          list[uniqueItems.indexOf(names[i].name)] = names[i];
        }
      }
      var sort = [];
      var order = [];
      // put indexes into array and sort
      for (var i in list){
        order.push([list[i]['soldqty'], list[i]]);
        sort.push([i, list[i].soldtotal]);
      }
      repdata = list;
        for (var i in repdata){
            rowdata = repdata[i];
            html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+rowdata.refs+'\');">'+rowdata.name+'</a></td><td>'+rowdata.soldqty+'</td><td>'+WPOS.util.currencyFormat(rowdata.discounttotal)+'</td><td>'+WPOS.util.currencyFormat(rowdata.taxtotal)+'</td><td>'+WPOS.util.currencyFormat(rowdata.soldtotal)+'</td><td>'+rowdata.refundqty+'</td><td>'+WPOS.util.currencyFormat(rowdata.refundtotal)+'</td><td>'+WPOS.util.currencyFormat(rowdata.balance)+'</td></tr>';
        }

        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }

    function populateTax(){
        var html = getReportHeader("Tax Breakdown");
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Name</td><td># Items</td><td>Sale Subtotal</td><td>Tax</td><td>Refund Subtotal</td><td>Refund Tax</td><td>Total Tax</td></tr></thead><tbody>";
        var rowdata;
        for (var i in repdata){
            if (i!=0){
                rowdata = repdata[i];
                html += '<tr><td><a onclick="WPOS.transactions.openTransactionList(\''+rowdata.refs+'\');">'+rowdata.name+'</a></td><td>'+rowdata.qtyitems+'</td><td>'+WPOS.util.currencyFormat(rowdata.saletotal)+'</td><td>'+WPOS.util.currencyFormat(rowdata.saletax)+'</td><td>'+WPOS.util.currencyFormat(rowdata.refundtotal)+'</td><td>'+WPOS.util.currencyFormat(rowdata.refundtax)+'</td><td>'+WPOS.util.currencyFormat(rowdata.balance)+'</td></tr>';
            }
        }

        html += "</tbody></table><br/>";

        rowdata = repdata[0];
        html += '<p style="text-align: center;">Note: <a onclick="WPOS.transactions.openTransactionList(\''+rowdata.refs+'\');">'+rowdata.qty+'</a> sales have been cash rounded to a total amount of '+WPOS.util.currencyFormat(rowdata.total)+'.<br/>Since tax is calculated on a per item level, rounding has not been included in the calculations above.<br/>Subtotals above have discounts applied.</p>';

        $("#reportcontain").html(html);
    }

    function populateStock(){
        var html = getCurrentReportHeader("Current Stock");
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Name</td><td>Location</td><td>Stock Qty</td><td>Stock Value</td></tr></thead><tbody>";
      var items = repdata;
      var names = [];
      for (var i in items) {
        names[items[i].name] = [];
        names[items[i].name].name = items[i].name;
        names[items[i].name].stockType = items[i].stockType;
        names[items[i].name].stocklevel = 0;
        names[items[i].name].stockvalue = 0;
        names[items[i].name].location = items[i].location;
      }
      for (var i in items) {
        names[items[i].name].stocklevel += parseInt(items[i].stocklevel);// Sum all the qty from same item name
        names[items[i].name].stockvalue += parseFloat(items[i].stockvalue);// Sum all the qty from same item name
      }
      var filteredItems = [];
      for (var i in items) {
        filteredItems.push(items[i].name);// get all names
      }
      var uniqueItems = [...new Set(filteredItems)]; //get only unque names
      var list = [];
      for(var i in names) {
        if (uniqueItems.indexOf(names[i].name) !== -1) {
          list[uniqueItems.indexOf(names[i].name)] = names[i];
        }
      }
      var sort = [];
      var order = [];
      // put indexes into array and sort
      for (var i in list){
        order.push([list[i]['soldqty'], list[i]]);
        sort.push([i, list[i].soldtotal]);
      }
      repdata = list;
        for (var i in repdata){
            rowdata = repdata[i];
            if (rowdata.stockType === '1')
              html += "<tr><td>"+rowdata.name+"</td><td>"+rowdata.location+"</td><td>"+rowdata.stocklevel+"</td><td>"+rowdata.stockvalue+"</td></tr>"
        }
        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }

    function populateDAA(){
        var html = getCurrentReportHeader("DDA List");
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Customer </td><td>Drug</td><td>Quantity</td></tr></thead><tbody>";
        for (var i in repdata){
            rowdata = repdata[i];
            html += "<tr><td>"+rowdata.customer+"</td><td>"+rowdata.drug+"</td><td>"+rowdata.qty+"</td></tr>"
        }
        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }

    function populateOrder(){
        var html = getCurrentReportHeader("Purchase Order");
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Name</td><td>Stock Qty</td><td>Reorder Point</td></tr></thead><tbody>";
        console.log(repdata)
        for (var i in repdata){
            rowdata = repdata[i];
            html += "<tr><td>"+rowdata.customer+"</td><td>"+rowdata.drug+"</td><td>"+rowdata.qty+"</td></tr>"
        }
        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }

    function populateOrder(filter=false){
        var html = getCurrentReportHeader("Purchase Order");
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Name</td><td>Supplier</td><td>Cost</td><td>Stock Qty</td><td>Reorder Point</td></tr></thead><tbody>";
        var sortable = {};

        for(var i in repdata) {
            var drug = repdata[i];
            if(drug['name']){
                if(drug['items'].length > 1 && filter) {
                    let min = drug.items[0];
                    drug.items.forEach(item=> {
                        if(item.cost <= min.cost)
                            min = item;
                    });
                    drug.items = [min];
                }
                sortable[drug['name'].toLowerCase()] = drug;
            }
        }
        for (var i in sortable){
            var items = sortable[i].items;
            for(var item in items){
                let rowdata = items[item];
                if (parseInt(rowdata.stocklevel) <= parseInt(rowdata.reorderPoint) && rowdata.stockType == '1'){
                    html += "<tr><td>"+rowdata.name+"</td><td>"+rowdata.supplier+"</td><td>"+rowdata.cost+"</td><td>"+rowdata.stocklevel+"</td><td>"+rowdata.reorderPoint+"</td></tr>"
                }
            }
        }
        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }

    function populateExpired(){
        var html = getCurrentReportHeader("Expired Items");
        html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Name</td><td>Supplier</td><td>Location</td><td>Stock Qty</td><td>Expiry Date</td></tr></thead><tbody>";
        for (var i in repdata){
            rowdata = repdata[i];
            var date = rowdata.expiryDate.split('/');
            if (new Date(date['2'], (date['1']-1), date['0']) <= new Date())
              html += "<tr><td>"+rowdata.name+"</td><td>"+rowdata.supplier+"</td><td>"+rowdata.location+"</td><td>"+rowdata.stocklevel+"</td><td>"+rowdata.expiryDate+"</td></tr>"
        }
        html += "</tbody></table>";

        $("#reportcontain").html(html);
    }

    function populateCost(){
      var html = getCurrentReportHeader("Suppliers Cost");
      html += "<table class='table table-stripped' style='width: 100%'><thead><tr><td>Name</td><td>Supplier</td><td>Cost</td></tr></thead><tbody>";
//      for (var i in repdata){
//        rowdata = repdata[i];
//        html += "<tr><td>"+rowdata.name+"</td><td>"+rowdata.supplier+"</td><td>"+rowdata.cost+"</td></tr>"
//      }
      html += "</tbody></table>";

      $("#reportcontain").html(html);
    }

    function printCurrentReport(){
        if($('#reportcontain').find('h3').text() == 'Current Stock')
            printCurrentStock();
        else
            browserPrintHtml($("#reportcontain").html())
    }

    function exportCurrentReport(){
        if($('#reportcontain').find('h3').text() === 'Purchase Order') {
            populateOrder(true);
        }
        var data  = WPOS.table2CSV($("#reportcontain"));
        var filename = $("#reportcontain div h3").text()+"-"+$("#reportcontain div h5").text();
        filename = filename.replace(" ", "");
        WPOS.initSave(filename, data);
    }

    function browserPrintHtml(html){
        // var printw = window.open('', 'wpos report', 'height=800,width=650');
        // printw.document.write('<html><head><title>Wpos Report</title>');
        // // printw.document.write('<link media="print" href="assets/css/bootstrap.min.css" rel="stylesheet"/><link media="all" rel="stylesheet" href="assets/css/font-awesome.min.css"/><link media="all" rel="stylesheet" href="assets/css/ace-fonts.css"/><link media="all" rel="stylesheet" href="assets/css/ace.min.css"/>');
        // printw.document.write('</head><body style="background-color: #FFFFFF;">');
        // printw.document.write(html);
        // printw.document.write('</body></html>');
        // printw.document.close();
        //
        // setTimeout(function () {
        //   // printw.print();
        //   printw.close();
        // }, 1000);
        WPOS.print.printCurrentReport();
    }

    function printCurrentStock() {
        var stock = [], item = {};
        for(var i in repdata){
            item = repdata[i];
            if (item['stockType'] == 1 && item['stocklevel'] > 0)
                stock.push({
                    name: item['name'],
                    qty: item['stocklevel'],
                    value: item['stockvalue']
                });
        }
        WPOS.print.printStock(stock);
    }

    $(function(){
        etime = new Date().getTime();
        stime = (etime - 604800000); // a week ago

        $("#repstime").datepicker({dateFormat:"dd/mm/yy", maxDate: new Date(etime),
            onSelect: function(text, inst){
                var date = $("#repstime").datepicker("getDate");
                date.setHours(0); date.setMinutes(0); date.setSeconds(0);
                stime = date.getTime();
                generateReport();
            }
        });
        $("#repetime").datepicker({dateFormat:"dd/mm/yy", maxDate: new Date(etime),
            onSelect: function(text, inst){
                var date = $("#repetime").datepicker("getDate");
                date.setHours(23); date.setMinutes(59); date.setSeconds(59);
                etime = date.getTime();
                generateReport();
            }
        });

        $("#repstime").datepicker('setDate', new Date(stime));
        $("#repetime").datepicker('setDate', new Date(etime));
        generateReport(); // generate initial report

        // hide loader
        WPOS.util.hideLoader();
    });
</script>
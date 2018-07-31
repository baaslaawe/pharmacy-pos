<!-- WallacePOS: Copyright (c) 2014 WallaceIT <micwallace@gmx.com> <https://www.gnu.org/licenses/lgpl.html> -->
<?php ini_set("date.timezone", "Africa/Nairobi"); ?>
<div class="page-header">
    <h1>
        Dashboard
        <small>
            <i class="icon-double-angle-right"></i>
            overview &amp; stats
        </small>
        <p class="pull-right">Subscription Status : <span id="status"></span></p>
    </h1>
</div><!-- /.page-header -->
<div class="row">
    <div class="space-6"></div>

<div class="col-sm-5">
    <div class="widget-box transparent">
        <div class="widget-header widget-header-flat">
            <h4 class="lighter">
                <i class="icon-dollar"></i>
                Today's Takings
            </h4>
            <div style="display: inline-block; vertical-align:middle; margin-right: 20px;">
                <label>Date: <input type="text" style="width: 85px;" id="repstime" onclick="$(this).blur();" /></label>
            </div>
        </div>
        <div class="widget-body" style="padding-top: 10px; text-align: center;">
          <div class="infobox infobox-green infobox-sales">
              <div class="infobox-icon">
                  <i class="icon-shopping-cart"></i>
              </div>

              <div class="infobox-data">
                  <span id="salenum" class="infobox-data-number">-</span>
                  <div class="infobox-content">POS Sales</div>
              </div>
              <div id="saletotal" class="stat stat-success">-</div>
          </div>
          <div class="infobox infobox-green infobox-invoices">
            <div class="infobox-icon">
              <span class="octicon octicon-clippy"></span>
            </div>
            <div class="infobox-data">
              <span id="invoicenum" class="infobox-data-number">-</span>
              <div class="infobox-content">Invoices</div>
            </div>
            <div id="invoicetotal" class="stat stat-success">-</div>
          </div>

          <div class="infobox infobox-red">
            <div class="infobox-icon">
              <span class="octicon octicon-diff"></span>
            </div>

            <div class="infobox-data">
              <div id="invoicesbalance" class="stat stat-success">-</div>
              <div class="infobox-content">Invoices Balance</div>
            </div>
          </div>

          <div class="infobox infobox-green">
            <div class="infobox-icon">
              <span class="octicon octicon-checklist"></span>
            </div>
            <div class="infobox-data">
              <div id="invoicespaid" class="stat stat-success">-</div>
              <div class="infobox-content">Invoices Paid</div>
            </div>
          </div>
          <div class="infobox infobox-orange infobox-refunds">
              <div class="infobox-icon">
                  <i class="glyphicon glyphicon-repeat"></i>
              </div>
              <div class="infobox-data">
                  <span id="refundnum" class="infobox-data-number">-</span>
                  <div class="infobox-content">Refunds</div>
              </div>
              <div id="refundtotal" class="stat stat-important">-</div>
          </div>
          <div class="infobox infobox-red infobox-voids">
              <div class="infobox-icon">
                  <i class="icon-ban-circle"></i>
              </div>
              <div class="infobox-data">
                  <span id="voidnum" class="infobox-data-number">-</span>
                  <div class="infobox-content">Voids</div>
              </div>
              <div id="voidtotal" class="stat stat-important">-</div>
          </div>
            <div class="infobox infobox-red infobox-voids">
                <div class="infobox-icon">
                    <i class="icon-ban-circle"></i>
                </div>
                <div class="infobox-data">
                    <span id="cash" class="infobox-data-number">-</span>
                    <div class="infobox-content">Total Cash</div>
                </div>
            </div>

            <div class="infobox infobox-green infobox-mpesa">
                <div class="infobox-icon">
                    <span class="octicon octicon-device-mobile"></span>
                </div>

                <div class="infobox-data">
                    <span id="mpesa" class="infobox-data-number">-</span>
                    <div class="infobox-content">Total Mpesa</div>
                </div>
            </div>

            <div class="infobox infobox-blue infobox-bank">
                <div class="infobox-icon">
                    <span class="octicon octicon-credit-card"></span>
                </div>

                <div class="infobox-data">
                    <span id="bank" class="infobox-data-number">-</span>
                    <div class="infobox-content">Total Bank</div>
                </div>
            </div>

            <div class="infobox infobox-red infobox-credit">
                <div class="infobox-icon">
                   <span class="octicon octicon-issue-opened"></span>
                </div>

                <div class="infobox-data">
                    <span id="credit" class="infobox-data-number">-</span>
                    <div class="infobox-content">Total Credit</div>
                </div>
            </div>
            <div class="infobox infobox-blue2 infobox-takings">
                <div class="infobox-icon">
                    <i class="icon-briefcase"></i>
                </div>
                <div class="infobox-data">
                    <span id="takings" class="infobox-data-number">-</span>
                    <div class="infobox-content">Revenue</div>
                </div>
            </div>
            <div class="infobox infobox-orange infobox-bills">
                <div class="infobox-icon">
                    <i class="icon-forward"></i>
                </div>
                <div class="infobox-data">
                    <span id="billsnum" class="infobox-data-number">-</span>
                    <div class="infobox-content">Bills</div>
                </div>
                <div id="bills" class="stat stat-important">-</div>
            </div>
            <div class="infobox infobox-orange infobox-expenses">
                <div class="infobox-icon">
                    <i class="icon-forward"></i>
                </div>
                <div class="infobox-data">
                    <span id="expensesnum" class="infobox-data-number">-</span>
                    <div class="infobox-content">Expenses</div>
                </div>
                <div id="expenses" class="stat stat-important">-</div>
            </div>
            <div class="infobox infobox-orange infobox-cost">
                <div class="infobox-icon">
                    <i class="icon-dollar"></i>
                </div>
                <div class="infobox-data">
                    <span id="cost" class="infobox-data-number">-</span>
                    <div class="infobox-content">Cost</div>
                </div>
            </div>
            <div class="infobox infobox-green infobox-actual-cash">
                <div class="infobox-icon">
                    <i class="icon-bitcoin"></i>
                </div>
                <div class="infobox-data">
                    <span id="actualcash" class="infobox-data-number">-</span>
                    <div class="infobox-content">Actual cash</div>
                </div>
            </div>
    </div>
</div>
</div>

<div class="vspace-sm"></div>

<div class="col-sm-7">
    <div class="widget-box transparent">
        <div class="widget-header widget-header-flat">
            <h4 class="lighter">
                <i class="icon-signal"></i>
                Sale Graph
            </h4>
            <div class="widget-toolbar no-border">
                <button class="btn btn-minier btn-primary dropdown-toggle" data-toggle="dropdown">
                    <span id="grange">This Week</span>
                    <i class="icon-angle-down icon-on-right bigger-110"></i>
                </button>

                <ul id="grangevalues" class="dropdown-menu pull-right dropdown-125 dropdown-lighter dropdown-caret">
                    <li onclick="setGraph($(this));" class="active" >
                        <a class="blue">
                            <i class="icon-caret-right bigger-110">&nbsp;</i>
                            <span class="grangeval">This Week</span>
                        </a>
                    </li>

                    <li onclick="setGraph($(this));">
                        <a>
                            <i class="icon-caret-right bigger-110 invisible">&nbsp;</i>
                            <span class="grangeval">Last Week</span>
                        </a>
                    </li>

                    <li onclick="setGraph($(this));">
                        <a>
                            <i class="icon-caret-right bigger-110 invisible">&nbsp;</i>
                            <span class="grangeval">This Month</span>
                        </a>
                    </li>

                    <li onclick="setGraph($(this));">
                        <a>
                            <i class="icon-caret-right bigger-110 invisible">&nbsp;</i>
                            <span class="grangeval">Last Month</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

                    <div class="widget-body">
                        <div class="widget-main padding-4">
                            <div id="sales-charts"></div>
                        </div><!-- /widget-main -->
                    </div><!-- /widget-body -->
                </div><!-- /widget-box -->
            </div><!-- /span -->

        </div><!-- /row -->

        <div class="vspace-sm"></div>
        <div class="hr hr32 hr-dotted hidden-480 hidden-320 hidden-xs"></div>


        <div class="row">
            <div class="col-sm-5">
                <div class="widget-box transparent">
                    <div class="widget-header widget-header-flat">
                        <h4 class="lighter">
                            <i class="icon-star orange"></i>
                            Popular Items This Month
                        </h4>
                    </div>

                    <div class="widget-body">
                        <div class="widget-main no-padding">
                            <table class="table table-bordered table-striped">
                                <thead class="thin-border-bottom">
                                <tr>
                                    <th>
                                        <i class="icon-caret-right blue"></i>
                                        Name
                                    </th>

                                    <th>
                                        <i class="icon-caret-right blue"></i>
                                        Qty Sold
                                    </th>

                                    <th>
                                        <i class="icon-caret-right blue"></i>
                                        Total
                                    </th>
                                </tr>
                                </thead>

                                <tbody id="popularitems">

                                </tbody>
                            </table>
                        </div><!-- /widget-main -->
                    </div><!-- /widget-body -->
                </div><!-- /widget-box -->
            </div>

            <div class="vspace-sm"></div>

            <div class="col-sm-7">
                <div class="widget-box transparent">
                    <div class="widget-header widget-header-flat">
                        <h4 class="lighter">
                            <i class="icon-plus-sign"></i>
                            Sale Stats
                        </h4>
                    </div>
                </div>
                <div class="widget-box">
                    <div class="widget-header widget-header-flat widget-header-small">
                        <i class="icon-signal"></i>
                        <div class="widget-toolbar no-border" style="float: none; display: inline-block; vertical-align: top;">
                            <button class="btn btn-minier btn-primary dropdown-toggle" data-toggle="dropdown">
                                <span id="pietype">Payments</span>
                                <i class="icon-angle-down icon-on-right bigger-110"></i>
                            </button>

                            <ul id="pietypevalues" class="dropdown-menu pull-right dropdown-125 dropdown-lighter dropdown-caret">
                                <li onclick="setPieType($(this));" class="active">
                                    <a class="blue">
                                        <i class="icon-caret-right bigger-110">&nbsp;</i>
                                        <span class="pietypeval">Payments</span>
                                    </a>
                                </li>

                                <li onclick="setPieType($(this));">
                                    <a>
                                        <i class="icon-caret-right bigger-110 invisible">&nbsp;</i>
                                        <span class="pietypeval">Devices</span>
                                    </a>
                                </li>

                                <li onclick="setPieType($(this));">
                                    <a>
                                        <i class="icon-caret-right bigger-110 invisible">&nbsp;</i>
                                        <span class="pietypeval">Locations</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <h5 style="display: inline-block; margin-top: 0;"></h5>
                        <div class="widget-toolbar no-border">
                            <button class="btn btn-minier btn-primary dropdown-toggle" data-toggle="dropdown">
                                <span id="pierange">This Week</span>
                                <i class="icon-angle-down icon-on-right bigger-110"></i>
                            </button>

                            <ul id="pierangevalues" class="dropdown-menu pull-right dropdown-125 dropdown-lighter dropdown-caret">
                                <li onclick="setPie($(this));" class="active" >
                                    <a class="blue">
                                        <i class="icon-caret-right bigger-110">&nbsp;</i>
                                        <span class="pierangeval">This Week</span>
                                    </a>
                                </li>

                                <li onclick="setPie($(this));">
                                    <a>
                                        <i class="icon-caret-right bigger-110 invisible">&nbsp;</i>
                                        <span class="pierangeval">Last Week</span>
                                    </a>
                                </li>

                                <li onclick="setPie($(this));">
                                    <a>
                                        <i class="icon-caret-right bigger-110 invisible">&nbsp;</i>
                                        <span class="pierangeval">This Month</span>
                                    </a>
                                </li>

                                <li onclick="setPie($(this));">
                                    <a>
                                        <i class="icon-caret-right bigger-110 invisible">&nbsp;</i>
                                        <span class="pierangeval">Last Month</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="widget-body">
                        <div class="widget-main">
                            <div id="piechart-placeholder"></div>

                            <div class="hr hr8 hr-double"></div>

                            <div class="clearfix">
                                <div class="grid3">
                                                            <span class="grey">
                                                                <i class="icon-shopping-cart icon-2x green"></i>
                                                                &nbsp;<span id="piesalenum">-</span> sales
                                                            </span>
                                    <h4 id="piesaletotal" class="bigger pull-right">-</h4>
                                </div>

                                <div class="grid3">
                                                            <span class="grey">
                                                                <i class="icon-ban-circle icon-2x orange"></i>
                                                                &nbsp;<span id="pierefundnum">-</span> refunds
                                                            </span>
                                    <h4 id="pierefundtotal" class="bigger pull-right">-</h4>
                                </div>

                                <div class="grid3">
                                                            <span class="grey">
                                                                <i class="icon-dollar icon-2x blue"></i>
                                                                &nbsp; total
                                                            </span>
                                    <h4 id="piebalance" class="bigger pull-right">-</h4>
                                </div>
                            </div>
                        </div><!-- /widget-main -->
                    </div><!-- /widget-body -->
                </div><!-- /widget-box -->
            </div>
        </div>
    </div>
</div>

<div class="hr hr32 hr-dotted"></div>
<!-- inline scripts related to this page -->

<script type="text/javascript">

    var placeholder = $('#piechart-placeholder').css({'width':'90%' , 'min-height':'150px'});
    var piedata = [];
    var etime;
    var stime;


    function loadTodayStats(totals, sales, invoices){
        if (!totals){
            return false;
        }
        // populate the fields
        $("#salenum").text(sales.salenum);
        $("#saletotal").text(WPOS.util.currencyFormat(sales.saletotal));
        $("#invoicenum").text(invoices.invoicenum);
        $("#invoicetotal").text(WPOS.util.currencyFormat(invoices.invoicetotal));
        $("#invoicesbalance").text(WPOS.util.currencyFormat(invoices.invoicebalance));
        $("#invoicespaid").text(WPOS.util.currencyFormat(parseFloat(invoices.invoicetotal) - parseFloat(invoices.invoicebalance)));
        $("#refundnum").text(totals.refundnum);
        $("#refundtotal").text(WPOS.util.currencyFormat(totals.refundtotal));
        $("#voidnum").text(totals.voidnum);
        $("#voidtotal").text(WPOS.util.currencyFormat(totals.voidtotal));
        $("#cash").text(WPOS.util.currencyFormat(totals.totalcash));
        $("#mpesa").text(WPOS.util.currencyFormat(totals.totalmpesa));
        $("#credit").text(WPOS.util.currencyFormat(totals.totalcredit));
        $("#bank").text(WPOS.util.currencyFormat(totals.totalbank));
        $("#takings").text(WPOS.util.currencyFormat((parseFloat(totals.totalpayments) - parseFloat(totals.refundtotal)), true));
        $("#expenses").text(WPOS.util.currencyFormat(totals.expenses, true));
        $("#expensesnum").text(totals.expensesnum);
        $("#bills").text(WPOS.util.currencyFormat(totals.bills, true));
        $("#billsnum").text(totals.billsnum);
        $("#cost").text(WPOS.util.currencyFormat(parseFloat(sales.cost) + parseFloat(invoices.invoicecost), true));
        $("#actualcash").text(WPOS.util.currencyFormat(totals.totalcash-totals.expenses - totals.bills, true));
        // $("#gprofit").text(WPOS.util.currencyFormat(totals.netprofit, true));
        // Set onclicks
        $(".infobox-sales").on('click', function(){ WPOS.transactions.openTransactionList(sales.salerefs); });
        $(".infobox-invoices").on('click', function(){ WPOS.transactions.openTransactionList(invoices.invoicerefs); });
        $(".infobox-refunds").on('click', function(){ WPOS.transactions.openTransactionList(totals.refundrefs); });
        $(".infobox-voids").on('click', function(){ WPOS.transactions.openTransactionList(totals.voidrefs); });
        $(".infobox-takings").on('click', function(){ WPOS.transactions.openTransactionList(totals.refs); });
        $(".infobox-expenses").on('click', function(){ WPOS.transactions.openExpensesList(totals.expensesrefs); });
        $(".infobox-bills").on('click', function(){ WPOS.transactions.openExpensesList(totals.billsrefs); });
        return true;
    }
    function loadPopularItems(items){
        if (!items){
            return false;
        }
        var names = [];
        for (var i in items) {
            names[items[i].name] = [];
            names[items[i].name].name = items[i].name;
            names[items[i].name].netqty = 0;
            names[items[i].name].soldtotal = 0;
        }
        for (var i in items) {
            names[items[i].name].netqty += parseInt(items[i].netqty);// Sum all the qty from same item name
            names[items[i].name].soldtotal += parseFloat(items[i].soldtotal);// Sum all the qty from same item name
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
        order.sort(function(a, b){ return b[0] - a[0] });
        sort.sort(function(a, b){ return b[1] - a[1];});

        for (i=0; (i<6 && i<sort.length); i++){
            $('#popularitems').append('<tr><td><b>'+order[sort[i][0]][1].name+'</b></td><td><b class="blue">'+order[sort[i][0]][1].netqty+'</b></td><td><b class="green">'+WPOS.util.currencyFormat(order[sort[i][0]][1].soldtotal)+'</b></td></tr>');
        }

        // for (i=0; (i<6 && i<sort.length); i++){
        //     $("#popularitems").append('<tr><td><b>'+items[sort[i][0]].name+'</b></td><td><b class="blue">'+items[sort[i][0]].soldqty+'</b></td><td><b class="green">'+WPOS.util.currencyFormat(items[sort[i][0]].soldtotal)+'</b></td></tr>');
        // }
        return true;
    }

    function getTodayTimeVals(){
        var stime = new Date();
        stime.setHours(0); stime.setMinutes(0); stime.setSeconds(0);
        var etime = new Date();
        etime.setHours(23); etime.setMinutes(59); etime.setSeconds(59);
        return [stime.getTime(), etime.getTime()];
    }

    function getTimeVals(text) {
        var etime = new Date();
        var stime = new Date();
        stime.setHours(0);
        stime.setMinutes(0);
        stime.setSeconds(0);
        switch (text) {
            case "This Week":
                stime = stime.getTime() - 604800000;
                etime = etime.getTime();
                break;
            case "Last Week":
                etime.setHours(23);
                etime.setMinutes(59);
                etime.setSeconds(59);
                stime = stime.getTime() - 1209600000;
                etime = etime.getTime() - 604800000;
                break;
            case "This Month":
                stime = stime.getTime() - 2419200000;
                etime = etime.getTime();
                break;
            case "Last Month":
                etime.setHours(23);
                etime.setMinutes(59);
                etime.setSeconds(59);
                stime = stime.getTime() - 4838400000;
                etime = etime.getTime() - 2419200000;
                break;
        }
        return [stime, etime];
    }

    function getPieValues(){
        // get range
        var vals = getTimeVals($("#pierange").text());
        var stime = vals[0]; var etime = vals[1]; var type;
        // get type
        switch ($("#pietype").text()){
            case "Locations": type = "stats/locations"; break;
            case "Devices": type = "stats/devices"; break;
            case "Payments": type = "stats/takings"; break;
        }
        return [type, stime, etime];
    }

    function reloadPieChart(){
        // show loader
        WPOS.util.showLoader();
        var vals = getPieValues();
        // fetch the data
        var data = WPOS.sendJsonData(vals[0], JSON.stringify({"stime":vals[1], "etime":vals[2], "totals":true}));
        generatePieChart(data);
        // hide loader
        WPOS.util.hideLoader();
    }

    function generatePieChart(data){
        if (!data){
            return false;
        }
        piedata = [];
        // generate pie chart data
        for (var i in data){
            if (i != "Totals"){
                piedata.push({ label:(data[i].hasOwnProperty('name')?data[i].name:i), refs:data[i].refs,  data:data[i].balance});
            }
        }
        $.plot(placeholder, piedata, {
            series: {
                pie: {
                    show: true,
                    tilt:0.8,
                    highlight: {
                        opacity: 0.25
                    },
                    stroke: {
                        color: '#fff',
                        width: 2
                    },
                    startAngle: 2
                }
            },
            legend: {
                show: true,
                position: "ne",
                labelBoxBorderColor: null,
                margin:[-30,15]
            }
            ,
            grid: {
                hoverable: true,
                clickable: true
            }
        });

        var totals = data['Totals'];
        // Fill total fields
        $("#piesaletotal").text(WPOS.util.currencyFormat(totals.saletotal));
        $("#piesalenum").text(totals.salenum);
        $("#pierefundtotal").text(WPOS.util.currencyFormat(totals.refundtotal));
        $("#pierefundnum").text(totals.refundnum);
        $("#piebalance").text(WPOS.util.currencyFormat(totals.totaltakings));

        return true;
    }

    function setPie(element){
        $("#pierange").text($(element).children().children('.pierangeval').text());
        $("#pierangevalues li").removeClass("active");
        $("#pierangevalues li i").addClass("invisible");
        $("#pierangevalues li a").removeClass("blue");

        $(element).addClass("active");
        $(element).children().children("i").removeClass("invisible");
        $(element).children().addClass("blue");

        reloadPieChart();
    }

    function setPieType(element){
        $("#pietype").text($(element).children().children('.pietypeval').text());
        $("#pietypevalues li").removeClass("active");
        $("#pietypevalues li i").addClass("invisible");
        $("#pietypevalues li a").removeClass("blue");

        $(element).addClass("active");
        $(element).children().children("i").removeClass("invisible");
        $(element).children().addClass("blue");

        reloadPieChart();
    }

    function reloadGraph(){
        // show loader
        WPOS.util.showLoader();
        var vals = getTimeVals($("#grange").text());
        // fetch the data
        var jdata = WPOS.sendJsonData("graph/general", JSON.stringify({"stime":vals[0], "etime":vals[1], "interval":86400000}));
        drawGraph(jdata);
        // hide loader
        WPOS.util.hideLoader();
    }

    // Graph functions
    function drawGraph(jdata){
        if (!jdata){
            return false;
        }
        // generate plot data
        var tempdate;
        var vals = getTimeVals($("#grange").text());
        var t = vals[0];
        var sales = [], refunds = [], takings = [],  cost = [],  profit = [], netprofit = [], salerefs = [], refundrefs = [], takingrefs = [], expenses = [], expensesrefs = [];
        // create the data object
        var sorted = [];
        for(var pointa in jdata){
            sorted.push(pointa);
        }
        sorted.sort(function(a, b){return a - b});
        for (var s=0;s<sorted.length;s++) {
            i = sorted[s];
            salerefs.push(jdata[i].salerefs);
            sales.push([ t, jdata[i].saletotal]);
            refundrefs.push(jdata[i].refundrefs);
            refunds.push([ t, jdata[i].refundtotal]);
            takingrefs.push(jdata[i].refs);
            takings.push([ t, jdata[i].totaltakings]);
            cost.push([ t, jdata[i].cost]);
            profit.push([ t, jdata[i].profit]);
            netprofit.push([ t, jdata[i].netprofit]);
            expenses.push([ t, jdata[i].expenses]);
            expensesrefs.push([ t, jdata[i].expensesrefs]);
            t = t + 86400000;
        }
        // for (var i in mdata) {
        //     tempdate = new Date();
        //     tempdate.setTime(stime);
        //     tempdate.setHours(0);
        //     tempdate.setMinutes(0);
        //     tempdate.setSeconds(0);
        //     tempdate = tempdate.getTime();
        //     stime = stime + 86400000;
        //     // i = i - (86400000);
        //     salerefs.push(mdata[i].salerefs);
        //     sales.push([ tempdate, mdata[i].saletotal]);
        //     refundrefs.push(mdata[i].refundrefs);
        //     refunds.push([ tempdate, mdata[i].refundtotal]);
        //     takingrefs.push(mdata[i].refs);
        //     takings.push([ tempdate, mdata[i].totaltakings]);
        //     cost.push([ tempdate, mdata[i].cost]);
        //     profit.push([ tempdate, mdata[i].profit]);
        // }
        var data = [{ label: "Gross profit", refs: takingrefs, data: profit, color: "#29AB87" }, { label: "Net profit", refs: takingrefs, data: netprofit, color: "#29AB87" }, { label: "Expenses", refs: expensesrefs, data: expenses, color: "#29AB15" },{ label: "Cost", refs: takingrefs, data: cost, color: "#EA3C53" },{ label: "Sales", refs:salerefs, data: sales, color: "#9ABC32" },{ label: "Refunds", refs:refundrefs, data: refunds, color: "#EDC240" },{ label: "Revenue", refs: takingrefs, data: takings, color: "#3983C2" }];
        // render the graph
        $.plot("#sales-charts", data, {
            hoverable: true,
            shadowSize: 0,
            series: {
                lines: { show: true },
                points: { show: true }
            },
            xaxis: {
                mode: "time",
                minTickSize: [1, "day"],
                timeformat: "%d/%m/%y",
                timezone: "browser"
            },
            yaxis: {
                ticks: 10
            },
            grid: {
                backgroundColor: { colors: [ "#fff", "#fff" ] },
                borderWidth: 1,
                borderColor: '#555',
                hoverable: true,
                clickable: true
            }
        });

        return true;
    }

    function setGraph(element){
        $("#grange").text($(element).children().children('.grangeval').text());
        $("#grangevalues li").removeClass("active");
        $("#grangevalues li i").addClass("invisible");
        $("#grangevalues li a").removeClass("blue");

        $(element).addClass("active");
        $(element).children().children("i").removeClass("invisible");
        $(element).children().addClass("blue");

        reloadGraph();
    }

    function loadSubscriptionStatus(data) {
        var isExipred = true;
        data = data.subscription;
        if (data.status === 'activated')
            isExpired =  new Date(data.expiryDate).getTime() > new Date().getTime();
        else{
            data = JSON.parse(data);
            isExpired =  new Date(data.expiryDate) > new Date();
        }
        isExpired ? $("#status").html('<span style="vertical-align: middle;margin-right: 5px;" class="label label-success arrowed">Activated</span> Expires on: <small>'+ new Date(data.expiryDate).toDateString() + '</small>'): $("#status").html('<span style="vertical-align: middle;" class="label label-danger arrowed">Expired</span>');
    }

    function initDashboard() {
        // Chart hover Tooltip
        var $tooltip = $("<div class='tooltip top in'><div class='tooltip-inner'></div></div>").hide().appendTo('body');
        var previousPoint = null;

        var tooltip = function (event, pos, item) {
            if(item) {
                if (previousPoint != item.seriesIndex) {
                    previousPoint = item.seriesIndex;
                    var tip;
                    if (item.series['percent']!=null){
                        tip = item.series['label'] + " : " + item.series['percent'].toFixed(2)+'% ('+ WPOS.util.currencyFormat(item.series['data'][0][1])+')';
                    } else {
                        tip = item.series['label'] + " : "+WPOS.util.currencyFormat(item.datapoint[1]);
                    }
                    $tooltip.show().children(0).text(tip);
                }
                var left, right;
                if ((pos.pageX + 10 + $tooltip.width())>window.innerWidth){
                    left = ""; right = 0;
                } else {
                    right = ""; left = pos.pageX + 10;
                }
                $tooltip.css({top:pos.pageY + 10, left: left, right: right});
            } else {
                $tooltip.hide();
                previousPoint = null;
            }
        };
        var clickpie = function(event, pos, item){
            if (item==null) return;
            WPOS.transactions.openTransactionList(item.series['refs']);
        };
        var clickgraph = function(event, pos, item){
            if (item==null) return;
            WPOS.transactions.openTransactionList(item.series['refs'][item.dataIndex]);
        };
        // graph tooltips
        placeholder.on('plothover', tooltip);
        placeholder.on('plotclick', clickpie);
        var salechart = $('#sales-charts');
        salechart.on('plothover', tooltip);
        salechart.on('plotclick', clickgraph);
        salechart.css({'width':'100%' , 'height':'220px'});

        var tmonth = getTimeVals("This Month");
        var ttoday = getTodayTimeVals();
        var pvals = getPieValues();
        var gvals = getTimeVals($("#grange").text());
        var sdate = $("#repstime").datepicker("getDate");
        sdate.setHours(0); sdate.setMinutes(0); sdate.setSeconds(0);
        var stime = sdate.getTime();
        var edate = sdate;
        edate.setHours(23); edate.setMinutes(59); edate.setSeconds(59);
        etime = edate.getTime();
        var req = {"stats/itemselling":{"stime":tmonth[0], "etime":tmonth[1]}, "stats/general":{"stime":stime, "etime":etime, "type": 'all'}, "graph/general":{"stime":gvals[0], "etime":gvals[1], "interval":86400000}, "pos/subscription":{}};
        req[pvals[0]] = {"stime":pvals[1], "etime":pvals[2], "totals":true};
        var data = WPOS.sendJsonData("multi", JSON.stringify(req));
        var sales = WPOS.sendJsonData('multi', JSON.stringify({"stats/general":{"stime":stime, "etime":etime, "type": 'sale'}}));
        var invoices = WPOS.sendJsonData('multi', JSON.stringify({"stats/general":{"stime":stime, "etime":etime, "type": 'invoice'}}));
        // Load today's stats
        loadTodayStats(data['stats/general'], sales['stats/general'], invoices['stats/general']);
        // load graph
        drawGraph(data['graph/general']);
        // initialize the initial pie chart
        generatePieChart(data[pvals[0]]);
        // load popular items
        loadPopularItems(data['stats/itemselling']);
        // load susbscription status
        loadSubscriptionStatus(data['pos/subscription']);
        // hide loader
        WPOS.util.hideLoader();
    }

    jQuery(function($) {
        var today = getTodayTimeVals();
        $("#repstime").datepicker({dateFormat:"dd/mm/yy", maxDate: new Date(today[1]),
            onSelect: function(text, inst){
                var date = $("#repstime").datepicker("getDate");
                date.setHours(0); date.setMinutes(0); date.setSeconds(0);
                stime = date.getTime();
                initDashboard();
            }
        });
        $("#repstime").datepicker('setDate', new Date(today[0]));
        initDashboard()
    });

</script>

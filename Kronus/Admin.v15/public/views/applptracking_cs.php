<?php
$pagetitle = "E-City Tracking";
include 'process/ProcessCSManagement.php';
include 'header.php';
$vaccesspages = array('6','18');
    $vctr = 0;
    if(isset($_SESSION['acctype']))
    {
        foreach ($vaccesspages as $val)
        {
            if($_SESSION['acctype'] == $val)
            {
                break;
            }
            else
            {
                $vctr = $vctr + 1;
            }
        }

        if(count($vaccesspages) == $vctr)
        {
            echo "<script type='text/javascript'>document.getElementById('blockl').style.display='block';
                         document.getElementById('blockf').style.display='block';</script>";
        }
        else
        {
?>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <form method="post" action="#">
        <br />
        <table align="left">
            <tr>
                <td width="130px">Site / PEGS</td>
                <td>
                <?php
                    $vsite = $_SESSION['siteids'];
                    echo "<select id=\"cmbsite\" name=\"cmbsite\">";
                    echo "<option value=\"-1\">Please Select</option>";

                    foreach ($vsite as $result)
                    {
                         $vsiteID = $result['SiteID'];
                         $vorigcode = $result['SiteCode'];

                         //search if the sitecode was found on the terminalcode
                         if(strstr($vorigcode, $terminalcode) == false)
                         {
                            $vcode = $vorigcode;
                         }
                         else
                         {
                           //removes the "icsa-"
                           $vcode = substr($vorigcode, strlen($terminalcode));
                         }
                         //removes Site HEad Office
                         if($vsiteID <> 1)
                         {
                           echo "<option value=\"".$vsiteID."\">".$vcode."</option>";  
                         }
                    }
                    echo "</select>";
                ?>
                     <label id="txtsitename"></label>
                </td>
            </tr>
            <tr>
                <td>Terminals</td>
                <td>
                    <select id="cmbterm" name="cmbterminal">
                        <option value="-1">Please Select</option>
                    </select>
                    <label id="txttermname"></label>
                </td>
            </tr>
            <tr>
                <td>Transaction Date</td>
                <td>
                 <input name="txtDate1" id="popupDatepicker1" readonly value="<?php echo date('Y-m-d')." "."06:00:00"; ?>"/>
                 <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="$('#lbldaterange').html('');unloadDataGrid(); javascript:NewCssCal('popupDatepicker1','yyyyMMdd','dropdown',true,'24',true)"/>
                </td>
<!--                <td>
                To:
                <input name="txtDate2" id="popupDatepicker2" readonly value="<?php echo date('Y-m-d')." "."06:00:00"; ?>"/>
                <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="unloadDataGrid(); javascript:NewCssCal('popupDatepicker2','yyyyMMdd','dropdown',true,'24',true)"/>
                </td>-->
            </tr>
        </table>
        <table border="1" id="tblproviders" align="right" width="20%" style="text-align: center;">

        </table>
        <div id="submitarea" style="float: right;">
            <input type="button" id="btnquery" value="Query" />
        </div>
        <div id="lbldaterange" align="left" style="float: left;"></div>
        <!-- Transaction Summary Grid-->
        <div align="center" style="float: left;">
            <table border="1" id="transsummary">
                
            </table>
            <div id="pager2"></div>
        </div>
        
        <!-- Transaction Details Grid -->
        <div align="center" style="float:left;margin-top: 20px;">
          <table border="1" id="transdetails">

          </table>
          <div id="pager3"></div>
        </div>
        
        <!-- Transaction Logs (E-City) Grid -->
        <div align="center" style="float:left;margin-top: 20px;">
            <table border="1" id="translogs">
                
            </table>
            <div id="pager4"></div>
        </div>
        
        <!-- Transaction History Table -->
        <div align="center" id="transhistory" style="float: left;margin: 20px 0 0 250px;">
            <table id="tblhistory" class="tablesorter">

            </table>
        </div>
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function()
    {
       var url = 'process/ProcessCSManagement.php';
       
       //ajax call: posting of providers on the table
       /*jQuery.ajax({
           url : url,
           type: 'post',
           data: {page2: function(){return 'GetProviders';}},
           dataType: 'json',
           success: function(data)
           {
               var tblRow = '<thead><tr>\n\
                               <th colspan="2">Provider Legend:</th></tr>\n\
                               <tr>\n\
                                  <th>Service ID</th>\n\
                                  <th>Service Name</th></tr>';
               jQuery.each(data, function(){
                   tblRow   += "<tbody><tr>"
                            +"<td>"+this.ServiceID+"</td>"
                            +"<td align=\"left\" style=\"padding: 2px;\">"+this.ServiceName+"</td>"
                            +"</tr></tbody>";
                   jQuery("#tblproviders").html(tblRow);
               });
           },
           error: function(XMLHttpRequest, e){
                alert(XMLHttpRequest.responseText);
                if(XMLHttpRequest.status == 401)
                {
                    window.location.reload();
                }
           }
       });*/
                                          
       //Get Date Range
        function getDateRange(startdate){
            var extract6am = startdate.toString(20).substring(0, 10);
            var date1= new Date(extract6am);
            var msg = "";
            var numberOfDaysToAdd = 1;
            date1.setDate(date1.getDate() + numberOfDaysToAdd); 

            //Format Date Year-Month-Day Hour:Minutes:Seconds
            var mm = date1.getMonth() + 1;
            var dd = date1.getDate();
            var yy = date1.getFullYear();
            var hh = startdate.toString(20).substring(11, 13);
            var ii = startdate.toString(20).substring(14, 16);
            var ss = startdate.toString(20).substring(17, 19)
            var ampm = "AM";
            
            //condition for formatting of AM:PM
            ampm = hh < 12 ? "AM": ii > 0 ? "PM": ss > 0 ? "PM":"AM";
            
            //condition for formatting month
            mm = mm < 10 ? mm = "0"+mm:mm=mm;

            //condition for formatting day
            dd = dd < 10 ? dd = "0"+dd:dd=dd;

            var date2 =  yy + "-" + mm + "-" + dd + " 06:00:00";
            msg = "Report Date Range: " + startdate + "  " + ampm + "  to  " + date2 + " AM";
            return msg;
        }
       
       //ajax call: loading of sites
       jQuery('#cmbsite').live('change', function()
       {
           
            unloadDataGrid();
           
            jQuery("#txttermname").text(" ");
            sendSiteID_cs($(this).val());
            $('#cmbterm').empty();
            $('#cmbterm').append($("<option />").val("-1").text("Please Select"));
            jQuery.ajax({
                  url: url,
                  type: 'post',
                  data: {cmbsitename: function(){return jQuery("#cmbsite").val();}},
                  dataType: 'json',
                  success: function(data){
                      if(jQuery("#cmbsite").val() > 0)
                      {
                        jQuery("#txtsitename").text(jQuery("#cmbsite").val()+" / "+data.SiteName);
                      }
                      else
                      {   
                        jQuery("#txtsitename").text(" ");
                      }
                  },
                  error: function(XMLHttpRequest, e){
                        alert(XMLHttpRequest.responseText);
                        if(XMLHttpRequest.status == 401)
                        {
                            window.location.reload();
                        }
                  }
            }); 
       }); 
       
       //ajax call: get sites with transactions
       $('#cmbterm').live('change', function(){ 
       
            unloadDataGrid();
       
            jQuery.ajax({
                url : url,
                type: 'get',
                data: {cmbterminal: function(){ return jQuery("#cmbterm").val();}},
                dataType : 'json',
                success: function(data)
                {
                    if(jQuery("#cmbterm").val() > 0)
                    {
                        jQuery("#txttermname").text(jQuery("#cmbterm").val()+" / "+data.TerminalName);
                    }
                    else
                    {
                        jQuery("#txttermname").text(" ");
                    }
                },
                error: function(XMLHttpRequest, e){
                        alert(XMLHttpRequest.responseText);
                        if(XMLHttpRequest.status == 401)
                        {
                            window.location.reload();
                        }
                }
            });               
       });
       
       //upon clicking of button; display 3 grids(Transaction details, summary, logs)
       jQuery("#btnquery").click(function()
       {
            //var validate = chkecitytrack(); //validate form

            var site = $("#cmbsite").val();
            var term = $("#cmbterm").val();
            
            if( site != '-1' && term != '-1' )
            {
                
//                var isValidDateTime = validateDateTime();
                  
                  var isValidDateTime = true;
                
                if(isValidDateTime == true ) {
                    
                    //Get Date Range
                    $("#lbldaterange").html("");
                    var date1 = $("#popupDatepicker1").val();
                    var daterange = "";
                     //get date range label
                    daterange = getDateRange(date1);
                    $("#lbldaterange").html("<p>"+daterange+"</p>");
                
                    //for displaying transaction details
                    //var value = 0;
                    //getdetailsbyID(value); //call function for transaction detailsddd

                    //Unload Trans Details and Trans Logs Grid
                    unloadDataGrid();

                        //for displaying transaction summary
                        jQuery("#transsummary").jqGrid({
                                url: url,
                                mtype: 'post',
                                postData: {
                                        cmbsite: function() {return $('#cmbsite').val(); },
                                        cmbterminal: function() { return $("#cmbterm").val(); },
                                        txtDate1: function() { return $("#popupDatepicker1").val(); },
                                        //txtDate2: function() { return $("#popupDatepicker2").val(); },
                                        paginate: function() {return 'LPTransactionSummary';}
                                        },
                                datatype: "json",
                                colNames:['Transaction Summary ID', 'POS Acct No.', 'Terminal Code', 'Deposit', 'Reload', 'Withdraw','Date Started', 'Date Ended', 'Created By'],
                                colModel:[
                                        {name:'TransactionSummaryID',index:'TransactionsSummaryID',align:'center'},
                                        {name:'SiteID',index:'SiteID',align:'center'},
                                        {name:'TerminalCode',index:'TerminalCode', align: 'center'},
                                        {name:'Deposit',index:'Deposit', align: 'right'},
                                        {name:'Reload',index:'Reload', align: 'right'},
                                        {name:'Withdraw',index:'Withdrawal', align: 'right'},
                                        {name:'DateStarted',index:'DateStarted', align: 'center'},
                                        {name:'DateEnded',index:'DateEnded', align: 'center'},
                                        {name:'CreatedByAID',index:'AID',align:'center'}
                                ],

                                rowNum:10,
                                rowList:[10,20,30],
                                height: 220,
                                width: 1100,
                                pager: '#pager3',
                                refresh: true,
                                viewrecords: true,
                                sortorder: "asc",
                                caption:"Transaction Summary",
                                onSelectRow: function()
                                {
                                    var rowid  = jQuery('#transsummary').jqGrid('getGridParam', 'selrow'); //get transaction summary ID

                                    //call function for displaying specific transaction summary on transaction details grid & ecity logs
                                    getdetailsbyID(rowid); 
                                    gettranslogs(rowid); 
                                }
                        });
                        jQuery("#transsummary").jqGrid('navGrid','#pager3',{edit:false,add:false,del:false, search:false, refresh: true});
                        jQuery('#transsummary').trigger("reloadGrid");

                        //click event: for displaying LP Transaction Logs, 
                        //gettranslogs(0);
                    
                }
                
            }
            else {
            
                if(site == '-1' && term == '-1') alert("Please select a Site/PEGS and Terminal.");
                else if(site == '-1' && term != '-1') alert("Please select a Site/PEGS.");
                else if(site != '-1' && term == '-1') alert("Please select a terminal.");
            
            }
        });
    });
    
    
    function gettranslogs(summaryid)
    {
        jQuery("#translogs").GridUnload();
        var url = 'process/ProcessCSManagement.php';
        jQuery("#translogs").jqGrid({
                    url: url,
                    mtype: 'post',
                    postData: {
                            cmbsite: function() {return $('#cmbsite').val(); },
                            cmbterminal: function() { return $("#cmbterm").val(); },
                            txtDate1: function() { return $("#popupDatepicker1").val(); },
                            //txtDate2: function() { return $("#popupDatepicker2").val(); },
                            paginate: function() {return 'LPTransactionLogs';},
                            summaryID: function() {return summaryid;}
                              },
                    datatype: "json",
                    colNames:['Transaction Logs ID', 'Transaction Reference ID', 'Site ID', 'Terminal ID', 'Transaction Type', 'Service Transaction ID', 'Service Status','Amount','Service Name','Date Started', 'Date Ended', 'Status'],
                    colModel:[
                            {name:'TransactionRequestLogLPID',index:'TransactionRequestLogLPID',align:'center'},
                            {name:'TransactionReferenceID',index:'TransactionReferenceID',align:'center', hidden: true},
                            {name:'SiteID',index:'SiteID',align:'center', hidden: true},
                            {name:'TerminalID',index:'TerminalID', align: 'center', hidden: true},
                            {name:'TransactionType',index:'TransactionType', align: 'left'},
                            {name:'ServiceTransactionID',index:'ServiceTransactionID', align: 'left', hidden: true},
                            {name:'ServiceStatus',index:'ServiceStatus', align: 'center', hidden: true},
                            {name:'Amount',index:'Amount',align:'right'},
                            {name:'ServiceID',index:'ServiceID',align:'center'},
                            {name:'Date Started',index:'StartDate', align: 'center'},
                            {name:'Date Ended',index:'EndDate', align: 'center'},
                            {name:'Status',index:'Status',align:'left'}
                    ],

                    rowNum:10,
                    rowList:[10,20,30],
                    height: 240,
                    width: 1100,
                    pager: '#pager4',
                    refresh: true,
                    viewrecords: true,
                    sortorder: "asc",
                    caption:"Transaction Logs (E-City)"
            });
            jQuery("#translogs").jqGrid('navGrid','#pager4',{edit:false,add:false,del:false, search:false, refresh: true});
            $('#translogs').trigger("reloadGrid");
    }
    
    //function for posting transaction details
    function getdetailsbyID(value)
    {
        jQuery("#transdetails").GridUnload();
        var url = 'process/ProcessCSManagement.php';
        jQuery("#transdetails").jqGrid({
                    url: url,
                    mtype: 'post',
                    postData: {
                            cmbsite: function() {return $('#cmbsite').val(); },
                            cmbterminal: function() { return $("#cmbterm").val(); },
                            txtDate1: function() { return $("#popupDatepicker1").val(); },
                            txtDate2: function() { return $("#popupDatepicker2").val(); },
                            paginate: function() {return 'LPTransactionDetails';},
                            summaryID: function() {return value;}
                              },
                    datatype: "json",
                    colNames:['Transaction Reference ID','Transaction Summary ID', 'Site ID', 'Terminal ID', 'Transaction Type', 'Service Name', 'Amount','Transaction Date', 'Created By','Status'],
                    colModel:[
                            {name:'TransactionReferenceID',index:'TransactionReferenceID',align: 'center', hidden: true},
                            {name:'TransactionSummaryID',index:'TransactionSummaryID',align:'center'},
                            {name:'SiteID',index:'SiteID',align:'center', hidden: true},
                            {name:'TerminalID',index:'TerminalID', align: 'center', hidden: true},
                            {name:'TransactionType',index:'TransactionType', align: 'left'},
                            {name:'ServiceID',index:'ServiceID', align: 'center'},
                            {name:'Amount',index:'Amount', align: 'right'},
                            {name:'DateCreated',index:'DateCreated', align: 'center'},
                            {name:'Name',index:'Name',align:'center'},
                            {name:'Status',index:'Status', align: 'center'},
                    ],

                    rowNum:10,
                    rowList:[10,20,30],
                    height: 220,
                    width: 1100,
                    pager: '#pager2',
                    refresh: true,
                    viewrecords: true,
                    sortorder: "asc",
                    caption:"Transaction Details"
            });
            jQuery("#transdetails").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false, refresh: true});
            
    }
  
    /**
    *ADDED AS OF JUNE 13, 2012
    *This is used to unload the grids 
    *@author Marx - Lenin Topico
    */
    function unloadDataGrid() {
    
        try{
                    
            jQuery("#translogs").jqGrid("GridUnload");
            jQuery("#transdetails").jqGrid("GridUnload");
            jQuery("#transsummary").jqGrid("GridUnload");

        }
        catch(err){  
            
        }
    
    }
    
    /**
    *ADDED AS OF JUNE 13, 2012
    *This is used to verify if the given date and time format is within 24 hrs
    *@author Marx - Lenin Topico
    */
   function validateDateTime () {
       
       var fromDateTime = $("#popupDatepicker1").val().split(" ");
       var toDateTime = $("#popupDatepicker2").val().split(" ");
       var fromTimeArray = fromDateTime[1].split(":");
       var fromTime = parseInt("".concat(fromTimeArray[0]).concat(fromTimeArray[1]).concat(fromTimeArray[2]), 10);
       var toTimeArray = toDateTime[1].split(":");
       var toTime = parseInt("".concat(toTimeArray[0]).concat(toTimeArray[1]).concat(toTimeArray[2]),10);
       var fromDate = fromDateTime[0].split("-");
       var toDateArray = toDateTime[0].split("-");
       var toDate = parseInt("".concat(toDateArray[0]).concat(toDateArray[1]).concat(toDateArray[2]));
       var fromDateAsInt = parseInt("".concat(fromDate[0]).concat(fromDate[1]).concat(fromDate[2]));
       
       var year = parseInt(fromDate[0], 10);
       var month = parseInt(fromDate[1], 10);
       var day = parseInt(fromDate[2], 10);
       
       var theNextDate = "";
       var leadingZero = "0";
       
       var currentDate = <?php echo date("Ymd"); ?>;
       
       /**
        * @Code Block to check validity of date and time parameters
        * 
        */    
       if(month == 1 || month == 3 || month == 5 || month == 7 || month == 8 || month == 10 || month == 12) { //31 Days
           
           if(month == 12) {
               
               if(day == 31) {
                   theNextDate = theNextDate.concat((year+1),'01','01');
               }
               else {
                   theNextDate = theNextDate.concat(year, '12', (leadingZero.concat((day+1).toString())).substr(-2));
               }
               
           }
           else {
               
               if(day == 31) {
                   theNextDate = theNextDate.concat(year, (leadingZero.concat((month+1).toString())).substr(-2),'01');
               }
               else {
                   theNextDate = theNextDate.concat(year, (leadingZero.concat((month).toString())).substr(-2), (leadingZero.concat((day+1).toString())).substr(-2));
               }
               
           }
           
       }
       else if (month == 4 || month == 6 || month == 9 || month == 11) { //30 Days
           
            if(day == 30) {
                theNextDate = theNextDate.concat(year, (leadingZero.concat((month+1).toString())).substr(-2),'01');
            }
            else {
                theNextDate = theNextDate.concat(year, (leadingZero.concat((month).toString())).substr(-2), (leadingZero.concat((day+1).toString())).substr(-2));
            }
           
       }
       else { //February
           
           if((year%4) == 0) {
               
               if(day == 29) {
                    theNextDate = theNextDate.concat(year, '03','01');
               }
               else {
                   theNextDate = theNextDate.concat(year, '02', (leadingZero.concat((day+1).toString())).substr(-2));
               }
               
           }
           else {
               
               if(day == 28) {
                    theNextDate = theNextDate.concat(year, '03','01');
               }
               else {
                   theNextDate = theNextDate.concat(year, '02', (leadingZero.concat((day+1).toString())).substr(-2));
               }
               
           }
           
       }
       
       if((toDate == fromDateAsInt || toDate == theNextDate) && (toDate <= currentDate && fromDateAsInt <= currentDate)) {
           
           if(toDate == fromDateAsInt) {
               
               if(fromTime == 0) {

                    return true;
       
               }
               else {
                   
                    if((toTime >= fromTime)&&(toTime <= 235959)){
                        return true;
                    }
                    else {
                        alert("Your Starting and Ending Date and Time must be within 24-Hour Frame");
                        return false;
                    }
               
               }
               
               
           }
           else {
               
               if(toTime <= fromTime) {
                   return true;
               }
               else {
                   alert("Your Starting and Ending Date and Time must be within 24-Hour Frame");
                   return false;
               }
               
           }
           
       }
       else {
           
           if((fromDateAsInt > toDate) || (toDate > currentDate || fromDateAsInt > currentDate)) {
               
               alert("Invalid Date");
               
           }
           else {
               
               alert("Your Starting and Ending Date and Time must be within 24-Hour Frame");
               
           }
           
           return false;
       }
       
       
       
       
       
   }

</script>
<?php  
    }
}
include "footer.php"; ?>
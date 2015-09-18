<?php
$pagetitle = "E-City Tracking";
include 'process/ProcessAppSupport.php';
include 'header.php';
$vaccesspages = array('9');
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
                 <input name="txtDate1" id="popupDatepicker1" readonly value="<?php echo date('Y-m-d')?>"/>
                 <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate1', false, 'ymd', '-');"/>
                </td>
<!--                <td>
                To:
                <input name="txtDate2" id="popupDatepicker2" readonly value="<?php echo date ( 'Y-m-d'); ?>"/>
                <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate2', false, 'ymd', '-');"/>
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
       var url = 'process/ProcessAppSupport.php';
       
       function getDateRange(startdate){
            var date1= new Date(startdate);
            var msg = "";
            var numberOfDaysToAdd = 1;
            date1.setDate(date1.getDate() + numberOfDaysToAdd); 

            //Format Date Year-Month-Day Hour:Minutes:Seconds
            var mm = date1.getMonth() + 1;
            var dd = date1.getDate();
            var yy = date1.getFullYear();
            
            //condition for formatting month
            mm = mm < 10 ? mm = "0"+mm:mm=mm;

            //condition for formatting day
            dd = dd < 10 ? dd = "0"+dd:dd=dd;

            var date2 =  yy + "-" + mm + "-" + dd + " 06:00:00";
            msg = "Report Date Range: " + startdate + " 06:00:00 AM  to  " + date2 + " AM";
            return msg;
        }
       
       //ajax call: loading of sites
       jQuery('#cmbsite').live('change', function()
       {
            jQuery("#txttermname").text(" ");
            sendSiteID($(this).val());
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
            var validate = true;
            if(validate == true)
            {
                var date1 = $("#popupDatepicker1").val();
                var daterange = "";
                 //get date range label
                daterange = getDateRange(date1);
                $("#lbldaterange").html("<p>"+daterange+"</p>");
                
                //for displaying transaction details
               var value = 0;
               getdetailsbyID(value); //call function for transaction detailsddd
            
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
                        colNames:['Transaction Summary ID', 'POS Account No', 'Terminal Code', 'Deposit', 'Reload', 'Withdraw','Date Started', 'Date Ended', 'Created By'],
                        colModel:[
                                {name:'TransactionSummaryID',index:'TransactionsSummaryID',align:'center'},
                                {name:'POSAccountNo',index:'POSAccountNo',align:'center'},
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
                gettranslogs(0);
            }
        });
    });
    
    
    function gettranslogs(summaryid)
    {
        jQuery("#translogs").GridUnload();
        var url = 'process/ProcessAppSupport.php';
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
                    colNames:['Transaction Logs ID', 'Transaction Reference ID', 'POSAccountNo', 'Terminal Code', 'Transaction Type', 'Service Transaction ID', 'Service Status','Amount','Service Name','Date Started', 'Date Ended', 'Status'],
                    colModel:[
                            {name:'TransactionRequestLogLPID',index:'TransactionRequestLogLPID',align:'center'},
                            {name:'TransactionReferenceID',index:'TransactionReferenceID',align:'center'},
                            {name:'POSAccountNo',index:'POSAccountNo',align:'center'},
                            {name:'TerminalCode',index:'TerminalCode', align: 'center'},
                            {name:'TransactionType',index:'TransactionType', align: 'left'},
                            {name:'ServiceTransactionID',index:'ServiceTransactionID', align: 'left'},
                            {name:'ServiceStatus',index:'ServiceStatus', align: 'center'},
                            {name:'Amount',index:'Amount',align:'right'},
                            {name:'ServiceName',index:'ServiceName',align:'center'},
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
        var url = 'process/ProcessAppSupport.php';
        jQuery("#transdetails").jqGrid({
                    url: url,
                    mtype: 'post',
                    postData: {
                            cmbsite: function() {return $('#cmbsite').val(); },
                            cmbterminal: function() { return $("#cmbterm").val(); },
                            txtDate1: function() { return $("#popupDatepicker1").val(); },
                            //txtDate2: function() { return $("#popupDatepicker2").val(); },
                            paginate: function() {return 'LPTransactionDetails';},
                            summaryID: function() {return value;}
                              },
                    datatype: "json",
                    colNames:['Transaction Reference ID','Transaction Summary ID', 'POS Account No', 'Terminal Code', 'Transaction Type', 'Service Name', 'Amount','Transaction Date', 'Created By','Status'],
                    colModel:[
                            {name:'TransactionReferenceID',index:'TransactionReferenceID',align: 'center'},
                            {name:'TransactionSummaryID',index:'TransactionSummaryID',align:'center'},
                            {name:'POSAccountNo',index:'POSAccountNo',align:'center'},
                            {name:'TerminalCode',index:'TerminalCode', align: 'center'},
                            {name:'TransactionType',index:'TransactionType', align: 'left'},
                            {name:'ServiceName',index:'ServiceName', align: 'center'},
                            {name:'Amount',index:'Amount', align: 'right'},
                            {name:'DateCreated',index:'DateCreated', align: 'center'},
                            {name:'UserName',index:'UserName',align:'center'},
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
</script>
<?php  
    }
}
include "footer.php"; ?>
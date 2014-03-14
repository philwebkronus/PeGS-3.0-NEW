<?php
$pagetitle = "E-City Report";
include 'process/ProcessPagcorMgmt.php';
include 'header.php';
$vaccesspages = array('11','12');
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
    <form id="frmecity" method="post" action="#">
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
                <td>Transaction Type</td>
                <td>
                    <select id="cmbtranstype" name="cmbtranstype">
                        <option value="All">All</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Transaction Date</td>
                <td>
                 <input name="txtDate1" readonly id="popupDatepicker1" value="<?php echo date('Y-m-d')?>"/>
                 <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate1', false, 'ymd', '-');"/>
                </td>
            </tr>
        </table>
        <div id="submitarea" style="float: right;">
            <div  style="float: right;">
                <input type="button" id="btnquery" value="Query" />
            </div>
            <div id="dateRange" style="float: left;">
                <label id="dRange"></label>
            </div>
        </div>
        
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
        
        <!-- Export to file -->
        <div align="center" id="export" style="display: none;float: right; margin-top: auto; margin-right: 350px;">
            <br />
<!--            <input type="button" id="btnpdf" value="Export to PDF" />-->
            <input type="button" id="btnexcel" value="Export to Excel" />
        </div>
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function()
    {
       var url = 'process/ProcessPagcorMgmt.php';
       
       //event: onclick of export to excel button 
       jQuery("#btnexcel").click(function(){
           jQuery('#frmecity').attr('action', 'process/ProcessPagcorMgmt.php?export=ECityReport&fn=E-CityReport');
           jQuery('#frmecity').submit();
       });
       
       //event: onclick of export to pdf button
       jQuery("#btnpdf").click(function(){
           jQuery("#frmecity").attr('action', 'process/ProcessPagcorMgmt.php?export=ExportToPDF');
           jQuery("#frmecity").submit();
       });
       
       //ajax call: posting of providers on the table
       
       
       //ajax call: loading of transaction types
       jQuery.ajax({
           url: url,
           type: 'post',
           data : {page2 : function(){return 'GetTransactionTypes';}},
           dataType: 'json',
           success: function(data)
           {
               jQuery.each(data, function(){
                   var transtype = jQuery("#cmbtranstype");
                   transtype.append(jQuery("<option />").val(this.TransactionTypeCode).text(this.Description));
               });
           },
           error: function(XMLHttpRequest, e)
           {
               alert(XMLHttpRequest.responseText);
               if(XMLHttpRequest.status == 401)
               {
                   window.location.reload();
               }
           }
       });
       
       //ajax call: loading of sites
       jQuery('#cmbsite').live('change', function()
       {
           var siteid = $(this).val();
           jQuery("#txttermname").text(" ");
           
           
           if(siteid > 0)
           {
               jQuery('#cmbterm').empty();
               jQuery('#cmbterm').append(jQuery("<option />").val("-1").text("Please Select"));
               jQuery('#cmbterm').append(jQuery("<option />").val("0").text("All"));
               sendSiteID(siteid);
           
               jQuery.ajax({
                      url: url,
                      type: 'post',
                      data: {cmbsitename: function(){return jQuery("#cmbsite").val();}},
                      dataType: 'json',
                      success: function(data){
                          if(jQuery("#cmbsite").val() > 0)
                          {
                                jQuery("#txtsitename").text(data.SiteName+" / "+data.POSAccNo);
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
           }else if(siteid == -1){
               jQuery("#txtsitename").text(" ");
               jQuery('#cmbterm').empty();
               jQuery('#cmbterm').append(jQuery("<option />").val("-1").text("Please Select"));
               
           }else{
           
               jQuery("#txtsitename").text(" ");
               jQuery('#cmbterm').empty();
               jQuery('#cmbterm').append(jQuery("<option />").val("0").text("All"));
           }
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
           var result = chkecitytrack(); //validate fields
           
           if(result == true)
           {
               var siteid = $('#cmbsite').val(); 
               gettranssum(siteid); //for displaying transaction summary
               jQuery("#export").show(); //show export button
               if(siteid > 0)
               {
                   //get date range and display
                      var date1 = $("#popupDatepicker1").val()
                      getdaterange(date1);
                   
                   //for displaying transaction details
                      var value = 0;
                      //alert("Entered(testing).");
                      getdetailsbyID(value, siteid, $("#cmbterm").val()); //call function for transaction details

                      //click event: for displaying LP Transaction Logs, 
                      gettranslogs(0, siteid, $("#cmbterm").val());
                      
               }
               else
               {
                   //unload transaction logs and details grid if site was selected all
                      jQuery("#translogs").GridUnload();
                      jQuery("#transdetails").GridUnload();
               }
           }
        });
    });
    
    function getdaterange(pickDate){
       var newDate1 = new Date(pickDate);
       var date1month = newDate1.getMonth()+1;
       var date1day = newDate1.getDate();
       var date1year = newDate1.getFullYear();
       var url = 'process/ProcessPagcorMgmt.php';
       window.setInterval(function(){
            $.ajax({
                url:  url,
                type: "POST",
                data: 'date1month='+date1month+"&date1day="+date1day+"&date1year="+date1year,
                cache: true,
                success: function(response){
                    $("#dRange").html(response);
                }
            });
        });
    }
    
    function gettranssum(siteid)
    {
        jQuery("#transsummary").GridUnload();
        var url = 'process/ProcessPagcorMgmt.php';
        jQuery("#transsummary").jqGrid({
                url: url,
                mtype: 'post',
                postData: {
                        cmbsite: function() {return  siteid; },
                        cmbterminal: function() { return $("#cmbterm").val(); },
                        txtDate1: function() { return $("#popupDatepicker1").val(); },
                        paginate: function() {return 'LPTransactionSummary';},
                        cmbtranstype : function(){ return $("#cmbtranstype").val();}
                          },
                datatype: "json",
                colNames:['Transaction Summary ID','Site ID','Terminal ID','Terminal Code', 'Deposit', 'Reload', 'Withdrawal','Date Started', 'Date Ended'],
                colModel:[
                        {name:'TransactionSummaryID',index:'TransactionSummaryID',width: 50, align: 'center', sortable: false},
                        {name:'SiteID',index:'SiteID', align: 'center',width: 50, sortable: false},
                        {name:'TerminalID',index:'TerminalID', width: 50,align: 'center', sortable: false},
                        {name:'TerminalCode',index:'TerminalCode',width: 150, align: 'center', sortable: false},
                        {name:'Deposit',index:'Deposit',width: 150, align: 'right', sortable: false},
                        {name:'Reload',index:'Reload',width: 150, align: 'right', sortable: false},
                        {name:'Withdraw',index:'Withdrawal', width: 150, align: 'right', sortable: false},
                        {name:'DateStarted',index:'DateStarted', width: 250, align: 'center', sortable: false},
                        {name:'DateEnded',index:'DateEnded', width: 250, align: 'center', sortable: false}
                ],

                rowNum:10,
                rowList:[10,20,30],
                height: 220,
                width: 1285,
                pager: '#pager3',
                refresh: true,
                viewrecords: true,
                sortorder: "asc",
                caption:"Session Summary",
                onSelectRow: function()
                {
                    var rowid  = jQuery('#transsummary').jqGrid('getGridParam', 'selrow'); //get transaction summary ID
                    var siteid = jQuery('#transsummary').jqGrid('getCell', rowid, 'SiteID');
                    var terminalid = jQuery('#transsummary').jqGrid('getCell', rowid, 'TerminalID');
                    
                    
                    //call function for displaying specific transaction summary on transaction details grid & ecity logs
                    getdetailsbyID(rowid, siteid, terminalid); 
                    gettranslogs(rowid, siteid, terminalid); 
                }
        });
        jQuery("#transsummary").jqGrid('navGrid','#pager3',{edit:false,add:false,del:false, search:false, refresh: true});
        jQuery('#transsummary').trigger("reloadGrid");
        jQuery("#transsummary").jqGrid('hideCol',["TransactionSummaryID","SiteID","TerminalID"]);
 
    }
    
    function gettranslogs(summaryid, siteid, terminalid)
    {
        jQuery("#translogs").GridUnload();
        jQuery("#transhistory").hide();
        var url = 'process/ProcessPagcorMgmt.php';
        jQuery("#translogs").jqGrid({
                    url: url,
                    mtype: 'post',
                    postData: {
                            cmbsite: function() {return siteid; },
                            cmbterminal: function() { return terminalid; },
                            txtDate1: function() { return $("#popupDatepicker1").val(); },
                            paginate: function() {return 'LPTransactionLogs';},
                            summaryID: function() {return summaryid;},
                            cmbtranstype: function() {return jQuery('#cmbtranstype').val();}
                              },
                    datatype: "json",
                    colNames:['Transaction Type','Amount','Service Name','Date Started', 'Date Ended'],
                    colModel:[
                            {name:'TransactionType',index:'TransactionType', width: 220, align: 'left'},
                            {name:'Amount',index:'Amount', width: 220, align:'right'},
                            {name:'ServiceName',index:'ServiceName', width: 220, align:'center'},
                            {name:'Date Started',index:'StartDate', width: 210, align: 'center'},
                            {name:'Date Ended',index:'EndDate', width: 210, align: 'center'}
                    ],

                    rowNum:10,
                    rowList:[10,20,30],
                    height: 240,
                    width: 1100,
                    pager: '#pager4',
                    refresh: true,
                    viewrecords: true,
                    sortorder: "asc",
                    caption:"LP Transaction"
            });
            jQuery("#translogs").jqGrid('navGrid','#pager4',{edit:false,add:false,del:false, search:false, refresh: true});
            $('#translogs').trigger("reloadGrid");
    }
    
    //function for posting transaction details
    function getdetailsbyID(value, siteid, terminalid)
    {
        jQuery("#transdetails").GridUnload();
        var url = 'process/ProcessPagcorMgmt.php';
        jQuery("#transdetails").jqGrid({
                    url: url,
                    mtype: 'post',
                    postData: {
                            cmbsite: function() {return siteid; },
                            cmbterminal: function() { return terminalid; },
                            txtDate1: function() { return $("#popupDatepicker1").val(); },
                            paginate: function() {return 'LPTransactionDetails';},
                            summaryID: function() {return value;},
                            cmbtranstype: function() {return jQuery('#cmbtranstype').val();}
                              },
                    datatype: "json",
                    colNames:['Transaction Type', 'Service Name', 'Amount','Transaction Date'],
                    colModel:[
                            {name:'TransactionType',index:'TransactionType', width: 260, align: 'left'},
                            {name:'ServiceName',index:'ServiceName', width: 250, align: 'center'},
                            {name:'Amount',index:'Amount', width: 250, align: 'right'},
                            {name:'DateCreated',index:'DateCreated', width: 300, align: 'center'}
                    ],

                    rowNum:10,
                    rowList:[10,20,30],
                    height: 220,
                    width: 1100,
                    pager: '#pager2',
                    refresh: true,
                    viewrecords: true,
                    sortorder: "asc",
                    caption:"Session Details"
            });
            jQuery("#transdetails").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false, refresh: true});
            $('#transdetails').trigger("reloadGrid");
    }
</script>
<?php  
    }
}
include "footer.php"; ?>
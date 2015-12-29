<?php
$pagetitle = "UB Transaction Tracking";
include 'process/ProcessRptFinance.php';
include 'header.php';
$vaccesspages = array('12');
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
                    //echo "<option value=\"0\">All</option>";
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
                        <option value="All">All</option>
                    </select>
                    <label id="txttermname"></label>
                </td>
            </tr>
            <tr>
                <td>Transaction Date</td>
                <td>
                 <input name="txtDate1" id="popupDatepicker1" readonly="readonly" value="<?php echo date('Y-m-d')?>"/>
                 <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate1', false, 'ymd', '-');"/>
                </td>
<!--                <td>
                To:
                <input name="txtDate2" id="popupDatepicker2" readonly="readonly" value="<?php echo date ( 'Y-m-d'); ?>"/>
                <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate2', false, 'ymd', '-');"/>
                </td>-->
            </tr>
        </table>
        <div id="submitarea" style="float: right;">
            <input type="button" id="btnquery" value="Query" />
        </div>
        
        <!-- Transaction Details Grid -->
        <div align="center" style="float: left;">
          <table border="1" id="transdetails">
          </table>
          <div id="pager3" style="height: 150px;">
              <table id="trans" style="background-color:#D6EB99; padding-left: 10px; display: none; font-size: 12px; height: 40% ">
                <tr>
                    <td>Summary per Page</td>
                    <td style="padding-left: 10px;"></td>
                    <td>Total Starting Balance</td>
                    <td id="totdeposit" style="font-weight: bold;"></td>
                    <td style="padding-left: 50px;"></td>
                    <td>Total e-SAFE Loads</td>
                    <td id="totreload" style="font-weight: bold;"></td>
                    <td style="padding-left: 50px;"></td>
                    <td>Total Ending Balance</td>
                    <td id="totwithdraw" style="font-weight: bold;"></td>
                </tr>
                <tr>
                    <td>Grand Total</td>
                    <td style="padding-left: 80px;"></td>
                    <td>Grand Starting Balance</td>
                    <td id="grdeposit" style="font-weight: bold;"></td>
                    <td style="padding-left: 50px;"></td>
                    <td>Grand e-SAFE Loads</td>
                    <td id="grreload" style="font-weight: bold;"></td>
                    <td style="padding-left: 50px;"></td>
                    <td>Grand Ending Balance</td>
                    <td id="withdraw" style="font-weight: bold;"></td>
                    <td style="padding-left: 20px;"></td>
                    <td>Grosshold</td>
                    <td id="grosshold" style="font-weight: bold;"></td>
                </tr>
              </table>
          </div>
        </div>
        
        <!-- Export to file -->
        <div id="senchaexport1" style="background-color: #6A6A6A; display: none;float: left; margin-top: auto;width: 1100px;padding-bottom: 20px;">           
            <br />
            <input type="button" id="btnpdf" value="Export to PDF" style="float: right;margin-right: 20px;"/>
            <input type="button" id="btnexcel" value="Export to Excel" style="float: right;" />
        </div>
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function()
    {
       var url = 'process/ProcessRptFinance.php';
       
       //event: onclick of export to excel button 
       jQuery("#btnexcel").click(function(){
           var sitecode = jQuery("#cmbsite").find("option:selected").text();
           jQuery('#frmecity').attr('action', 'process/ProcessRptFinance.php?exportUBtrans=UBtrack&fn=UBTransactionTracking&sitecode='+sitecode);
           jQuery('#frmecity').submit();
       });
       
       //event: onclick of export to pdf button
       jQuery("#btnpdf").click(function(){
           var sitecode = jQuery("#cmbsite").find("option:selected").text();
           jQuery("#frmecity").attr('action', 'process/ProcessRptFinance.php?exportUBtrans=ExportToPDF&sitecode='+sitecode);
           jQuery("#frmecity").submit();
       });
       
       
       //ajax call: loading of sites
       jQuery('#cmbsite').live('change', function()
       {
           jQuery("#senchaexport1").hide();
           jQuery('#transdetails').GridUnload();
           createtable(); //call creating of table
           var siteid = $(this).val();
           jQuery("#txttermname").text(" ");
           
           if(siteid > 0)
           {
               jQuery('#cmbterm').empty();
               jQuery('#cmbterm').append(jQuery("<option />").val("All").text("All"));
               popterminals(siteid, url);
           
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
           }
           else
           {
               jQuery("#txtsitename").text(" ");
               jQuery('#cmbterm').empty();
               jQuery('#cmbterm').append(jQuery("<option />").val("0").text("All"));
           }
       }); 
       
       
       //ajax call: get sites with transactions
       $('#cmbterm').live('change', function(){  
            jQuery('#transdetails').GridUnload();
            createtable(); //call creating of table
            jQuery("#senchaexport1").hide();
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
               var terminal = jQuery("#cmbterm").val();
               var datefrom = jQuery("#popupDatepicker1").val();
               //var dateto = jQuery("#popupDatepicker2").val();
               jQuery("#senchaexport1").show(); //show export button
               jQuery('#transdetails').GridUnload();
               createtable(); //call creating of table
               jqgrid(url, siteid, terminal, datefrom);
           }
        });
        
        
    });
    
    //function for jqgrid
    function jqgrid(url, siteid, terminal, datefrom)
    {
           jQuery("#transdetails").jqGrid(
           {    
               url:url,
               mtype: 'post',
               postData: {
                            financetrackUB: function() {return 'TransactionDetailsUB';},
                            txtDate1: function() {return datefrom;},
                            //txtDate2: function() {return dateto},
                            cmbsite: function() {return siteid; },
                            cmbterminal : function() {return terminal },
                            sitecode : function(){return jQuery("#cmbsite").find("option:selected").text();}
                         },
               datatype: "json",
               colNames:['Site Code','Terminal','Service Name','Starting Balance','Total eSAFE loads<br>(with session)','Ending Balance','Date Started','Date Ended'],
               colModel:[
                         {name:'SiteCode', index:'SiteCode', align:'center', sortable: false, width: 90},
                         {name:'TerminalCode',index:'TerminalCode', align: 'center', sortable: false, width: 80},
                         {name:'ServiceName',index:'ServiceName', align: 'center', sortable: false},
                         {name:'StartBalance',index:'Deposit', align: 'right', sortable: false},
                         {name:'WalletReloads',index:'Reload', align: 'right', sortable: false},
                         {name:'EndBalance',index:'Withdrawal', align: 'right', sortable: false},
                         {name:'DateStarted',index:'DateStarted', align: 'center', sortable: false, width: 250},
                         {name:'DateEnded', index:'DateEnded',align:'center', sortable: false, width: 270}
                        ],
               rowNum:10,
               rowList:[10,20,30],
               height: 220,
               width: 1200,
               pager: '#pager3',
               refresh: true,
               viewrecords: true,
               sortorder: "asc",
               loadComplete: function (){ gettotal(url,siteid, terminal, datefrom);},
               
               caption:"Transaction Tracking"
         });
         jQuery("#transdetails").jqGrid('navGrid','#pager3',{edit:false,add:false,del:false, search:false, refresh: true});
    }
    
    //function for getting the sum of each transaction type
    function gettotal(url, siteid, terminal, datefrom)
    {
                jQuery.ajax({
                   url: url,
                   data: {
                             gettotalUB: function(){return "GetTotalsUB"},
                             txtDate1: function() {return datefrom;},
//                             txtDate2: function() {return dateto},
                             cmbsite: function() {return siteid; },
                             cmbterminal : function() {return terminal }
                         },
                   type: 'post',
                   dataType: 'json',
                   success: function (data){
                       var withdraw = data.withdraw;
                       var deposit = data.deposit;
                       var reload = data.reload;
                       var granddeposit = data.granddeposit;
                       var grandreload = data.grandreload;
                       var grandwithdraw = data.grandwithdraw;
                       var sales = data.sales;
                       var grandsales = data.grandsales;
                       
                       //display summary per page
                       jQuery("#trans").show();
                       jQuery("#totdeposit").html(deposit);
                       jQuery("#totreload").html(reload);
                       jQuery("#totwithdraw").html(withdraw);
                       jQuery("#grdeposit").html(granddeposit);
                       jQuery("#grreload").html(grandreload);
                       jQuery("#withdraw").html(grandwithdraw);
                       jQuery("#grosshold").html(data.grosshold);
                   },
                   error: function(e)
                   {
                       alert(e.responseText);
                   }
                });
    }
     
    //populate terminals by site
    function popterminals(siteid, url)
    {
        jQuery.ajax({
            url : url,
            type : 'post',
            data : {sendSiteID : function(){return siteid;}},
            dataType : 'json',
            success : function(data)
            {
                var terminal = $("#cmbterm");
                jQuery.each(data, function(){
                    terminal.append($("<option />").val(this.TerminalID).text(this.TerminalCode));
                });
            }
        });
    }
    
    function createtable()
    {
        var content = '';
            content += '<table id=\"trans\" style=\"background-color:#D6EB99; padding-left: 10px;display: none; font-size: 12px; height: 40%; \">';
            content += '<tr>';
            content += '<td>Summary per Page</td>'
            content += '<td style="padding-left: 10px;"></td>';
            content += '<td>Total Starting Balance</td>';
            content += '<td id="totdeposit" style="font-weight: bold;"></td>';
            content += '<td style="padding-left: 50px;"></td>';
            content += '<td>Total e-SAFE Loads</td>';
            content += '<td id="totreload" style="font-weight: bold;"></td>';
            content += '<td style="padding-left: 50px;"></td>';
            content += '<td>Total Ending Balance</td>';
            content += '<td id="totwithdraw" style="font-weight: bold;"></td>';
            content += '</tr>';
            content += '<tr>';
            content += '<td>Grand Total</td>';
            content += '<td style="padding-left: 80px;"></td>';
            content += '<td>Grand Starting Balance</td>';
            content += '<td id="grdeposit" style="font-weight: bold;"></td>';
            content += '<td style="padding-left: 50px;"></td>';
            content += '<td>Grand e-SAFE Loads</td>';
            content += '<td id="grreload" style="font-weight: bold;"></td>';
            content += '<td style="padding-left: 50px;"></td>';
            content += '<td>Grand Ending Balance</td>';
            content += '<td id="withdraw" style="font-weight: bold;"></td>';
            content += '<td style="padding-left: 20px;"></td>';
            content += '<td>Grosshold</td>';
            content += '<td id="grosshold" style="font-weight: bold;"></td>';
            content += '</tr>';
            content += '</table>';
            jQuery("#pager3").append(content);
    }
</script>
<?php  
    }
}
include "footer.php"; ?>
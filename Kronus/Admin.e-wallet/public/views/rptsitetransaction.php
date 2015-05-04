<?php 
$pagetitle = "Site Transactions";
include 'process/ProcessRptOptr.php';
include 'header.php';

$vaccesspages = array('2');
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

<script type="text/javascript">
    
    jQuery(document).ready(function()
    {
       var sitecode;
       
       jQuery("#cmbsite").live('change', function(){
          var url = 'process/ProcessRptOptr.php';
                    jQuery.ajax({
                          url: url,
                          type: 'post',
                          data: {cmbsitename: function(){return jQuery("#cmbsite").val();}},
                          dataType: 'json',
                          success: function(data){
                              if(jQuery("#cmbsite").val() > 0)
                              {
                                jQuery("#txtsitename").text(data.SiteName+" / ");
                                jQuery("#txtposaccno").text(data.POSAccNo);
                              }
                              else
                              {    
                                jQuery("#txtsitename").text(" ");
                                jQuery("#txtposaccno").text(" ");
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
       
       jQuery('#btnsubmit').click(function(){
           
           
           var result = validatedate(jQuery("#rptDate").val());
           
           if(result == true)
           {
               if(document.getElementById('cmbsite').value == "-1")
               {
                   alert("Please select site");
                   return false;
               }
               else
               {
                   jqgrid();
                   //jqgrid2();
                   gettotal();
               }
           }
           
           
//           var result = validatedate(jQuery("#rptDate").val());
//           
//           if(result == true)
//           {
//               if(document.getElementById('cmbsite').value == "-1")
//               {
//                   alert("Please select site");
//                   return false;
//               }
//               else
//               {
//                   jqgrid();
//                   jQuery("#senchaexport1").show();   
//               }
//           }
       });
   });
    
    //function for jqgrid
    function jqgrid()
    {
       //jQuery("#userdata").GridUnload();
       jQuery("#userdata").jqGrid(
       {    
           url:'process/ProcessRptOptr.php',
           mtype: 'post',
           postData: {
                        //paginate: function() {return $("#paginate").val();},
                        paginate: function() {return 'DailySiteTransaction';},
                        rptDate: function() {return $("#rptDate").val();},
                        cmbsitename: function() {return $("#cmbsite").val();},
                        sitecode: function(){return $('#cmbsite').find("option:selected").text();}
                     },
           datatype: "json",
           colNames:['Site / PEGS Code','Terminal Code', 'Deposit','Reload','Withdrawal','Date Started','Date Ended'],
           colModel:[
                     {name:'SiteCode', index:'SiteCode', align:'center', sortable: false},
                     {name:'TerminalCode',index:'TerminalCode', align: 'center', sortable: false},
                     {name:'Deposit',index:'Deposit', align: 'right', sortable: false},
                     {name:'Reload',index:'Reload', align: 'right', sortable: false},
                     {name:'Withdrawal',index:'Withdrawal', align: 'right', sortable: false},
                     {name:'DateStarted',index:'DateStarted', align: 'center', sortable: false},
                     {name:'DateEnded', index:'DateEnded',align:'center', sortable: false}
                    ],
           rowNum:10,
           rowList:[10,20,30],
           height: 150,
           width: 1200,
           pager: '#pager1',
           refresh: true,
           viewrecords: true,
           sortorder: "asc",
           //loadComplete: function (){gettotal();},
           caption:"Site Transaction Per Day"
     });
     jQuery("#userdata").jqGrid('navGrid','#pager1',{edit:false,add:false,del:false, search:false, refresh: true});
     jQuery('#userdata').trigger("reloadGrid");
     jQuery("#senchaexport1").show();
     jqgrid2();
     
   }
   
   //function for jqgrid
    function jqgrid2()
    {
     //jQuery("#userdata2").GridUnload();
     jQuery("#userdata2").jqGrid(
       {    
           url:'process/ProcessRptOptr.php',
           mtype: 'post',
           postData: {
                        paginate: function() {return 'DailySiteTransaction2';},
                        rptDate: function() {return $("#rptDate").val();},
                        cmbsitename: function() {return $("#cmbsite").val();},
                        sitecode: function(){return $('#cmbsite').find("option:selected").text();}
                     },
           datatype: "json",
           colNames:['Site / PEGS Code', 'Card Number','e-wallet Loads', 'e-wallet Withdrawals','Date Started','Date Ended'],
           colModel:[
                     {name:'SiteCode',index:'SiteCode',align: 'center', sortable: false},
                     {name:'CardNumber', index:'CardNumber', align:'center', sortable: false},
                     {name:'EwalletLoads',index:'EwalletLoads', align: 'center', sortable: false},
                     {name:'EwalletWithdrawals',index:'EwalletWithdrawals', align: 'right', sortable: false},
                     {name:'StartDate',index:'StartDate', align: 'center', sortable: false},
                     {name:'EndDate', index:'EndDate',align:'center', sortable: false}
                    ],
           rowNum:10,
           rowList:[10,20,30],
           height: 150,
           width: 1200,
           pager: '#pager2',
           refresh: true,
           viewrecords: true,
           sortorder: "asc",
           //loadComplete: function (){gettotal();},
           caption:"e-wallet Transactions Per Day"
     });
     jQuery("#userdata2").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false, refresh: true});
     jQuery('#userdata2').trigger("reloadGrid");
     //gettotal();
     jQuery("#senchaexport2").show();
     
   }
    
    //function for getting the sum of each transaction type
    function gettotal()
    {
                jQuery.ajax({
                   url: 'process/ProcessRptOptr.php',
                   data: {
                             gettotal: function(){return "GetTotals"},
                             rptDate: function() {return $("#rptDate").val();},
                             cmbsitename: function() {return $("#cmbsite").val();}
                         },
                   type: 'post',
                   dataType: 'json',
                   success: function (data){
                       //var withdraw = data.withdraw;
                       //var deposit = data.deposit;
                       //var reload = data.reload;
                       var grandsales = data.grandsales;
                       var grandredemption = data.grandredemption;
                       var grandticketencashment = data.grandticketencashment;
//                       var sales = data.sales;
//                       var redemption = data.redemption;                       //var grandsales = data.grandsales;
                       var grandcashonhand = data.grandcashonhand;
                       //var ticketencashment = data.ticketencashment;
                       
                       document.getElementById('trans').style.display='block';
                       //display summary per page
                       //document.getElementById('totsales').innerHTML = sales;
                       //document.getElementById('totwithdraw').innerHTML = withdraw;
                       document.getElementById('sales').innerHTML = grandsales;
                       document.getElementById('redemption').innerHTML = grandredemption;
                       document.getElementById('ticketencashment').innerHTML = grandticketencashment;
                       //document.getElementById('grosshold').innerHTML = data.grosshold;
                       document.getElementById('cashonhand').innerHTML = grandcashonhand;
                       //document.getElementById('encashment').innerHTML = data.encashment;
                   },
                   error: function(e)
                   {
                       alert(e.responseText);
                   }
                });
     }
    
</script>
<div id="workarea">
  <div id="pagetitle"><?php echo $pagetitle; ?></div>
  <br />
  <form method="post" action="#">
    <input type="hidden" name="paginate" id="paginate" value="DailySiteTransaction" />  
    <table> 
        <tr>
          <td>Transaction Date</td>
          <td>
              <input name="rptDate" id="rptDate" readonly value="<?php echo date('Y-m-d')?>"/>
              <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('rptDate', false, 'ymd', '-');"/>
          </td>
        </tr>
        <tr>
          <td>Site / PEGS </td>
          <td>
              <?php
                   $vsite = $_SESSION['siteids'];
                   echo "<select id=\"cmbsite\" name=\"cmbsite\">";
                   echo "<option value=\"-1\">Please Select</option>";
                   echo "<option value=\"0\">All</option>";
                   
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
                         //remove the "icsa-"
                         $vcode = substr($vorigcode, strlen($terminalcode));
                      }
                                    
                      if($_SESSION['acctype'] == 2)
                      {
                         $vsitesowned = $_SESSION['pegsowned'];
                                
                         foreach ($vsitesowned as $results)
                         {
                            $vownedsites = $results['SiteID'];
                            if( $vownedsites == $vsiteID)
                            {                                        
                               echo "<option value=\"".$vownedsites."\">".$vcode."</option>";
                            }
                         }
                      }
                   }
                   
                   echo "</select>";
               ?>
               <label id="txtsitename"></label><label id="txtposaccno"></label>
           </td>
        </tr>
    </table>
    <div id="submitarea">
        <input type="button" value="Query" id="btnsubmit" />
    </div>
  </form>
  
  <!--jqgrid pagination on this part-->
  <div align="center">
    <table border="1" id="userdata"></table>
    <input type="hidden" name="paginate" id="paginate" value="DailySiteTransaction" />
    <input type="hidden" name="siteid1" id="siteid1" value="<?php echo $_SESSION['siteid1'];?>" />
    <div id="pager1" style="height: 100px;">
<!--        <table id="trans" style="background-color:#D6EB99; padding-left: 10px; display: none; font-size: 14px; height: 40% ">
            <tr>
                <td>Grand Total</td>
                <td style="padding-left: 30px;"></td>
                <td>Sales</td>
                <td id="sales" style="font-weight: bold;"></td>
                <td style="padding-left: 30px;"></td>
                <td>Redemption</td>
                <td id="redemption" style="font-weight: bold;"></td>
                <td style="padding-left: 30px;"></td>
                <td>Ticket Encashments</td>
                <td id="ticketencashment" style="font-weight: bold;"></td>
                <td style="padding-left: 30px;"></td>
                <td>Cash on Hand</td>
                <td id="cashonhand" style="font-weight: bold;"></td>
                <td style="padding-left: 30px;"></td>
            </tr>
        </table>-->
    </div>
<!--    <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 60px; display: none; width: 1200px;">
        <br />
        <input type='button' name='exportPDF' id='exportPDF' value='Export to PDF File' 
               onclick="window.location.href='process/ProcessRptOptr.php?pdf=sitetrans&date='+document.getElementById('rptDate').value+'&cmbsitename='+document.getElementById('siteid1').value+'&sitecode='+$('#cmbsite').find('option:selected').text()" style="float: right;" />  
        <input type="button" name="exportExcel" id="exportExcel" value="Export to Excel File" 
               onclick="window.location.href='process/ProcessRptOptr.php?excel=sitetrans&date='+document.getElementById('rptDate').value+'&fn=Site_Transaction_for_'+document.getElementById('rptDate').value+'&cmbsitename='+document.getElementById('siteid1').value+'&sitecode='+$('#cmbsite').find('option:selected').text()" style="float: right;"/>
    </div>-->
    <br />
    <table border="1" id="userdata2"></table>
    <input type="hidden" name="paginate2" id="paginate2" value="DailySiteTransaction2" />
    <input type="hidden" name="siteid2" id="siteid2" value="<?php echo $_SESSION['siteid2'];?>" />
    <div id="pager2" style="height: 50px;">
        <table id="trans" style="background-color:#D6EB99; padding-left: 10px; display: none; font-size: 14px; height: 40%; width: 1200px; ">
            <tr>
                <td>Grand Total</td>
                <td style="padding-left: 30px;"></td>
                <td>Sales</td>
                <td id="sales" style="font-weight: bold;"></td>
                <td style="padding-left: 30px;"></td>
                <td>Redemption</td>
                <td id="redemption" style="font-weight: bold;"></td>
                <td style="padding-left: 30px;"></td>
                <td>Ticket Encashments</td>
                <td id="ticketencashment" style="font-weight: bold;"></td>
                <td style="padding-left: 30px;"></td>
                <td>Cash on Hand</td>
                <td id="cashonhand" style="font-weight: bold;"></td>
<!--                <td style="padding-left: 30px;"></td>-->
            </tr>
        </table>
    </div>
    <div id="senchaexport2" style="background-color: #6A6A6A; padding-bottom: 60px; display: none; width: 1200px;">
        <br />
        <input type='button' name='exportPDF' id='exportPDF' value='Export to PDF File' 
               onclick="window.location.href='process/ProcessRptOptr.php?pdf2=e-walletsitetrans&date='+document.getElementById('rptDate').value+'&cmbsitename='+document.getElementById('siteid2').value+'&sitecode='+$('#cmbsite').find('option:selected').text()" style="float: right;" />  
        <input type="button" name="exportExcel" id="exportExcel" value="Export to Excel File" 
               onclick="window.location.href='process/ProcessRptOptr.php?excel2=e-walletsitetrans&date='+document.getElementById('rptDate').value+'&fn=site_transaction_for_'+document.getElementById('rptDate').value+'&cmbsitename='+document.getElementById('siteid2').value+'&sitecode='+$('#cmbsite').find('option:selected').text()" style="float: right;"/>
    </div>
  </div>
</div>
<?php  
    }
}
include "footer.php"; ?>
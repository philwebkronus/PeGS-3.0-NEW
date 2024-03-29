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
       jQuery('#btnsubmit').click(function(){
           $('#userdata').trigger("reloadGrid");
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
                   jQuery("#senchaexport1").show();   
               }
           }
       });
       
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
   });
    
    //function for jqgrid
    function jqgrid()
    {
       jQuery("#userdata").jqGrid(
       {    
           url:'process/ProcessRptOptr.php',
           mtype: 'post',
           postData: {
                        paginate: function() {return $("#paginate").val();},
                        rptDate: function() {return $("#rptDate").val();},
                        cmbsitename: function() {return $("#cmbsite").val();},
                        sitecode: function(){return $('#cmbsite').find("option:selected").text();}
                     },
           datatype: "json",
           colNames:['Transaction Summary ID', 'Site Code','Terminal Code', 'Deposit','Reload','Redemption','Date Started','Date Ended'],
           colModel:[
                     {name:'TransactionSummaryID',index:'TransactionsSummaryID',align: 'center', sortable: false},
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
           height: 220,
           width: 1200,
           pager: '#pager2',
           refresh: true,
           viewrecords: true,
           sortorder: "asc",
           loadComplete: function (){gettotal();},
           caption:"Site Transaction Per Day"
     });
     jQuery("#userdata").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false, refresh: true});
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
                       var withdraw = data.withdraw;
                       var deposit = data.deposit;
                       var reload = data.reload;
                       var granddeposit = data.granddeposit;
                       var grandreload = data.grandreload;
                       var grandwithdraw = data.grandwithdraw;
                       var sales = data.sales;
                       var grandsales = data.grandsales;
                       
                       document.getElementById('trans').style.display='block';
                       //display summary per page
                       document.getElementById('totsales').innerHTML = sales;
                       document.getElementById('totwithdraw').innerHTML = withdraw;
                       document.getElementById('sales').innerHTML = grandsales;
                       document.getElementById('withdraw').innerHTML = grandwithdraw;
                       document.getElementById('grosshold').innerHTML = data.grosshold;
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
    <div id="pager2" style="height: 150px;">
        <table id="trans" style="background-color:#D6EB99; padding-left: 10px; display: none; font-size: 14px; height: 40% ">
            <tr>
                <td>Summary per Page</td>
                <td style="padding-left: 170px;"></td>
                <td>Total Sales</td>
                <td id="totsales" style="font-weight: bold;"></td>
                <td style="padding-left: 90px;"></td>
                <td>Total Redemption</td>
                <td id="totwithdraw" style="font-weight: bold;"></td>
            </tr>
            <tr>
                <td>Grand Total</td>
                <td style="padding-left: 300px;"></td>
                <td>Sales</td>
                <td id="sales" style="font-weight: bold;"></td>
                <td style="padding-left: 90px;"></td>
                <td>Redemption</td>
                <td id="withdraw" style="font-weight: bold;"></td>
                <td style="padding-left: 30px;"></td>
                <td>Gross Hold</td>
                <td id="grosshold" style="font-weight: bold;"></td>
            </tr>
        </table>
    </div>
    <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 60px; display: none; width: 1200px;">
        <br />
        <input type='button' name='exportPDF' id='exportPDF' value='Export to PDF File' 
               onclick="window.location.href='process/ProcessRptOptr.php?pdf=sitetrans&date='+document.getElementById('rptDate').value+'&cmbsitename='+document.getElementById('cmbsite').value+'&sitecode='+$('#cmbsite').find('option:selected').text()" style="float: right;" />  
        <input type="button" name="exportExcel" id="exportExcel" value="Export to Excel File" 
               onclick="window.location.href='process/ProcessRptOptr.php?excel=sitetrans&date='+document.getElementById('rptDate').value+'&fn=Site_Transaction_for_'+document.getElementById('rptDate').value+'&cmbsitename='+document.getElementById('cmbsite').value+'&sitecode='+$('#cmbsite').find('option:selected').text()" style="float: right;"/>
    </div>
  </div>
</div>
<?php  
    }
}
include "footer.php"; ?>
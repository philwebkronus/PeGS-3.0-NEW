<?php 
$pagetitle = "Gross Hold Monitoring";
include "header.php";
function createSelect($id) 
{
    $html = '<select id="'.$id.'" name="'.$id.'">
            <option value="">Select</option>
            <option value=">">&gt;</option>
            <option value="<">&lt;</option>
            <option value=">=">&gt;=</option>
            <option value="<=">&lt;=</option>
            </select>';
    return $html;
}

$vaccesspages = array('5','11');
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

<form method="post" id="frmreport">
<div id="workarea">
    <div id="pagetitle">Gross Hold Monitoring</div>
    <br />
    
    <table>
        <tr>
            <td>Start Date</td>
            <td>
                <input type="text" value="<?php echo date('Y-m-d'); ?>" id="startdate" readonly="readonly" name="startdate" />
               <!-- <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('startdate', false, 'ymd', '-');"/> -->
            </td>
            <td>End Date</td>
            <td>
                <input type="text" value="<?php echo date('Y-m-d',strtotime(date("Y-m-d", strtotime(date('Y-m-d')))))  ?>" id="enddate" readonly="readonly" name="enddate" />
                <!-- <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('enddate', false, 'ymd', '-');"/> -->
            </td>
        </tr>
        <tr>
            <td>Site / PEGS </td>
            <td>
                 <select name="selSiteCode" id="selSiteCode">
                    <option value="">All</option>
                    <?php foreach($param['sitCode'] as $v): ?>
                    <option label="<?php echo $v['SiteName']." / ".$v['POSAccountNo']; ?>" value="<?php echo $v['SiteID']; ?>"><?php echo substr($v['SiteCode'], strlen(BaseProcess::$sitecode)); ?></option>
                    <?php endforeach; ?>
                </select>       
                <label id="lblsitename"></label>
            </td>
        </tr>
        <tr>
            <td>Amount Range </td>
            <td>
                <?php echo createSelect('sel1comp'); ?>
                <input type="text" id="firstamount" name="firstamount" class="auto" />
            </td>
            <td align="center">AND</td>
            <td>
                <?php echo createSelect('sel2comp'); ?>
                <input type="text" id="secondamount" name="secondamount" class="auto" />      
            </td>
        </tr>
        <tr>
            <td>With Confirmation</td>
            <td>
                <select name="selwithconfirmation" id="selwithconfirmation">
                    <option value="">ALL</option>
                    <option value="Y">Y</option>
                    <option value="N">N</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Location</td>
            <td>
                <select name="sellocation" id="sellocation">
                    <option value="">ALL</option>
                    <option value="Metro Manila">Metro Manila</option>
                    <option value="Provincial">Provincial</option>
                </select>
            </td>
        </tr>
    </table>
    <div id="submitarea">
        <input type="button" value="Search" id="btnsearch"/>
    </div>
    <br />
    <div align="center" id="pagination"> 
      <table id="ghmgrid">

      </table>
     <div id="pager2"></div>
    </div>
    <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 60px; width: 1200px;">
        <br />
        <input type="button" value="Export to PDF File" id="btnpdf" style="float:right;"/>
        <input type="button" value="Export to Excel File" id="btnexcel" style="float:right;"/> 
    </div>
  
</div>
</form>    
<style type="text/css">
   .ui-jqgrid .ui-jqgrid-htable th div {
      height: 21px;
   }   
</style>
<script type="text/javascript" src="jscripts/topup_date_validation.js" ></script>
<script type="text/javascript">
   jQuery(document).ready(function(){
      jQuery('#sel1comp').val('>'); 
      var amount  = CommaFormatted(eval(<?php echo base_gh; ?>));
      jQuery('#firstamount').val(amount); // grossh hold default amount
      jQuery('#btnpdf').click(function(){
          jQuery('#frmreport').attr('action','process/ProcessTopUpGenerateReports.php?action=grossholdmonpdf');
          jQuery('#frmreport').submit();
      });
      
      jQuery('#btnexcel').click(function(){
          jQuery('#frmreport').attr('action','process/ProcessTopUpGenerateReports.php?action=grossholdmonexcel');
          jQuery('#frmreport').submit();          
      });
      
      jQuery("#ghmgrid").jqGrid({
         url : 'process/ProcessTopUpPaginate.php?action=getdata&withconfirm='+jQuery('#selwithconfirmation option:selected').val()+'&sellocation='+jQuery('#sellocation option:selected').val()+'&comp1='+jQuery('#sel1comp option:selected').val()+'&num1='+jQuery('#firstamount').val(),
         datatype: "json",
         colNames:['Site / PEGS Name', 'Site / PEGS Code', 'POS Account','BCF', 'Deposit', 'Reload', 'Withdrawal', 'Manual Redemption','Gross Hold','With Confirmation','Location'],
         rowNum:10,
         rowList:[10,20,30],
         height: 280,
         width: 1200,
         pager: '#pager2',
         viewrecords: true,
         sortorder: "asc",
         caption:"Gross Hold Monitoring",
         colModel:[
            {name:'SiteName',index:'SiteName',align:'left'},
            {name:'SiteCode',index:'SiteCode',align:'left'},
            {name:'POSAccountNo', index:'POSAccountNo', align:'left'},
            {name:'BCF', index:'BCF', align:'right'},
            {name:'Deposit',index:'Deposit',align:'right',sortable:false},
            {name:'Reload',index:'Reload',align:'right',sortable:false},
            {name:'Withdrawal',index:'Withdrawal',align:'right',sortable:false},
            {name:'ActualAmount',index:'ActualAmount',align:'right',sortable:false},
            {name:'GrossHold',index:'GrossHold',align:'right',sortable:false},
            {name:'WithConfirmation',index:'WithConfirmation',sortable:false},
            {name:'Location',index:'Location',sortable:false}
         ],
         loadComplete: function(response) {
             
             if(response.rows != undefined)
             {
                for(var i=0;i<response.rows.length;i++) {
                     var gh = response.rows[i].cell[7].replace(/\,/g,'');
                     //console.log(response.rows);

                     //&& (response.rows[i].cell[9] > <?php echo _GREEN_1 ?> && response.rows[i].cell[9] <= <?php echo _GREEN_2 ?>)
                     if((parseFloat(gh) > <?php echo _GREEN_1 ?> && parseFloat(gh) <= <?php echo _GREEN_2 ?>) && response.rows[i].cell[8] == 'Y' && response.rows[i].cell[9] == 'Provincial') {
                         $('#' + response.rows[i].id).css('background-color','green');
                     } else if((parseFloat(gh) > <?php echo _ORANGE_1 ?> && parseFloat(gh) <= <?php echo _ORANGE_2 ?>) && response.rows[i].cell[8] == 'Y' && response.rows[i].cell[9] == 'Provincial') {
                         $('#' + response.rows[i].id).css('background-color','orange');
                     } else if((parseFloat(gh) > <?php echo _BLUE_ ?>) && response.rows[i].cell[8] == 'Y' && response.rows[i].cell[9] == 'Provincial') {
                         $('#' + response.rows[i].id).css('background-color','#3385FF'); //blue
                     } else if(parseFloat(gh) > <?php echo _RED_ ?> && response.rows[i].cell[8] == 'N' && response.rows[i].cell[9] == 'Provincial') {
                         $('#' + response.rows[i].id).css('background-color','red');
                     }
                 }
                 if(response.sitecode != undefined) {
                     jQuery('#lblsitecode').html(response.sitecode);
                 } else {
                     jQuery('#lblsitecode').html('');
                 }
             }
         },
         resizable:true
      });
      jQuery("#ghmgrid").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false, refresh: true});

      jQuery('#selSiteCode').live('change', function(){
          jQuery('#lblsitename').html(jQuery(this).children('option:selected').attr('label'));
      });
      
      jQuery('#btnsearch').click(function(){
          if(!validateDateTopup()) {
              return false;
          }
          
          var siteid = jQuery('#selSiteCode option:selected').val();
          var startdate = jQuery('#startdate').val();
          var enddate = jQuery('#enddate').val();
          var comp1 = jQuery('#sel1comp option:selected').val();
          var comp2 = jQuery('#sel2comp option:selected').val();
          var num1 = jQuery('#firstamount').val();
          var num2 = jQuery('#secondamount').val();
          var withconfirm = jQuery('#selwithconfirmation option:selected').val();
          var sellocation = jQuery('#sellocation option:selected').val();
          
          if(comp1 == '' && num1 != '') {
              alert('Please select first comparison'); return false;
          }
          
          if(comp1 != '' && num1 == '') {
              alert('Please enter first number'); return false;
          }
          
          if(comp2 == '' && num2 != '' && comp1 != '') {
              alert('Please select second comparison'); return false;
          }
          
          if(comp2 != '' && num2 == '' && comp1 != '') {
              alert('Please enter second number'); return false;
          }          
          
          if(Date.parse(startdate) > Date.parse(enddate)) {
              alert('Start date should less than end date'); return false;
          }
          
          jQuery("#ghmgrid").jqGrid('setGridParam',{url:"process/ProcessTopUpPaginate.php?action=getdata&siteid="+siteid+
            "&startdate="+startdate+"&enddate="+enddate+"&comp1="+comp1+"&comp2="+comp2+"&num1="+num1+"&num2="+num2+
            "&withconfirm="+withconfirm+"&sellocation="+sellocation,page:1}).trigger("reloadGrid"); 
      });
   });
</script>
<?php  
    }
}
include "footer.php"; ?>

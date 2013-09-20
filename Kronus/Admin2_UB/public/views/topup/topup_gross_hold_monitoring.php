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
<!--<style type="text/css">
    #tblgrosshold th{
        background-color: green;
        color: white;
    }
</style>-->
<form method="post" id="frmreport">
<div id="workarea">
    <div id="pagetitle">Gross Hold Monitoring</div>
    <br />
    
    <table>
        <tr>
<!--            <td>Start Date</td>
            <td>
                <input type="text" value="<?php //echo date('Y-m-d'); ?>" id="startdate" readonly="readonly" name="startdate" />
                <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('startdate', false, 'ymd', '-');"/>
            </td>
            <td>End Date</td>
            <td>
                <input type="text" value="<?php //echo date('Y-m-d',strtotime(date("Y-m-d", strtotime(date('Y-m-d')))))  ?>" id="enddate" readonly="readonly" name="enddate" />
                <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('enddate', false, 'ymd', '-');" />
            </td>-->
            <td>Transaction Date</td>
            <td>
                <input type="text" value="<?php echo date('Y-m-d'); ?>" id="startdate" readonly="readonly" name="startdate" />
                <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('startdate', false, 'ymd', '-');"/>
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
<!--        <tr>
            <td>With Confirmation</td>
            <td>
                <select name="selwithconfirmation" id="selwithconfirmation">
                    <option value="">ALL</option>
                    <option value="Y">Y</option>
                    <option value="N">N</option>
                </select>
            </td>
        </tr>-->        
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
    <div id="loading" style="position: fixed; z-index: 5000; background: url('images/Please_wait.gif') no-repeat; height: 162px; width: 260px; margin: 50px 0 0 400px; display: none;"></div>
    <div id="submitarea">
        <input type="button" value="Search" id="btnsearch"/>
    </div>
    <br />
    <div  id="tblgrosshold" style="display: none;width: 1200px; height: 358px; overflow-y: auto;">
        <table id="tblgrossholdbody"></table>
    </div>
    <div id="fade" class="black_overlay" oncontextmenu="return false"></div>
</div>
</form>    
<style type="text/css">
   .ui-jqgrid .ui-jqgrid-htable th div {
      height: 21px; 
/*      background-color: */
   }   
   #tblgrosshold {
       border-radius: 5px 5px 5px 5px;
       -moz-border-top-left-radius: 5px;
       -moz-border-top-right-radius: 5px;
       -moz-border-radius-bottomleft: 5px;
       -moz-border-radius-bottomright: 5px;   
       border-left: 2px solid #AAAAAA;
       border-top: 2px solid #AAAAAA;
       border-right: 3px solid #AAAAAA;
       border-bottom: 3px solid #AAAAAA;
       /*border: 2px solid #AAAAAA;*/
   }
   
   #tblgrossholdbody {
       background-color: #fcfdfd;
   }
   
   #tblgrossholdbody td {
       border: 2px solid #AAAAAA;
       height: 20px;
   }
   
   #tblgrossholdbody {
       border-collapse:collapse;
   }
   
   #tblgrossholdbody {
       border-radius: 5px 5px 5px 5px;
       -moz-border-top-left-radius: 5px;
       -moz-border-top-right-radius: 5px;
       -moz-border-radius-bottomleft: 5px;
       -moz-border-radius-bottomright: 5px;   
   }
   
   #tblfooter-banner {
       border-bottom-left-radius: 5px;
       border-bottom-right-radius: 5px;
       -moz-border-radius-bottomleft: 5px;
       -moz-border-radius-bottomright: 5px; 
   }
   
    #tblheader-banner {
       border-top-left-radius: 5px;
       border-top-right-radius: 5px;
       -moz-border-top-left-radius: 5px;
       -moz-border-top-right-radius: 5px;
       position: relative; top: 0; left: 0; 
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
      
//      jQuery("#ghmgrid").jqGrid({
//         url : 'process/ProcessTopUpPaginate.php?action=getdata&withconfirm='+jQuery('#selwithconfirmation option:selected').val()+'&sellocation='+jQuery('#sellocation option:selected').val()+'&comp1='+jQuery('#sel1comp option:selected').val()+'&num1='+jQuery('#firstamount').val(),
//         datatype: "json",
//         colNames:['Site / PEGS Name', 'Site / PEGS Code', 'POS Account','BCF', 'Deposit', 'Reload', 'Withdrawal', 'Manual Redemption','Gross Hold','With Confirmation','Location'],
//         rowNum:10,
//         rowList:[10,20,30],
//         height: 280,
//         width: 1200,
//         pager: '#pager2',
//         viewrecords: true,
//         sortorder: "asc",
//         caption:"Gross Hold Monitoring",
//         colModel:[
//            {name:'SiteName',index:'SiteName',align:'left'},
//            {name:'SiteCode',index:'SiteCode',align:'left'},
//            {name:'POSAccountNo', index:'POSAccountNo', align:'left'},
//            {name:'BCF', index:'BCF', align:'right'},
//            {name:'Deposit',index:'Deposit',align:'right',sortable:false},
//            {name:'Reload',index:'Reload',align:'right',sortable:false},
//            {name:'Withdrawal',index:'Withdrawal',align:'right',sortable:false},
//            {name:'ActualAmount',index:'ActualAmount',align:'right',sortable:false},
//            {name:'GrossHold',index:'GrossHold',align:'right',sortable:false},
//            {name:'WithConfirmation',index:'WithConfirmation',sortable:false},
//            {name:'Location',index:'Location',sortable:false}
//         ],
//         loadComplete: function(response) {
//             
//             if(response.rows != undefined)
//             {
//                $('#loading').hide();
//                document.getElementById('loading').style.display='none';
//                document.getElementById('fade').style.display='none';
//                
//                for(var i=0;i<response.rows.length;i++) {
//                     var gh = response.rows[i].cell[7].replace(/\,/g,'');
//                     //console.log(response.rows);
//
//                     //&& (response.rows[i].cell[9] > <?php echo _GREEN_1 ?> && response.rows[i].cell[9] <= <?php echo _GREEN_2 ?>)
//                     if((parseFloat(gh) > <?php echo _GREEN_1 ?> && parseFloat(gh) <= <?php echo _GREEN_2 ?>) && response.rows[i].cell[8] == 'Y' && response.rows[i].cell[9] == 'Provincial') {
//                         $('#' + response.rows[i].id).css('background-color','green');
//                     } else if((parseFloat(gh) > <?php echo _ORANGE_1 ?> && parseFloat(gh) <= <?php echo _ORANGE_2 ?>) && response.rows[i].cell[8] == 'Y' && response.rows[i].cell[9] == 'Provincial') {
//                         $('#' + response.rows[i].id).css('background-color','orange');
//                     } else if((parseFloat(gh) > <?php echo _BLUE_ ?>) && response.rows[i].cell[8] == 'Y' && response.rows[i].cell[9] == 'Provincial') {
//                         $('#' + response.rows[i].id).css('background-color','#3385FF'); //blue
//                     } else if(parseFloat(gh) > <?php echo _RED_ ?> && response.rows[i].cell[8] == 'N' && response.rows[i].cell[9] == 'Provincial') {
//                         $('#' + response.rows[i].id).css('background-color','red');
//                     }
//                 }
//                 if(response.sitecode != undefined) {
//                     jQuery('#lblsitecode').html(response.sitecode);
//                 } else {
//                     jQuery('#lblsitecode').html('');
//                 }
//             }
//         },
//         resizable:true
//      });
//      jQuery("#ghmgrid").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false, refresh: true});

      jQuery('#selSiteCode').live('change', function(){
          jQuery('#lblsitename').html(jQuery(this).children('option:selected').attr('label'));
      });
      
      jQuery('#btnsearch').click(function(){
          if(!validateDateTopup()) {
              return false;
          }
          
          var siteid = jQuery('#selSiteCode option:selected').val();
          var startdate = jQuery('#startdate').val();
          var comp1 = jQuery('#sel1comp option:selected').val();
          var comp2 = jQuery('#sel2comp option:selected').val();
          var num1 = jQuery('#firstamount').val();
          var num2 = jQuery('#secondamount').val();
          
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
          
//          if(Date.parse(startdate) > Date.parse(enddate)) {
//              alert('Start date should less than end date'); return false;
//          }
        document.getElementById('loading').style.display='block';
        document.getElementById('fade').style.display='block';
        
        $.ajax({
                url: 'process/ProcessTopUpPaginate.php?action=getdata&sellocation='+jQuery('#sellocation option:selected').val()+'&comp1='+jQuery('#sel1comp option:selected').val()+'&num1='+jQuery('#firstamount').val()+'&comp2='+jQuery('#sel2comp option:selected').val()+'&num2='+jQuery('#secondamount').val(),
                type: 'GET',
                data : {
                                sord : function() {return "asc"; },
                                startdate : function(){return startdate;},
                                siteid : function(){return siteid;}
                            },
                dataType: 'json',
                success: function(data)
                {
                    $("#tblgrosshold").css("display", "block");
                    
                    var header = "<thead style='position: relative; top:0; left: 0; height: -63px;'><tr><td  id='tblheader-banner' colspan='11' style='font-style: Arial, calibri, helvetica; font-size: 14px; font-weight: bold; background-color: #3d561c; color: white; height: 30px;'>Gross Hold Monitoring</td>"+
                                            "</tr><tr style='font-style: Arial, calibri, helvetica; font-size: 12px; text-align: center;font-weight: bold; background-color: #D6EB99; color: black; height: 25px;'>"+
                                            "<td style='width: 100px;'>POS Account</td><td style='width: 230px;'>Site / PEGS Name</td><td style='width: 130px;'>BCF</td><td style='width: 130px;'>Deposit</td>"+
                                            "<td style='width: 130px;'>Reload</td><td style='width: 130px;'>Withdrawal</td><td style='width: 130px;'>Manual Redemption</td><td style='width: 130px;'>Voucher</td><td style='width: 130px;'>Gross Hold</td><td style='width: 90px;'>Location</td>"+
                                            "</tr></thead>";
                    
                    var footer = "<tfoot style='position: relative; top:0; left: 0; height: -37px;'><tr style='font-style: Arial, calibri, helvetica; font-size: 12px; text-align: center;font-weight: bold; background-color: #D6EB99; color: black; height: 35px;'>"+
                                            "<td colspan='11' id='tblfooter-banner'></td></tr></tfoot>";
                                        
                    $("#tblgrossholdbody").html("");
                    $("#tblgrossholdbody").html(header);
                    
                     
                    
                    $('#loading').hide();
                    document.getElementById('loading').style.display='none';
                    document.getElementById('fade').style.display='none';
                    $("#tblgrossholdbody").append("<tbody>");
                    for(var itr = 0; itr < data.CountOfSites; itr++){
                        var part = "<tr id='"+data[itr].POS+"'><td >"+data[itr].POS+"</td><td>"+data[itr].SiteName+"</td><td style='text-align: right;'>"+data[itr].BCF+"</td>"+
                                            "<td style='text-align: right;'>"+data[itr].Deposit+"</td><td style='text-align: right;'>"+data[itr].Reload+"</td><td style='text-align: right;'>"+data[itr].Withdrawal+"</td><td style='text-align: right;'>"+data[itr].ManualRedemption+"</td>"+
                                            "<td style='text-align: right;'>"+data[itr].Coupon+"</td>"+"<td style='text-align: right;'>"+data[itr].GrossHold+"</td><td>"+data[itr].Location+"</td></tr>";
                        
                        $("#tblgrossholdbody").append(part);
                        
                        var grosshold = data[itr].GrossHold;
                        var balance = data[itr].BCF;
                        var gh = grosshold.replace(/,/g,"");
                        var bcf = balance.replace(/,/g,"");
                        var threshold = data[itr].MinBalance;
                        
                        //if gross hold >= 200K, color row will be green
                        if(parseFloat(gh) >= parseFloat('<?php echo _GREEN_1; ?>')) {
                             $('#' + data[itr].POS).css('background-color','#32CD32');
                        } 
                        
                        //if site balance < minimum threshold, color will be red
                        if(parseFloat(bcf) < parseFloat(threshold)) {
                             $('#' + data[itr].POS).css('background-color','red');
                        }

                    }
                    $("#tblgrossholdbody").append("</tbody>");          
                }
        });
        
      });
   });
</script>
<?php  
    }
}
include "footer.php"; ?>

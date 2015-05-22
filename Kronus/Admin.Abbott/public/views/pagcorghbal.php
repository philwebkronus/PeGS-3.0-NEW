<?php 
$pagetitle = "GH Balance Per Cut-off"; 
include 'process/ProcessPagcorMgmt.php';
include "header.php";
$vaccesspages = array('11');
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
    <form method="post" id="frmexport">
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <table>
            <tr>
                <td>Transaction Date</td>
                <td colspan="3">
                    <input type="text" value="<?php echo date('Y-m-d') ?>" id="startdate" readonly="readonly" name="startdate" />&nbsp;<img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('startdate', false, 'ymd', '-');"/>
                </td>
<!--                End Date
                &nbsp;
                    <input type="text" value="<?php echo date('Y-m-d',strtotime(date("Y-m-d", strtotime(date('Y-m-d'))))) ?>" id="enddate" readonly="readonly" name="enddate" />&nbsp;<img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('enddate', false, 'ymd', '-');"/> 
                </td>-->
            </tr>
            <tr>
                <td width="130px">Site / PEGS</td>
                <td>
                <?php
                    $vsite = $_SESSION['siteids'];
                    echo "<select id=\"selsitecode\" name=\"selsitecode\">";
                    echo "<option value=\"\">All</option>";
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
        </table>
        <div id="submitarea">
            <input type="button" value="Search" id="btnsearch"/>
        </div>
        <br /><br />
        <div align="center" id="pagination">
            <table id="tblreplenish">
            </table>
            <div id="pager2"></div>
        </div>
        <div id="senchaexport1" style="background-color: #6A6A6A; padding-bottom: 60px; width: 1200px;">
            <br />
            <input type="button" id="btnpdf" value="Export to PDF File" style="float: right;"/>
            <input type="button" id="btnexcel" value="Export to Excel File" style="float: right;"/>
        </div>
    </form>
</div>
<script type="text/javascript" src="jscripts/topup_date_validation.js" ></script>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var url = 'process/ProcessPagcorMgmt.php';
        jQuery('#selsitecode').live('change', function(){
           var siteid = $(this).val();
           if(siteid > 0)
           {
               jQuery.ajax({
                      url: url,
                      type: 'post',
                      data: {cmbsitename: function(){return siteid;}},
                      dataType: 'json',
                      success: function(data){
                          if(siteid > 0)
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
           }
           else
           {
               jQuery("#txtsitename").text(" ");
           } 
        });
      
        jQuery('#btnpdf').click(function(){
            jQuery('#frmexport').attr('action','process/ProcessPagcorMgmt.php?export=grossholdbalancepdf');
            jQuery('#frmexport').submit();            
        });
        
        jQuery('#btnexcel').click(function(){
            jQuery('#frmexport').attr('action','process/ProcessPagcorMgmt.php?export=grossholdbalanceexcel&fn=grossholdpercutoff');
            jQuery('#frmexport').submit();  
        });        
        
        jQuery('#tblreplenish').jqGrid({
            url : 'process/ProcessPagcorMgmt.php?action=GHBalancePerCutoff&site='+jQuery('#selsitecode').val(),
            datatype: "json",
            colNames:['Site / PEGS Code', 'Site / PEGS Name', 'POS Account', 'Initial Deposit', 'Reload','Redemption','Manual Redemption','Gross Hold'],
            rowNum:10,
            height: 280,
            width: 1200,
            pager: '#pager2',
            viewrecords: true,
            rowList:[10,20,30],
            sortorder: "asc",
            caption: "GH Balance Per Cut-off",            
            colModel:[
                {name:'SiteCode',index:'SiteCode',align:'left'},
                {name:'SiteName',index:'SiteName',align:'left'},
                {name:'POSAccountNo', index:'POSAccountNo',align:'center'},
                {name:'InitialDeposit',index:'InitialDeposit',align:'right'},
                {name:'Reload',index:'Reload',align:'right'},
                {name:'Redemption',index:'Redemption',align:'right'},
                {name:'ManualRedemption',index:'manualredemption',align:'right'},
                {name:'GrossHold',index:'GrossHold',align:'right'}
            ]
        });
        
        jQuery('#btnsearch').click(function(){
            if(!validateDateTopup()) {
              return false;
            }
            var startdate = jQuery('#startdate').val();
            //var enddate = jQuery('#enddate').val();
            var site = jQuery('#selsitecode').val();
            
            jQuery("#tblreplenish").jqGrid('setGridParam',{url:"process/ProcessPagcorMgmt.php?action=GHBalancePerCutoff&startdate="+startdate+
                "&site="+site,page:0}).trigger("reloadGrid");  
        });
    });
</script>
<?php  
    }
}
include "footer.php"; ?>
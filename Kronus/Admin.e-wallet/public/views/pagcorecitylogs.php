<?php
$pagetitle = "Extract E-City Logs";
include 'process/ProcessPagcorMgmt.php';
include 'header.php';
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
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <form method="post" action="#" id="frmexport">
        <br />
        <table>
            <tr>
                <td>Date Range</td>
                <td>
                From: 
                 <input name="txtDate1" id="rptDate" readonly value="<?php echo date('Y-m-d')?>"/>
                 <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate1', false, 'ymd', '-');"/>
                </td>
                <td>
                To:
                <input name="txtDate2" id="rptDate2" readonly value="<?php echo date ( 'Y-m-d'); ?>"/>
                <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate2', false, 'ymd', '-');"/>
                </td>
            </tr>
        </table>
        <div id="submitarea">
            <input type="button" value="Export to Excel" id="btnexport" />
        </div>
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        //event: onclick of export to excel button 
        jQuery("#btnexport").click(function(){
            var validate = chkdateformat();
            if(validate == true)
            {
                jQuery("#frmexport").attr('action', 'process/ProcessPagcorMgmt.php?export=ECityLogs&fn=ECityLogs');
                jQuery("#frmexport").submit(); 
            }
        });
    });
</script>
<?php  
    }
}
include "footer.php"; ?>
        

<?php
$pagetitle = "Extract Transaction Details";
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
    <br />
    <form method="post" action="#" id="frmexcel">
        <input type="hidden" id="txtservices" name="txtservices" />
        <table>
            <tr>
                <td>Date Range</td>
                <td>
                From: 
                 <input name="txtDate1" id="popupDatepicker1" readonly value="<?php echo date('Y-m-d')?>"/>
                 <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate1', false, 'ymd', '-');"/>
                </td>
                <td>
                To:
                <input name="txtDate2" id="popupDatepicker2" readonly value="<?php echo date ( 'Y-m-d'); ?>"/>
                <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtDate2', false, 'ymd', '-');"/>
                </td>
            </tr>
            <tr>
                <td>Services</td>
                <td>
                    <select id="cmbservices" name="cmbservices">
                        <option value="-1">Please Select</option>
                        <option value="RTG">RTG</option>
                        <option value="MG">MG</option>
                    </select>
                </td>
            </tr>
        </table>
        <div id="submitarea">
            <input type="button" value="Export to Excel" id="btnexcel"/>
        </div>
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery("#btnexcel").click(function(){
           var validate = chktransaction();
           if(validate == true)
           {
                jQuery("#frmexcel").attr('action', 'process/ProcessPagcorMgmt.php?export=TransactionDetails&fn=TransactionDetails');
                jQuery("#frmexcel").submit();
           }
        });
    });
</script>
<?php  
    }
}
include "footer.php"; ?>
        

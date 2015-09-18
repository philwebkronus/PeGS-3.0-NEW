<?php 
$pagetitle = "Change Account Status";  
include 'process/ProcessAccManagement.php';
include "header.php";

$vaccesspages = array('1', '8' , '2');
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
        if((!isset($_SESSION['accounts'])) && (!isset($_SESSION['accid'])))
        {
            echo "<script type='text/javascript'>window.location.href='accountview.php';</script>";
        }
        else
        {
            $raccstatus = $_SESSION['accounts'];
            $raid = $_SESSION['accid'];
            foreach($raccstatus as $results)
            {
                $raccname = $results['UserName'];
                $racctype = $results['AccountTypeID'];
                $raccID = $results['Status'];
                switch ($raccID)
                {
                    case 0:
                        $rstatus = "Inactive";
                    break;
                    case 1:
                        $rstatus = "Active";
                    break;
                    case 2: 
                        $rstatus = "Suspended";
                    break;
                    case 3:
                        $rstatus = "Locked (Attempts)";
                    break;
                    case 4:
                        $rstatus = "Locked (Admin)";
                    break;
                    case 5:
                        $rstatus = "Terminated";
                    break;
                    case 6:
                        $rstatus = "Expired Password";
                    break;                
                }
            }
        }

?>
<script type="text/javascript">
    jQuery(document).ready(function(){
<?php
     if($raccID == 5)
     {
?>
        jQuery(':radio').attr('disabled', 'disabled'); //disable radio buttons if account was terminated
<?php
     }

?>
    });
</script>
<div id="workarea">
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <form method="post" name="frmstatus" action="process/ProcessAccManagement.php">
            <input type="hidden" name="page" value="StatusUpdate" />
            <input type="hidden" name="txtaccid" value="<?php echo $raid; ?>" />
            <input type="hidden" name="txtoldstatus" value="<?php echo $raccID; ?>" />
            <input type="hidden" name="txtaccname" value="<?php echo $raccname;?>" />
            <input type="hidden" name="txtacctype" value="<?php echo $racctype; ?>">
            <table>
                <tr>
                    <td width="130px">Current Status:</td>
                    <td>
                        <input type="text" readonly="readonly" size="10" value="<?php echo  $rstatus; ?>"/>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                       <?php if($raccID == 1) {?>
                           Active<input type="radio" id="optstatyes" name="optstatus" value="1" checked = CHECKED />
                           Inactive<input type="radio" id="optstatno" name="optstatus" value="0"  />
                       <?php } else { ?>
                           Active<input type="radio" id="optstatyes" name="optstatus" value="1" />
                           Inactive<input type="radio" id="optstatno" name="optstatus" value="0" checked =CHECKED/>
                       <?php } ?>
                    </td>
                </tr>
            </table>
            
            <div id="submitarea">
                <input type="submit" value="Submit" onclick="return chkStatus();"/>
            </div>
        </form>
</div>
    
<?php  
    }
}
include "footer.php"; ?>
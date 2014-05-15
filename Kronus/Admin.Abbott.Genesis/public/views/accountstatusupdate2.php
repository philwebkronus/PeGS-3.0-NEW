<?php $pagetitle = "Update Status";  ?>

<?php
//session_start();
$raccstatus = $_SESSION['accounts'];
$raid = $_SESSION['accid'];
foreach($raccstatus as $results)
{
    $raccID = $results['Status'];
    if($raccID == 1)
    {
        $rstatus = "Active";
    }
    else{
        $rstatus = "Inactive";
    }
}
?>

<?php  include "header.php"; ?>

<div id="workarea">

    <div id="pagetitle">Update Status</div>
    
        <form method="post" name="frmstatus" action="process/ProcessAccManagement.php">
            <input type="hidden" name="page" value="StatusUpdate" />
            <input type="hidden" name="txtaccid" value="<?php echo $raid; ?>" />
            <table>
                <tr>
                    <td>Current Status:</td>
                    <td>
                        <input type="text" readonly="readonly" value="<?php echo  $rstatus; ?>"/>
                    </td>
                </tr>
                <tr>
                    <td>
                       <?php if($raccID == 1) {?>
                           Active<input type="radio" id="optstatyes" name="optstatus" value="1" CHECKED />
                           Inactive<input type="radio" id="optstatno" name="optstatus" value="0"  />
                       <?php } else { ?>
                           Active<input type="radio" id="optstatyes" name="optstatus" value="0" />
                           Inactive<input type="radio" id="optstatno" name="optstatus" value="1" CHECKED/>
                       <?php } ?>
                    </td>
                </tr>
            </table>
            <input type="button" value="Back" onclick="history.go(-1);"/>
            <input type="submit" value="submit" onclick="return chkStatus();"/>
        </form>


</div>
    
<?php  include "footer.php"; ?>
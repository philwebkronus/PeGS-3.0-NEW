<?php  $pagetitle = "Update Terminal Status";  ?>


<?php
session_start();
$rterminalID = $_SESSION['termid'];
$rserviceID = $_SESSION['serviceid'];
$rupdstat = $_SESSION['updstatus'];
foreach($rupdstat as $results)
{
    $rstatID = $results['Status'];
    if($rstatID == 1)
    {
        $rstatus = "Active";
    }
    else
    {
        $rstatus = "Inactive";
    }
}
?>

<?php  include "header.php"; ?>

<div id="workarea">

        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />

        <form method="post" action="../process/ProcessTerminalMgmt.php">
            <input type="hidden" name="page" value="ServiceUpdate" />
            <input type="hidden" name="txttermid" value="<?php echo $rterminalID; ?>" />
            <input type="hidden" name="txtserviceid" value="<?php echo $rserviceID; ?>" />
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
                       <?php if($rstatID == 1) {?>
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
<!--                <input type="button" value="Back" onclick="history.go(-1);"/>-->
                <input type="submit" value="Submit"/>
            </div>
        </form>

</div>

<?php  include "footer.php"; ?>

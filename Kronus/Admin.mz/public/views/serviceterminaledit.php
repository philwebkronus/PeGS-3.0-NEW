<?php  
$pagetitle = "Update MG OC Account Status";  
include "header.php";
$vaccesspages = array('8');
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
            if((!isset($_SESSION['stermid'])) && (!isset ($_SESSION['ststatus'])))
            {
                echo "<script type='text/javascript'>window.location.href='serviceterminalview.php';</script>";
            }
            else
            {
                $rsterminalID = $_SESSION['stermid'];
                $rupdstat = $_SESSION['ststatus'];
                foreach($rupdstat as $results)
                {
                    $rstatID = $results['Status'];
                    if($rstatID == 1)
                    {
                        $rstatus = "Active";
                    }
                    else{
                        $rstatus = "Inactive";
                    }
                }
            }
?>

<div id="workarea">
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <form method="post" action="process/ProcessTerminalMgmt.php">
            <input type="hidden" name="page" value="ServiceTerminalUpdate" />
            <input type="hidden" name="txtstermid" value="<?php echo $rsterminalID; ?>" />
            <input type="hidden" name="txtoldstat" value="<?php echo $rstatID; ?>" />
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
                <input type="submit" value="Submit"/>
            </div>
        </form>
</div>
<?php  
    }
}
include "footer.php"; ?>

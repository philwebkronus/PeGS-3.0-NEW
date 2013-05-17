<?php  
$pagetitle = "Update Topup";  
include "process/ProcessTopUp.php";
include "header.php";

$vaccesspages = array('5');
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
            if((!isset($_SESSION['site'])) && (!isset($_SESSION['BCF'])))
            {
                echo "<script type='text/javascript'>window.location.href='topupview.php';</script>";
            }
            else
            {
                $vsite = $_SESSION['site'];
                $rbcf = $_SESSION['BCF'];
                foreach ($rbcf as $rresult)
                {
                   $ramount = $rresult['Balance'];
                   $rminbal = $rresult['MinBalance'];
                   $rmaxbal = $rresult['MaxBalance'];
                   $rpickup = $rresult['PickUpTag'];
                   $rtopuptype = $rresult['TopUpType'];
                }
                $rolddetails = array($rminbal, $rmaxbal, $rpickup, $rtopuptype);
                $olddetails = implode(",", $rolddetails);
            }
?>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <form method="post" action="process/ProcessTopUp.php">
        <input type="hidden" name="page" value="UpdateSiteParam" />
        <input type="hidden" name="txtsite" value="<?php echo $vsite[0]; ?>" />
        <input type="hidden" name="txtprevbal" value="<?php echo $ramount; ?>" />
        <input type="hidden" name="txtolddetails" value="<?php echo $olddetails; ?>" />
        <input type="hidden" name="txtsitecode" value="<?php echo $vsite[1]; ?>" />
        <table>
            <tr>
                <td>Site Balance</td>
                <td>
                    <input type="text" id="txtamount" name="txtamount" value="<?php echo  number_format($ramount,2); ?>" READONLY  />
                </td>
            </tr>
            <tr>
                <td>Minimum Balance</td>
                <td>
                    <input type="text" class="auto" id="txtminbal" name="txtminbal" value="<?php echo number_format($rminbal,2); ?>"/>
                </td>
            </tr>
            <tr>
                <td>Maximum Balance</td>
                <td>
                    <input type="text" class="auto" id="txtmaxbal" name="txtmaxbal" value="<?php echo number_format($rmaxbal,2); ?>" />
                </td>
            </tr>
            <tr>
                <td>Pick Up</td>
                <td>
                   <?php if( $rpickup == 1){ ?>
                      Metro Manila<input type="radio" id="optpickyes" name="optpick" value="1"  checked =CHECKED/>
                      Provincial<input type="radio" id="optpickno" name="optpick" value="0" />
                   <?php } else { ?>
                       Metro Manila<input type="radio" id="optpickyes" name="optpick" value="1"  />
                      Provincial<input type="radio" id="optpickno" name="optpick" value="0" checked =CHECKED/>
                   <?php } ?>
                </td>
            </tr>
            <tr>
                <td>Top-up Type</td>
                <td>
                   <?php if( $rtopuptype == 1){ ?>
                      Variable<input type="radio" id="opttypeyes" name="opttype" value="1"  checked =CHECKED/>
                      Fixed<input type="radio" id="opttypeno" name="opttype" value="0" />
                   <?php } else { ?>
                      Variable<input type="radio" id="opttypeyes" name="opttype" value="1"  />
                      Fixed<input type="radio" id="opttypeno" name="opttype" value="0" checked =CHECKED/>
                   <?php } ?>
                </td>
            </tr>
        </table>
        <div id="submitarea">
            <input type="submit" value="Submit" id="btnSubmit" onclick="return chkupdtopup();"/>
        </div>
    </form>
</div>
<?php  
    }
}
include "footer.php"; ?>


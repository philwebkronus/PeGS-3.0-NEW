<?php  
$pagetitle = "Terminal Update Status"; 
include 'process/ProcessTerminalMgmt.php';
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
            if((!isset($_SESSION['updterms'])))
            {
                echo "<script type='text/javascript'>window.location.href='terminalview.php';</script>";
            }
            else
            {
                $rtermdetails = $_SESSION['updterms'];
                foreach($rtermdetails as $results)
                {
                  $rstatID = $results['Status'];
                  $rtermcode = $results['TerminalCode'];
                  $rsiteID = $results['SiteID'];
                  if($rstatID == 1)
                  {
                    $rstatus = "Active";
                  }
                  else
                  {
                    $rstatus = "Inactive";
                  }
                }
            }
?>

<div id="workarea">    
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <form method="post" action="process/ProcessTerminalMgmt.php" name="frmstatus">
            <input type="hidden" name="page" value="TerminalUpdateStatus" />
<!--            <input type="hidden" name="txttermid" value="<?php //echo $rterminalID; ?>" />-->
            <input type="hidden" name="txttermcode" value="<?php echo $rtermcode; ?>" />
            <input type="hidden" name="txtsiteID" value="<?php echo $rsiteID; ?>" />
            <input type="hidden" name="txtoldstat" value="<?php echo $rstatID; ?>" />
            <table>
                 <tr>
                    <td width="130px">Current Status:</td>
                    <td>
                        <input type="text" readonly="readonly" value="<?php echo $rstatus; ?>"/>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                      <?php if($rstatID == 1){ ?>
                          Active<input type="radio" id="optstatyes" name="optstatus" value="1"  checked =CHECKED/>
                          Inactive<input type="radio" id="optstatno" name="optstatus" value="0" />
                      <?php } else { ?>
                          Active<input type="radio" id="optstatyes" name="optstatus" value="1"  />
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
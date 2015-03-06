<?php 
$pagetitle = "Update Site Status"; 
include 'process/ProcessSiteManagement.php';
include "header.php";
$vaccesspages = array('8','5');
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
    if((!isset($_SESSION['ressitedet'])) && (!isset($_SESSION['siteid'])))
    {
        echo "<script type='text/javascript'>window.location.href='siteview.php';</script>";
    }
    else
    {
        $raid = $_SESSION['siteid'];
        $rsitestatus = $_SESSION['ressitedet'];
        foreach($rsitestatus as $results)
        {
            $rstatID = $results['Status'];
            switch ($rstatID)
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
                    $rstatus = "Deactivated";
                break;
                default :
                    $rstatus = "Invalid Status";
                break;
            }
        }
    }
?>

<div id="workarea">    
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        
        <form method="post" name="frmstatus" action="process/ProcessSiteManagement.php">
            <input type="hidden" name="page" value="UpdateStatus" />
            <input type="hidden" name="txtsiteid" value="<?php echo $raid; ?>" />
            <input type="hidden" name="txtoldstat" id="txtoldstat" value="<?php echo $rstatID; ?>"/>
            <table>
                <tr>
                    <td width="130px">Current Status:</td>
                    <td>
                        <input type="text" readonly="readonly" value="<?php echo  $rstatus; ?>"/>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                      <?php 
                            switch ($rstatID)
                            {
                                case 0:
                       ?>
                           Active<input type="radio" id="optstatyes" name="optstatus" value="1"  /> &nbsp;
                           Inactive<input type="radio" id="optstatno" name="optstatus" value="0" checked = CHECKED /> &nbsp;
                           Suspended<input type="radio" id="optsuspended" name="optstatus" value="2" /> &nbsp;
                           Deactivated<input type="radio" id="optlattempts" name="optstatus" value="3" />
                       <?php
                                break;
                                case 1:
                       ?>
                           Active<input type="radio" id="optstatyes" name="optstatus" value="1" checked = CHECKED />&nbsp;
                           Inactive<input type="radio" id="optstatno" name="optstatus" value="0"  />&nbsp;
                           Suspended<input type="radio" id="optsuspended" name="optstatus" value="2" />&nbsp;
                           Deactivated<input type="radio" id="optlattempts" name="optstatus" value="3" />
                       <?php
                                break;
                                case 2: 
                       ?>
                           Active<input type="radio" id="optstatyes" name="optstatus" value="1" />&nbsp;
                           Inactive<input type="radio" id="optstatno" name="optstatus" value="0"  />&nbsp;
                           Suspended<input type="radio" id="optsuspended" name="optstatus" value="2" checked = CHECKED />&nbsp;
                           Deactivated<input type="radio" id="optlattempts" name="optstatus" value="3" />
                       <?php
                                break;
                                case 3:
                       ?>
                           Active<input type="radio" id="optstatyes" name="optstatus" value="1" />&nbsp;
                           Inactive<input type="radio" id="optstatno" name="optstatus" value="0"  />&nbsp;
                           Suspended<input type="radio" id="optsuspended" name="optstatus" value="2" />&nbsp;
                           Deactivated<input type="radio" id="optlattempts" name="optstatus" value="3" checked = CHECKED />
                       <?php
                                break;
                                default:
                       ?>
                           Active<input type="radio" id="optstatyes" name="optstatus" value="1" />&nbsp;
                           Inactive<input type="radio" id="optstatno" name="optstatus" value="0"  />&nbsp;
                           Suspended<input type="radio" id="optsuspended" name="optstatus" value="2" />&nbsp;
                           Deactivated<input type="radio" id="optlattempts" name="optstatus" value="3" />
                       <?php
                                break;
                            }
                       ?> 
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
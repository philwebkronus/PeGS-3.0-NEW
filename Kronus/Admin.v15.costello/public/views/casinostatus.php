<?php 
$pagetitle = "Casino Services Update Status"; 
include 'process/ProcessCasinoMgmt.php';
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
    if((!isset($_SESSION['reservicedet'])) && (!isset($_SESSION['serviceid'])))
    {
        echo "<script type='text/javascript'>window.location.href='siteview.php';</script>";
    }
    else
    {
        $rsitestatus = $_SESSION['reservicedet'];
        
        foreach($rsitestatus as $results)
        {
            $rserviceID = $results['ServiceID']; 
                $rstatus = $results['Status'];
            switch ($rstatus)
            {
                case 0:
                    $rstatuz = "Inactive";
                break;
                case 1:
                    $rstatuz = "Active";
                break;
            }
        }
    }
?>


<div id="workarea">    
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        
        <form method="post" name="frmstatus" action="process/ProcessCasinoMgmt.php">
            <input type="hidden" name="page" value="UpdateStatus" />
            <input type="hidden" name="txtsiteid" value="<?php echo $rserviceID; ?>" />
            <input type="hidden" name="txtoldstat" id="txtoldstat" value="<?php echo $rstatus; ?>"/>
            <table>
                <tr>
                    <td width="130px">Current Status:</td>
                    <td>
                        <input type="text" readonly="readonly" value="<?php echo  $rstatuz; ?>"/>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>
                      <?php 
                            switch ($rstatus)
                            {
                                case 0:
                       ?>
                           Active<input type="radio" id="optstatyes" name="optstatus" value="1"  /> &nbsp;
                           Inactive<input type="radio" id="optstatno" name="optstatus" value="0" checked = CHECKED /> &nbsp;
                           
                       <?php
                                break;
                                case 1:
                       ?>
                           Active<input type="radio" id="optstatyes" name="optstatus" value="1" checked = CHECKED />&nbsp;
                           Inactive<input type="radio" id="optstatno" name="optstatus" value="0"  />&nbsp;
                          
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
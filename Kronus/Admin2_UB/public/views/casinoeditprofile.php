<?php
$pagetitle = "Casino Services Profile Management";
include 'process/ProcessCasinoMgmt.php';
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
            if((!isset($_SESSION['reservicedet'])))
            {
                echo "<script type='text/javascript'>window.location.href='casinoviewprofile.php';</script>";
            }
            else
            {
              
                $rservicedet = $_SESSION['reservicedet'];
                foreach ($rservicedet as $resultviews)
                {
                $rserviceID = $resultviews['ServiceID'];
                $rservicegrpID = $resultviews['ServiceGroupID'];
                $rservicgename = $resultviews['ServiceGroupName'];
                $rservname = $resultviews['ServiceName'];
                $rsalias= $resultviews['Alias'];
                $rscode = $resultviews['Code'];
                $rservcdesc = $resultviews['ServiceDescription'];
                $rusermode = $resultviews['UserMode'];
                $rstatus = $resultviews['Status'];
               }
                $_SESSION['serviceid'] = $rserviceID;
               
                $arrolddetails = array($rserviceID, $rservicgename, $rservname, $rsalias, $rscode, $rservcdesc, $rusermode, $rstatus);
                $olddetails = implode("-", $arrolddetails);
            }
?>

<div id="workarea">    
     
        
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        
        <form method="post" action="process/ProcessCasinoMgmt.php" onsubmit="return chktrailingservicespaces();">
            <input type="hidden" name="page" value='ServiceUpdate'>
            <input type="hidden" name="txtserviceid" value="<?php echo $rserviceID; ?>" />
            <input type="hidden" name="txtolddetails" value="<?php echo $olddetails; ?>" />
            <table>
              
                <tr>
                    <td>Service Name</td>
                    <td>
                        <input type="text" name="txtservicename" id="txtservicename" value="<?php echo $rservname; ?>" maxlength="30" size="30" onkeypress="return alphanumeric4(event);" />
                    </td>
                </tr>

                <tr>
                    <td>Service Alias</td>
                    <td>
                        <input type="text" name="txtalias" id="txtalias" value="<?php echo $rsalias; ?>" maxlength="30" size="30" onkeypress="return letteronly2(event);" />
                        
                    </td>
                </tr>

                <tr>
                    <td>Code</td>
                    <td>
                        <input type="text" name="txtservicecode" id="txtservicecode" value="<?php echo $rscode; ?>" maxlength="2" size="3" onkeypress="return letteronly(event);"/>
                    </td>
                </tr>

                <tr>
                    <td title="Certificate To Operate">Description</td>
                    <td>
                        <input type="text" name="txtservcdesc" id="txtservcdesc" value="<?php echo $rservcdesc; ?>" maxlength="50" size="50" onkeypress="return alphanumeric3(event);"/>
                    </td>
                </tr>
                 <tr>
                    <td width="130px;">Service Group</td>
                    <td>
                        <?php
                            $vowner = $_SESSION['servicegrpid'];
                            $status = "";
                            echo "<select id=\"cmbservicegrp\" name=\"cmbservicegrp\" >";
                            echo "<option value=\"\">Please Select</option>";
                            foreach ($vowner as $resultdet){
                               $vaccID = $resultdet['ServiceGroupID'];
                               $vaccname = $resultdet['ServiceGroupName'];
                              if($rservicegrpID == $vaccID)
                               {
                                    echo "<option value=\"".$rservicegrpID."\" selected=\"selected\">".$vaccname."</option>";
                                    
                                    
                               }
                             else 
                               {
                                    echo "<option value=\"".$vaccID."\">".$vaccname."</option>";
                               }
                            }
                             echo "</select>";
                        ?>
                   
                    </td>
                    <input type="hidden" name="txtoldowner" value="<?php echo $rservicegrpID; ?>" />
                </tr>
                <tr>
                    <td>Mode</td>
                    <td>
                        <?php if($rusermode == 0) { ?>
                            Account<input type="radio" id="usermodeub" name="usermode" value="1" />
                            Terminal<input type="radio" id="usermodeter" name="usermode" value="0"  checked/>
                        <?php }else{ ?>
                            Account<input type="radio" id="usermodeub" name="usermode" value="1" checked/>
                            Terminal<input type="radio" id="usermodeter" name="usermode" value="0"  />
                        <?php }?>
                    </td>
                    <input type="hidden" name="txtoldusermode" value="<?php echo $rusermode; ?>" />
                </tr>
            </table>
            <div id="submitarea">
                <input type="submit" value="Submit" onclick="return chkeditservices();"/>
                <input type="button" value="Change Status" onclick="window.location.href='process/ProcessCasinoMgmt.php?serviceid=<?php echo $rserviceID; ?>'+'&statuspage='+'UpdateStatus'"/>
                
            </div>
        </form>
        
</div>
<?php  
    }
}
include "footer.php"; ?>

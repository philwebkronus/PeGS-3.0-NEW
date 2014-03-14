<?php  
$pagetitle = "Update MG Agent";  
include "process/ProcessTerminalMgmt.php";
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
            
             if((!(isset($_SESSION['viewagent']))) && (!isset($_SESSION['agentid'])))
             {
                 echo "<script type='text/javascript'>window.location.href='serviceagentview.php';</script>";
             }
             else
             {
                 $vviewagent = $_SESSION['viewagent'];
                 $vviewagentid = $_SESSION['agentid'];

                 foreach ($vviewagent as $results)
                 {
                    $vagentname = $results['Username'];
                    $vagentpwd = $results['Password'];
                    $rsiteID = $results['SiteID'];
                 }

                 $arrolddetails = array($rsiteID, $vagentname, $vagentpwd);
                 $olddetails = implode(",", $arrolddetails);
             }
?>

<script type="text/javascript">
    jQuery(document).ready(function(){
       var url = '../process/ProcessTerminalMgmt.php';
       jQuery("#cmbsitename").live('change', function()
       {
          //this part is for displaying site name
             jQuery.ajax(
             {
                url: url,
                type: 'post',
                data: {cmbsitename: function(){return jQuery("#cmbsitename").val();}},
                dataType: 'json',
                success: function(data)
                {
                   if(jQuery("#cmbsitename").val() > 0)
                   {
                      jQuery("#txtsitename").text(data.SiteName);
                   }
                   else
                   {   
                      jQuery("#txtsitename").text(" ");
                   }
                },
                error: function(e)
                {
                   alert(e.responseText);
                }
             });  
       });
    });
</script>
<div id="workarea">
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
          <form method="post" action="process/ProcessTerminalMgmt.php">
                <input type="hidden" name="page" value="ServiceAgentUpdate" />
                <input type="hidden" name="agentid" value="<?php echo $vviewagentid; ?>" />
                <input type="hidden" name="txtolddetails" value="<?php echo $olddetails; ?>" /> 
                <table>
                  <tr>
                   <td width="130px">Site / PEGS Name</td>
                      <td>
                        <?php
                            $sites = $_SESSION['siteids'];
                            echo "<select id=\"cmbsitename\" name=\"cmbsitename\">";
                            echo "<option value=\"-1\">Please Select</option>";
                            foreach ($sites as $result)
                            {
                               $vsiteID = $result['SiteID'];
                               $vname = $result['SiteName'];
                               $vorigcode = $result['SiteCode'];
                               if($vsiteID  <> 1)
                               {
                                   //search if the sitecode was found on the terminalcode
                                   if(strstr($vorigcode, $terminalcode) == false)
                                   {
                                     $vcode = $vorigcode;
                                   }
                                   else
                                   {
                                     //removes the "icsa-"
                                     $vcode = substr($vorigcode, strlen($terminalcode));
                                   }
                                   
                                   if($rsiteID == $vsiteID)
                                   {
                                       echo "<option value=\"".$rsiteID."\" selected=\"selected\">".$vcode."</option>";
                                       $rsitename = $vname;
                                   }
                                   else
                                   {
                                      echo "<option value=\"".$vsiteID."\">".$vcode."</option>";
                                   }
                               }
                            }
                            echo "</select>";
                        ?>
                          <label id="txtsitename"><?php echo $rsitename;?></label>
                      </td>
                    </tr> 
                    <tr>
                        <td width="130px">Username</td>
                        <td>
                            <input type="text" id="txtusername" name="txtusername" value="<?php echo $vagentname; ?>" maxlength="20" onkeypress="javascript: return numberandletter(event);"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Password</td>
                        <td>
                            <input type="text" id="txtpassword" name="txtpassword" value="<?php echo $vagentpwd; ?>" maxlength="50" onkeypress="javascript: return numberandletter(event);" />
                        </td>
                    </tr>
                </table>
                <br />

                <div id="submitarea">
                    <input type="submit" value="Update Provider Agent" onclick="return chkagentcreation();"/>
                </div>
          </form>
</div>
<?php  
    }
}
include "footer.php"; ?>

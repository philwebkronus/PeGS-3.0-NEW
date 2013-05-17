<?php
$pagetitle = "Edit Account Details";
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
        if((!isset($_SESSION['accid'])) && (!isset($_SESSION['accounts'])))
        {
            echo "<script type='text/javascript'>window.location.href='accountview.php';</script>";
        }
        else
        {
            $raid = $_SESSION['accid'];
            $raccounts = $_SESSION['accounts'];

            foreach ($raccounts as $resultviews)
            {
                $rpasskey = $resultviews['WithPasskey'];
                $racctype = $resultviews['AccountTypeID'];
                $rsiteID = $resultviews['SiteID'];
                $vusername = $resultviews['UserName'];
                $rname = $resultviews['Name'];
                $vemail = $resultviews['Email'];
                $vlandLine = explode("-",$resultviews['LandLine']);
                $vmobile = explode("-",$resultviews['MobileNumber']);
                $vcto = $resultviews['Option1'];
                $vaddress = $resultviews['Address'];
                $vDateCreated = $resultviews['DateCreated'];
                $rdesignationID = $resultviews['DesignationID'];
            }

            if(count($vlandLine) == 1)
            {
                $vcountryLine = $vlandLine[0];
                $vareaLine = "";
                $vnumLine = "";
            }
            elseif(count($vlandLine) == 2)
            {
                $vcountryLine = $vlandLine[0];
                $vareaLine = $vlandLine[1];
                $vnumLine = "";
            }
            else{
                list($vcountryLine, $vareaLine, $vnumLine) = $vlandLine;
            }

            if(count($vmobile) == 1)
            {
                $vcountrymobile = $vmobile[0];
                $vareamobile = "";
                $vnummobile = "";
            }
            elseif(count($vmobile) == 2)
            {
                $vcountrymobile = $vmobile[0];
                $vareamobile = $vmobile[1];
                $vnummobile = "";
            }
            else{
                list($vcountrymobile, $vareamobile, $vnummobile) = $vmobile;
            }

            $volddetails = array($vusername, $rname, $vemail, $vcountryLine, $vareaLine, $vnumLine, $vcountrymobile, $vareamobile, $vnummobile, $vaddress); //store old details on an array (for auditing)
            $oldetails = implode("-", $volddetails);

            //if administrator type, use corporate email
            if($_SESSION['acctype'] == 1)
            {
                   $vcorpemail = preg_replace("/[0-9]$/", "", $resultviews['Email']);
                   $vemail = explode("@philweb.com.ph", $resultviews['Email']);
                   $vappendnum = explode($vcorpemail, $resultviews['Email']);
            }
        }
?>

<?php if($_SESSION['acctype'] == 2) {?>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            jQuery("#trpasskey").hide();
               if(jQuery("#cmbacctype").val() == 4)
                   {
                       jQuery("#trpasskey").show();
                       document.getElementById("optkeyyes").checked = true;
                   }
               else
                   {
                       jQuery("#trpasskey").hide();
                       document.getElementById("optkeyno").checked = true;
                   }

        });
    </script>
<?php } ?>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br />
        <form method="post" action="process/ProcessAccManagement.php" onsubmit="return chktrailingaccspaces();">
            <input type="hidden" name="page" value="AccountsUpdate" />
            <input type="hidden" name="txtaccid" value="<?php echo $raid; ?>" />
            <input type="hidden" name="txtacctype" value="<?php echo $racctype; ?>">
            <input type="hidden" name="txtsiteid" value="<?php echo $rsiteID; ?>">
            <input type="hidden" name="txtolddetails" value="<?php echo $oldetails; ?>" />
            <table>
                <tr>
                    <td width="130px">Account Type</td>
                    <td>
                        <?php
                                $vacctype = $_SESSION['acctypes'];
                                echo "<select id=\"cmbacctype\" name=\"cmbacctype\" disabled=\"disabled\">";
                                echo "<option value=\"-1\">Please Select</option>";
                                foreach ($vacctype as $result){
                                   $vaccID = $result['AccountTypeID'];
                                   $vname = $result['Name'];
                                   if($racctype == $vaccID)
                                   {
                                        echo "<option value=\"".$racctype."\" selected=\"selected\">".$vname."</option>";
                                   }
                                   else
                                   {
                                        echo "<option value=\"".$vaccID."\">".$vname."</option>";
                                   }
                                }
                                echo "</select>";
                         ?>

                    </td>
                </tr>
                <?php if($_SESSION['acctype'] == 1) { ?>
                <tr>
                    <td>Corporate Designation</td>
                    <td>
                        <?php                         
                         $vdesignations = $_SESSION['designations'];
                         echo "<select id=\"cmbdesignation\" name=\"cmbdesignation\">";
                         echo "<option value=\"-1\">Please Select</option>";
                         foreach ($vdesignations as $results)
                         {
                             $vdesignationID = $results['DesignationID'];
                             $vdesignatioName = $results['DesignationName'];
                             if($rdesignationID == $vdesignationID)
                             {
                               echo "<option value=\"".$rdesignationID."\" selected=\"selected\">".$vdesignatioName."</option>";    
                             }
                             else
                             {
                               echo "<option value=\"".$vdesignationID."\">".$vdesignatioName."</option>";
                             }
                         }
                         echo "</select>";
                        ?>
                    </td>
                </tr>
                <?php }?>
                
                <?php 
                //if operator or PEGS Ops access
                if($_SESSION['acctype'] == 2 || $_SESSION['acctype'] == 8) {?>
                <tr id="trpasskey">
                    <td>With Passkey</td>
                     <?php  if($rpasskey == 1){ ?>
                        <td>
                           Yes<input type="radio" id="optpkeyyes" name="optpkey" value="1" checked = "checked" readonly="readonly" />
                           No<input type="radio" id="optpkeyno" name="optpkey" value="0" readonly="readonly" />
                        </td>
                      <?php } else { ?>
                        <td>
                           Yes<input type="radio" id="optpkeyyes" name="optpkey" value="1" readonly="readonly" />
                           No<input type="radio" id="optpkeyno" name="optpkey" value="0" checked = "checked" readonly="readonly" />
                        </td>
                       <?php  }  ?>
                </tr>
                <?php } ?>
                <tr>
                    <td>Username</td>
                    <td width="10">
                        <input type="text" id="txtusername" name="txtusername" value="<?php echo $vusername; ?>" maxlength="20" size="20" readonly="readonly" onkeypress="return alphanumeric(event);" />
                        &nbsp;&nbsp;&nbsp;&nbsp;Date Created: <?php echo date('m-d-Y h:i:s', strtotime($vDateCreated));?>   
                    </td>
                    <td>
                        
                    </td>
                </tr>
                <tr>
                    <td>Name</td>
                    <td><input type="text" id="txtname" name="txtname" value="<?php echo $rname; ?>" maxlength="150" size="80" onkeypress="return letter(event);"/></td>
                </tr>
                <tr>
                    <td>Email Address</td>
                    <td>
                        <?php 
                          if($_SESSION['acctype'] == 1)
                          {
                                echo '<input type="text" id="txtcorpemail" name="txtcorpemail" maxlength="85" size="50" value="'.$vemail[0].'" onkeypress="return corpoemail(event);" />'.'@philweb.com.ph'.$vappendnum[1];
                                echo '<input type="hidden" id="txtemail" name="txtemail" value="hidden"/>';  
                                echo '<input type="hidden" id="txtappendnum" name="txtappendnum"  value="'.$vappendnum[1].'" />';  
                                echo '<input type="hidden" id="txtemail2" name="txtemail2" value="'.$vemail[0].'"/>';  
                          }
                          else
                          {
                                echo '<input type="text" id="txtemail" name="txtemail" maxlength="100" size="80" value="'.$vemail.'" onblur="validateEmail();" onkeypress="return emailkeypress(event);" />';  
                                echo '<input type="hidden" id="txtcorpemail" name="txtcorpemail" value="hidden"/>';
                                echo '<input type="hidden" id="txtemail2" name="txtemail2" value="'.$vemail.'"/>';  
                          }
                        ?>
                    </td>
                </tr>
               <tr>
                    <td>Phone Number</td>
                    <td>
                           <label>Country Code </label>
                           <input type="text" id="txtctrycode" name="txtctrycode" value="<?php echo $vcountryLine; ?>"  maxlength="5" size="5"  onkeypress="return numberonly(event);"/>
                           <label>Area Code </label>
                           <input type="text" id="txtareacode" name="txtareacode" value="<?php echo $vareaLine; ?>" maxlength="5" size="5"  onkeypress="return numberonly(event);"/>
                           <input type="text" id="txtphone" name="txtphone" value="<?php echo $vnumLine; ?>" maxlength="7" size="7"  onkeypress="return numberonly(event);"/>
                    </td>
                </tr>
                <tr>
                    <td>Mobile Number</td>
                    <td>
                           <label>Country Code </label>
                           <input type="text" id="txtctrycode2" name="txtctrycode2" value="<?php echo $vcountrymobile; ?>" maxlength="5" size="5"  onkeypress="return numberonly(event);"/>
                           <label>Area Code </label>
                           <input type="text" id="txtareacode2" name="txtareacode2" value="<?php echo $vareamobile; ?>" maxlength="5" size="5"  onkeypress="return numberonly(event);"/>
                           <input type="text" id="txtmobile" name="txtmobile" value="<?php echo $vnummobile; ?>" maxlength="7" size="7" onkeypress="return numberonly(event);"/>
                    </td>
                </tr>
                <tr>
                    <td>Address</td>
                    <td>
                        <input type="text" id="txtaddress" name="txtaddress" value="<?php echo $vaddress; ?>" maxlength="150"  size="80" onkeypress="return numberandletter1(event);"/>
                    </td>
                </tr>
            </table>
            
            <div id="submitarea">
                <input type="button" value="Change Status" onclick="window.location.href='process/ProcessAccManagement.php?accid=<?php echo $raid; ?>'+'&statuspage='+'UpdateStatus'"/>
                <input type="submit" value="Submit" id="btnsubmit" onclick="return chkupdacc();" />
            </div>
            <table>
             <tr height="50">
                <td></td>
                <td>
                    <b>Assigned Site/s</b>
                   <br /><br/>
                   <?php
                      $vsitesowned = $_SESSION['sitesowned'];
                      echo "<div style=\"overflow-y: auto; width: 500px; height: 115px; border: 1;\" >";
                      echo "<table border=\"1\" width=\"100%\" style=\"text-align: center;\">
                                <thead>
                                  <tr>
                                    <td>Site Code</td>
                                    <td>Site Name</td>
                                  </tr>
                                </thead>
                                <tbody>";
                      foreach ($vsitesowned as $results)
                      {
                          $vsitecode = $results['SiteCode'];
                          $vsitename = $results['SiteName'];
                          //search if the sitecode was found on the terminalcode
                          if(strstr($vsitecode, $terminalcode) == false)
                          {
                             $vownedsites = $vsitecode;
                          }
                          else
                          {
                             //remove the "icsa-"
                             $vownedsites = substr($vsitecode, strlen($terminalcode));
                          }
                          echo "<tr>";
                          echo "<td>".$vownedsites."</td>";
                          echo "<td>".$vsitename."</td>";
                          echo "</tr>";
                      }
                      echo "</tbody></table>";
                      echo "</div>";
                  ?>
               </td>
            </tr>
          </table>
        </form>
</div>
    
<?php  
    }
}
include "footer.php"; ?>
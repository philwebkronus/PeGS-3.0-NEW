<?php  
$pagetitle = "MG Agent Creation";  
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
?>

<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery("#cmbsitename").change(function(){
           //this part is for displaying site name
           var url = 'process/ProcessTerminalMgmt.php';
           jQuery.ajax({
               url: url,
               type: 'post',
               data: {cmbsitename: function(){return jQuery("#cmbsitename").val();}},
               dataType: 'json',
               success: function(data){
                      if(jQuery("#cmbsitename").val() > 0)
                      {
                        jQuery("#txtsitename").text(data.SiteName+" / ");
                        jQuery("#txtposaccno").text(data.POSAccNo);
                      }
                      else
                      {   
                        jQuery("#txtsitename").text(" ");
                        jQuery("#txtposaccno").text(" ");
                      }
               },
               error: function(XMLHttpRequest, e){
                    alert(XMLHttpRequest.responseText);
                    if(XMLHttpRequest.status == 401)
                    {
                        window.location.reload();
                    }
               }
            }); 
        }); 
    });
</script>
<div id="workarea">
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
    
          <form method="post" action="process/ProcessTerminalMgmt.php">
                <input type="hidden" name="page" value="ServiceAgentCreation" />
                <table>
                     <tr>
                        <td width="130px">Site / PEGS </td>
                        <td>
                          <?php
                            $vsiteID = $_SESSION['siteids'];
                            echo "<select id=\"cmbsitename\" name=\"cmbsitename\">";
                            echo "<option value=\"-1\">Please Select </option>";
                            foreach ($vsiteID as $result)
                            {
                               $rsiteID = $result['SiteID'];
                               $vorigcode = $result['SiteCode'];
                          
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
                               if($rsiteID <> 1)
                               {
                                 echo "<option value=\"".$rsiteID."\">".$vcode."</option>";
                               }
                            }
                            echo "</select>";
                          ?>
                            <label id="txtsitename"></label><label id="txtposaccno"></label>
                        </td>
                    </tr>
                    <tr>
                        <td width="130px">Username</td>
                        <td>
                            <input type="text" id="txtusername" name="txtusername" maxlength="20" onkeypress="javascript: return numberandletter(event);"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Password</td>
                        <td>
                            <input type="password" id="txtpassword" name="txtpassword" maxlength="50" onkeypress="javascript: return numberandletter(event);" />
                        </td>
                    </tr>
                </table>
                <br />
            
                <div id="submitarea"> 
                    <input type="submit" value="Create Provider Agent" onclick="return chkagentcreation();"/>
                </div>
          </form>
        
</div>
<?php  
    }
}
include "footer.php"; ?>

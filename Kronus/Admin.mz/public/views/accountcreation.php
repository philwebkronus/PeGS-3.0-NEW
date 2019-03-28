<?php 
$pagetitle = "Account Creation";  
include "process/ProcessAccManagement.php";
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
?>

<script type="text/javascript">
    jQuery(document).ready(function()
    {
        var url = 'process/ProcessAccManagement.php';
        jQuery("#trpasskey").hide();
        jQuery("#cmbacctype").live('change', function()
        {
             var value = (jQuery(this).find("option:selected").val());             
             jQuery("#txtsitename").text("");
             //do ajax request only if account type is 8 or PEGS ops Access
             if(jQuery("#acctype").val() == 8)
             {
                 //populate sites drop down box based on account type
                 if(value == 10 || value == 2 || value == 11 || value == 7 || value == 3 || value == 4)
                 {
                     jQuery("#cmbsite").empty();
                     jQuery("#cmbsite").append(jQuery("<option />").val("-1").text("Please Select"));  
                     // if liason account was selected, then get sites that has no liason
                     jQuery.ajax({
                            url: url,
                            type: 'post',
                            data : {cmbacctype: function(){return value; },
                                    page : function(){ return "LoadSite";}
                                   },
                            dataType: 'json',
                            success: function(data)
                            {
                                var site = jQuery("#cmbsite");
                                jQuery.each(data, function(){
                                    site.append(jQuery("<option />").val(this.SiteID).text(this.SiteCode));
                                });
                            },
                            error: function(XMLHttpRequest, e){
                                alert(XMLHttpRequest.responseText);
                                if(XMLHttpRequest.status == 401)
                                {
                                    window.location.reload();
                                }
                            }
                     });       
                 }
                 if(value < 0)
                 {
                    jQuery("#cmbsite").empty();
                    jQuery("#cmbsite").append(jQuery("<option />").val("-1").text("Please Select"));
                 }
             }
             //if cashier account type was selected
             if(value == 4)
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
        
        jQuery("#cmbsite").live('change', function()
        {   
            //for displaying of site name label
            jQuery.ajax({
                  url: url,
                  type: 'post',
                  data: {cmbsitename: function(){return jQuery("#cmbsite").val();}},
                  dataType: 'json',
                  success: function(data){
                      if(jQuery("#cmbsite").val() > 0)
                      {
                        jQuery("#txtsitename").text(data.SiteName);
                      }
                      else
                      {   
                        jQuery("#txtsitename").text(" ");
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

        <div id="pagetitle"><?php echo $pagetitle;?></div>
        <br />
        
        <form method="post" action="process/ProcessAccManagement.php">
            <input type="hidden" name="page" value="AccountCreation"/>
            <input type="hidden" id="acctype" value="<?php echo $_SESSION['acctype']; ?>" />
            <table>
                <tr>
                    <td width="130px">Account Type</td>
                    <td>
                        <?php                   
                            function sortArray($data, $field)
                            {
                              if(!is_array($field)) $field = array($field);
                              usort($data, function($a, $b) use($field) {
                                $retval = 0;
                                foreach($field as $fieldname) {
                                  if($retval == 0) $retval = strnatcmp($a[$fieldname],$b[$fieldname]);
                                }
                                return $retval;
                              });
                              return $data;
                            }
                            
                            $vacctype = $_SESSION['acctypes'];
                            $vacctype = sortArray($vacctype,'Name');
                            
                            echo "<select id=\"cmbacctype\" name=\"cmbacctype\">";
                            echo "<option value=\"-1\">Please Select</option>";

                            foreach ($vacctype as $result)
                            {
                                  $vaccID = $result['AccountTypeID'];
                                  $vname = $result['Name'];
                                  echo "<option value=\"".$vaccID."\">".$vname."</option>";                        
                            }

                            echo "</select>";                              
                       ?>
                    </td>
                </tr>  
                <tr>
                    <td>Site / PEGS </td>
                    <td>
                        <?php
                                $vsite = $_SESSION['sites'];
                                $vopssiteID = $_SESSION['opssite'];
                                echo "<select id=\"cmbsite\" class=\"cmbliason\" name=\"cmbsite\">";
                                echo "<option value=\"-1\">Please Select</option>";
                                
                                foreach ($vsite as $result)
                                {
                                    $vsiteID = $result['SiteID'];
                                    $vorigcode = $result['SiteCode'];
                                     
                                    //search if the sitecode was found on the terminalcode
                                    if(strstr($vorigcode, $terminalcode) == false)
                                    {
                                       $vcode = $vorigcode;
                                    }
                                    else
                                    {
                                       //remove the "icsa-"
                                       $vcode = substr($vorigcode, strlen($terminalcode));
                                    }

                                    if($_SESSION['acctype'] == 1)
                                    {
                                        if($vsiteID == 1)
                                        {
                                           echo "<option value=\"".$vsiteID."\">".$vcode."</option>";
                                        }
                                    }
                                    elseif($_SESSION['acctype'] == 2 || $_SESSION['acctype'] == 10) 
                                    {
                                        $vsitesowned = $_SESSION['pegsowned'];
                                        
                                        foreach ($vsitesowned as $results)
                                        {
                                            $vownedsites = $results['SiteID'];
                                            if( $vownedsites == $vsiteID)
                                            {                                        
                                                echo "<option value=\"".$vownedsites."\">".$vcode."</option>";
                                            }
                                        }
                                    }
                                    else
                                    {
                                        //remove Site Head Office
                                        if($vsiteID <> 1)
                                        {
                                            //put a comment, because loading of site/s will be through ajax call
                                           //echo "<option value=\"".$vsiteID."\">".$vcode."</option>";
                                        }
                                    }
                                }
                                echo "</select>";
                         ?>
                        <label id="txtsitename"></label>
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
                             echo "<option value=\"".$vdesignationID."\">".$vdesignatioName."</option>";
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
                    <td>
                        Yes<input type="radio" id="optkeyyes" name="optpkey" value="1" checked/>
                        No<input type="radio" id="optkeyno" name="optpkey" value="0"  />
                    </td>
                </tr>
                <?php }else{?>
                <tr id="trpasskey">
                    <td>With Passkey</td>
                    <td>
                        Yes<input type="radio" id="optkeyyes" name="optpkey" value="1" />
                        No<input type="radio" id="optkeyno" name="optpkey" value="0" checked />
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td>Username</td>
                    <td><input type="text" id="txtusername" name="txtusername" maxlength="20"  size="20" onkeypress="return numberandletter(event);" /></td>
                </tr>
                <tr>
                    <td>Name</td>
                    <td><input type="text" id="txtname" name="txtname" maxlength="150" size="80" onkeypress="return letter(event);"/></td>
                </tr>
                <tr>
                    <td>Email Address</td>
                    <td>
                        <?php 
                          if($_SESSION['acctype'] == 1)
                          {
                            echo '<input type="text" id="txtcorpemail" name="txtcorpemail" maxlength="85" size="50" onblur="validateCorpEmail();" onkeypress="return emailkeypress(event);" />';
                            echo '<input type="hidden" id="txtemail" name="txtemail" value="hidden"/>';  
                          }
                          
                          else{
                            echo '<input type="text" id="txtemail" name="txtemail" maxlength="100" size="80"  onblur="validateEmail();" onkeypress="return emailkeypress(event);"/>';  
                            echo '<input type="hidden" id="txtcorpemail" name="txtcorpemail" value="hidden"/>';  
                          }
                        ?>
                    </td>
                </tr>
                <tr></tr>
                <tr>
                    <td>Phone Number</td>
                    <td colspan="4">
                        <label>Country Code </label>
                          <input type="text" id="txtctrycode" name="txtctrycode" maxlength="5" size="5"  onkeypress="return numberonly(event);"/>
                        <label>Area Code </label>
                          <input type="text" id="txtareacode" name="txtareacode" maxlength="5" size="5"  onkeypress="return numberonly(event);"/>
                        <input type="text" id="txtphone" name="txtphone" maxlength="7" size="7"  onkeypress="return numberonly(event);"/></td>
                </tr>
                <tr>
                    <td>Mobile Number</td>
                    <td colspan="4">
                        <label>Country Code </label>
                          <input type="text" id="txtctrycode2" name="txtctrycode2" maxlength="5" size="5"  onkeypress="return numberonly(event);"/>
                        <label>Area Code </label>
                          <input type="text" id="txtareacode2" name="txtareacode2" maxlength="5" size="5"  onkeypress="return numberonly(event);"/>
                        <input type="text" id="txtmobile" name="txtmobile" maxlength="7" size="7" onkeypress="return numberonly(event);"/></td>
                </tr>
                <tr>
                    <td>Address</td>
                    <td><input type="text" id="txtaddress" name="txtaddress" maxlength="150"  size="80" onkeypress="return numberandletter1(event);"/></td>
                </tr>
            </table>
        
        <div id="submitarea">
            <input type="submit" class="btn" value="Submit" onclick="return chkcreateacc();" />
        </div>
        

        </form>
        
</div>
    
<?php  
    }
}
include "footer.php"; ?>

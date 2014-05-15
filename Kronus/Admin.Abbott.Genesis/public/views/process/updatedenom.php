<?php 
$pagetitle = "Update Site / PEGS Denomination";
include 'process/ProcessSiteManagement.php';
include 'header.php';
$rsite = $_SESSION['viewsites'];
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
        if(isset($_SESSION['denomination']) && isset($_SESSION['denomamount']))
        {
          $otherminvalues = array();
          $othermaxvalues = array();  
          $vdenomination = $_SESSION['denomination'];
          $arrdenomamt = $_SESSION['denomamount'];
          foreach($arrdenomamt as $val)
          {
              array_push($otherminvalues, $val['Amount']);
              array_push($othermaxvalues, $val['Amount']);
          }
          rsort($othermaxvalues, SORT_NUMERIC); //sorts amount into reverse order for maximum amount values
          
          $olddetails = implode(",", array($vdenomination[0]['DenominationName'], $vdenomination[0]['MinDenominationValue'], 
                  $vdenomination[0]['MaxDenominationValue'], $vdenomination[0]['DenominationType'], 
                  $vdenomination[1]['DenominationName'], $vdenomination[1]['MinDenominationValue'], 
                  $vdenomination[1]['MaxDenominationValue'], $vdenomination[1]['DenominationType'], 
                  $vdenomination[2]['DenominationName'], $vdenomination[2]['MinDenominationValue'], 
                  $vdenomination[2]['MaxDenominationValue'], $vdenomination[2]['DenominationType'], 
                  $vdenomination[3]['DenominationName'], $vdenomination[3]['MinDenominationValue'], 
                  $vdenomination[3]['MaxDenominationValue'], $vdenomination[3]['DenominationType']));
        }
        else
        {
          $vdenomination = null;    
        }

?>
<script type="text/javascript"> 
    jQuery(document).ready(function(){
        var url = 'process/ProcessSiteManagement.php';
        jQuery("#cmbsitename").live('change', function()
        {
            jQuery("#txtcode").hide();
            var sitecode = (jQuery(this).find("option:selected").text());
            jQuery("#txtsitecode").val(sitecode);
            jQuery("#cmbmininitial").empty();
            jQuery("#cmbmaxinitial").empty();
            jQuery("#cmbminregular").empty();
            jQuery("#cmbmaxregular").empty();
            jQuery("#cmbmininitvip").empty();
            jQuery("#cmbmaxinitvip").empty();
            jQuery("#cmbminrelvip").empty();
            jQuery("#cmbmaxrelvip").empty();

            if(document.getElementById('cmbsitename').value > 0)
            {
              jQuery("#info").hide();  
            }
            else
            {
              jQuery("#info").hide();  
            }
            
            //this part is for displaying site name
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
        
        jQuery("#btnrestore").click(function(){
             
            document.getElementById('confirm').style.display='block';
            document.getElementById('fade').style.display='block';
            
            jQuery("#cmbmininitial").empty();
            jQuery("#cmbmaxinitial").empty();
            jQuery("#cmbminregular").empty();
            jQuery("#cmbmaxregular").empty();
            jQuery("#cmbmininitvip").empty();
            jQuery("#cmbmaxinitvip").empty();
            jQuery("#cmbminrelvip").empty();
            jQuery("#cmbmaxrelvip").empty();
            
            if(document.getElementById('cmbsitename').value > 0)
            {
              jQuery("#info").show();    
            }
            else
            {
              jQuery("#info").hide();  
            }

            //ajax call: get default denomination values
            jQuery.ajax({
              url: url,
              type: 'post',
              data: {
                    restore: function() {return jQuery("#reload").val();}
                    },
              dataType: 'json',
              success: function(data){
                  jQuery.each(data, function(key, value){
                     if(key == 'initialreg')
                         {
                           jQuery("<option />").val(this.MinDenominationValue).text(this.MinDenominationValue).appendTo('#cmbmininitial');
                           jQuery("<option />").val(this.MaxDenominationValue).text(this.MaxDenominationValue).appendTo('#cmbmaxinitial');
                           jQuery("#txtinitialreg").val(this.DenominationName);
                         }
                     else if(key == 'reloadreg')
                         {
                           jQuery("<option />").val(this.MinDenominationValue).text(this.MinDenominationValue).appendTo('#cmbminregular');
                           jQuery("<option />").val(this.MaxDenominationValue).text(this.MaxDenominationValue).appendTo('#cmbmaxregular');
                           jQuery("#txtreloadreg").val(this.DenominationName);
                         }
                     else if(key == 'initialvip')
                         {
                           jQuery("<option />").val(this.MinDenominationValue).text(this.MinDenominationValue).appendTo('#cmbmininitvip');
                           jQuery("<option />").val(this.MaxDenominationValue).text(this.MaxDenominationValue).appendTo('#cmbmaxinitvip');  
                           jQuery("#txtinitialvip").val(this.DenominationName);
                         }
                     else if(key == 'reloadvip')
                         {
                           jQuery("<option />").val(this.MinDenominationValue).text(this.MinDenominationValue).appendTo('#cmbminrelvip');
                           jQuery("<option />").val(this.MaxDenominationValue).text(this.MaxDenominationValue).appendTo('#cmbmaxrelvip');
                           jQuery("#txtreloadvip").val(this.DenominationName);
                         }
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
        });
        
        jQuery("#btnconfirm").click(function(){
          jQuery("#frmdenom").submit();
        });
        
        jQuery("#btncancel").click(function(){
           document.getElementById('confirm').style.display='none';
           document.getElementById('fade').style.display='none'; 
           window.location.reload();
        });
        
        jQuery("#getdenom").click(function(){
            if(jQuery("#cmbsitename").val() == "-1")
            {
                alert("Please select site");
                return false;
            }
            else
            {
                jQuery("#txtcode").show();
                return true;
            }
        });
    });
</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br />
    <form method="post" id="frmgetdenom" action="process/ProcessSiteManagement.php">
        <input type="hidden" name="page" id="page" value="SiteDenomination" />
        <input type="hidden" name="txtsitecode" id="txtsitecode" />
        <table>
            <tr>
                <td>Sites / PEGS </td>
                <td>
                    <?php 
                      echo "<select id=\"cmbsitename\" name=\"cmbsitename\">";
                      echo "<option value=\"-1\">Please Select </option>";
                      foreach ($rsite as $results)
                      {
                          $rsiteID = $results['SiteID'];
                          $vorigcode = $results['SiteCode'];
                          
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
                               echo "<option value=\"$rsiteID\">$vcode</option>"; 
                            }  
                      }
                      echo "</select>";
                    ?>
                </td>
                <td>
                    <label id="txtsitename"></label><label id="txtposaccno"></label>
                </td>
                <td>
                    <input type="submit" id="getdenom" name="getdenom" value="Load"/>
                </td>
            </tr>
        </table>
    </form>
    <form method="post" id="frmdenom" action="process/ProcessSiteManagement.php">
        <input type="hidden" name="page" value="UpdateDenomination" />
         <input type="hidden" name="txtolddetails" value="<?php echo $olddetails; ?>" />
        <br/>
        <?php if(isset($_GET['table']) == 'enable'){ ?>
        <?php if(isset($_SESSION['site'])){ ?>
        <div style="padding-left: 500px;">
            <input type="hidden" name="cmbsitename" id="cmbsitename" value="<?php echo $_SESSION['site'][0]; ?>" />
            <div id="txtcode">
                Site Code &nbsp;<input type="text" size="5" readonly="readonly" name="sitecode" value="<?php echo $_SESSION['site'][1]; ?>" />
            </div>
        </div>
        <?php }?>
        <div id="info" style="display: block; margin: 20px 0 0 300px;">
            <table style="text-align: center;">
                <thead>
                  <tr>
                    <td><b>Load Type</b></td>
                    <td><b>Min</b></td>
                    <td><b>Max</b></td>
                  </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Initial (Regular)</td>
                        <td>
                            <?php
                             $rmininitial =  $vdenomination[0]['MinDenominationValue'];
                             echo "<select id=\"cmbmininitial\" name=\"cmbmininitial\">";
                             for ($x=0;$x<count($otherminvalues);$x++)
                             {
                                 if($rmininitial == $otherminvalues[$x])
                                 {
                                     echo "<option value=\"".$rmininitial."\" selected=\"selected\">".number_format($rmininitial, 2, ".", ",")."</option>";
                                 }
                                 else{
                                     echo "<option value=\"".$otherminvalues[$x]."\">".number_format($otherminvalues[$x], 2, ".", ",")."</option>";
                                 }
                             }
                             echo "</select>";
                            ?>
                        </td>
                        <td>
                            <?php
                             $rmaxinitial =  $vdenomination[0]['MaxDenominationValue'];
                             echo "<select id=\"cmbmaxinitial\" name=\"cmbmaxinitial\">";
                             for ($x=0;$x<count($othermaxvalues);$x++)
                             {
                                 if($rmaxinitial == $othermaxvalues[$x])
                                 {
                                     echo "<option value=\"".$rmaxinitial."\" selected=\"selected\">".number_format($rmaxinitial, 2, ".", ",")."</option>";
                                 }
                                 else{
                                     echo "<option value=\"".$othermaxvalues[$x]."\">".number_format($othermaxvalues[$x], 2, ".", ",")."</option>";
                                 }
                             }
                             echo "</select>";
                             ?>
                        </td>
                        <input type="hidden" name="txtinitialreg" id="txtinitialreg" value="<?php echo $vdenomination[0]['DenominationName']; ?>" />
                    </tr>
                    <tr>
                        <td>Reload (Regular)</td>
                        <td>
                            <?php
                             $rminregular =  $vdenomination[1]['MinDenominationValue'];
                             echo "<select id=\"cmbminregular\" name=\"cmbminregular\">";
                             for ($x=0;$x<count($otherminvalues);$x++)
                             {
                                 if($rminregular == $otherminvalues[$x])
                                 {
                                     echo "<option value=\"".$rminregular."\" selected=\"selected\">".number_format($rminregular, 2, ".", ",")."</option>";
                                 }
                                 else{
                                     echo "<option value=\"".$otherminvalues[$x]."\">".number_format($otherminvalues[$x], 2, ".", ",")."</option>";
                                 }
                             }
                             echo "</select>";
                            ?>
                        </td>
                        <td>
                             <?php
                             $rmaxregular =  $vdenomination[1]['MaxDenominationValue'];
                             echo "<select id=\"cmbmaxregular\" name=\"cmbmaxregular\">";
                             for ($x=0;$x<count($othermaxvalues);$x++)
                             {
                                 if($rmaxregular == $othermaxvalues[$x])
                                 {
                                     echo "<option value=\"".$rmaxregular."\" selected=\"selected\">".number_format($rmaxregular, 2, ".", ",")."</option>";
                                 }
                                 else{
                                     echo "<option value=\"".$othermaxvalues[$x]."\">".number_format($othermaxvalues[$x], 2, ".", ",")."</option>";
                                 }
                             }
                             echo "</select>";
                             ?>
                        </td>
                        <input type="hidden" name="txtreloadreg" id="txtreloadreg" value="<?php echo $vdenomination[1]['DenominationName']; ?>" />
                    </tr>
                    <tr>
                        <td>Initial (VIP)</td>
                        <td>
                            <?php
                             $vmininitial =  $vdenomination[2]['MinDenominationValue'];
                             echo "<select id=\"cmbmininitvip\" name=\"cmbmininitvip\">";
                             for ($x=0;$x<count($otherminvalues);$x++)
                             {
                                 if($vmininitial == $otherminvalues[$x])
                                 {
                                     echo "<option value=\"".$vmininitial."\" selected=\"selected\">".number_format($vmininitial, 2, ".", ",")."</option>";
                                 }
                                 else{
                                     echo "<option value=\"".$otherminvalues[$x]."\">".number_format($otherminvalues[$x], 2, ".", ",")."</option>";
                                 }
                             }
                             echo "</select>";
                            ?>
                        </td>
                        <td>
                            <?php
                             $vmaxinitial =  $vdenomination[2]['MaxDenominationValue'];
                             echo "<select id=\"cmbmaxinitvip\" name=\"cmbmaxinitvip\">";
                             for ($x=0;$x<count($othermaxvalues);$x++)
                             {
                                 if($vmaxinitial == $othermaxvalues[$x])
                                 {
                                     echo "<option value=\"".$vmaxinitial."\" selected=\"selected\">".number_format($vmaxinitial, 2, ".", ",")."</option>";
                                 }
                                 else{
                                     echo "<option value=\"".$othermaxvalues[$x]."\">".number_format($othermaxvalues[$x], 2, ".", ",")."</option>";
                                 }
                             }
                             echo "</select>";
                             ?>
                        </td>
                        <input type="hidden" name="txtinitialvip" id="txtinitialvip" value="<?php echo $vdenomination[2]['DenominationName']; ?>"/>
                    </tr>
                    <tr>
                        <td>Reload (VIP)</td>
                        <td>
                            <?php
                             $vminregular =  $vdenomination[3]['MinDenominationValue'];
                             echo "<select id=\"cmbminrelvip\" name=\"cmbminrelvip\">";
                             for ($x=0;$x<count($otherminvalues);$x++)
                             {
                                 if($vminregular == $otherminvalues[$x])
                                 {
                                     echo "<option value=\"".$vminregular."\" selected=\"selected\">".number_format($vminregular, 2, ".", ",")."</option>";
                                 }
                                 else{
                                     echo "<option value=\"".$otherminvalues[$x]."\">".number_format($otherminvalues[$x], 2, ".", ",")."</option>";
                                 }
                             }
                             echo "</select>";
                            ?>
                        </td>
                        <td>
                            <?php
                             $vmaxregular =  $vdenomination[3]['MaxDenominationValue'];
                             echo "<select id=\"cmbmaxrelvip\" name=\"cmbmaxrelvip\">";
                             for ($x=0;$x<count($othermaxvalues);$x++)
                             {
                                 if($vmaxregular == $othermaxvalues[$x])
                                 {
                                     echo "<option value=\"".$vmaxregular."\" selected=\"selected\">".number_format($vmaxregular, 2, ".", ",")."</option>";
                                 }
                                 else{
                                     echo "<option value=\"".$othermaxvalues[$x]."\">".number_format($othermaxvalues[$x], 2, ".", ",")."</option>";
                                 }
                             }
                             echo "</select>";
                            ?>
                        </td>
                        <input type="hidden" name="txtreloadvip" id="txtreloadvip" value="<?php echo $vdenomination[3]['DenominationName'];?>"/>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td align="left">
                            <input type="submit" value="Update" style="width: 205px;" onclick="return chkdenomination();"/>  
                        </td>
                        <td>&nbsp;</td>
                        <td>
                            <input type="button" value="Restore Defaults?" id="btnrestore" />
                        </td>
                    </tr>
                </tbody>
            </table>
            <input type="hidden" name="reload" id="reload" value="RestoreChanges" />
        </div>
        <?php } else{?>
        <div id="info" style="display: none; margin: 20px auto;"></div>
        <?php } ?>
        <div id="confirm" class="white_confirm">
            <br /><br />
            <p align="center"> Restore Changes? </p>
            <table align="center">
                <tr>
                    <td>
                        <input type="button" value="Submit" id="btnconfirm" />
                    </td>
                    <td>
                        <input type="button" value="Cancel" id="btncancel" />
                    </td>
                </tr>
            </table>
        </div>
        <div id="fade" class="black_overlay"></div>
    </form>
</div>
<?php  
    }
}
include "footer.php"; ?>
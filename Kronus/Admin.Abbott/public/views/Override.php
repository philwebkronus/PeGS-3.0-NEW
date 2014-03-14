<?php 
/*
 * Created by: Sheryl S. Basbas
 * Date Created : March 8, 2012
 */

$pagetitle = "Override";  
include "process/ProcessOverride.php";
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
?>

<script type="text/javascript">
    jQuery(document).ready(function(){
        
        //binds onclick event of radio button for enabling/disabling auto-top-up
        jQuery(".optradio").bind('click',function(){
            var pick = $(this).val();
            if (pick == 1)
            {
                jQuery("#txttopupamt").attr('disabled', false);
            }
            if (pick == 0)
            {
                jQuery("#txttopupamt").attr('disabled', true);
            }
        });
        
        /**
         * On change of site combobox 
         * Get 
         */
        jQuery("#cmbsite").live('change', function(){
          var site = jQuery("#cmbsite").val();
          
          if(site > 0)
          {
              var selected = ($(this).find("option:selected").text()); 
              jQuery("#txtsitecode").val(selected);

              //this part is for displaying site name
              jQuery.ajax({
                    url: 'process/ProcessOverride.php',
                    type: 'post',
                    data: {cmbsitename: function(){return jQuery("#cmbsite").val();}},
                    dataType: 'json',
                    success: function(data){
                          if(jQuery("#cmbsite").val() > 0)
                          {
                            jQuery("#txtsitename").text(data.SiteName+" / ");
                            jQuery("#txtposaccno").text(data.POSAccNo);
                            jQuery("#txtposacc").attr('value',data.POSAccNo);
                            if(data.AutoTopup == 1)
                            {
                               jQuery("#optpickyes").attr('checked', true);
                               jQuery("#txttopupamt").attr('disabled', false);
                               jQuery("#txttopupamt").val(data.TopupAmt);
                            }
                            else if(data.AutoTopup == 0)
                            {
                               jQuery("#optpickno").attr('checked', true);
                               jQuery("#txttopupamt").attr('disabled', true);
                                jQuery("#txttopupamt").val("");
                            }
                            else
                            {
                                jQuery("#optpickyes").attr('checked', false);
                                jQuery("#optpickno").attr('checked', false);
                                jQuery("#cmbsite").val('-1');
                                jQuery("#txtsitename").text(" ");
                                jQuery("#txtposaccno").text("");
                                jQuery("#txtposacc").attr('value',"");
                                jQuery("#txtposacc").attr('disabled', false);
                                jQuery("#title").html("Error");
                                jQuery("#msg").html("Site Balance not yet created!");
                                jQuery("#light").attr('style', 'display:block');
                                jQuery("#fade").attr('style', 'display:block');
                            }
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
              jQuery("#txtposacc").attr('disabled', true);
              
              if (!checkRadio("frmmanual","optpick"))
              {
                 jQuery("#txttopupamt").attr('disabled', true);
              }
          }
          else
          {
              jQuery("#txtposacc").attr('disabled', false);
              jQuery("#txttopupamt").attr('disabled', true);
              jQuery("#txtsitename").text(" ");
              jQuery("#txtposaccno").text(" ");
          }
          
       }); 
       
       jQuery("#txtposacc").live('change', function(){    
          var site = jQuery("#txtposacc").val();
          if(site > 0)
          {
              var selected = ($(this).find("option:selected").text()); 
              jQuery("#txtsitecode").val(selected);       

              //this part is for displaying site name
              jQuery.ajax({
                    url: 'process/ProcessOverride.php',
                    type: 'post',
                    data: {POSAccount: function(){return jQuery("#txtposacc").val();}},
                    dataType: 'json',
                    success: function(data){
                          if(jQuery("#txtposacc").val() > 0)
                          {
                            jQuery("#txtsitename").text(data.SiteName+" / ");
                            jQuery("#txtposaccno").text(data.POSAccNo);
                            jQuery("#cmbsite").val(data.SiteID);     
                            if(data.SiteID == ""){
                                alert("Invalid POS Account Number");
                            }
                            if(data.AutoTopup == 1)
                            {
                               jQuery("#optpickyes").attr('checked', true);
                               jQuery("#txttopupamt").attr('disabled', false);
                               jQuery("#txttopupamt").val(data.TopupAmt);
                            }
                            else if(data.AutoTopup == 0)
                            {
                               jQuery("#optpickno").attr('checked', true);
                               jQuery("#txttopupamt").attr('disabled', true);
                               jQuery("#txttopupamt").val("");
                            }
                            else
                            {
                                jQuery("#optpickyes").attr('checked', false);
                                jQuery("#optpickno").attr('checked', false);
                                jQuery("#cmbsite").val('-1');
                                jQuery("#txtsitename").text(" ");
                                jQuery("#txtposaccno").text("");
                                jQuery("#txtposacc").attr('value',"");
                                jQuery("#txtposacc").attr('disabled', false);
                                jQuery("#title").html("Error");
                                jQuery("#msg").html("Site Balance not yet created!");
                                jQuery("#light").attr('style', 'display:block');
                                jQuery("#fade").attr('style', 'display:block');
                            }
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
          }
          else
          {
              jQuery("#txtposacc").attr('disabled', false);
              jQuery("#txtsitename").text(" ");
              jQuery("#txtposaccno").text(" ");
          }
          
       }); 
       
       //display site name on label
       jQuery('#cmbsite').change(function(){
            jQuery('#lblsite').html(jQuery(this).children('option:selected').attr('label'));
       });
       
       //disable enter key
        jQuery('body').bind('keypress', function (e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if(code == 13) { //Enter keycode
                e.preventDefault();
            }
        });
        
    });
</script>
<div id="light" class="white_confirm">    
    <br />
    <div id="title" class="light-title" align="center"></div>
    <div id="msg" class="light-message" align="center"></div>
    <div id="button" class="light-button" align="center">
        <br />
        <input type="button" onclick="javascript: document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';" value="Okay"/>
</div>
    </div>
<div id="workarea">
    <div id="fade" class="black_overlay"></div>
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <form method="post" action="" name="frmmanual" id="frmmanual">
            <input type="hidden" name="page" value="PostManualTopUp" />
            <input type="hidden" name="txtsitecode" id="txtsitecode" />
                <table>
                   <tr>
                        <td width="130px">Site / PEGS</td>
                        <td>
                        <?php                        
                            $vsite = $_SESSION['sites'];
                            echo "<select id=\"cmbsite\" name=\"cmbsite\">";
                            echo "<option value=\"-1\">Please Select</option>";

                            foreach ($vsite as $result)
                            {
                                    $vsiteID = $result['SiteID'];
                                    $vorigcode = $result['SiteCode'];

                                    //search if the sitecode was found on the terminalcode
                                    if(strstr($vorigcode, $terminalcode) == false)
                                    {
                                       $vcode = $result['SiteCode'];
                                    }
                                    else
                                    {
                                       //removes the "icsa-"
                                       $vcode = substr($vorigcode, strlen($terminalcode));
                                    }
                                    //remove Site Head Office
                                    if($vsiteID <> 1) 
                                    {
                                       echo "<option value=\"".$vsiteID."\">".$vcode."</option>";
                                    }
                            }
                            echo "</select>";
                        ?>
                            <label id="txtsitename"></label><label id="txtposaccno"></label>
                        </td>
                    </tr>
                    <tr>
                        <td>POS Account</td>
                        <td>
                            <input type="text" id="txtposacc" name="txtposacc" onkeypress="return numberonly(event);" maxlength="10" size="10" />
                        </td>
                    </tr>                    
                    <tr>
                        <td>Enabled Auto Top-up ?</td>
                        <td>
                            <input type="radio" id="optpickyes" class="optradio" name="optpick" value="1"/>Yes
                            <input type="radio" id="optpickno" class="optradio" name="optpick" value="0"/>No
                        </td>
                    </tr>
                    <tr>
                        <td>Auto Top-up Amount</td>
                        <td>
                            <input class="auto" type="text" id="txttopupamt" name="txttopupamt" onkeypress="return numberonly(event);" />
                        </td>
                    </tr>
                </table>
            <div id="submitarea">
                <input type="submit" value="Submit" id="btnSubmit" name="submit" id="submit" onclick="return chkOverride();"/>
            </div>
     </form>
        
</div>       
<?php  
    }
}
include "footer.php";
if($_SESSION['alert']!='')
{
    $alert = $_SESSION['alert'];
    if($alert == 'Please select a Site')
    {
        echo"<script>alert('Please select a Site');</script>";
    }
    if($alert == 'Site Balance not yet created')
    {
        echo"<script>alert('Site Balance not yet created');</script>";
    }
    if($alert == 'Update Site Balance Parameter: Record updated.')
    {
        echo"<script>alert('Update Site Balance Parameter: Record updated.');</script>";
    }
    if($alert == 'Update Site Balance Parameter: Record unchanged.')
    {
        echo"<script>alert('Update Site Balance Parameter: Record unchanged.');</script>";
    }
    if($alert == "Top-up Amount is required.")
    {
        echo"<script>alert('Top-up Amount is required.');</script>";
    }
    
    unset ($_SESSION['alert']);

}?>


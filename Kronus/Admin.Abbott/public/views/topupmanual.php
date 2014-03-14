<?php  
$pagetitle = "Initial Posting of Manual Top-up";  
include "process/ProcessTopUp.php";
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
        var url = 'process/ProcessTopUp.php';
       //post site code 
       jQuery("#cmbsite").live('change', function(){
          var site = jQuery("#cmbsite").val();
          
          if(site > 0)
          {
              var selected = ($(this).find("option:selected").text()); 
              jQuery("#txtsitecode").val(selected);

              //this part is for displaying site name
              jQuery.ajax({
                    url: url,
                    type: 'post',
                    data: {cmbsitename: function(){return jQuery("#cmbsite").val();}},
                    dataType: 'json',
                    success: function(data){
                          if(jQuery("#cmbsite").val() > 0)
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
              jQuery("#txtposacc").attr('disabled', true);
          }
          else
          {
              jQuery("#txtposacc").attr('disabled', false);
              jQuery("#txtsitename").text(" ");
              jQuery("#txtposaccno").text(" ");
          }
          
       }); 
       
       //pos account number; onchange event
       jQuery("#txtposacc").bind('change',function(){
          var site = jQuery("#txtposacc").val();
          if(site > 0)
          {
              //this part is for displaying site name
              jQuery.ajax({
                    url: url,
                    type: 'post',
                    data: {cmbsitename: function(){return 0;},
                           txtposacc : function(){return site}
                    },
                    dataType: 'json',
                    success: function(data){
                          if(site > 0)
                          {
                            jQuery("#sitecode").text(data.SiteCode);
                          }
                          else
                          {   
                            jQuery("#sitecode").text(" ");
                            jQuery("#txtposacc").val("");
                          }
                    },
                    error: function(XMLHttpRequest, e){
                        alert(XMLHttpRequest.responseText);
                        jQuery("#sitecode").text(" ");
                        jQuery("#txtposacc").val("");
                        jQuery("#txtposacc").focus();
                        if(XMLHttpRequest.status == 401)
                        {
                            window.location.reload();
                        }
                    }
              });
          }
          else
          {
              jQuery("#sitecode").text(" ");
              jQuery("#txtposacc").val("");
          }
       });
       
       //display site name on label
       jQuery('#cmbsite').change(function(){
            jQuery('#lblsite').html(jQuery(this).children('option:selected').attr('label'));
       });
        
    });
</script>

<div id="workarea">
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <form method="post" action="process/ProcessTopUp.php" name="frmmanual">
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
                            <label id="sitecode"></label>
                        </td>
                    </tr>
                    <tr>
                        <td>Amount</td>
                        <td>
                            <input class="auto" type="text" id="txtamount" name="txtamount" onkeypress="return numberonly(event);" />
                        </td>
                    </tr>
                    <tr>
                        <td>Minimum Balance</td>
                        <td>
                            <input class="auto" type="text" id="txtminbal" name="txtminbal" onkeypress="return numberonly(event);" value="100,000.00"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Maximum Balance</td>
                        <td>
                            <input class="auto" type="text" id="txtmaxbal" name="txtmaxbal" onkeypress="return numberonly(event);" value="250,000.00"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Pick Up</td>
                        <td>
                            <input type="radio" id="optpickyes" name="optpick" value="1"/>Metro Manila
                            <input type="radio" id="optpickno" name="optpick" value="0"/>Provincial
                        </td>
                    </tr>
                </table>
            <div id="submitarea">
                <input type="submit" value="Submit" id="btnSubmit" onclick="return chktopup();"/>
            </div>
     </form>
        
</div>       
<?php  
    }
}
include "footer.php"; ?>

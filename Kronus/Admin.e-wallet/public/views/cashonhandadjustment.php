<?php
$pagetitle = "Cash on Hand Adjustment";  
include("process/ProcessTopUp.php");
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
        
        jQuery("#cmbsitename").live('change', function(){
            var site = jQuery("#cmbsitename").val();
            
            if(site > 0)
            {
                var selected = ($(this).find("option:selected").text()); 
                jQuery("#txtsitecode").val(selected);
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
                jQuery("#txtposacc").attr('disabled', true);
            }
            else
            {
              jQuery("#txtposacc").attr('disabled', false);
              jQuery("#txtsitename").text(" ");
              jQuery("#txtposaccno").text(" ");
            }
        });
        
        
        
        //whatever bank was selected, get equivalent bank code, then post it on hidden textbox
        jQuery("#ddlBank").live('change', function(){
            var bank = ($(this).find("option:selected").val());
             var textvalues = [];
             
             $('#ddlbankcode option').each(function(i, selected)
             {
                textvalues[i] = $(selected).val();
                if(textvalues[i] == bank)
                {
                    $("#txtbankcode").val($(selected).text());
                }
             });
        });
        
        //disable enter key
        jQuery('body').bind('keypress', function (e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if(code == 13) { //Enter keycode
                e.preventDefault();
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
    });
</script>
<div id="workarea">
  <div id="pagetitle"><?php echo $pagetitle; ?></div>
  <br />
  <form method="post" action="process/ProcessTopUp.php">
      <input type="hidden" id="hidden_date" value="<?php echo date('Y-m-d');?>" />
      <input type="hidden" name="page" value="CashOnHandAdjustment" />
      <input type="hidden" name="txtsitecode" id="txtsitecode" />
      <input type="hidden" name="txtbankcode" id="txtbankcode" />
      <table>
        <tr>
            <td>Site / PEGS </td>
            <td>
               <?php 
                 $vviewsite = $_SESSION['sites'];
                 echo "<select id=\"cmbsitename\" name=\"cmbsitename\">";
                 echo "<option value=\"-1\">Please select</option>";

                 foreach($vviewsite as $resultviews)
                 {
                    $vsiteID = $resultviews['SiteID'];      
                    $vorigcode = $resultviews['SiteCode'];
                            
                    //search if the sitecode was found on the terminalcode
                    if(strstr($vorigcode, $terminalcode) == false)
                    {
                       $vcode = $resultviews['SiteCode'];
                    }
                    else
                    {
                       //removes the "icsa-"
                       $vcode = substr($vorigcode, strlen($terminalcode));
                    }

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
               <input id="txtAmount" name="txtAmount" maxlength='10' class="auto2"/>
<!--               <input id="txtAmount" name="txtAmount" maxlength='10' onkeyup='this.value=addSeparator(this.value);' onkeypress="javascript: return isNumberKey(event);"/>-->
           </td>
        </tr>
    
        <tr>
           <td>Reason</td>
           <td><input id="txtReason" name="txtReason" maxlength="100" size="50" onkeypress="return numberandletter1(event);"/></td>
        </tr>
     </table>
     <div id="submitarea">
         <input type="button" value="Confirm" onclick="return chkcshonhandposting();"/>
     </div>
      <div id="light" class="white_page" style="width: 400px; margin-left: 200px;">
        <div class="close_popup" id="btnClose" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"></div>
        <br />
        <p align="center" style="font-weight: bold;">Please enter your credentials to continue</p>
        <table align="center">
            <tr>
                <td>Username</td>
                <td>
                    <input type =" text" name="txtusername" id="txtusername" maxlength="20" onkeypress="javascript: return numberandletter(event);" ondragstart="return false" onselectstart="return false" onpaste="return false"/>
                </td>
            </tr>
            <tr>
                <td>Password</td>
                <td>
                    <input type ="password" name="txtpassword" id="txtpassword" maxlength="50"  onkeypress="javascript: return numberandletter(event);" ondragstart="return false" onselectstart="return false" onpaste="return false" />
                </td>
            </tr>
        </table>
        <br />
        <div style="float:right;">
            <input type="button" value="Cancel" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';" />
        </div>
        <div style="float: left;">
            <input type ="submit" value="Submit" onclick="return chklogin();" />
        </div>
       </div>
  </form>
  
</div>
<?php  
    }
}
include "footer.php"; ?>
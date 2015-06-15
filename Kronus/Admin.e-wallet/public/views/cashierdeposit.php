<?php
$pagetitle = "Posting of Collection";  
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
        
        //Check if the amount > 100,000,00
        jQuery("#txtAmount").live('change',function(){
            var nStr = jQuery("#txtAmount").val();
            var splitnum = nStr.split(/,/);
            var itr = 0;
            var input = '';
            var lastnum = '';
            if(splitnum.length > 0){
                while(itr <= splitnum.length-1){
                    if(itr == splitnum.length-1){
                        lastnum = splitnum[itr].split(/\./);
                        input += lastnum[0];
                    }else{
                        input +=splitnum[itr];
                    }
                    ++itr;
                }
            }

            if(input.length > 8){
                alert("Amount should not be greater than or equal to 100,000,000");
                $("#txtAmount").val("");
                return false;
            } else {
                return true;
            }      
        });   
              
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
        
        jQuery("#ddlRemittanceType").live('change', function(){
                var remittancetypeid = $("#ddlRemittanceType").val();

                if(remittancetypeid == 5 || remittancetypeid == 7){
                    return false;
                }
                else {
                    //ajax for getting bank names and code
                    jQuery.ajax({
                        url: url,
                        type: 'post',
                        data: {page: function(){return "GetBankName"}},
                        dataType: 'json',
                        success: function (data){
                           jQuery.each(data, function(){
                              jQuery("#ddlBank").append(jQuery("<option />").val(this.BankID).text(this.BankName));
                              jQuery("#ddlbankcode").append(jQuery("<option />").val(this.BankID).text(this.BankCode));
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
      <input type="hidden" name="page" value="PostingOfDeposit" />
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
               <input class="auto" id="txtAmount" name="txtAmount" maxlength='11'  onkeypress="javascript: return numberonlydecimal(event);"/>
           </td>
        </tr>
        <tr>
           <td>Remittance Type</td>
           <td>
               <?php 
                   $vremittanceTypes = $_SESSION['remittype'];
                   echo "<select id=\"ddlRemittanceType\" name=\"ddlRemittanceType\" onchange=\"javascript: return onchange_remittancetype();\">";
                   echo "<option value=\"0\">Please select</option>";
                   foreach ($vremittanceTypes as $row)
                   {
                       echo "<option value=\"".$row['RemittanceTypeID']."\">".$row['RemittanceName']."</option>";
                   }
                   echo "</select>";
               ?>
           </td>
        </tr>
        <tr>
           <td>Bank Name</td>
           <td>
               <select id='ddlBank' name='ddlBank' >
                    <option value='0'>Please Select</option>
                </select>
               
                <select id='ddlbankcode' name='ddlbankcode' style="display: none;">
                    <option value='0'>Please Select</option>
                </select>
           </td>
        </tr>
        <tr>
           <td>Branch</td>
           <td>
               <input id="txtBranch" name="txtBranch" maxlength="30" onkeypress="return numberandletter1(event);" />
           </td>
        </tr>
        <tr>
           <td>Bank Transaction ID</td>
           <td><input id="txtBankTransID" name="txtBankTransID" maxlength="30" onkeypress="return numberandletter1(event);"/></td>
        </tr>
        <tr>
           <td>Bank Transaction Date</td>
           <td>
              <input id="txtBankTransDate" name="txtBankTransDate" maxlength="26" readonly value="<?php echo date('Y-m-d')?>"/>
              <img id="cal" name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onClick="displayDatePicker('txtBankTransDate', false, 'ymd', '-');"/>
           </td>
        </tr>
        <tr>
           <td>Cheque Number</td>
           <td><input id="txtChequeNo" name="txtChequeNo" maxlength="30" onkeypress="return numberandletter1(event);" /></td>
        </tr>
        <tr>
           <td>Particulars</td>
           <td><input id="txtParticulars" name="txtParticulars" size="85" maxlength="100" onkeypress="return numberandletter1(event);"/></td>
        </tr>
     </table>
     <div id="submitarea">
         <input type="submit" value="Submit" onclick="return checkdepositposting();"/>
     </div> 
  </form>
</div>
<?php  
    }
}
include "footer.php"; ?>
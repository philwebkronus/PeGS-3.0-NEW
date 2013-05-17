<?php
$pagetitle = 'Passkey Retrieval';
include 'process/ProcessCSManagement.php';
include 'header.php';
    $vaccesspages = array('6');
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
        var url = 'process/ProcessCSManagement.php';
        jQuery("#cmbsite").live('change', function()
        {
            jQuery("#tblpasskey").hide();
            
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
                    }
            }); 
            
            jQuery("#cmbcashier").empty();
            
            //function: get cashier ID and name
            jQuery.ajax({
               url: url,
               type: 'post',
               data: {cashiersiteID: function(){return (jQuery("#cmbsite").find("option:selected").val());}},
               dataType: 'json',
               success: function(data){
                   var cmbcashier = jQuery("#cmbcashier");
                   cmbcashier.append(jQuery("<option />").val("-1").text("Please Select"));
                   jQuery.each(data, function(){
                      cmbcashier.append(jQuery("<option />").val(this.AID).text(this.UserName));
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
        
        jQuery("#cmbcashier").live('change', function(){
            //for retrieving of passkey
            jQuery.ajax({
               url: url,
               type: 'post',
               data: {page: function(){return 'GetPasskey';},
                      cmbcashier: function(){ return (jQuery("#cmbcashier").find("option:selected").val());},
                      cmbsite: function(){return (jQuery("#cmbsite").find("option:selected").text());},
                      posaccountno: function(){return jQuery("#txtposaccno").text()}
                     },
               dataType: 'json',
               success: function(data){
                     jQuery.each(data, function(){
                        jQuery("#tblpasskey").show();
                        //display on table
                            jQuery("#tdpos").text(this.POS);
                            jQuery("#tdpcode").text(this.SiteCode);
                            jQuery("#tdpasskey").text(this.Passkey);
                            jQuery("#tddateissued").text(this.DateIssued);
                            jQuery("#tddateexpires").text(this.DateExpired);
                            jQuery("#tdemail").text(this.Email);
                        //pass to hidden text boxes
                            jQuery("#txtemail").val(this.Email);
                            jQuery("#txtpasskey").val(this.Passkey);
                            jQuery('#dateissued').val(this.DateIssued);
                            jQuery("#dateexpires").val(this.DateExpired);
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
    });
</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <form method="post" action="process/ProcessCSManagement.php">
        <input type="hidden" name="page" value="PasskeyNotification" />
        <input type="hidden" name="txtpasskey" id="txtpasskey" />
        <input type="hidden" name="dateissued" id="dateissued" />
        <input type="hidden" name="dateexpires" id="dateexpires" />
        <input type="hidden" name="txtemail" id="txtemail" />
        <br />
        <table>
            <tr>
                <td>Site / PEGS</td>
                <td>
                    <?php 
                        $vviewsite = $_SESSION['siteids'];
                        echo "<select id=\"cmbsite\" name=\"cmbsite\">";
                        echo "<option value=\"-1\">Please Select</option>";

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
                    <label id="txtsitename"></label> <label id="txtposaccno"></label>
                </td>
            </tr>
            <tr>
                <td>Cashier</td>
                <td>
                    <select id="cmbcashier" name="cmbcashier">
                        <option value="-1">Please Select</option>
                    </select>
                </td>
            </tr>
        </table>
        <table width="100%" id="tblpasskey" style="display: none;text-align: center;margin: 50px auto;" border="1">
            <thead>
                <tr>
                    <td>Account Number</td>
                    <td>PEGS Code</td>
                    <td>Passkey</td>
                    <td>Issued On</td>
                    <td>Expires On</td>
                    <td>Email Address</td>
                    <td>Action</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td id="tdpos"></td>
                    <td id="tdpcode"></td>
                    <td id="tdpasskey"></td>
                    <td id="tddateissued"></td>
                    <td id="tddateexpires"></td>
                    <td id="tdemail"></td>
                    <td>
                        <input type="submit" value="Send Email" />
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>
<?php  
    }
}
include "footer.php"; ?>
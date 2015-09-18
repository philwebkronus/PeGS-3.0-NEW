<?php  
$pagetitle = "Reversal of Manual Top-up";  
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
<div id="workarea">
    <script type="text/javascript">
        $(document).ready(function(){
            var url = 'process/ProcessTopUp.php';
            jQuery("#btnsearch").click(function(){
                $("#userdata tbody").html("");
                var site = jQuery("#cmbsite").val();
                var selected = ($(this).find("option:selected").text()); 
                jQuery("#txtsitecode").val(selected);
                var posaccount = jQuery("#txtposacc").val();
                 
                if((site > 0) || (posaccount.length > 0))
                {
                    //for display of site top-up information
                    $.ajax({
                       url : url,
                       type : 'post',
                       data : {page: function(){return 'GetAllBCF';},
                               cmbsite: function(){return site;},
                               txtposacc : function() {return posaccount;}
                              },
                       dataType : 'json',
                       success : function(data){
                               if(data != null)
                               {
                                   var tblRow = "<thead>"
                                            +"<tr>"
                                            +"<th colspan='5' class='header'>Manual Top-up Reversal</th>"
                                            +"</tr>"
                                            +"<tr>"
                                            +"<th>Balance</th>"
                                            +"<th>Minimum Balance</th>"
                                            +"<th>Maximum Balance</th>"
                                            +"<th>Topup Type</th>"
                                            +"<th>Pickup Tag</th>"
                                            +"</tr>"
                                            +"</thead>";

                                  $.each(data, function(i,user)
                                  {    
                                        var xtopuptype = this.TopUpType;
                                        var xpickuptag = this.PickUpTag;
                                        var xbal =CommaFormatted(this.Balance);
                                        var xminbal = CommaFormatted(this.MinBalance);
                                        var xmaxbal = CommaFormatted(this.MaxBalance);
                                        var topuptypedesc;
                                        var pickuptagdesc;

                                        jQuery("#txtminbal").val(xminbal);
                                        jQuery("#txtmaxbal").val(xmaxbal);
                                        jQuery("#txtprevbal").val(xbal);

                                        if(xtopuptype == 0)
                                             topuptypedesc = 'Fixed';
                                        else
                                             topuptypedesc = 'Variable';

                                        if(xpickuptag  == 0)
                                             pickuptagdesc = 'Provincial';
                                        else
                                             pickuptagdesc = 'Metro Manila';

                                        tblRow  += "<tbody>"
                                                + "<tr>"
                                                + "<td>"+xbal +"</td>"
                                                + "<td>"+xminbal+"</td>"
                                                + "<td>"+xmaxbal+"</td>"
                                                + "<td>"+topuptypedesc+"</td>"
                                                + "<td>"+pickuptagdesc+"</td>"
                                                + "</tr>"
                                                + "</tbody>";

                                        $('#results').show();
                                        $('#userdata').html(tblRow);
                                    });
                               }
                               else
                               {
                                     $('#results').hide();
                                     $('#userdata').html(" ");
                               }
                       },
                       error: function(XMLHttpRequest, e){
                            alert(XMLHttpRequest.responseText);
                            $('#results').hide();
                            $('#userdata').html(" ");
                            if(XMLHttpRequest.status == 401)
                            {
                                window.location.reload();
                            }
                        }
                    });
                }
                else
                {
                    alert("Please select Site/PEGS or input the pos account number");
                }
            });
            
            $('#cmbsite').live('change', function(){
                var site = jQuery("#cmbsite").val();
                $('#results').hide();
                $('#userdata').html(" ");
                if(site > 0)
                {
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
                    jQuery("#txtposacc").attr('disabled', true);
                }
                else
                {
                    jQuery("#txtposacc").attr('disabled', false);
                    jQuery("#txtsitename").text(" ");
                    jQuery("#txtposaccno").text(" ");
                }
                
            });
            
            //format the amount
            jQuery('.rev').bind('blur', function()
            {
                var amount = CommaFormatted(jQuery(this).val());
                jQuery(this).val(amount);
                return false;
            });
            
            //disable enter key
            jQuery('body').bind('keypress', function (e) {
                var code = (e.keyCode ? e.keyCode : e.which);
                if(code == 13) { //Enter keycode
                    e.preventDefault();
                }
            });
        
            //clear the userdata table
            jQuery("#txtposacc").keypress(function(){
               $('#results').hide();
               $('#userdata').html(" "); 
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
    
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <form method="post" action="process/ProcessTopUp.php" id="frmtopup">
            <input type="hidden" name="txtsitecode" id="txtsitecode" />
            <input type="hidden" name="page" value="ManualTopUpReversal" />
            <input type="hidden" id="txtminbal" name="txtminbal"/>
            <input type="hidden" id="txtmaxbal" name="txtmaxbal"/>
            <input type="hidden" id="txtprevbal" name="txtprevbal" />
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
                                        $vname = $result['SiteName'];

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
                </table>
                <div id="submitarea">
                    <input type="button" value="Search" id="btnsearch" />
                </div>
                <div id="results" style="display: none;">
                    <table id="userdata" class="tablesorter">

                    </table>
                    <br />
                    <table>
                        <tr>
                            <td>Amount</td>
                            <td>
                                <input class="rev" type="text" id="txtamount" name="txtamount" onkeypress="return numberonly(event);"/>
                            </td>
                        </tr>
                    </table>
                    <div id="submitarea">
                        <input type="button" value="Confirm" onclick="return chkreversal();"/>
                    </div>
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
                <div id="fade" class="black_overlay"></div>
        </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery('#cmbsite').change(function(){
            jQuery('#lblsite').html(jQuery(this).children('option:selected').attr('label'));
        });
    });
</script>  
<?php  
    }
}
include "footer.php"; ?>
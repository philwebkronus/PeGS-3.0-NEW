<?php
$pagetitle = 'Post Replenishment';
include 'process/ProcessTopUp.php';
include 'header.php';
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
       
//       jQuery("#btnconfirm").click(function(){
//           var site = jQuery("#cmbsitename").val();
//           var posaccount = jQuery("#txtposacc").val();
//           if((site > 0) || (posaccount.length > 0))
//           {
//               jQuery.ajax({
//                  url: url,
//                  type: 'post',
//                  data: {page: function(){ return "GetOperator";},
//                         cmbsitename: function(){return jQuery("#cmbsitename").val();},
//                         txtposacc : function() {return posaccount;}
//                        },
//                  dataType: 'json',
//                  success: function(data){
//                      jQuery.each(data, function()
//                      {
//                          jQuery("#lblopsname").text(this.Username);
//                          jQuery("#txtownerID").val(this.AccountTypeID);
//                          jQuery("#txtsiteID").val(this.SiteID);
//                          var sitecode = jQuery("#cmbsitename").find("option:selected").text();
//                          jQuery("#lblsitecode").text(sitecode);
//                          jQuery("#txtsitecode").val(sitecode);
//                      });
//                      document.getElementById('light').style.display='block';
//                      document.getElementById('fade').style.display='block';
//                  },
//                  error: function(XMLHttpRequest, e){
//                        alert(XMLHttpRequest.responseText);
//                        if(XMLHttpRequest.status == 401)
//                        {
//                            window.location.reload();
//                        }
//                  }
//               });
//           }
//           else
//           {
//               alert("Please select Site/PEGS or input the pos account number");
//           }
//           
//       });
       
       jQuery("#cmbsitename").live('change', function(){
            var site = jQuery("#cmbsitename").val();
            if(site > 0)
            {
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
        
        jQuery("#cmbreplenishment").live('change', function(){
            var replenishmenttype = jQuery("#cmbreplenishment").val();
            if(replenishmenttype == 1) {
                $('#txtrefnum').val("");
                $('#txtrefnum').attr("disabled", true);
            } else {
                $('#txtrefnum').val("");
                $('#txtrefnum').attr("disabled", false);
            } 
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
        <input type="hidden" name="page" value="InsertReplenishment" />
        <input type="hidden" name="txtsitecode" id="txtsitecode" />
        <table>
            <tr>
                <td>Site / PEGS</td>
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
                  <label id="txtsitename"></label>  <label id="txtposaccno"></label>
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
                    <input type="text" class="auto" name="txtamount" id="txtamount" onkeypress="return numberonlydecimal(event);" />
                </td>
            </tr>
            <tr>
                <td>Type</td>
                <td>
                    <?php 
                        $vviewreplenishment = $otopup->getActiveReplenishment();

                        echo "<select id=\"cmbreplenishment\" name=\"cmbreplenishment\">";
                        echo "<option value=\"-1\">Please select</option>";

                        foreach($vviewreplenishment as $resultviews)
                        {
                           $vreplenishmentID = $resultviews['ReplenishmentTypeID'];      
                           $vreplenishmentName = $resultviews['ReplenishmentName'];                        


                              echo "<option value=\"".$vreplenishmentID."\">".$vreplenishmentName."</option>";

                        }
                        echo "</select>";
                    ?>
                </td>
            </tr>
            <tr>
                <td>Reference Number</td>
                <td>
                    <input type="text" name="txtrefnum" id="txtrefnum" maxlength="20" size="30" onkeypress="return numberandletter1(event);" />
                </td>
            </tr>
        </table>
        <div id="submitarea">
            <input type="submit" value="Submit" onclick="return chkreplenishment();" />
        </div>
<!--        <div id="light" class="white_page">
            <div class="close_popup" id="btnClose" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"></div>
            <p align="center" style="font-weight: bold;"> Replenishment Platform </p>
            <table align="center">
                <tr>
                    <td>Account Name: </td>
                    <td>
                        <input type="text" readonly="readonly" id="txtaccname" name="txtaccname" />
                    </td>
                </tr>
                <tr>
                    <td>Owner ID: </td>
                    <td>
                        <input type="text" readonly="readonly" id="txtownerID" name="txtownerID" size="10" /> <label id="lblopsname"></label>  
                    </td>
                </tr>
                <tr>
                    <td>Site ID</td>
                    <td>
                        <input type="text" readonly="readonly" id="txtsiteID" name="txtsiteID" size="10"/> <label id="lblsitecode"></label>  
                    </td>
                </tr>
                
                <tr>
                    <td>Date / Time: </td>
                    <td>
                        <input type="text" id="txtdate" name="txtdate" maxlength="26" readonly value="<?php //echo date('Y-m-d')." "."06:00:00"; ?>"/>
                        <img name="cal" src="images/cal.gif" width="16" height="16" border="0" alt="Pick a date" onclick="javascript:NewCssCal('txtdate','yyyyMMdd','dropdown',true,'24',true)"/>
                    </td>
                </tr>
                
                
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr align="right">
                    <br />
                    <td>
                        <input type="submit" value="Submit" onclick="return chkreplenishment();"/>
                    </td>
                    <td>
                        <input type="button" value="Cancel" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';document.getElementById('calcancel').style.display= 'none';" />
                    </td>
                </tr>
            </table>
        </div>
        <div id="fade" class="black_overlay"></div>-->
    </form>
</div>
<?php  
    }
}
include "footer.php"; ?>

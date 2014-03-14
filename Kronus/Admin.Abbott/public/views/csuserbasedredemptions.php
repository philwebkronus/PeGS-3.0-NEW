<?php 
$pagetitle = "User Based Redemption";  
include "process/ProcessCSManagement.php";
include "header.php";
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

<div id="workarea"> 
    <script type="text/javascript">
            $(document).ready(function(){
                
            $("#txtcardnumber").focus(function(){
                    $("#txtcardnumber").bind('paste', function(event) {
                        setTimeout(function(event) {
                            var data = $("#txtcardnumber").val();
                            if(!specialcharacter(data)){
                                $("#txtcardnumber").val("");
                                $("#txtcardnumber").focus();
                            }
                        }, 0);
                    });
            });
                
//                $("#checkbox1").change(function() 
//                {                
//                    if($('#checkbox1').attr("checked"))
//                    {
//                        document.getElementById('cmbsite').value="-1";
//                        document.getElementById('cmbterminal').value="-1";
//                        document.getElementById('txtcardnumber').value="";
//                        document.getElementById('txtsitename').textContent = "";
//                        document.getElementById('txtposaccno').textContent="";                    
//                        document.getElementById('txttermname').textContent="";
//
//                        document.getElementById('cmbsite').disabled=true;
//                        document.getElementById('cmbterminal').disabled=true;
//                        document.getElementById('txtcardnumber').disabled=false;
//                        document.getElementById('txtcardnumber').readOnly=false;
//                    }
//                    else
//                    {
//                        document.getElementById('cmbsite').value="-1";
//                        document.getElementById('cmbterminal').value="-1";
//                        document.getElementById('txtcardnumber').value="";
//                        document.getElementById('txtsitename').textContent="";
//                        document.getElementById('txtposaccno').textContent="";                    
//                        document.getElementById('txttermname').textContent="";
//
//                        document.getElementById('cmbsite').disabled=false;
//                        document.getElementById('cmbterminal').disabled=false;
//                        document.getElementById('txtcardnumber').disabled=true;
//                        document.getElementById('txtcardnumber').readOnly=true;
//                    }
//                
//                });
                
                
                $('#cmbsite').live('change', function()
                {
                    var cmbsite = document.getElementById('cmbsite').value;
                    if(cmbsite == '-1'){
                        jQuery("#txttermname").text(" ");
                        $('#cmbterminal').empty();
                        $('#cmbterminal').append($("<option />").val("-1").text("Please Select"));
                        jQuery("#txtsitename").text(" ");
                        jQuery("#txtposaccno").text(" ");
                        jQuery("#txtcardnumber").val("");
                    }
                    else{
                        jQuery("#txttermname").text(" ");
                        sendSiteID2($(this).val());
                        $('#cmbterminal').empty();
                        $('#cmbterminal').append($("<option />").val("-1").text("Please Select"));
                        jQuery("#txtcardnumber").val("");
                        var url = 'process/ProcessCSManagement.php';
                        jQuery.ajax({
                              url: url,
                              type: 'post',
                              data: {cmbsitename: function(){return jQuery("#cmbsite").val();}},
                              dataType: 'json',
                              success: function(data){
                                  if(jQuery("#cmbsite").val() > 0)
                                  {
                                    jQuery("#txtsitename").text(data.SiteName+" / ");
                                    jQuery("#txtposaccno").text(data.POS);
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
                });
                
                $('#cmbterminal').live('change', function(){
                    var services = jQuery("#cmbterminal").val();
                    var provider = ($(this).find("option:selected").text());
                    var url = 'process/ProcessCSManagement.php';
                    document.getElementById('txtservices').value = provider;
                    if(services != "-1")
                    {
                    jQuery.ajax({
                            url: url,
                            type: 'post',
                            data: {
                                cmbservices: function(){return jQuery("#cmbterminal").val();}},
                            dataType: 'json',
                            success: function(data){
                                if(data.loyaltyCard == null){
                                    alert('User Based Redemption: Cant get Membership Card Number');
                                    window.location.reload();
                                }
                                else{
                                    jQuery("#txtcardnumber").val(data.loyaltyCard);
                                    document.getElementById('txtcardnumber').disabled=false;
                                    document.getElementById('txtcardnumber').readOnly = true
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
                    else{
                    
                    }
                });
                

                $('#btnSubmit').click(function()
                {
                    var url = 'process/ProcessCSManagement.php';
                    
                    //check if checkbox is checked
//                    var checkbox = document.getElementById('checkbox1').checked;
//                    if(checkbox == false)
//                    {
//                            if(document.getElementById('cmbsite').value == "-1")
//                            {
//                                alert("Please select site");
//                                document.getElementById('cmbsite').focus();
//                                return false;
//                            }
//                            if(document.getElementById('cmbterminal').value == "-1")
//                            {
//                                alert("Please select terminal");
//                                document.getElementById('cmbterminal').focus();
//                                return false;
//                            }
//                            
//                             getMembershipandCasinoInfo(url);            
//                    } 
//                    //with membership card provided
//                    else {
                            
                            if(document.getElementById('txtcardnumber').value == "" || document.getElementById('txtcardnumber').length == 0)
                            {
                                alert("Please Input Membership Card Number");
                                document.getElementById('txtcardnumber').focus();
                                return false;
                            }

                            getMembershipandCasinoInfo(url);
//                    }
                });
                
                
              });
              
              function getMembershipandCasinoInfo(url){
                jQuery.ajax({
                    url: url,
                    type: 'post',
                    data: {page: function(){ return "GetLoyaltyCard";},

                        txtcardnumber: function(){return jQuery("#txtcardnumber").val();}
                     },
                    dataType : 'json',  
                    success : function(data){
                        $.each(data, function(i,user){
                            if(this.StatusCode == 9){
                                      alert("Card is Banned");
                            }
                            if(this.CardNumber == null)
                            {
                                alert("User Based Redemption: Invalid Card Number");
                                $('#loading').hide();
                                $('#light2').hide();
                                $('#fade2').hide();
                                window.location.reload();
                            }
                           else
                           {
                                document.getElementById('loading').style.display='block';
                                document.getElementById('fade2').style.display='block';
                                jQuery.ajax(
                               {
                               url: url,
                               type: 'post',
                               data: {page: function(){ return "GetCasino";},
                                      txtcardnumber: function(){return jQuery("#txtcardnumber").val();}
                                     },
                               dataType : 'json',  
                                  success : function(data)
                                  {
                                     var tblRow = "<thead>"
                                         +"<tr>"
                                         +"<th colspan='2' class='header'>User Based Redemption </th>"
                                         +"</tr>"
                                         +"<tr>"
                                         +"<th>Casino</th>"
                                         +"<th>Balance</th>"
                                         +"</tr>"
                                         +"</thead>";

                                   $.each(data, function(i,user)
                                   {

                                          $('#loading').hide();
                                          document.getElementById('loading').style.display='none';
                                          document.getElementById('light2').style.display='block';
                                          document.getElementById('fade2').style.display='block';

                                      tblRow +=
                                                  "<tbody>"
                                                  +"<tr>"
                                                  +"<td>"+this.ServiceName+"</td>"   
                                                  +"<td align='right'>"+this.Balance+"</td>"
                                                  +"</tr>"
                                                  +"</tbody>";
                                                  $('#userdata2').html(tblRow);

                                   });

                                  },
                                  error : function(XMLHttpRequest, e)
                                  {
                                      alert(XMLHttpRequest.responseText);
                                      if(XMLHttpRequest.status == 401)
                                      {
                                          window.location.reload();
                                      }
                                   }
                               });  
                            }
                        });
                    },
                    error : function(XMLHttpRequest, e)
                    {
                        alert(XMLHttpRequest.responseText);
                        if(XMLHttpRequest.status == 401)
                        {
                            window.location.reload();
                        }
                    }
           });
              }
    </script>

        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <input type="hidden" name="chkbalance" id="chkbalance" value="CheckBalance" />
        <form method="post" action="#" id="frmredemption" name="frmcs" class="frmmembership">
            <input type="hidden" name="page" id="page" value="ManualRedemptionUB" />
            <input type="hidden" name="txtterminal" id="txtterminal"/>
            <input type="hidden" name="txtservices" id="txtservices" />
            <input type="hidden" name="terminalcode" id="terminalcode" />
            <table>
<!--                <tr>
                    <div id="check1">
                    <input type="checkbox" name="checkbox1" id="checkbox1" value="1" > With Membership Card Number
                    </div>
                  
                </tr>
                    <td width="130px">Site / PEGS</td>
                    <td>
                    //<?php
//                    
//                        $siteList = $_SESSION['siteids'];
//                        $vsite = $siteList;
//                        echo "<select id=\"cmbsite\" name=\"cmbsite\">";
//                        echo "<option value=\"-1\">Please Select</option>";
//
//                        foreach ($vsite as $result)
//                        {
//                             $vsiteID = $result['SiteID'];
//                             $vorigcode = $result['SiteCode'];
//                             
//                             //search if the sitecode was found on the terminalcode
//                             if(strstr($vorigcode, $terminalcode) == false)
//                             {
//                                $vcode = $vorigcode;
//                             }
//                                    
//                             else
//                             {
//                               //removes the "icsa-"
//                               $vcode = substr($vorigcode, strlen($terminalcode));
//                             }
//                             if($vsiteID <> 1)
//                             {
//                               echo "<option value=\"".$vsiteID."\">".$vcode."</option>";  
//                             }
//                        }
//                        echo "</select>";
//                    ?>
                        <label id="txtsitename"></label><label id="txtposaccno"></label>
                    </td>
                </tr>
                <tr>
                    <td>Terminals</td>
                    <td>
                        <select id="cmbterminal" name="cmbterminal">
                            <option value="-1">Please Select</option>
                        </select>
                        <label id="txttermname"></label>
                    </td>
                </tr>-->
                <tr>
                <td>
                    Card Number
                    <input type="text" size="30" class="txtmembership" id="txtcardnumber" name="txtcardnumber" maxlength="30" size="30" onkeypress="return loyaltycardnumber(event);" />
                    <div for="txtcardnumber" align='center'>Membership | Temporary</div>
                </td>
                </tr>

            </table>            
            <div id="loading"></div>
            <div id="submitarea"> 
                <input type="button" value="Submit" id="btnSubmit"/>
            </div>
            
            <div id="cont">
              <div id="light" class="white_content" oncontextmenu="return false" style="width: 308px; height:212px;">
              <div class="close_popup" id="btnClose" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"></div>
              <input type="hidden"  name="Withdraw" value="Withdraw" />
                <br />
                <div id="userdata"></div>
                
                <input type="hidden" id="txtamount" name="txtamount"/>
                <input type="button" id="btnok" value="OK" style="margin-left: 130px;" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none'" />
              </div>
              <div id="fade" class="black_overlay" oncontextmenu="return false"></div>
            </div>
            
            <div id="light2" class="white_page">
            <div class="close_popup" id="btnClose" onclick="document.getElementById('light2').style.display='none';document.getElementById('fade2').style.display='none';"></div>
            <input type="hidden" name="page" value="InsertPegsConfirmation2" />
            <input type="hidden" name="txtsitecode" id="txtsitecode" />
            <table id="userdata2" class="tablesorter" align="center">
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr align="right">
                    <br />

                </tr>
            </table>
            <br />
            <div align="right">
                <input type="button" id="btnok" value="OK" style="margin-left: 130px;" onclick="document.getElementById('light2').style.display='none';document.getElementById('fade2').style.display='none'" />
            </div>        
        </div>
            <div id="fade2" class="black_overlay"></div>
        </form>
</div>
<?php  
    }
}
include "footer.php"; ?>
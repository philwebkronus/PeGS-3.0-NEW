<?php 
$pagetitle = "Redemption";  
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
                $('#cmbsite').live('change', function()
                {
                    jQuery("#txttermname").text(" ");
                    sendSiteID2($(this).val());
                    $('#cmbterminal').empty();
                    $('#cmbterminal').append($("<option />").val("-1").text("Please Select"));
                    $('#cmbservices').empty();
                    $('#cmbservices').append($("<option />").val("-1").text("Please Select"));
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
                });
                
                $('#cmbterminal').live('change', function(){
                    var terminal = jQuery("#cmbterminal").val();
                    var url = 'process/ProcessCSManagement.php';
                    if(terminal != "-1")
                    {
                        sendTerminalID($(this).val()); //// function to get ServiceID, ServiceCode
                        var terminalcode = ($(this).find("option:selected").text());
                        document.getElementById('txtterminal').value = terminalcode;
                        //for displaying of terminal name
                        jQuery.ajax({
                            url: url,
                            type: 'post',
                            data: {cmbterminal: function(){return jQuery("#cmbterminal").val();}},
                            dataType: 'json',
                            success: function(data){
                                jQuery("#txttermname").text(data.TerminalName);
                                jQuery("#terminalcode").val(data.TerminalCode);
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
                        jQuery("#txttermname").text("");
                    }
                    $('#cmbservices').empty();
                    $('#cmbservices').append($("<option />").val("-1").text("Please Select"));
                });
                
                $('#cmbservices').live('change', function(){
                    var provider = ($(this).find("option:selected").text());
                    document.getElementById('txtservices').value = provider; 
                });

                $('#btnSubmit').click(function()
                {                        
                    var retmessage;
                    var url = 'process/ProcessCSManagement.php';
                    var data = $('#frmredemption').serialize();
                    
                    if(document.getElementById('cmbsite').value == "-1")
                    {
                        alert("Please select site");
                        document.getElementById('cmbsite').focus();
                        return false;
                    }
                    if(document.getElementById('cmbterminal').value == "-1")
                    {
                        alert("Please select terminal");
                        document.getElementById('cmbterminal').focus();
                        return false;
                    }
                    if(document.getElementById('cmbservices').value == "-1")
                    {
                        alert("Please select services");
                        document.getElementById('cmbservices').focus();
                        return false;
                    }
                    
                    document.getElementById('loading').style.display='block';
                    document.getElementById('fade').style.display='block';
                    
                    $.ajax(
                    {
                      url : url,
                      type : 'post',
                      data : {
                            page: function() {return $("#page").val();},
                            txtterminal: function() {return $("#txtterminal").val();},
                            terminalcode: function() {return $("#terminalcode").val()},
                            txtservices: function() {return $("#txtservices").val();},
                            cmbterminal: function() {return $("#cmbterminal").val();},
                            cmbservices: function() {return $("#cmbservices").val();},
                            cmbsite: function() {return $("#cmbsite").val();},
                            chkbalance: function() {return $("#chkbalance").val();}
                            },
                      dataType : 'json',
                      success : function(data)
                      {
                         var redeemable = data.Balance;
                         $('#loading').hide();
                         document.getElementById('loading').style.display='none';
                         document.getElementById('light').style.display='block';
                         document.getElementById('fade').style.display='block';
                         
                         if(redeemable != "0.00")
                         {
                             retmessage = "<p>"
                                        + "<p> Actual balance PHP "+redeemable+"</p>"
                                        + "</p>";
                         }
                         else
                         {
                             retmessage = "<p>"
                                        + "<p align=\"center\">Balance is zero</p>"
                                        + "</p>";
                         }
                         
                         $('#txtamount').val(redeemable);
                         $('#userdata').html(retmessage);
                      },
                      error: function(XMLHttpRequest, e) 
                      {
                         document.getElementById('loading').style.display='none';
                         document.getElementById('light').style.display='block';
                         document.getElementById('fade').style.display='block';
                         
                         retmessage = "<p>"
                                    + "<p align=\"center\">"+XMLHttpRequest.responseText+"</p>"
                                    + "</p>";
                         $('#userdata').html(retmessage); 
                         if(XMLHttpRequest.status == 401)
                         {
                             window.location.reload();
                         }
                      }
                   });   
                });
              });
    </script>

        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <input type="hidden" name="chkbalance" id="chkbalance" value="CheckBalance" />
        <form method="post" action="#" id="frmredemption" name="frmcs">
            <input type="hidden" name="page" id="page" value="ManualRedemption" />
            <input type="hidden" name="txtterminal" id="txtterminal"/>
            <input type="hidden" name="txtservices" id="txtservices" />
            <input type="hidden" name="terminalcode" id="terminalcode" />
            <table>
                <tr>
                    <td width="130px">Site / PEGS</td>
                    <td>
                    <?php
                    
                        $siteList = $_SESSION['siteids'];
                        $vsite = $siteList;
                        echo "<select id=\"cmbsite\" name=\"cmbsite\">";
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
                    <td>Terminals</td>
                    <td>
                        <select id="cmbterminal" name="cmbterminal">
                            <option value="-1">Please Select</option>
                        </select>
                        <label id="txttermname"></label>
                    </td>
                </tr>
                <tr>
                    <td>Services</td>
                    <td>
                        <select id="cmbservices" name="cmbservices">
                            <option value="-1">Please Select</option>
                        </select>
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
<!--                <input type="button" id="btnCancel" value="Cancel" style="float: right;" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none'" />-->
                <input type="button" id="btnok" value="OK" style="margin-left: 130px;" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none'" />
<!--                <input type="submit" style="float: left;" value="Withdraw" id="btnWithdraw" />-->
              </div>
              <div id="fade" class="black_overlay" oncontextmenu="return false"></div>
            </div>
        </form>
</div>
<?php  
    }
}
include "footer.php"; ?>
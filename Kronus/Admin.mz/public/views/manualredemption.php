<?php 
$pagetitle = "Terminal Based Manual Redemption";  
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
    function checkForm()
            {
                return false;
                alert('aaa');
                if($('txtticket').val == '')
                   {
                       $('#error').attr('style','display:block');
                       alert('bbb');
                       return false
                   }
                   else
                       {
                           return true;
                       }
                   
            }
        $(document).ready(function(){
            var url = 'process/ProcessTopUp.php';

            $('#cmbsite').live('change', function()
            {
                var site = $('#cmbsite').val();
                    if(site == "-1"){
                        $('#cmbterminal').empty();
                        $('#cmbterminal').append($("<option />").val("-1").text("Please Select"));
                        $('#cmbservices').empty();
                        $('#cmbservices').append($("<option />").val("-1").text("Please Select"));
                        jQuery("#txtsitename").text(" ");
                        jQuery("#txtposaccno").text(" ");
                        jQuery("#txttermname").text("");
                    }
                    else{
                        jQuery("#txttermname").text(" ");
                            sendSiteID2($(this).val()); // function to get TerminalID, TerminalCode

                            // this clears out sites data on combo boxes upon change of combo box
                            $('#cmbterminal').empty();
                            $('#cmbterminal').append($("<option />").val("-1").text("Please Select"));
                            $('#cmbservices').empty();
                            $('#cmbservices').append($("<option />").val("-1").text("Please Select"));

                            //for displaying of site name
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
                    }
                 
            });

            $('#cmbterminal').live('change', function(){
                var terminal = jQuery("#cmbterminal").val();
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
            
            $('#btnWithdraw').click(function(){
                redeemable = $('#txtamount').val();
                
                $('#txtamount2').val(redeemable);
                document.getElementById('loading').style.display='none';
                document.getElementById('light1').style.display='none';
                document.getElementById('light2').style.display='block';
                document.getElementById('fade').style.display='block';
                
            });
             $('#btnWithdraw1').click(function()
             {
                 if(document.getElementById('txtticket').value == "" || 
                     (document.getElementById('txtticket').value.indexOf(" ") == 0))
                {
                    alert("Blank or Ticket ID with leading space/s is/are not allowed");
                    $('#error').attr('style','display:block');
                    return false;
                }
                else
                    {
                       document.forms["frmcs"].submit();
                    }
             });
            $('#btnSubmit').click(function()
            {   
                var retmessage;
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

                //for displaying of lightbox containing the balance
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
                     document.getElementById('light1').style.display='block';
                     document.getElementById('fade').style.display='block';

                     if(redeemable != "0.00")
                     {
                         retmessage = "<p>"
                                    + "<p> Actual balance PHP "+redeemable+"</p>"
                                    + "<p align=\"center\"> Do you want to continue?</p>"
                                    + "</p>";
                         $('#btnWithdraw').show();
                         $('#btnCancel').show();
                         $('#btnok').hide();
                     }
                     else
                     {
                         retmessage = "<p>"
                                    + "<p align=\"center\">Balance is zero</p>"
                                    + "</p>";
                         $('#btnWithdraw').hide();
                         $('#btnCancel').hide();
                         $('#btnok').show();
                     }

                     $('#txtamount').val(redeemable);
                     $('#userdata').html(retmessage);
                  },
                  error: function(XMLHttpRequest, e) 
                  {
                     document.getElementById('loading').style.display='none';
                     document.getElementById('light1').style.display='block';
                     document.getElementById('fade').style.display='block';

                     retmessage = "<p>"
                                + "<p align=\"center\">"+XMLHttpRequest.responseText+"</p>"
                                + "</p>";
                     $('#userdata').html(retmessage); 
                     $('#btnok').show();
                     $('#btnWithdraw').hide();
                     $('#btnCancel').hide();
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
        <br />
        <input type="hidden" name="chkbalance" id="chkbalance" value="CheckBalance" />
        <form method="post" action="process/ProcessTopUp.php" id="frmredemption" name="frmcs">
            <input type="hidden" name="page" id="page" value="ManualRedemption" />
            <input type="hidden" name="txtterminal" id="txtterminal"/>
            <input type="hidden" name="txtservices" id="txtservices" />
            <input type="hidden" name="terminalcode" id="terminalcode" />
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
              <div id="light1" class="white_content" oncontextmenu="return false" style="width: 308px; height:212px;">
              <div class="close_popup" id="btnClose" onclick="document.getElementById('light1').style.display='none';document.getElementById('fade').style.display='none';"></div>
              <input type="hidden"  name="Withdraw" value="Withdraw" />
                <br />
                <div id="userdata"></div>
                
                <input type="hidden" id="txtamount" name="txtamount"/>
                <input type="button" id="btnCancel" value="Cancel" style="float: right;" onclick="document.getElementById('light1').style.display='none';document.getElementById('fade').style.display='none'" />
                <input type="button" id="btnok" value="OK" style="margin-left: 130px; display: none;" onclick="document.getElementById('light1').style.display='none';document.getElementById('fade').style.display='none'" />
                <input type="button" style="float: left;" value="Redeem" id="btnWithdraw" class="btnWithdraw" />
              </div>
                
                
              <div id="light2" class="white_content" oncontextmenu="return false" style="width: 325px; height:235px;">
              <div class="close_popup" id="btnClose" onclick="document.getElementById('light2').style.display='none';document.getElementById('fade').style.display='none';"></div>
              <input type="hidden"  name="Withdraw" value="Withdraw" />
                <br />
                <div id="userdata"></div>
                
                <input type="hidden" id="txtamount2" name="txtamount2"/>
                <table>                    
                    <tr>
                        <td>Ticket ID <span style='color:red'>*<div id="error" style="display:none">Required</div></span></td>
                        <td><input type="text" id="txtticket" name="txtticket" maxlength="20" onkeypress='return numberandletter1(event);'/></td>
                    </tr>
                    <tr>
                        <td>Remarks:</td>
                        <td><textarea cols="23" rows="7" maxlength="250" id="txtremarks" name="txtremarks" onkeypress='return numberandletter1(event);'></textarea></td>
                    </tr>
                </table>
                <input type="hidden"  name="Withdraw" value="Withdraw" />
                <input type="button" id="btnCancel" value="Cancel" style="float: right;" onclick="document.getElementById('light2').style.display='none';document.getElementById('fade').style.display='none'" />
                <input type="button" id="btnok" value="OK" style="margin-left: 130px; display: none;" onclick="document.getElementById('light2').style.display='none';document.getElementById('fade').style.display='none'" />
                 </form>
                <input type="button" style="float: left;" value="Redeem" id="btnWithdraw1"  />
              </div>
              <div id="fade" class="black_overlay" oncontextmenu="return false"></div>
            </div>
            
           
       
</div>
<?php  
    }
}
include "footer.php"; ?>
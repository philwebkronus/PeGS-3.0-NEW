<?php 
$pagetitle = "Manual Casino Fulfillment";  
include "process/ProcessManualAPIFulfillment.php";
include "header.php";
$vaccesspages = array('9');
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
                $('#cmbsite').live('change', function(){
                    jQuery("#txttermname").text(" ");
                    sendSiteIDz($(this).val());
                    $('#cmbterm').empty();
                    $('#cmbterm').append($("<option />").val("-1").text("Please Select"));
                    
                });

                $('#cmbterm').live('change', function(){  
                    sendterminalname($(this).val()) ;                 
                });
                
                //submit button event to display loyalty card info
                jQuery("#btnSubmit").click(function()
                {
                    var site = jQuery("#cmbsite").val();
                    var term = jQuery("#cmbterm").val();

                    if((site < 1) || (site.length < 1))
                    {
                     alert("Please Choose a Site");
                    }
                    else if((term < 1) || (term.length < 1))
                    {
                     alert("Please Choose a Terminal");   
                    }
                    else
                    {
                        var url = 'process/ProcessManualAPIFulfillment.php';
                       jQuery.ajax({
                                    url: url,
                                    type: 'post',
                                    data: {page: function(){return "CheckPending";},
                                         cmbsite: function(){return jQuery("#cmbsite").val();},
                                         cmbterm: function(){return jQuery("#cmbterm").val();}
                                           },
                                    dataType: 'json',
                                    success: function(data){
                                        jQuery("#txtusermode").val(data);

                                            if(data != 0)
                                            {
                                                jQuery.ajax({
                                                      url: url,
                                                      type: 'post',
                                                      data: {page: function(){return "GetCardInfo";},
                                                            cmbsite: function(){return jQuery("#cmbsite").val();},
                                                            txtusermode: function(){return jQuery("#txtusermode").val();},
                                                            cmbterm: function(){return jQuery("#cmbterm").val();}
                                                             },
                                                      dataType: 'json',
                                                      success: function(data){
                                                          if(data == null)
                                                          {
                                                            alert('Manual Casino Fulfillment: No Pending Transactions Found');    
                                                          }
                                                          else
                                                          {
                                                              $.each(data, function(i,user)
                                                              {
                                                              $.each(data, function(i,user)
                                                              {
                                                              jQuery("#txtusername").val(this.Login);
                                                              });
                                                              });
                                                              //show Pending Transaction Table and Card Information
                                                              showCardInfoTable(); 
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
                                                alert('Manual Casino Fulfillment: No Pending Transactions Found');
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


                 //on click function for Verify button
                 jQuery("#btnVerify").click(function()
                 {
                     document.getElementById('loading').style.display='block';
                     document.getElementById('fade2').style.display='block';
                    var url = 'process/ProcessManualAPIFulfillment.php';
                    var retmessage;
                        jQuery.ajax({
                                   url: url,
                                   type: 'post',
                                   data: {page: function(){return "VerifyCasino";},
                                       txtcasino: function(){return jQuery("#txtcasino").val();},
                                       txtsource: function(){return jQuery("#txtsource").val();},
                                       cmbsite: function(){return jQuery("#cmbsite").val();},
                                       cmbterm: function(){return jQuery("#cmbterm").val();},
                                       txttransrefid: function(){return jQuery("#txttransrefid").val();},
                                       txttranstype: function(){return jQuery("#txttranstype").val();},
                                       txtserviceid: function(){return jQuery("#txtserviceid").val();}
                                          },
                                   dataType: 'json',
                                   success: function(data){
                                       jQuery("#txttransstatus").val(data);
                                       $('#loading').hide();
                                       if(data == 1){
                                           retmessage = "<p>"
                                        + "<p align=\"center\">Casino Transaction was Approved!</p>"
                                        + "</p>";
                                            document.getElementById('light2').style.display='block';
                                            document.getElementById('fade2').style.display='block';
                                            $('#userdata3').html(retmessage);
                                       }
                                       else if(data == 2)
                                       {
                                            retmessage = "<p>"
                                        + "<p align=\"center\">Casino Transaction was Disapproved!</p>"
                                        + "</p>";
                                            document.getElementById('light2').style.display='block';
                                            document.getElementById('fade2').style.display='block';
                                            $('#userdata3').html(retmessage);
                                       }
                                       else
                                       {
                                           alert(data);
                                           $('#light2').hide();
                                           $('#fade2').hide();
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

                //on click function for Proceed button
                jQuery("#btnProceed").click(function()
                {
                    document.getElementById('loading').style.display='block';
                    document.getElementById('fade2').style.display='block';
                    var url = 'process/ProcessManualAPIFulfillment.php';
                    jQuery.ajax({
                                   url: url,
                                   type: 'post',
                                   data: {page: function(){return "Proceed";},
                                       cmbsite: function(){return jQuery("#cmbsite").val();},
                                       cmbterm: function(){return jQuery("#cmbterm").val();},
                                       txttransstatus: function(){return jQuery("#txttransstatus").val();},
                                       txtsource: function(){return jQuery("#txtsource").val();},
                                       txttransrefid: function(){return jQuery("#txttransrefid").val();},
                                       txttranstype: function(){return jQuery("#txttranstype").val();},
                                       txtamount: function(){return jQuery("#txtamount").val();},
                                       txtserviceid: function(){return jQuery("#txtserviceid").val();},
                                       txtcasino: function(){return jQuery("#txtcasino").val();},
                                       txtusername: function(){return jQuery("#txtusername").val();},
                                       txtusermode: function(){return jQuery("#txtusermode").val();},
                                       txtcardnumber: function(){return jQuery("#txtcardnumber").val();}
                                          },
                                   dataType: 'json',
                                   success: function(data){
                                       $('#loading').hide();
                                       $('#fade2').hide();
                                       alert(data);
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
        
    function showCardInfoTable()
    {
        var url = 'process/ProcessManualAPIFulfillment.php';
            //for displaying site / pegs information
            jQuery.ajax(
            {
               url: url,
               type: 'post',
               data: {page: function(){ return "GetLoyaltyCard";},
                   txtusername: function(){return jQuery("#txtusername").val();},
                   txtusermode: function(){return jQuery("#txtusermode").val();},
                   cmbterm: function(){return jQuery("#cmbterm").val();}       
                     },
               dataType : 'json',     
               success: function(data)
               {
                    if(data.Casino == null || data.Casino == '')
                    {
                        alert('Manual Casino Fulfillment: Invalid Casino Provider');

                    }
                    else
                    {
                        $.each(data, function(i,user)
                        {

                                var tblRow = "<thead>"
                                   +"<tr>"
                                   +"<th colspan='6' class='header'>Casino Fulfillment Information </th>"
                                   +"</tr>"
                                   +"<tr>"
                                   +"<th>Casino</th>"
                                   +"<th>Login</th>"
                                   +"<th>Casino Mode</th>"
                                   +"<th>Transaction Type</th>"
                                   +"<th>Origin Source</th>"
                                   +"<th>Amount</th>"
                                   +"</tr>"
                                   +"</thead>";

                               document.getElementById('light').style.display='block';
                               document.getElementById('fade').style.display='block';
                               jQuery("#txtcasino").val(data.Casino);
                               jQuery("#txtserviceid").val(data.ServiceID);
                               jQuery("#txtsource").val(data.Source);
                               jQuery("#txttransrefid").val(data.TransRefID);
                               jQuery("#txttranstype").val(data.TransType);
                               jQuery("#txtamount").val(data.Amount);
                               jQuery("#txtcardnumber").val(data.CardNumber);
                               jQuery("#txtusername").val(data.UserName);
                               
                               var balance = CommaFormatted(data.Amount);
                           tblRow +=
                                       "<tbody>"
                                       +"<tr>"
                                       +"<td>"+data.Casino+"</td>"   
                                       +"<td>"+data.Login+"</td>"
                                       +"<td>"+data.UserMode+"</td>"
                                       +"<td>"+data.TransType+"</td>"
                                       +"<td>"+data.Source+"</td>"
                                       +"<td>"+balance+"</td>"
                                       +"</tr>"
                                       +"</tbody>";
                                       $('#userdata2').html(tblRow);
                        });
                    }
               },
               error: function(XMLHttpRequest, e)
               {
                     alert(XMLHttpRequest.responseText);
                     if(XMLHttpRequest.status == 401)
                     {
                         window.location.reload();
                     }
               }
            });
           
    }
    
     function sendSiteIDz(str){
                var suppURL = 'process/ProcessManualAPIFulfillment.php';
                $.post(suppURL,{sendSiteIDz: str},
                function(data){
                    var terminal = $("#cmbterm");
                    $.each(data, function() {
                        terminal.append($("<option />").val(this.TerminalID).text(this.TerminalCode));
                    });
                }, "json");
            }
            
            //send terminal name to get providers list
            function sendterminalname(str){
                var suppURL = 'process/ProcessManualAPIFulfillment.php';
                $.get(suppURL, {cmbterminal: str},
                function(data){                    
                    $.each(data, function() {
                          jQuery("#txttermname").text(data.TerminalName);
                        
                    });
                }, "json");
            } 
    </script>
    <div id="pagetitle"><?php echo "$pagetitle";?></div>
        <br />
        <form method="post" action="" id="frmapps" name="frmapps">
            <input type="hidden" name="paginate" id="paginate" value="ViewSupport" />
            <input type="hidden" name="txtusername" id="txtusername" value="" />
            <input type="hidden" name="txtcasino" id="txtcasino" value="" />
            <input type="hidden" name="txtserviceid" id="txtserviceid" value="" />
            <input type="hidden" name="txtsource" id="txtsource" value="" />
            <input type="hidden" name="txttransrefid" id="txttransrefid" value="" />
            <input type="hidden" name="txttranstype" id="txttranstype" value="" />
            <input type="hidden" name="txtcardnumber" id="txtcardnumber" value="" />
            <input type="hidden" name="txtusermode" id="txtusermode" value="" />
            <table>
                <tr>
                    <td width="130px">Site / PEGS</td>
                    <td>
                    <?php
                        $vsite = $_SESSION['siteids'];
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
                             //removes Site HEad Office
                             if($vsiteID <> 1)
                             {
                               echo "<option value=\"".$vsiteID."\">".$vcode."</option>";  
                             }
                        }
                        echo "</select>";
                    ?>
                         <label id="txtsitename"></label>
                    </td>
                </tr>
                <tr>
                    <td>Terminals</td>
                    <td>
                        <select id="cmbterm" name="cmbterminal">
                            <option value="-1">Please Select</option>
                        </select>
                        <label id="txttermname"></label>
                    </td>
                </tr>
                
            </table>
            <div id="loading"></div>
            
            <div id="submitarea"> 
                <input type="button" value="Submit" id="btnSubmit"/>
            </div>
            <div id="light" class="white_page">
            <div class="close_popup" id="btnClose" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"></div>
            <p align="center" style="font-weight: bold;"> Please verify if the following information are correct </p>
            <input type="hidden" name="page" value="ManualAPIFulfillment" />
            <input type="hidden" name="txtsitecode" id="txtsitecode" />
            <input type="hidden" name="txtok" id="txtok" value="" />
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
                 <input type="button" value="Verify" id="btnVerify" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"/>
                    
                 <input type="button" value="Cancel" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';" />
            </div>        
        </div>
            <div id="fade" class="black_overlay"></div>
            
            <div id="light2" class="white_page">
            <div class="close_popup" id="btnClose" onclick="document.getElementById('light2').style.display='none';document.getElementById('fade2').style.display='none';"></div>
            <input type="hidden" name="page" value="ManualAPIFulfillment" />
            <input type="hidden" name="txttransstatus" id="txttransstatus" value="" />
            <input type="hidden" name="txtamount" id="txtamount" value="" />
            <input type="hidden" name="txtok" id="txtok" value="" />
            <div id="userdata3"></div>
            <p align="center" style="font-weight: bold;"> Do you want to continue ? </p>
            <br />
            <div align="right">
                 <input type="button" value="Proceed" id="btnProceed" onclick="document.getElementById('light2').style.display='none';document.getElementById('fade2').style.display='none';"/>
                    
                 <input type="button" value="Cancel" onclick="document.getElementById('light2').style.display='none';document.getElementById('fade2').style.display='none';" />
            </div>        
        </div>
            <div id="fade2" class="black_overlay"></div>
        </form>
     
           <div align="center">
             <table border="1" id="userdata">

             </table>
             <div id="pager2"></div>
          </div>
     
</div>
<?php  
    }
}
include "footer.php"; ?>

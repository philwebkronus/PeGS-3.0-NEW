<?php 
/**
 * Manual e-Wallet Fulfillment View
 * @date Febraury 3, 2015
 * @author Mark Kenneth Esguerra
 */
$pagetitle = "Manual e-Wallet Fulfillment";  
include "process/ProcessEwalletManualAPIFulfillment.php";
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
<script type="text/javascript">
    $(document).ready(function(){
        var url = "process/ProcessEwalletManualAPIFulfillment.php";
        $("#btnSubmit").live('click', function(e){
           e.preventDefault();
           
           $.ajax({
              url : url, 
              type : 'post', 
              dataType: 'json', 
              data : {
                  page : "CheckPendingTrans", 
                  cardnumber : $("#txtCardNumber").val()
              }, 
              success : function(data) {
                  if (data.ErrorCode == 0) {
                      getTransDetails(url, data.CardNumber, data.MID, data.Source);
                  }
                  else {
                      alert(data.Message);
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
    });
    
    $("#btnVerify").live("click", function(){
        var url = "process/ProcessEwalletManualAPIFulfillment.php";
        var retmessage;
        document.getElementById('loading').style.display='block';
        document.getElementById('fade2').style.display='block';
        $.ajax({
            url : url, 
            type : 'post', 
            dataType : 'json', 
            data: {
                   page: function(){return "VerifyCasino";},
                   txtcasino: function(){return jQuery("#txtcasino").val();},
                   txtcardnumber: function(){return jQuery("#txtcardnumber").val();}, 
                   txtewallettransid: function(){return jQuery("#txtewallettransid").val();},
                   txttranstype: function(){return jQuery("#txttranstype").val();},
                   txtserviceid: function(){return jQuery("#txtserviceid").val();}, 
                   txtsiteid: function(){return jQuery("#txt_site").val();}, 
                   txtterminalid: function(){return jQuery("#txt_terminal").val();}, 
                   txtsource: function(){return jQuery("#txtsource").val();}
            },
            success : function(data){
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
            }
        });
    });
    //on click function for Proceed button
    jQuery("#btnProceed").live("click", function()
    {
        document.getElementById('loading').style.display='block';
        document.getElementById('fade2').style.display='block';
        var url = 'process/ProcessEwalletManualAPIFulfillment.php';
        jQuery.ajax({
               url: url,
               type: 'post',
               data: {page: function(){return "Proceed";},
                   txtsite: function(){return jQuery("#txt_site").val();},
                   txtterminal: function(){return jQuery("#txt_terminal").val();},
                   txttransstatus: function(){return jQuery("#txttransstatus").val();},
                   txtsource: function(){return jQuery("#txtsource").val();},
                   txtewallettransid: function(){return jQuery("#txtewallettransid").val();},
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
    function getTransDetails (url, cardnumber, mid, source) {
        $.ajax({
           url : url, 
           type : 'post', 
           dataType : 'json', 
           data : {
               page : "GetTransDetails", 
               cardnumber : cardnumber, 
               mid : mid, 
               source : source
           }, 
           success : function(data) {
                if (data.ErrorCode == 0) {
                    var tblRow = "<thead>"
                           +"<tr>"
                           +"<th colspan='6' class='header'>Casino Fulfillment Information </th>"
                           +"</tr>"
                           +"<tr>"
                           +"<th>Casino</th>"
                           +"<th>Login</th>"
                           +"<th>Casino Mode</th>"
                           +"<th>Transaction Type</th>"
                           +"<th>Amount</th>"
                           +"<th>Source</th>"
                           +"</tr>"
                           +"</thead>";
                       document.getElementById('light').style.display='block';
                       document.getElementById('fade').style.display='block';
                       jQuery("#txtcasino").val(data.Casino);
                       jQuery("#txtserviceid").val(data.ServiceID);
                       jQuery("#txtewallettransid").val(data.EWalletTransID);
                       jQuery("#txttranstype").val(data.TransType);
                       jQuery("#txtamount").val(data.Amount);
                       jQuery("#txtcardnumber").val(data.CardNumber);
                       jQuery("#txtusername").val(data.UserName);
                       jQuery("#txt_site").val(data.SiteID);
                       jQuery("#txt_terminal").val(data.TerminalID);
                       jQuery("#txtsource").val(data.Source);
                       jQuery("#txtusermode").val(data.UserMode);
                       var balance = CommaFormatted(data.Amount);

                        tblRow +=
                                "<tbody>"
                                +"<tr>"
                                +"<td>"+data.Casino+"</td>"   
                                +"<td>"+data.Login+"</td>"
                                +"<td>"+data.UserMode+"</td>"
                                +"<td>"+data.TransType+"</td>"
                                +"<td>"+balance+"</td>"
                                +"<td>"+data.Source+"</td>"
                                +"</tr>"
                                +"</tbody>";
                                $('#userdata2').html(tblRow);   
               }
               else {
                   alert (data.Message);
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
</script>    
<div id="workarea">
       <div id="pagetitle"><?php echo "$pagetitle";?></div>
       <br />
       <form method="post" action="" id="frmapps" name="frmapps" class="frmmembership">
           <input type="hidden" name="paginate" id="paginate" value="ViewSupport" />
           <input type="hidden" name="txtusername" id="txtusername" value="" />
           <input type="hidden" name="txtcasino" id="txtcasino" value="" />
           <input type="hidden" name="txtserviceid" id="txtserviceid" value="" />
           <input type="hidden" name="txtewallettransid" id="txtewallettransid" value="" />
           <input type="hidden" name="txttranstype" id="txttranstype" value="" />
           <input type="hidden" name="txtcardnumber" id="txtcardnumber" value="" />
           <input type="hidden" name="txtusermode" id="txtusermode" value="" />
           <input type="hidden" name="txtsite" id="txt_site" value="" />
           <input type="hidden" name="txtterminal" id="txt_terminal" value="" />
           <input type="hidden" name="txtsource" id="txtsource" value="" />
           
           <label for="txtCardNumber">Card Number: &nbsp;</label>
           <input type="text" id="txtCardNumber" name="txtCardNumber" maxlength="30" size="30" onkeypress="return loyaltycardnumber(event);"/>
           <br /><br />
           <div id="submitarea"> 
                <input type="button" value="Submit" id="btnSubmit"/>
           </div>
       </form>
       
       <div id="loading"></div>
       
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

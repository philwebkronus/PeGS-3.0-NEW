<?php  
$pagetitle = "Manual Ending of Terminal Session";  
include 'process/ProcessTerminalMgmt.php';
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
            var url = 'process/ProcessAppSupport.php';
            
             //On Click function of Submit button    
            $('#btnOK').click(function()
            {
                showLoading();
                $.ajax({
                    url :  url, 
                    type : 'post', 
                    dataType : 'json', 
                    data : {
                        page : function () { return $("#txtsitepage").val(); }, 
                        cardnumber : function(){ return $("#txtCardnumber").val(); }
                    }, 
                    success: function(data)
                    {
                        hideLoading();
                        if(data == "8"){
                            alert("Migrated Temporary Card");
                        }
                        else{
                            var tblRow = "<thead>"
                                      +"<tr>"
                                      +"<th colspan='6' class='header'>Member Information </th>"
                                      +"</tr>"
                                      +"<tr>"
                                      +"<th>Member Name</th>"
                                      +"<th>Mobile No</th>"
                                      +"<th>Email Address</th>"
                                      +"<th>Birth Date</th>"
                                      +"<th>Casino</th>"
                                      +"<th>Login</th>"
                                      +"</tr>"
                                      +"</thead>";

                           $.each(data, function(i,user)
                           {
                               if(this.CardNumber == null)
                               {
                                   alert("Invalid Card Number");
                                   $('#light').hide();
                                   $('#fade').hide();
                               }
                               else
                               {
                                   if(this.MobileNumber == null){
                                       this.MobileNumber = '';
                                   }
                                   if(this.StatusCode == 9){
                                       alert("Card is Banned");
                                   }
                                   document.getElementById('light').style.display='block';
                                   document.getElementById('fade').style.display='block';


                                  tblRow +=
                                           "<tbody>"
                                           +"<tr>"
                                           +"<td>"+this.UserName+"</td>"   
                                           +"<td>"+this.MobileNumber+"</td>"
                                           +"<td>"+this.Email+"</td>"
                                           +"<td>"+this.Birthdate+"</td>"
                                           +"<td>"+this.Casino+"</td>"
                                           +"<td>"+this.Login+"</td>"
                                           +"</tr>"
                                           +"</tbody>";
                                           $('#userdata2').html(tblRow);

                                          }
                                     });
                                  }
                             },
                             error: function(XMLHttpRequest, e)
                             {
                                   hideLoading();
                                   alert(XMLHttpRequest.responseText);
                                   if(XMLHttpRequest.status == 401)
                                   {
                                       window.location.reload();
                                   }
                             }
                  });
            });
            
             //On Click function of Submit button    
            $('#btnEnd').live('click', function() {
                $("#light2").show();
                $("#fade2").show();
                $("#light3").hide();
            });
            
            $('#btnSubmit').click(function()
            {
                jQuery.ajax({
                        url: url,
                        type: 'post',
                        dataType: 'json',
                        data: {
                            page: function(){ return $("#txtprocess").val(); } ,
                            login: function(){ return $("#txtlogin").val(); }, 
                            terminal: function(){ return $("#txtterminal").val(); }, 
                            cardnumber: function(){ return $("#txt-card-number").val(); }
                        }, 
                        success: function(data){
                            alert(data.Message);
                            window.location.href = "manualterminalremove.php";
                        },
                        error: function(XMLHttpRequest, e){
                            alert(XMLHttpRequest.responseText);
                            if(XMLHttpRequest.status == 401)
                            {
                                window.location.reload();
                            }
                            window.location.href = "manualterminalremove.php";
                        }
                    });
                    $("#txtCardnumber").val('');
            });
            $("#btnVerify").live('click', function(){
                showLoading();
                $.ajax({
                   url : url, 
                   type : 'post', 
                   dataType : 'json', 
                   data : {
                        page : function() { return "CheckValidUB"; }, 
                        cardnumber : function() { return $("#txtCardnumber").val(); }
                   }, 
                   success : function(data) {
                       if (data.ErrorCode == 0) {
                            $("#txtlogin").val(data.Login);
                            $("#txtterminal").val(data.TerminalCode);
                            $("#txt-card-number").val(data.TerminalCode);
                            
                            var tblRow = "<thead>"
                                        +"<tr>"
                                        +"<th colspan='6' class='header'>Terminal Session Info </th>" 
                                        +"</tr>"
                                        +"<tr>"
                                        +"<th>Login</th>"
                                        +"<th>Card Number</th>"
                                        +"<th>Site</th>"
                                        +"<th>Terminal</th>";
                                        +"</tr>"
                                        +"</thead>";
                                tblRow +=
                                   "<tbody>"
                                   +"<tr>"
                                   +"<td>"+data.Login+"</td>"   
                                   +"<td>"+data.CardNumber+"</td>"
                                   +"<td>"+data.SiteCode+"</td>"
                                   +"<td>"+data.TerminalCode+"</td>"
                                   +"</tr>"
                                   +"</tbody>";
                            $('#userdata3').html(tblRow);
                            $("#light3").show();
                            $("#fade2").show();
                            $("#light").hide();
                            $("#fade").hide();
                       }
                       else {
                           alert(data.Message);
                           window.location.href = "manualterminalremove.php";
                       }
                       hideLoading();
                   }, 
                   error : function(XMLHttpRequest, e) {
                       alert(XMLHttpRequest.responseText);
                       if (XMLHttpRequest.status == 401) {
                           window.locaton.reload();
                       }
                   }
                });
            });
         });
    function showLoading() {
        $("#loading").show();
        $("#fade").show();
    }
    function hideLoading() {
       $("#loading").hide();
       $("#fade").hide();
    }
    </script>
    <div id="loading"></div> 
    <div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br/>
        <input type="hidden" name="txtsitepage" id="txtsitepage" value="CheckLoyaltyCard" />
        <form method="post" id="frmterminals" action="#" class="frmmembership">
            <input type="hidden" name="page" value="TerminalViews"/>
            <input type="hidden" name="oldterminaltype" id="oldterminaltype" value="" />
            <input type="hidden" name="terminaltype" id="terminaltype" value="" />
            Card Number: <input type="text" name="txtCardNumber" id="txtCardnumber" maxlength="30" size="30" onkeypress="return loyaltycardnumber(event);"/>
            <div id="submitarea">
                <input type="button" name="btnOK" id="btnOK" value="Submit" />
            </div> 
            
            <div id="light2" class="white_content" style="width: 370px; height:140px;">
                <br />
                <div align="center">
                  Are you sure you want to remove the session for this terminal?
                </div>
                <br />  
                <br />
                <br />
                <div align="right">
                    <input type="button" id="btnSubmit" value="YES" onclick="document.getElementById('light2').style.display='none';document.getElementById('fade2').style.display='none'" />

                    <input type="button" id="btnCancel" value=" NO" onclick="document.getElementById('light2').style.display='none';document.getElementById('fade2').style.display='none';" />
                </div>        
            </div>
            <div id="fade2" class="black_overlay"></div>
        </form>
        <div id="light" class="white_page">
            <div class="close_popup" id="btnClose" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"></div>
            <p align="center" style="font-weight: bold;"> Please verify if the following information are correct </p>
            <input type="hidden" name="process" id="txtprocess" value="RemoveTerminal"/>
            <input type="hidden" name="loginusername" id="txtlogin" />
            <input type="hidden" name="terminal" id="txtterminal" />
            <input type="hidden" name="cardnumber" id="txt-card-number" />
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
                 <input type="button" value="OK" id="btnVerify" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"/>

                 <input type="button" value="Cancel" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';" />
            </div>        
        </div>
        <div id="light3" class="white_page">
           <div id="showsessiontbl">
                <table id="userdata3" class="tablesorter" align="center">
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    <tr align="right">
                        <br />


                    </tr>
                </table>
            </div>
            <br />
            <div align="right">
                 <input type="button" value="End Session" id="btnEnd" name="btnEnd" />

                 <input type="button" value="Cancel" onclick="document.getElementById('light3').style.display='none';document.getElementById('fade2').style.display='none';" />
            </div>     
        </div>
        <div id="fade" class="black_overlay"></div>
    </div>
</div>
<?php  
    }
}
include "footer.php"; ?>

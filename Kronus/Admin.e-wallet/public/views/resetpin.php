<?php 
$pagetitle = "Reset Player PIN";  
include 'process/ProcessCSManagement.php';
include "header.php";

    $vaccesspages = array('18');
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
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <form method="post">
            <input type="hidden" name="page" value="ResetPin">
            <table>
                <tr>
                    <td>Card Number</td>
                    <td>
                    <input type="text" name="cardno" id="cardno" maxlength="30" size="30" onkeypress="return loyaltycardnumber(event);" />
                    </td>
                </tr>
            </table>
            
            <div id="submitarea">
                <input type="submit" value="Submit" id="btnSubmit"/>
            </div>

            <div id="light" class="white_page">
                <div class="close_popup" id="btnClose" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"></div>
                <p align="center" style="font-weight: bold;"> Please verify if the following user information are correct </p>
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
                     <input type="button" value="Submit" id="btnOK" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"/>

                     <input type="button" value="Cancel" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';" />
                </div>        
            </div>
            <div id="fade" class="black_overlay"></div>
            
        </form>
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){
        $('#light').hide();
        $('#light').hide();
        $('#fade').hide();
        $("#cardno").focus(function(){
            $("#cardno").bind('paste', function(event) {
                setTimeout(function(event) {
                    var data = $("#cardno").val();
                    if(!specialcharacter(data)){
                        $("#cardno").val("");
                        $("#cardno").focus();
                    }
                }, 0);
            });
        });
        
    jQuery('#btnSubmit').click(function(){
            if(document.getElementById('cardno').value == "")
           {
               alert("Please Enter Membership Card Number");
               jQuery('#gridwrapper').hide();
               return false;
           }
           else
           {
            showCardInfoTable();
            return false;
           }
            
            
        });
        
    jQuery('#btnOK').click(function(){
        var cardnumber = $("#cardno").val();
        jQuery.ajax({
            url: "process/ProcessCSManagement.php",
            type: 'post',
            data: {page: function(){ return "ResetPin";}, cardno: function(){return cardnumber;}},
            success: function(data){
                alert(data);
                $("#cardno").val("");
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
       });
        
    });

    function showCardInfoTable()
        {
            var cardnumber = jQuery("#cardno").val();
                //for displaying site / pegs information
                jQuery.ajax(
                {
                  url: "process/ProcessTopUpPaginate.php?action=getcardnumber",
                          type: 'post',
                          data: {cardnumber: function(){return cardnumber;},
                                 rstpin: function(){return "ResetPin"}
                          },
                          dataType: 'json',  
                   success: function(data)
                   {
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
                   },
                   error: function(XMLHttpRequest, e)
                   {
                         alert(XMLHttpRequest.responseText);
                         jQuery("#cardno").val("");
                         if(XMLHttpRequest.status == 401)
                         {
                             window.location.reload();
                         }
                   }
                });
        }

</script>

    <?php  
    }
}
include "footer.php"; ?>

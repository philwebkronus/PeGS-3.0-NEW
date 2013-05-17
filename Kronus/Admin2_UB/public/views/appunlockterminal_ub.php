<?php
$pagetitle = "Reset Casino Account User Based";
include 'process/ProcessAppSupport.php';
include 'header.php';
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
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <form method="post" action="process/ProcessAppSupport.php">
        <input type="hidden" name="page2" value="UnlockMGTerminalUB" />
        <input type="hidden" name="txtterminalcode" id="txtterminalcode" />
        <input type="hidden" name="txtsitecode" id="txtsitecode" />
        <br />
        <table>
            <tr>
                <td>Card Number</td>
                <td>
                    <input type="text" size="30"  id="txtcardnumber" name="txtcardnumber" value="" maxlength="30" size="30" onkeypress="return loyaltycardnumber(event);" />
                    <div for="txtcardnumber" align='center'>Membership | Temporary</div>
                </td>
            </tr>
            
        </table>
        <div id="loading"></div>
        <div id="submitarea"> 
                <input type="button" value="Submit" id="btnconfirm" ondblclick=""/>
            </div>
        
        <div id="light" class="white_page">
            <div class="close_popup" id="btnClose" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"></div>
            <p align="center" style="font-weight: bold;"> Please verify if the following user information are correct </p>
            
            <input type="hidden" name="page2" value="UnlockMGTerminalUB" />
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
            <tr>
                <td>Casino</td>
                <td>
                    <select id="cmbservices" name="cmbservices">
                    <option value="-1">Please Select</option>  
                    </select>
                    <label id="txttermname"></label>
                </td>
            </tr>
            <br />
            <input type="hidden" name="txtservicename" id="txtservicename" />
            <div align="right">
                 <input type="button" value="Reset" id="btnsubmit" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"/>
                    
                 <input type="button" value="Cancel" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';" />
            </div>        
        </div>
            <div id="fade" class="black_overlay"></div>
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        var url = 'process/ProcessAppSupport.php'; 
       
            //submit button event to display loyalty card info
            jQuery("#btnconfirm").click(function()
            {
                var cardnumber = jQuery("#txtcardnumber").val();
                          
                if((cardnumber.length < 1))
                {
                   alert("Please Input Membership Card Number");
                }
                else
                {
                    jQuery.ajax({
                            url: url,
                            type: 'post',
                            data: {page: function(){return "GetServices";},
                                txtcardnumber: function(){return jQuery("#txtcardnumber").val();}
                                   },
                            dataType: 'json',
                            success: function(data){
                                var terminal = $("#cmbservices");
                                $('#cmbservices').empty();
                                $('#cmbservices').append($("<option />").val("-1").text("Please Select"));
                                jQuery.each(data, function(){
                                    terminal.append($("<option />").val(this.ServiceID).text(this.ServiceName));
                                });
                                //terminal.append($("<option />").val("All").text("All"));
                                showCardInfoTable();
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
            
            //casino from membership info
            $('#cmbservices').live('change', function(){
                var name = jQuery("#cmbservices option:selected").text();
                jQuery("#txtservicename").val(name);
            });
            
            
            //submit button event to display loyalty card info
            jQuery("#btnsubmit").click(function()
            {
               
                var cardnumber = jQuery("#cmbservices").val();
        
                if((cardnumber < 1))
                {
                  alert("Please Select Casino");
                  showCardInfoTable();
                }
                else
                {
                     jQuery.ajax({
                                url: url,
                                type: 'post',
                                data: {page2: function() {return "UnlockMGTerminalUB";},
                                        cmbnewservice: function() {return $("#cmbservices").val();},
                                        txtservicename: function() {return $("#txtservicename").val();},
                                        txtcardnumber: function(){return jQuery("#txtcardnumber").val();}
                                      },
                                dataType : 'json',  
                                success : function(data)
                                {
                                     alert(data);
                                     $('#light').hide();
                                     $('#fade').hide();

                                },
                                error : function(XMLHttpRequest, e){
                                    alert(XMLHttpRequest.responseText);
                                }
                     });
                }
            });
    });
    
    function showCardInfoTable()
    {
        var url = 'process/ProcessAppSupport.php';
            //for displaying site / pegs information
            jQuery.ajax(
            {
               url: url,
               type: 'post',
               data: {page: function(){ return "GetLoyaltyCard";},
                      txtcardnumber: function(){return jQuery("#txtcardnumber").val();}
                     },
               dataType : 'json',     
               success: function(data)
               {

                      $.each(data, function(i,user)
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
                              alert("Reset Casino Account User Based: Invalid Card Number");
                              $('#light').hide();
                              $('#fade').hide();
                          }
                          else
                          {
                             if(this.MobileNumber == null)
                             {
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

                      });
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
</script>
<?php  
    }
}
include "footer.php"; 
?>
<?php
$pagetitle = "User Based Casino Back End Inquiry";  
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
    $(document).ready(function()
    {
        var url = "process/ProcessAppSupport.php";
        
        $("#btnSubmit").live('click', function()
        {
            showLoading();

            $.ajax(
            {
                url :  url, 
                type : 'post', 
                dataType : 'json', 
                data : 
                {
                    page : function () { return $("#txtsitepage").val(); }, 
                    cardnumber : function(){ return $("#txtubcard").val(); }
                }, 
                success: function(data)
                {
                    hideLoading();
                    if(data == "8")
                    {
                        alert("Migrated Temporary Card");
                    }
                    else
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
                                        +"</tr>"
                                    +"</thead>";

                        if(data[0].CardNumber == null)
                        {
                            alert("Invalid Card Number");
                            $('#light').hide();
                            $('#fade').hide();
                        }
                        else
                        {
                            if(data[0].MobileNumber == null)
                            {
                                data[0].MobileNumber = '';
                            }

                            if(data[0].StatusCode == 9)
                            {
                                alert("Card is Banned");
                            }
                            document.getElementById('light').style.display='block';
                            document.getElementById('fade').style.display='block';

                            tblRow +=
                               "<tbody>"
                                    +"<tr>"
                                        +"<td>"+data[0].UserName+"</td>"   
                                        +"<td>"+data[0].MobileNumber+"</td>"
                                        +"<td>"+data[0].Email+"</td>"
                                        +"<td>"+data[0].Birthdate+"</td>"
                                    +"</tr>"
                                +"</tbody>";
                                $('#userdata2').html(tblRow);
                        }

                        $("#showsessiontbl").hide();
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
            $("#showsessiontbl").hide();
        });
        
        $("#btnSubmit2").live("click", function()
        {
            $.ajax(
            {
                url :  url, 
                type : 'post', 
                dataType : 'json', 
                data : 
                {
                    page : function () { return "CheckIfExistingInBE"; }, 
                    //page : function () { return "CheckActiveSession"; }, 
                    ubcard : function(){ return $("#txtubcard").val(); }
                }, 
                success : function(data) 
                {
                    if (data.ErrorCode == 1) 
                    {
                        alert(data.Message);
                    }
                    else 
                    {
                        var tblRow = "<thead>"
                                        +"<tr>"
                                            +"<th colspan='3' class='header'>Casino Back End Information</th>"
                                        +"</tr>"
                                    +"</thead>";
                        tblRow += "<tr>"
                                    +"<th class='header'><center>Service</center></th>"
                                    +"<th class='header'><center>Login</center></th>"
                                    +"<th class='header'><center>Information</center></th>"
                                +"</tr>";
                        $.each(data, function(index, keys)
                        {
                            tblRow += 
                                +"<tr>" 
                                    +"<td align='left'>"+data[index].Service+"</td>"
                                    +"<td align='left'>"+data[index].Login+"</td>"
                                    +"<td align='left'>"+data[index].Info+"</td>"
                                +"</tr>";
                        });
                        $("#userdata3").html(tblRow);
                        $("#light").hide();
                        $("#fade").hide(); 
                        $("#showsessiontbl").show();
                    }
                }
            });
        });
    });
    
    function showLoading() 
    {
        $("#loading").show();
        $("#fade").show();
    }
    
    function hideLoading() 
    {
       $("#loading").hide();
       $("#fade").hide();
    }
    
</script>

<div id="loading"></div> 
<div id="workarea">
<div id="pagetitle"><?php echo $pagetitle; ?></div>
    <form action="#" method="post" class="frmcasinobeinfo">
        <br>
        <input type="hidden" name="txtsitepage" id="txtsitepage" value="CheckLoyaltyCard" />
        Card Number: <input type="text" name="txtubcard" id="txtubcard" maxlength="30" size="30" onkeypress="return loyaltycardnumber(event);"/>
        <div id="submitarea">
            <input type="button" name="btnSubmit" id="btnSubmit" value="Submit" />
        </div>    
    </form>
<div id="showsessiontbl" style="display:none">
    <table id="userdata3" class="tablesorter" align="center">
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr align="right">
    </tr>
    </table>   
</div>
<div id="light" class="white_page">
    <div class="close_popup" id="btnClose" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"></div>
    <p align="center" style="font-weight: bold;"> Please verify if the following information are correct </p>
    <table id="userdata2" class="tablesorter" align="center">
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr align="right"></tr>
    </table>
    <div align="right">
         <input type="button" value="OK" id="btnSubmit2" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';" />
         <input type="button" value="Cancel" id="btnCancel" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';" />
    </div>        
</div>   
<div id="fade" class="black_overlay" oncontextmenu="return false"></div>
</div> 
<?php
    }
}
include "footer.php"; ?>
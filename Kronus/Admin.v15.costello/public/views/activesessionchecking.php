<?php
$pagetitle = "Active Session Checking";
//include "process/processbatchterminals.php";
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
        var url = "process/ProcessAppSupport.php";
        $("#btnSubmit").live('click', function(){
           showLoading();
           $.ajax({
              url :  url, 
              type : 'post', 
              dataType : 'json', 
              data : {
                  page : function () { return $("#txtsitepage").val(); }, 
                  cardnumber : function(){ return $("#txtubcard").val(); }
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
        $("#btnSubmit2").live("click", function(){
                $.ajax({
                  url :  url, 
                  type : 'post', 
                  dataType : 'json', 
                  data : {
                      page : function () { return "CheckActiveSession"; }, 
                      ubcard : function(){ return $("#txtubcard").val(); }
                  }, 
                  success : function(data) {
                      if (data.ErrorCode == 1) {
                          alert(data.Message);
                      }
                      else {
                            var tblRow = "<thead>"
                                         +"<tr>"
                                         +"<th colspan='6' class='header'>"+data[0].CardNumber+"</th>"
                                         +"</tr>"

                                         +"</thead>";
                            tblRow += "<tr><td style='width: 250px;text-align:left'><b>Site</b></td>";
                            $.each(data, function(index, keys){
                                tblRow += 
                                      +"<tr>"
                                      +"<td>"+data[index].Site+"</td>";
                            });
                            tblRow += "</tr>";
                            tblRow += "<tr><td style='width: 250px;text-align:left'><b>Terminal</b></td>";
                            $.each(data, function(index, keys){
                                tblRow += 
                                      +"<tr>"
                                      +"<td>"+data[index].Terminal+"</td>";
                            });
                            tblRow += "</tr>";
                            tblRow += "<tr><td style='width: 250px;text-align:left'><b>Service</b></td>";
                            $.each(data, function(index, keys){
                                tblRow += 
                                      +"<tr>" 
                                      +"<td>"+data[index].Service+"</td>";
                            });
                            tblRow += "</tr>";
                            tblRow += "<tr><td style='width: 250px;text-align:left'><b>Date/Time</b></td>";
                            $.each(data, function(index, keys){
                                tblRow += 
                                      +"<tr>"
                                      +"<td>"+data[index].DateAndTime+"</td>";
                            });
                            tblRow += "</tr>";
                            tblRow += "<tr><td style='width: 250px;text-align:left'><b>Session Started In</b></td>";
                            $.each(data, function(index, keys){
                                tblRow += 
                                      +"<tr>"
                                      +"<td>"+data[index].SessionStartedIn+"</td>";
                            });
                            tblRow += "</tr>";
                            $("#userdata3").html(tblRow);
                            $("#light").hide();
                            $("#fade").hide(); 
                            $("#showsessiontbl").show();
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
<br />
    <form action="#" method="post" class="frmmembership">
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
            <br />


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
        <tr align="right">
            <br />


        </tr>
    </table>
    <br />
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

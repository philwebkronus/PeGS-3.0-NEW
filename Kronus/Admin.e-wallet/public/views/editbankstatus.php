<?php
$pagetitle = "Edit Bank Status";
include "process/ProcessBanks.php";
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
    $(document).ready(function(){
       var url = "process/ProcessBanks.php";
       $.ajax({
          url : url, 
          type : 'post', 
          data : {loadbanknames : true}, 
          success : function(data) {
              $("#cmbbanknames").html(data);
          }, 
          error: function(XMLHttpRequest, e){
               alert(XMLHttpRequest.responseText);
               if(XMLHttpRequest.status == 401)
               {
                   window.location.reload();
               }
           }
       });
       
       $("#cmbbanknames").live("change", function(){
          $.ajax({
             url : url, 
             type : 'post', 
             dataType : 'json', 
             data : {
                 sitepage : function() { return "GetBankStatus"; }, 
                 bankID : function () { return $("#cmbbanknames").val(); }
             }, 
             success : function(data) {
                 if (data.Success == 1) {
                    $("#cmbstatus").html("<option value=''>-Please Select-</option>");
                    $("#currstat").html(data.StringStat);
                    $("#currstatdlg").html(data.Option.O_StringStat);
                    $("#cmbstatus").append("<option value="+ data.Option.O_Status +">"+ data.Option.O_StringStat +"</option>");
                    $("#cmbstatus").removeAttr('disabled');   
                 }
                 else {
                    $("#currstat").html("");
                    $("#cmbstatus").attr('disabled', 'disabled');
                 }
             }, 
             error : function (XMLHttpRequest, e) {
                alert(XMLHttpRequest.responseText);
                if (XMLHttpRequest.status == 401) {
                    window.location.reload();
                }
             }
          });
       });
       
       $("#btnSubmit").live('click', function(e){
           e.preventDefault();
           var status = $("#cmbstatus").val();
           var bank = $("#cmbbanknames").val();
           
           if (bank !== "") {
               if (status !== "") {
                   $("#light2").show();
                   $("#fade").show();
               }
               else {
                   alert("Please enter status.");
               }
           }
           else {
               alert("Please select bank.");
           }
       });
       
       $("#btnYes").live('click', function(){
           $.ajax ({
              url : url, 
              type : 'post', 
              dataType : 'json', 
              data: {
                  sitepage : function () { return $("#txtsitepage").val(); }, 
                  bankID : function () { return $("#cmbbanknames").val(); }, 
                  status : function() { return $("#cmbstatus").val(); }
              }, 
              success : function(data) {
                   $("#light2").hide();
                   alert (data.Message);
                   window.location.href = "editbankstatus.php";
              }, 
              error : function (XMLHttpRequest, e) {
                   alert (XMLHttpRequest.responseText);
                   if (XMLHttpRequest.status == 401) {
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
<form action="process/ProcessBanks.php" id="frmupdatebankstat" method="post">
    <input type="hidden" name="page" id="txtsitepage" value="EditBankStatus" />
    <table>
        <tr>
            <td>
                Bank Name: 
            </td>    
            <td>
                <select name="cmbbankname" id="cmbbanknames">
                </select>&nbsp;&nbsp; <b><span id="currstat"></span></b>
            </td>
        </tr>  
        <tr>
            <td>
                Status: 
            </td>    
            <td>
                <select name="cmbstatus" id="cmbstatus" disabled>
                    <option value="">-Please Select-</option>
                </select>    
            </td>
        </tr>  
    </table>    
    <div id="submitarea"> 
        <input type="submit" value="Submit" id="btnSubmit" />
    </div> 
</form>   
<div id="light2" class="white_content" style="width: 370px; height:140px;">
    <br />
    <div align="center">
        Are you sure you want to change the bank status into <b><span id="currstatdlg"></span></b>?
    </div>
    <br />  
    <br />
    <br />
    <div align="right">
        <input type="button" id="btnYes" value="YES" />

        <input type="button" id="btnNo" value=" NO" onclick="document.getElementById('light2').style.display='none';document.getElementById('fade').style.display='none';"/>
    </div>        
</div>
<div id="fade" class="black_overlay" oncontextmenu="return false"></div>
</div>

<?php
    }
}
include "footer.php"; ?>

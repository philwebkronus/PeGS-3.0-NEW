<?php
$pagetitle = "Add Bank";
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
       
       $("#btnSubmit").live('click', function(){
           $.ajax ({
               url : url, 
               type : 'post', 
               dataType : 'json', 
               data : { 
                       sitepage : function(){ return "CheckInputValidity"; }, 
                       process : function() { return "AddBankInputs"; }, 
                       bankcode : function() { return $("#txtbankcode").val(); }, 
                       bankname : function() { return $("#txtbankname").val(); }, 
                       isaccredited : function () { return $("#isaccredited").val(); }
               },
               success : function(data) {
                   if (data.ErrorCode == 0) {
                       $("#msg").html(data.Message);
                       $("#light2").show();
                       $("#fade").show();
                   }
                   else {
                       alert(data.Message);
                   }
               }, 
               error : function(XMLHttpRequest, e) {
                   alert(XMLHttpRequest.responseText);
                   if (XMLHttpRequest.status == 401) {
                       window.location.reload();
                   }
               }
           });
           
       });
       $("#btnYes").live("click", function(){
           $.ajax ({
               url : url, 
               type : 'post', 
               dataType : 'json', 
               data : { 
                       sitepage : function(){ return $("#txtsitepage").val(); }, 
                       bankcode : function() { return $("#txtbankcode").val(); }, 
                       bankname : function() { return $("#txtbankname").val(); }, 
                       isaccredited : function () { return $("#isaccredited").val(); }
               },
               success : function(data) {
                   $("#light2").hide();
                   $("#fade").hide();
                   alert(data.Message);
                   if (data.ErrorCode == 0) {
                       window.location.href = "addbank.php";
                   }
               }, 
               error : function(XMLHttpRequest, e) {
                   alert(XMLHttpRequest.responseText);
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
<form action="process/ProcessBanks.php" id="frmaddbank" method="post">
    <input type="hidden" name="page" id="txtsitepage" value="AddBank" />
    <table>
        <tr>
            <td>
                Bank Code: 
            </td>    
            <td>
                <input type="text" name="txtbankcode" id="txtbankcode" onkeypress="return alphanumeric1(event);" />
            </td>
        </tr>   
        <tr>
            <td>
                Bank Name: 
            </td>
            <td>
                <input type="text" name="txtbankname" id="txtbankname" onkeypress="return numberandletter1(event);"/>
            </td>
        </tr>
        <tr>
            <td>
                Is Accredited: 
            </td>
            <td>
                <input type="checkbox" name="=chkaccredited" id="chkaccredited" value="1" />
            </td>
        </tr>    
    </table>    
    <div id="submitarea"> 
        <input type="button" value="Submit" id="btnSubmit" />
    </div>
</form>   
<div id="light2" class="white_content" style="width: 370px; height:140px;">
    <br />
    <div id="msg">
    </div>
    <br />  
    <br />
    <br />
    <div align="right">
        <input type="button" id="btnYes" value="YES" />

        <input type="button" id="btnNo" value="NO" onclick="document.getElementById('light2').style.display='none';document.getElementById('fade').style.display='none';"/>
    </div>        
</div>
<div id="fade" class="black_overlay" oncontextmenu="return false"></div>
</div>

<?php
    }
}
include "footer.php"; ?>

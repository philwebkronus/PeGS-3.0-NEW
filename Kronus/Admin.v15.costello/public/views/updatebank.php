<?php
$pagetitle = "Update Bank";
include "process/ProcessBanks.php";
if (isset($_SESSION['bank']['bank_id']))
{
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
                $bankid     = $_SESSION['bank']['bank_id'];
                $bankcode   = $_SESSION['bank']['bank_code'];
                $bankname   = $_SESSION['bank']['bank_name'];
                $checked    = $_SESSION['bank']['is_accredited'];
                unset($_SESSION['bank']);
?>
<script type="text/javascript">
    $(document).ready(function(){
       var url = "process/ProcessBanks.php";
       var currbankname = $("#txtbankname").val();
       $("#btnSubmit").live("click", function(){
            var ischecked = $("#chkaccredited").is(":checked") ? 1 : 0;
            
            $.ajax({
               url : url, 
               type : 'post', 
               dataType : 'json', 
               data : {
                   sitepage : function(){ return "CheckInputValidity"; }, 
                   process : function() { return "UpdateBankInputs"; }, 
                   txtbankid : function() { return $("#txtbankid").val(); },
                   txtbankcode : function() { return $("#txtbankcode").val(); }, 
                   txtbankname : function() { return $("#txtbankname").val(); }, 
                   isaccredited : function(){ return ischecked; }  
               }, 
               success : function(data){
                   if (data.ErrorCode == 0) {
                       $("#msg").html(data.Message + currbankname + "?");
                       $("#light2").show();
                       $("#fade").show();
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
       $("#btnYes").live("click", function(){
            var ischecked = $("#chkaccredited").is(":checked") ? 1 : 0;
            
            $.ajax({
               url : url, 
               type : 'post', 
               dataType : 'json', 
               data : {
                   txtbankid : function() { return $("#txtbankid").val(); },
                   txtbankcode : function() { return $("#txtbankcode").val(); }, 
                   txtbankname : function() { return $("#txtbankname").val(); }, 
                   isaccredited : function(){ return ischecked; }, 
                   sitepage : function() { return $("#txtsitepage").val(); }
               }, 
               success : function(data){
                   $("#light2").hide();
                   $("#fade").hide();
                   alert(data.Message);
                   if (data.ErrorCode == 0)
                        window.location.href = "viewbanks.php";
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
    
</script>    
<div id="workarea">
<div id="pagetitle"><?php echo $pagetitle; ?></div>
<br />
<form action="process/ProcessBanks.php" id="frmupdate" method="post">
    <input type="hidden" name="sitepage" id="txtsitepage" value="UpdateBank" />
    <input type="hidden" name="txtbankid" id="txtbankid" value="<?php echo $bankid; ?>" />
    <table>
        <tr>
            <td>
                Bank Code: 
            </td>    
            <td>
                <input type="text" name="txtbankcode" id="txtbankcode" value="<?php echo $bankcode; ?>" onkeypress="return alphanumeric1(event);" />
            </td>
        </tr>   
        <tr>
            <td>
                Bank Name: 
            </td>
            <td>
                <input type="text" name="txtbankname" id="txtbankname" value="<?php echo $bankname; ?>" onkeypress="return numberandletter1(event);" />
            </td>
        </tr>
        <tr>
            <td>
                Is Accredited: 
            </td>
            <td>
                <input type="checkbox" name="chkaccredited" id="chkaccredited" value="1" <?php echo $checked; ?> />
            </td>
        </tr>    
    </table>    
    <div id="submitarea"> 
        <input type="button" value="Update" id="btnSubmit" />
        <input type="button" value="Cancel" id="btnCancel" onclick="window.location.href = 'viewbanks.php'"/>
    </div>
</form>  
<div id="light2" class="white_content" style="width: 370px; height:140px;">
    <br />
    <div id="msg"></div>
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
    include "footer.php";
}
else {
    header("Location: viewbanks.php");
}

?>
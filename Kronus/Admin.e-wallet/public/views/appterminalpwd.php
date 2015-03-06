<?php
$pagetitle = "Change Terminal Password";
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
        <input type="hidden" name="txtterminalID" id="txtterminalID"/>
        <br />
        <table>
            <tr>
                <td>Terminal Code</td>
                <td>
                    <input type="text" name="txttcode" id="txttcode" maxlength="50" onkeypress="return alphanumeric2(event);" />
                </td>
            </tr>
            <tr>
                <td>Server</td>
                <td>
                    <select id="cmbnewservice" name="cmbnewservice">
                        <option value="-1">Please Select</option>
                    </select>
                </td>
            </tr>
        </table>        
        <div id="submitarea">
            <input type="button" id="btnsearch" value="Search" />
        </div>
        <div id="light" class="white_confirm">
            <input type="hidden" name="page2" value="ChangeTerminalPassword" />
            <input type="hidden" name="txtservice" id="txtservice" />
            <div class="close_popup close" id="btnClose" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"></div>
            <br /><br />
            <table>
                <tr>
                    <td>Old Terminal Password</td>
                    <td>
                        <input type="password" name="txtoldpwd" id="txtoldpwd" readonly="readonly"/>
                    </td>
                </tr>
                <tr>
                    <td>New Terminal Password</td>
                    <td>
                        <input type="password" name="txtnewpwd" id="txtnewpwd" maxlength="8" onkeypress="return numberandletter1(event);" />
                    </td>
                </tr>
                <tr>
                    <td>Confirm Password</td>
                    <td>
                        <input type="password" name="txtconfirmpass" id="txtconfirmpass"  maxlength="8" onkeypress="return numberandletter1(event);" />
                    </td>
                </tr>
            </table>
            <br />
            <input type="submit" value="OK" style="float: left;" onclick="return chkterminalpwd();"/>
            <input type="button" id="btnCancel" class="close" value="Cancel" style="float: right;" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';" />
        </div>
        <div id="fade" class="black_overlay"></div>
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var url = 'process/ProcessAppSupport.php';
        jQuery("#btnsearch").click(function(){
            var terminalID = jQuery("#txtterminalID").val();
            var serviceID = jQuery("#cmbnewservice").val();
            if(terminalID.length < 1)
            {
                alert("Please enter terminal code");
                return false;
            }
            else if(serviceID < 1)
            {
                alert("Please select service");
                return false;
            }
            else
            {
                jQuery.ajax({
                   url : url,
                   type : 'post',
                   data : {page2 : function(){ return 'GetTerminalCredentials'},
                           terminalID : function() { return terminalID;},
                           serviceID : function(){return serviceID;}
                          },
                   dataType : 'json',
                   success : function(data){
                        jQuery("#light").show();
                        jQuery("#fade").show();
                        jQuery("#txtoldpwd").val(data.ServicePassword);
                        jQuery("#txtterminalID").val(data.TerminalID);
                        var servicename = jQuery("#cmbnewservice").find("option:selected").text();
                        jQuery("#txtservice").val(servicename);
                   },
                   error : function (XMLHttpRequest, e)
                   {
                       alert(XMLHttpRequest.responseText);
                       if(XMLHttpRequest.status == 401)
                        {
                            window.location.reload();
                        }
                   }
                });
            }
        });
        
        jQuery("#txttcode").change(function(){
            var tcode = jQuery("#txttcode").val();
            jQuery("#cmbnewservice").empty();
            jQuery("#cmbnewservice").append(jQuery("<option />").val("-1").text("Please Select"));
            if(tcode.length > 0)
            {
                jQuery.ajax({
                   url : url,
                   type : 'post',
                   data : {page2 : function(){ return 'ViewTerminalPassword'},
                           terminalCode : function() { return tcode;}
                          },
                   dataType : 'json',
                   success : function(data){
                        jQuery("#txtterminalID").val(data.TerminalID);
                        getservices(url, data.TerminalID);
                   },
                   error : function (XMLHttpRequest, e)
                   {
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
                alert("Please enter terminal code");
                return false;
            }
        });
        
        jQuery(".close").click(function(){
            jQuery("#txtconfirmpass").val(null);
            jQuery("#txtnewpwd").val(null);
        });
        
        //disable enter key
        jQuery('body').bind('keypress', function (e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if(code == 13) { //Enter keycode
                e.preventDefault();
            }
        });
    });
    
    function getservices(url, terminalID)
    {
        var server = jQuery("#cmbnewservice");
        //this will display the current RTG Server of terminal
        jQuery.ajax({
           url: url,
           type: 'post',
           data: {terminalserver: function(){return terminalID;}},
           dataType: 'json',
           success: function (data)
           {
               jQuery.each(data, function()
               {  
                   server.append(jQuery("<option />").val(this.ServiceID).text(this.ServiceName));
               });
           }
       });
    }
</script>
<?php  
    }
}
include "footer.php"; ?>

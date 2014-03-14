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

<script type="text/javascript">
    jQuery(document).ready(function()
    {
        <?php if(isset ($_SESSION['createterminals'])): ?>
                 if (confirm('Do you want to create these terminal accounts?'))
                 {
                    $("#frmbatchpwd").submit();
                    return true;
                 }
        <?php endif; ?>
                
        var url = 'process/ProcessAppSupport.php';
        var maxterminals = '<?php echo $maxterminals; ?>';
        
        $('#tblterminals').find('.btnselect, #chosen, #cmbsite').attr('disabled', true);
        
        jQuery("#cmbsite").live('change', function()
        {
           jQuery("#txttermname").text(" ");
           $('#cmbterminal').empty();
           jQuery("#chosen").empty();
           jQuery("#txtsitename").text(" ");
           jQuery("#chosen").append($("<option />").val("temp").text("Make your choice on the left"));
           if(jQuery("#cmbsite").val() == "-1")
           {
               jQuery("#chosen").empty();
               jQuery("#txtsitename").text(" ");
               jQuery("#chosen").append($("<option />").val("temp").text("Make your choice on the left"));
           }
           else
           {
               //this will display the sitename
               jQuery.ajax({
                      url: url,
                      type: 'post',
                      data: {sitecredentials: function(){return jQuery("#cmbsite").val();}},
                      dataType: 'json',
                      success: function(data){
                          if(jQuery("#cmbsite").val() > 0)
                          {
                            jQuery("#txtsitename").text(data.SiteName);
                            jQuery("#txtsitecode").val(data.SiteCode);
                          }
                          else
                          {   
                            jQuery("#txtsitename").text(" ");
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

                //ajax: current services
                jQuery.ajax({
                    url : url,
                    type:'post',
                    data : {page2: function() {return 'GetProviders';}},
                    dataType: 'json',
                    success : function(data)
                    {
                        jQuery.each(data, function(){
                            var currentserver = jQuery("#cmbservices");
                            currentserver.append(jQuery("<option />").val(this.ServiceID).text(this.ServiceName));
                        });
                    }
                });

                 //this will display terminalcode
                jQuery.ajax({
                    url: url,
                    type: 'post',
                    data: {sendSiteID2: function(){return jQuery("#cmbsite").val();}},
                    dataType: 'json',
                    success: function(data){
                        var terminal = $("#cmbterminal");
                        jQuery.each(data, function(){
                            terminal.append($("<option />").val(this.TerminalCode).text(this.TerminalCode));
                        });
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
        
        $(".optpwd").click(function(){
            $('#tblterminals').find('.btnselect, #chosen, #cmbsite').attr('disabled', false);
        });
        
        $("#btnsubmit").click(function(){
              if(document.getElementById('cmbsite').value == "-1")
               {
                    alert("Please select Site/PEGS");
                    document.getElementById('cmbsite').focus();
                    return false;
               }
               if($("#chosen option").length > maxterminals){
                   $("#btnsubmit").hide();
                   $('#tblterminals').find('#btnchosen').attr('disabled', true);
                   alert("Maximum number of terminals has been reached");
                   return false;
               }
               if($('#chosen').val() == 'temp' || $('#chosen').text() == ''
                    || $('#chosen').text() == 'Make your choice on the left')
               {
                    alert("Please select terminal/s");
                    document.getElementById('chosen').focus();
                    return false;
               }
               else{
                   return true;
               }
        });
        
        $(".btnselect").click(function(){
           if($("#cmbterminal option:selected").length > maxterminals){
               alert("The allowed maximum number of terminals is 10");
               return false;
           }
           if($("#chosen option").length > maxterminals){
               $("#btnsubmit").hide();
               $('#tblterminals').find('#btnchosen').attr('disabled', true);
               alert("Maximum number of terminals has been reached");
               return false;
           }
           else{
               $('#tblterminals').find('#btnchosen').attr('disabled', false);
               return true;
           }
        });
        
        $('#btnfrom').click(function(){
           if($("#chosen option").length <= maxterminals){
               $('#tblterminals').find('#btnchosen').attr('disabled', false);
               $("#btnsubmit").show(); 
           }
        });
    });
</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <form method = "POST" onsubmit="allSelect()" name="forms" id="frmbatchpwd">
        <br/>
            <table>
                <tr>
                    <td><b>Please select a choice: </b></td>
                </tr
                <tr>
                    <td>
                        For Launchpad Deployment <input type="radio" class="optpwd" id="optlp" name="optpwd" value="1" />
                        Change Password <input type="radio" class="optpwd" id="optpwd" name="optpwd" value="0" />
                    </td>
                </tr>
            </table>
        <div id="tblterminals">
            <table>
                <input type="hidden" name="page2" value="ChangeTerminalPassword" />
                <input type="hidden" name="txtterminal" id="txtterminal" />
                <input type="hidden" id="txtservices" name="txtservices" />
                <input type="hidden" id="txtsitecode" name="txtsitecode" />
                <input type="hidden" name="txtservicegrp" id="txtservicegrp" />
                <tr>
                    <td>Site / PEGS</td>
                    <td>
                        <?php
                            $vsite = $_SESSION['siteids'];
                            echo "<select id=\"cmbsite\" name=\"cmbsite\">";
                            echo "<option value=\"-1\">Please Select</option>";

                            foreach ($vsite as $result)
                            {
                                 $vsiteID = $result['SiteID'];
                                 $vorigcode = $result['SiteCode'];

                                 //search if the sitecode was found on the terminalcode
                                 if(strstr($vorigcode, $terminalcode) == false)
                                 {
                                    $vcode = $vorigcode;
                                 }
                                 else
                                 {
                                   //removes the "icsa-"
                                   $vcode = substr($vorigcode, strlen($terminalcode));
                                 }
                                 //removes Site HEad Office
                                 if($vsiteID <> 1)
                                 {
                                   echo "<option value=\"".$vsiteID."\">".$vcode."</option>";  
                                 }
                            }
                            echo "</select>";
                        ?>
                             <label id="txtsitename"></label>
                    </td>
                </tr>
            </table>
            <table>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>Terminals:</td>
                    <td>
                        <select name="cmbterminal[]" id="cmbterminal" size="4" multiple="multiple" style="width: 200px;"></select>
                    </td>
                    <td align="center" style="width: 50px;padding-right: 30px;">
                       <input type="button" id="btnchosen" class="btnselect" onclick="javascript:copyToList('cmbterminal','chosen');" value="-->"/>
                       <br />
                       <input type="button" id="btnfrom" onclick="javascript:copyToList('chosen','cmbterminal');" value="<--"/>
                    </td>
                    <td align="left">
                        <select name="chosen[]" id="chosen" size="4" multiple="multiple" width="260" style="width: 260px;">
                            <option value="temp">Make your choice on the left</option>
                        </select>
                    </td>
                    <label id="txttermname"></label>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </div>
         <div id="submitarea">
            <input type="submit" value="Submit" id="btnsubmit"/>
        </div>
     </form> 
</div>
<?php  
    }
}
include "footer.php"; ?>
<?php
$pagetitle = "Reset Casino Account";
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
        <input type="hidden" name="page2" value="UnlockMGTerminal" />
        <input type="hidden" name="txtterminalcode" id="txtterminalcode" />
        <input type="hidden" name="txtsitecode" id="txtsitecode" />
        <br />
        <table>
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
            <tr>
                <td>Terminals</td>
                <td>
                    <select id="cmbterm" name="cmbterminal">
                       <option value="-1">Please Select</option>
                    </select>
                    <label id="txttermname"></label>
                </td>
            </tr>
            <tr>
                <td>Current Server</td>
                <td>
                    <select id="cmbnewservice" name="cmbnewservice">
                        <option value="-1">Please Select</option>
                    </select>
                </td>
            </tr>
        </table>
        <div id="submitarea">
            <input type="submit" value="Submit" id="btnsubmit" onclick="return  chkResetCasinoAcct();"/>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        var url = 'process/ProcessAppSupport.php'; 
        jQuery('#cmbsite').live('change', function()
        {
           jQuery('#cmbterm').empty();
           jQuery('#cmbterm').append($("<option />").val("-1").text("Please Select"));
           jQuery("#cmbnewservice").empty();
           jQuery("#cmbnewservice").append($("<option />").val("-1").text("Please Select"));
           
           if(jQuery('#cmbsite').val() == "-1")
           {
               jQuery("#txttermname").text(" ");
               jQuery("#txtsitename").text(" ");
           }
           else
           {
               //this will display the sitename
               jQuery.ajax({
                      url: url,
                      type: 'post',
                      data: {cmbsitename: function(){return jQuery("#cmbsite").val();}},
                      dataType: 'json',
                      success: function(data){
                          if(jQuery("#cmbsite").val() > 0)
                          {
                            jQuery("#txtsitename").text(data.SiteName);
                            jQuery("#txtsitecode").val(jQuery("#cmbsite").find("option:selected").text());
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

                //this will display terminalcode
                jQuery.ajax({
                    url: url,
                    type: 'post',
                    data: {getsiteterminals: function(){return jQuery("#cmbsite").val();}
                           },
                    dataType: 'json',
                    success: function(data){
                        var terminal = $("#cmbterm");
                        jQuery.each(data, function(){
                            terminal.append($("<option />").val(this.TerminalID).text(this.TerminalCode));
                        });
                        //terminal.append($("<option />").val("All").text("All"));
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
        
        $('#cmbterm').live('change', function()
        {   
           var server = jQuery("#cmbnewservice"); 
           var terminal = ($(this).find("option:selected").text());
           jQuery("#txtterminalcode").val(terminal);
           sendterminalname($(this).val());  
           server.empty();
           server.append(jQuery("<option />").val("-1").text("Please Select"));
           
            //this will display the current RTG Server of terminal
            jQuery.ajax({
                   url: url,
                   type: 'post',
                   data: {mgterminalserver: function(){return jQuery('#cmbterm').val();}},
                   dataType: 'json',
                   success: function (data)
                   {
                       jQuery.each(data, function()
                       {  
                           server.append(jQuery("<option />").val(this.ServiceID).text(this.ServiceName));
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
            jQuery("#cmbnewservice").removeAttr('disabled','disabled');
       });
    });
</script>
<?php  
    }
}
include "footer.php"; 
?>
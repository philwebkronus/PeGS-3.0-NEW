<?php
$pagetitle = "Switching of Servers";
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
        var url = 'process/ProcessAppSupport.php';
        
        jQuery("#cmbsite").live('change', function()
        {
           jQuery("#cmboldservice").empty();
           jQuery("#cmboldservice").append($("<option />").val("-1").text("Please Select"));
           jQuery("#txttermname").text(" ");
           $('#cmbterminal').empty();
           
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
                            jQuery("#txtpasscode").val(data.PassCode);
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
                            var currentserver = jQuery("#cmboldservice");
                            currentserver.append(jQuery("<option />").val(this.ServiceID).text(this.ServiceName));
                        });
                    }
                });

                
           }
        });
        
        jQuery("#cmboldservice").live('change', function()
        {    
             var server = ($(this).find("option:selected").text()); //check first if MG or RTG
             jQuery("#cmbnewservice").empty();
             jQuery("#cmbnewservice").append($("<option />").val("-1").text("Please Select"));
             jQuery("#txtoldserver").val(server);
             //if server was selected, disable its equivalent option on another combo box
            
            $('#cmbterminal').empty();
            $('#chosen').val("temp");
            //get terminals from a particular provider and site
            jQuery.ajax({
               url : url,
               type: 'post',
               data : {page2: function(){return 'GetTerminals';},
                       SiteID: function(){return jQuery("#cmbsite").find("option:selected").val();},
                       ServiceID: function(){return jQuery("#cmboldservice").find("option:selected").val();}
                      },
               dataType: 'json',
               success: function(data)
               {
                   var terminal = jQuery("#cmbterminal");
                   jQuery.each(data, function(){
                       terminal.append(jQuery("<option />").val(this.TerminalCode).text(this.TerminalCode));
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
            
            //ajax: rtg services only
            jQuery.ajax({
                url : url,
                type:'post',
                data : {page2: function() {return 'RTGServers';}, 
                        servicename : function () { return jQuery("#txtoldserver").val();}},
                dataType: 'json',
                success : function(data)
                {
                    jQuery.each(data, function(){
                        var newserver = jQuery("#cmbnewservice");
                        newserver.append(jQuery("<option />").val(this.ServiceID).text(this.ServiceName));
                    });
                }
            });
        });
        
        //Disables the current server from the selection
        jQuery("#cmbnewservice").live('click', function(){
             var textvalues = [];
             var server = jQuery("#txtoldserver").val(); //check first if MG or RTG
             
             $('#cmbnewservice option').each(function(i, selected)
             {
                textvalues[i] = $(selected).text();
                if(textvalues[i] == server)
                {
                    $(selected).attr("disabled", true);
                }
                else
                {
                    $(selected).attr("disabled", false);
                }
             });
        });
        
        jQuery("#cmbnewservice").live('change', function(){
            var newserver = (jQuery(this).find("option:selected").text());
            jQuery("#txtnewserver").val(newserver);
            //Get Service Group ID
            $.ajax({
                   url : url,
                   type : 'post',
                   data : {page2 : function(){ return 'GetServiceGroup'; },
                           serviceid : function() {return $('#cmbnewservice').find("option:selected").val(); }
                          },
                   dataType : 'json',
                   success : function (data){
                       $.each(data, function(){
                           $('#txtswitchgrp').val(this.ServiceGroupID);
                       });
                   },
                   error : function(XMLHttpRequest, e){
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
    <form method = "POST" onsubmit="allSelect()" name="forms">
        <br/>
            <TABLE>
                <input type="hidden" name="page2" value="ReAssignServer" />
                <input type="hidden" name="txtterminal" id="txtterminal" />
                <input type="hidden" id="txtnewserver" name="txtnewserver" />
                <input type="hidden" id="txtoldserver" name="txtoldserver" />
                <input type="hidden" id="txtsitecode" name="txtsitecode" />
                <input type="hidden" id="txtpasscode" name="txtpasscode" />
                <input type="hidden" id="txtswitchgrp" name="txtswitchgrp" />
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
                <tr>
                    <td>Current Server</td>
                    <td>
                       <select id="cmboldservice" name="cmboldservice" />
                            <option value="-1">Please Select</option>
                       </select>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>Terminals:</td>
                    <td>
                        <select id="cmbterminal" name="cmbterminal" size="4" multiple="multiple" style="width: 200px;"></select>
                    </td>
                    <td align="center" style="width: 50px;padding-right: 30px;">
                       <input type="button" onclick="javascript:copyToList('cmbterminal','chosen');" value="-->"/>
                       <br />
                       <input type="button" onclick="javascript:copyToList('chosen','cmbterminal');" value="<--"/>
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
                <tr>
                    <td>Change to:</td>
                    <td>
                        <select id="cmbnewservice" name="cmbnewservice">
                            <option value="-1">Please Select</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Remarks</td>
                    <td>
                        <input type="text" id="txtremarks" name="txtremarks" onkeypress="return numberandletter1(event);"/>
                    </td>
                </tr>
            </TABLE>
            <div id="submitarea">
                <input type="submit" value="Submit" onclick="return chkswitchserver();"/>
            </div>
     </form> 
</div>
<?php  
    }
}
include "footer.php"; ?>
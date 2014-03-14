<?php  
$pagetitle = "Terminal Mapping";  
include "process/ProcessTerminalMgmt.php";
include "header.php";

$vaccesspages = array('8');
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
        var url = 'process/ProcessTerminalMgmt.php';
        $('#cmbsitename').live('change', function()
        {
            jQuery("#txttermname").text(" ");
            //sendSiteID1($(this).val()); //populate combobox (ajax.js)
            $('#cmbterminals').empty();
            $('#cmbterminals').append($("<option />").val("-1").text("Please Select"));
             
             //this part is for displaying site name
             jQuery.ajax({
                  url: url,
                  type: 'post',
                  data: {cmbsitename: function(){return jQuery("#cmbsitename").val();}},
                  dataType: 'json',
                  success: function(data){
                      if(jQuery("#cmbsitename").val() > 0)
                      {
                        jQuery("#txtsitename").text(data.SiteName+" / ");
                        jQuery("#txtposaccno").text(data.POSAccNo);
                      }
                      else
                      {   
                        jQuery("#txtsitename").text(" ");
                        jQuery("#txtposaccno").text(" ");
                      }
                  }
             });
             
             //this part populates comboboxes of terminals that not yet mapped
             jQuery.ajax({
                 url : url,
                 type: 'post',
                 data: {cmbsite: function(){return jQuery("#cmbsitename").val();},
                        page: function(){return 'DisplayMGTerminals'}
                        },
                 dataType: 'json',
                 success: function(data)
                 {
                     var serviceID = "";
                     var terminals = jQuery("#cmbterminals");
                     jQuery.each(data, function(){
                         terminals.append(jQuery("<option />").val(this.TerminalID).text(this.TerminalCode));
                         serviceID = this.ServiceID;
                     });
                     jQuery("#txtservice").val(serviceID);
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
        $('#cmbterminals').live('change', function()
        {
            var terminal = $(this).find("option:selected").text();
            jQuery("#txttermcode").val(terminal);
            //get terminal name
            jQuery.ajax({
                    url: url,
                    type: 'post',
                    data: {cmbterminal: function(){return jQuery("#cmbterminals").val();}},
                    dataType: 'json',
                    success: function(data){
                        jQuery("#txttermname").text(data.TerminalName);
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
    <form method="post" action="process/ProcessTerminalMgmt.php">
        <input type="hidden" name="page" value="TerminalMapping" />
        <input type="hidden" id="txttermcode" name="txttermcode" />
        <input type="hidden" id="txtservice" name="txtservice" /> <!-- service ID, to be posted-->
        <table>
            <tr>
               <td width="130px">Site / PEGS </td>
               <td>
                <?php
                    $vsiteID = $_SESSION['siteids'];
                    echo "<select id=\"cmbsitename\" name=\"cmbsitename\">";
                    echo "<option value=\"-1\">Please Select </option>";
                    foreach ($vsiteID as $result)
                    {
                      $rsiteID = $result['SiteID'];
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
                        if($rsiteID <> 1)
                        {
                           echo "<option value=\"".$rsiteID."\">".$vcode."</option>";
                        }
                    }
                    echo "</select>";
                 ?>
                 <label id="txtsitename"></label><label id="txtposaccno"></label>
               </td>
            </tr>
            <tr>
               <td>Terminals</td>
               <td>
                   <select id="cmbterminals" name="cmbterminals">
                       <option value="-1">Please Select</option>
                   </select>
                   <label id="txttermname"></label>
               </td>
            </tr>
            <tr>
                <td>Service Terminals</td>
                <td>
                <?php
                    $vserviceTerminals = $_SESSION['assignedoc'];
                    echo "<select id=\"cmbserviceterms\" name=\"cmbserviceterms\">";
                    echo "<option value=\"-1\">Please select</option>";
                    foreach ($vserviceTerminals as $result)
                    {
                      $rservtermID = $result['ServiceTerminalID'];
                      $vname = $result['ServiceTerminalAccount'];
                      echo "<option value=\"".$rservtermID."\">".$vname."</option>";                              
                    }
                    echo "</select>";
                ?>
                </td>
            </tr>
        </table>
        <br />
        <div id="submitarea"> 
            <input type="submit" value="Assign OC to Terminal" onclick="return chkmapping();"/>
        </div>
    </form>
</div>
<?php  
    }
}
include "footer.php"; ?>
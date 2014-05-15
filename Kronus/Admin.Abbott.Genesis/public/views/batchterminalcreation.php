<?php
$pagetitle = "Batch Terminal Creation";
include "process/processbatchterminals.php";
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
    jQuery(document).ready(function(){
        var url = 'process/processbatchterminals.php';

        jQuery("#cmbsitename").live('change', function()
        { 
                if(jQuery("#cmbsitename").val() < 1){
                    jQuery("#txtsitename").text("");
                    jQuery("#txtposaccno").text("");
                }
                else{
                     //for displaying site name
                    jQuery.ajax({
                            url: url,
                            type: 'post',
                            data: {
                            cmbsitename: function(){return jQuery("#cmbsitename").val();}
                            },
                            dataType: 'json',
                            success:function(data){
                                    jQuery("#txtsitename").text(data.SiteName+" / ");
                                    jQuery("#txtposaccno").text(data.POSAccNo);
                                    jQuery('#txtsitecode').val(data.sitecode);
                                    jQuery('#txtpasscode').val(data.passcode);
                                    jQuery('#txtlastterm').val(data.lastterminal);
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

        jQuery("#btngenerate").click(function(){
                if(document.getElementById('cmbsitename').value == "-1")
                {
                        alert("Please select site name");
                        document.getElementById('cmbsitename').focus();
                        return false;
                }
                else if(document.getElementById('cmbterminals').value == "-1")
                {
                        alert("Please select number of terminals to be created");
                        document.getElementById('cmbterminals').focus();
                        return false;
                }
                else
                {
                        jQuery.ajax({
                                url: url,
                                type: 'post',
                                data: {page: function(){return "GenerateServers"}},
                                dataType: 'json',
                                success: function(data)
                                {
                                        var terminals = jQuery("#cmbterminals").val();
                                        var ctr;
                                        var tblRow = "";
                                        document.getElementById('light').style.display='block';
                                        document.getElementById('fade').style.display='block';
                                        tblRow += "<tr><td>Terminal</td><td id =\"mg\" style=\"text-align: center;\"> MG Servers</td>";
                                        tblRow += "<td id=\"pt\" style=\"text-align: center;\"> PT Servers</td>";
                                        tblRow += "<td id=\"rtg\" style=\"text-align: center;\"> RTG Servers</td></tr>";
                                        mgserver = 0;
                                        rtgserver = 0;
                                        ptserver = 0;

                                        for(ctr = 1; ctr <= terminals; ctr++ )
                                        {
                                                tblRow += "<tr><td>"+ctr+"</td>";

                                                jQuery.each(data, function()
                                                {
                                                        j = this.ServiceName.split('-');
                                                        j = this.ServiceName.split(' ');
                                                        
                                                        var id = j[0]+':'+this.ServiceID+':'+this.ServiceGroupID;

                                                        switch(this.ServiceGroupID){
                                                            case '2':
                                                                mgserver = mgserver + 1;
                                                                tblRow += "<td><input type=\"radio\" id=\"optserver["+ctr+"]\" name=\"optserver["+ctr+"]\" value="+id+" />"+this.ServiceName+"</td>";
                                                                break;
                                                            case '1':
                                                                rtgserver = rtgserver + 1;
                                                                tblRow += "<td><input type=\"radio\" id=\"optserver1["+ctr+"]\" name=\"optserver1["+ctr+"]\" value="+id+" />"+this.ServiceName+"</td>";
                                                                break;
                                                            case '4':
                                                                rtgserver = rtgserver + 1;
                                                                tblRow += "<td><input type=\"radio\" id=\"optserver1["+ctr+"]\" name=\"optserver1["+ctr+"]\" value="+id+" />"+this.ServiceName+"</td>";
                                                                break;    
                                                            case '3':
                                                                ptserver = ptserver + 1;
                                                                tblRow += "<td><input type=\"radio\" id=\"optserver2["+ctr+"]\" name=\"optserver2["+ctr+"]\" value="+id+" />"+this.ServiceName+"</td>";
                                                                break;
                                                        }
                                                });
                                                
                                                mgcolspan = mgserver/terminals;
                                                rtgcolspan = rtgserver/terminals;
                                                ptcolspan = ptserver/terminals;
                                                tblRow += "</tr>";
                                        }
                                        
                                        jQuery("#terminals").html(tblRow);
                                        jQuery("#mg").attr('colspan',mgcolspan);
                                        jQuery("#rtg").attr('colspan',rtgcolspan);
                                        jQuery("#pt").attr('colspan',ptcolspan);
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

    });
</script>

<div id="workarea">
<div id="pagetitle"><?php echo $pagetitle; ?></div>
<br />
<input type="hidden" name="lastterminal" id="lastterminal" value="GetLastTerminal" />
<form method="post" action="process/processbatchterminals.php" name="frmbatch">
<input type="hidden" name="page" type="page" value="BatchTerminalCreation" />
<input type="hidden" name="txtlastterm" id="txtlastterm" />
<input type="hidden" name="txtpasscode" id="txtpasscode" />
<input type="hidden" name="txtsitecode" id="txtsitecode" />
<div id="loading"></div>
<table>
<tr>
<td>Site / PEGS </td>
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
<td>No of Terminals</td>
<td>
<select id="cmbterminals" name="cmbterminals">
<option value="-1">Please Select</option>
<?php
                         for($x = 1; $x <= 10; $x++)
                         {
                           echo "<option value=\"$x\">".$x."</option>";
                         }
                        ?>
</select>
</td>
</tr>
</table>
<div id="submitarea">
<input type="button" value="Generate" id="btngenerate"/>
</div>
<div id="light" class="white_page" oncontextmenu="return false" style="margin-left: -90px; width: 900px;">
<div class="close_popup" id="btnClose" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"></div>
<br />
<table id="terminals" border="1" style="width: 100%; height: auto; border-collapse:collapse">

</table>
<div style="float: right;">
<br />
<input type="submit" value="Submit" onclick="return chkbatchterminal();"/>
</div>
</div>
<div id="fade" class="black_overlay" oncontextmenu="return false"></div>
</form>
</div>

<?php
    }
}
include "footer.php"; ?>

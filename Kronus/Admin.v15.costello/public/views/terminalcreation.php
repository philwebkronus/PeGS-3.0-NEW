<?php  
$pagetitle = "Terminal Creation";  
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
   jQuery(document).ready(function(){
      var url = 'process/ProcessTerminalMgmt.php'; 
      jQuery("#cmbsitename").live('change', function(){
         var data = {page: function(){return jQuery("#page").val();}}
         jQuery("#submit").attr("disabled", true);
        document.getElementById('loading').style.display='block';
         document.getElementById('fade2').style.display='block';
         jQuery.ajax({
             url: url,
             type: 'post',
             data: {page: function(){return jQuery("#page").val();},
                    cmbsitename: function(){return jQuery("#cmbsitename").val();}
                   },
             dataType: 'json',
             success:function(data){
                 jQuery('#txtcode').val(data.sitecode);
             },
             error: function(XMLHttpRequest, e){
                    alert(XMLHttpRequest.responseText);
                    if(XMLHttpRequest.status == 401)
                    {
                        window.location.reload();
                    }
             }
         });

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
                  jQuery("#txttermcode").val(data.TerminalID);
                  $('#loading').hide();
                document.getElementById('loading').style.display='none';
                document.getElementById('fade2').style.display='none';
                jQuery("#submit").attr("disabled", false);
              }
         });
      }); 
   });
</script>
<div id="workarea">

        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <input type="hidden" id="page" value="PostSiteCode" />
        <form method="post" action="process/ProcessTerminalMgmt.php" name="frmterminal" onsubmit="return chkterminalspaces();">
            <input type="hidden" name="page" value="TerminalCreation" />
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
<!--                <tr>
                    <td width="130px">Terminal Name</td>
                    <td>
                        <input type="text" id="txttermname" name="txttermname" maxlength="30" onkeypress="return alphanumeric1(event);"/>
                    </td>
                </tr>-->
                <tr>
                    <td>Terminal Code</td>
                    <td>
                        <input type="text" readonly="readonly" id="txtcode" name="txtcode" size="8"/>
                        <input type="text" readonly="readonly" id="txttermcode" name="txttermcode" maxlength="20" size="10" value="<?php //echo $_SESSION['lasttermID']; ?>" onkeypress="return numberandletter(event);" onblur="if((this.value < 10) && (this.value > 0) && (document.getElementById('txttermcode').value.indexOf('0') != 0)){this.value = '0' + (this.value).toString();}"/>
                    </td>
                </tr>
            </table>
            
            <div id="submitarea"> 
                <input disabled="true" type="submit" id="submit" value="Submit" onclick="return chkcreateterminal();"/>
            </div>
            <div id="loading" style="position: fixed; z-index: 5000; background: url('images/Please_wait.gif') no-repeat; height: 162px; width: 260px; margin: 50px 0 0 400px; display: none;"></div>
            <div id="fade2" class="black_overlay" oncontextmenu="return false"></div>
        </form>

</div>
<?php  
    }
}
include "footer.php"; ?>
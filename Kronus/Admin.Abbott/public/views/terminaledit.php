<?php
$pagetitle = "Edit Terminal Details";
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
            if((!isset($_SESSION['updterms'])))
            {
                echo "<script type='text/javascript'>window.location.href='terminalview.php';</script>";
            }
            else
            {
                $rterminal = $_SESSION['updterms'];

                foreach ($rterminal as $resultviews)
                {
                   $rterminalID = $resultviews['TerminalID'];
                   $rname = $resultviews['TerminalName'];
                   $rcode = $resultviews['TerminalCode'];
                   $rsiteID = $resultviews['SiteID'];
                }  
                $varrolddetails = array($rname, $rcode);
                $olddetails = implode(",", $varrolddetails);
            }
?>

<script type="text/javascript">
    jQuery(document).ready(function(){
         var url = 'process/ProcessTerminalMgmt.php';
         var data = {page: function(){return jQuery("#page").val();}}
         
         //get sitecode
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
             error: function(e){
                 alert(e.resposeText);
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
                },
                error: function(e){
                  alert(e.responseText);
                }
        });
    });
</script>
<div id="workarea">

        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <input type="hidden" id="page" value="PostSiteCode" />
        <form method="post" action="process/ProcessTerminalMgmt.php" name="frmterminal" onsubmit="return chkterminalspaces();">
            <input type="hidden" name="page" value="TerminalUpdateDetails" />
            <input type="hidden" name="terminalID" value="<?php echo $rterminalID; ?>" />
            <input type="hidden" name="oldsiteID" value="<?php echo $rsiteID; ?>" />
            <input type="hidden" name="txtolddetails" value="<?php echo $olddetails; ?>" />
            <table>
                <tr>
                    <td><input type="hidden" id="txttermname" name="txttermname" value="<?php echo $rname; ?>" maxlength="30" onkeypress="return alphanumeric1(event);" /></td>
                </tr>
                <tr>
                    <td>Site / PEGS </td>
                    <td>
                        <?php
                            $vsiteID = $_SESSION['siteids'];
                            echo "<select id=\"cmbsitename\" name=\"cmbsitename\">";
                            foreach ($vsiteID as $result)
                            {
                               $siteID = $result['SiteID'];
                               $vname = $result['SiteName'];
                               $vorigcode = $result['SiteCode'];

                               //search if the sitecode was found on the terminalcode
                               if(strstr($vorigcode, $terminalcode) == false)
                               {
                                  $vcode = $result['SiteCode'];
                               }
                               else
                               {
                                  //removes the "icsa-"
                                  $vcode = substr($vorigcode, strlen($terminalcode));
                               }

                               if($siteID  <> 1)
                               {
                                   if($rsiteID == $siteID)
                                   {
                                       echo "<option value=\"".$rsiteID."\" selected=\"selected\">".$vcode."</option>";
                                       $sitename = $vname;
                                   }
                               }
                            }
                            echo "</select>";
                        ?>
                        <label id="txtsitename"></label><label id="txtposaccno"></label>
                    </td>
                </tr>
                <tr>
                    <td>Terminal Code</td>
                    <td>
                        <input type="text" name="txtcode" id="txtcode" readonly="readonly" size="5"/>
                        <input type="text" id="txttermcode" readonly="readonly" name="txttermcode" value="<?php echo $rcode;?>" maxlength="20" size="10" onkeypress="return numberandletter(event);"/>
                    </td>
                </tr>
            </table>
            
            <div id="submitarea"> 
                <input type="button" value="Change Status" onclick="window.location.href='process/ProcessTerminalMgmt.php?termid=<?php echo $rterminalID; ?>'+'&terminalstatus='+'TerminalUpdate'"/>
            </div>
        </form>
</div>
<?php  
    }
}
include "footer.php"; ?>
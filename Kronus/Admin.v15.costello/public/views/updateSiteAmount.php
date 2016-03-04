<?php 
$pagetitle = "Update Site / PEGS Amount";  
include 'process/ProcessSiteManagement.php';
include "header.php";
$vviewsite = $_SESSION['viewsites'];
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
        if(isset($_SESSION['accID']))
        {
            $aid = $_SESSION['accID'];
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
    <script type="text/javascript">
        $(document).ready(function(){
            var url = 'process/ProcessSiteManagement.php';
            $('#cmbsite').live('change', function()
            {
                var site = document.getElementById('cmbsite').value;
                var data = $('#frmsites').serialize();
                
                //this part is for displaying site name
                jQuery.ajax({
                        url: url,
                        type: 'post',
                        data: {cmbsitename: function(){return jQuery("#cmbsite").val();}},
                        dataType: 'json',
                        success: function(data){
                              if(jQuery("#cmbsite").val() > 0)
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
            });
         });
    </script>
        
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <form method="post" id="frmupdateamt" action="process/ProcessSiteManagement.php">
            <input type="hidden" name="page" value="updateloadamt"/>
             <input type="hidden" name="AID" value="<?php echo $aid;?>"/>
             <table style="width: 500px">
                <tr>
                    <td width="280px">Site / PEGS</td>
                    <td>
                        <?php 
                        echo "<select id=\"cmbsite\" name=\"cmbsite\">";
                        echo "<option value=\"-1\">Please Select</option>";

                        foreach($vviewsite as $resultviews)
                        {
                            $vsiteID = $resultviews['SiteID'];      
                            $vorigcode = $resultviews['SiteCode'];
                            
                            //search if the sitecode was found on the terminalcode
                            if(strstr($vorigcode, $terminalcode) == false)
                            {
                               $vcode = $resultviews['SiteCode'];
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
                    </td>
                    <td width="280px">
                        <label id="txtsitename"></label> <label id="txtposaccno"></label>
                    </td>
                </tr>
                <tr>
                    <td width="280px">
                        <label>Load Amount Divisible by:</label> 
                    </td>
                    <td>
                        <input type="text" id="txtloadamt" name="txtloadamt" size="<?php echo $siteamtlength ?>" maxlength="<?php echo $siteamtlength ?>" onkeypress="return numberonly(event);"/>
                    </td>
                </tr>           
            </table>
           <div id="submitarea">
            <input type="submit" id="btnsubmit" value="Submit" onclick="return validateupdateloadamt();"/>
        </div>
        </form>
</div>
<?php  
    }
}
include "footer.php"; ?>

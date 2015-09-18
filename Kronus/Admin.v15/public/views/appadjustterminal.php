<?php 
$pagetitle = "Adjust Cashier Terminal";  
include 'process/ProcessAppSupport.php';
include "header.php";
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
        <input type="hidden" name="page2" value="AddCashierMachine" />
        <input type="hidden" id="txtsitecode" name="txtsitecode" />
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
                <td>Current No. of Cashier</td>
                <td>
                    <input type="text" readonly="readonly" size="2"  id="txtcurrent" name="txtcurrent" value="0"/>
                </td>
            </tr>
            <tr>
                <td>Adjust Cashier Count (+/-)</td>
                <td>
                    <input type="text" id="txtaddcashier" name="txtaddcashier" size="1" maxlength="3" />
                </td>
            </tr>
        </table>
        <div id="submitarea">
            <input type="submit" id="btnsubmit" value="Submit" onclick="return chkcashierterminal();"/>
        </div>
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var url = 'process/ProcessAppSupport.php';
        jQuery("#cmbsite").live('change', function()
        {
           var siteid = ($(this).find("option:selected").val());
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
            
            //this will display the total number of cashier per site
            jQuery.ajax({
               url : url,
               type : 'post',
               data : {page2: function(){return 'CashierMachineCount'},
                       siteid : function(){return siteid}
                      },
               dataType : 'json',
               success: function(data)
               {
                   if(data.CashierMachineCount > 0)
                   {
                      jQuery("#txtcurrent").val(data.CashierMachineCount);
                   }
                   else
                   {
                      jQuery("#txtcurrent").val(0);
                   }
               }
            });
            
        });
         $(function () {
                $('#txtaddcashier').keydown(function (e) {
                    if (e.altKey) {
                    e.preventDefault();
                    }
                    else if (e.shiftKey)
                    {
                        e.preventDefault();
                    }
                var key = e.keyCode;
                
                if (!((key == 8) ||(key==173)|| (key == 109) ||(key >= 48 && key <= 57) || (key >= 96 && key <= 105))) {
                e.preventDefault();
                }              
                });
             });
    });
</script>
<?php  
    }
}
include "footer.php"; 
?>

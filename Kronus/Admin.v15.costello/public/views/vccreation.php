<?php  
$pagetitle = "Manual Creation of Virtual Cashier";  
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
    <script type="text/javascript"> 
        $(document).ready(function(){
           $("#btnSubmit").live('click', function(e){
               e.preventDefault();
               var siteID = $("#cmbsite").val();
               var vctypegen = $("#chkgen").is(":checked");
               var vctypeesafe = $("#chkesafe").is(":checked");
               var prompt_result = "<thead>" + 
                                        "<tr>" + 
                                            "<th colspan='2' class='header'>Summary after Creation</th>" + 
                                        "</tr>" +
                                   "</thead>" + 
                                   "<tbody>";
               
               $.ajax({
                   url: 'process/ProcessAppSupport.php', 
                   type: 'post', 
                   dataType: 'json', 
                   data: {
                       siteID : siteID, 
                       vctypegen: vctypegen, 
                       vctypeesafe: vctypeesafe, 
                       page : function() {
                           return "CreateVirtualCashier";
                       }
                   }, 
                   success: function(data){
                       $.each(data, function(i, user) {
                           if (data[i].ErrorCode != 2) {
                               if (data[i].ErrorCode != 1) {
                                   prompt_result += "<tr>"+
                                                        "<td style='text-align:left'>" + data[i].VCType + "</td>" + 
                                                        "<td><b style='color:green'>Created</b></td>"
                                                    "</tr>";
                               }
                               else {
                                   prompt_result += "<tr>"+
                                                        "<td style='text-align:left'>" + data[i].VCType + "</td>" + 
                                                        "<td><b style='color:red'>Failed. "+ data[i].Message + "</b></td>";
                                                    "</tr>";
                               }
                               prompt_result += "</tbody>";
                               $("#userdata").html(prompt_result);
                               $("#light").show();
                               $("#fade").show();
                           }
                           else {
                               alert(data[i].Message);
                           }
                       });
                   }, 
                   error: function(XMLHttpRequest, e) {
                        alert(XMLHttpRequest.responseText);
                        if(XMLHttpRequest.status == 401)
                        {
                            window.location.href = 'login.php';
                        }
                   }
               });
           });
           $("#btnClose").live('click', function(){
               $('#cmbsite option').attr('selected', false);
               $("#chkgen").removeAttr('checked');
               $("#chkesafe").removeAttr('checked');
               
               $("#fade").hide();
               $("#light").hide();
           });
        });
    </script>
    <div id="loading"></div> 
    <div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br/>
        <input type="hidden" name="txtsitepage" id="txtsitepage" value="CreateVirtualCashier" />
        <form method="post" id="frmterminals" action="#" class="frmmembership">
            <input type="hidden" name="page" value="TerminalViews"/>
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
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Virtual Cashier: </td>
                    <td>
                        <input type="checkbox" value="1" class="vctype" id="chkgen" name="virtualcashier" /> <label for="chkgen">Genesis</label> &nbsp;&nbsp;&nbsp;
                        <input type="checkbox" value="2" class="vctype" id="chkesafe" name="virtualcashier" /> <label for="chkesafe">e-SAFE </label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </td>
                </tr>
            </table>
            <div id="submitarea">
                <input type="button" name="btnSubmit" id="btnSubmit" value="Submit" />
            </div>
        </form>
        <!------------------Summary after creation---------------->
        <div id="fade" class="black_overlay"></div>
        <div id="light" class="white_page">
            <table id="userdata" class="tablesorter" align="center">
            </table>
            <br />
            <div align="right">
                <input type="button" value="Close" id="btnClose" />
            </div>        
        </div>
        <!------------------------------------------------------->
    </div>
</div>
<?php  
    }
}
include "footer.php"; ?>

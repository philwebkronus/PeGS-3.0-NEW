<?php 
   $pagetitle = "Application Support"; 
   include "process/ProcessAppSupport.php";
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

<script type="text/javascript">
      $(document).ready(function(){
         $('#cmbsite').live('change', function(){
            cashiersiteID($(this).val()); //function: get cashier ID and name
            
            //clear the cashier dropdown box
            $('#cmbcashier').empty();
            $('#cmbcashier').append($("<option />").val("-1").text("Please Select"));
            
            //hide radio buttons
            $("#passkey").hide();
            
            var url = 'process/ProcessAppSupport.php';
            jQuery.ajax({
               url: url,
               type: 'post',
               data: {cmbsitename: function(){return jQuery("#cmbsite").val();}},
               dataType: 'json',
               success: function(data){
                   if(jQuery("#cmbsite").val() > 0)
                   {
                      jQuery("#txtsitename").text(data.SiteName);
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
         });
         
         $('#cmbcashier').live('change', function()
         {
             checkwithpasskey($(this).val()); //function: check if cashier have passkey or not
             var cashiername = ($(this).find("option:selected").text()); //get cashier name
             var passkey = $("#cmbcashier").val();
             document.getElementById('txtcashier').value = cashiername;
             if(passkey > 0){
               $("#passkey").show();    
             }
             else
              $("#passkey").hide();
         });
      });
      
      
      function getCheckedValue(radioObj) 
      {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
      }

</script>    
<div id="workarea">
     <div id="pagetitle">On/Off Passkey</div>
     <br />
     <form method="post" action= "process/ProcessAppSupport.php"  id="frmapps">
            <input type="hidden" name="page2" value="withpasskey" />
            <table>
                <tr>
                    <td width="130px">Site / PEGS</td>
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
                    <td>Cashier</td>
                    <td>
                        <select id="cmbcashier" name="cmbcashier">
                            <option value="-1">Please Select</option>
                        </select>
                        <input type="hidden" id="txtcashier" name="txtcashier" />
                    </td>
                </tr>                   
            </table>            
            <div id ="passkey" style="display: none;"> 
               <table>
                   <tr>
                       <td width="130px">With passkey?</td>                       
                       <td>Yes</td>
                       <td><input type="radio" name="optpasskey" id="optyes" value="1" /></td>
                       <td>No</td>
                       <td><input type="radio" name="optpasskey" id="optno"  value="0"  /></td>
                   </tr>    
                </table>   
                <div id="submitarea"> 
                      <input type="submit" class="btn" value="Submit" onclick="return chkoffpasskey();"/>
                </div>
            </div>
     </form>
</div>
<?php  
    }
}
include "footer.php"; ?>
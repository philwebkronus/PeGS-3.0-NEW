<?php 
$pagetitle = "Cashier URL Management";  
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

<div id="workarea">
        <script type="text/javascript">
      $(document).ready(function(){
          var url = 'process/ProcessAppSupport.php';
          
          $('#txtcashierversion').attr('disabled', 'disabled');
            
        $('#cmbsite').live('change', function(){
            $('#spyderno').attr('checked',false);
            $('#spyderyes').attr('checked',false);
            
            if($('#cmbsite').val() == '-1'){
               alert("Please Select Site");
            }
            else
            {
                jQuery.ajax({
                        url: url,
                        type: 'post',
                        data: {
                            cmbsitez: function(){return jQuery("#cmbsite").val();}},
                        dataType: 'json',
                        success: function(data){
                            $('#txtcashierversion').removeAttr('disabled');
                            $('#txtcashierversion').val(data);
                            $('#txtoldcashierversion').val(data);
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
             
             //On Click function of Submit button    
            $('#btnOK').click(function()
            {
                if(document.getElementById('cmbsite').value == "-1")
                {
                    alert("Please select site");
                    document.getElementById('cmbsite').focus();
                    return false;
                }
                else
                {
                document.getElementById('loading').style.display='none';
                document.getElementById('light2').style.display='block';
                document.getElementById('fade2').style.display='block';
                }
            });
             
            //On Click function of YES button    
            $('#btnSubmit').click(function()
            {
                    
                    jQuery.ajax({
                        url: url,
                        type: 'post',
                        data: {
                            page2: function(){return jQuery("#paginate").val();},
                            cmbsite: function(){return jQuery("#cmbsite").val();},
                            txtoldcversion: function(){return jQuery("#txtoldcashierversion").val();},
                            txtcversion: function(){return jQuery("#txtcashierversion").val();}},
                        
                        dataType: 'json',
                        success: function(data){
                                document.getElementById('cmbsite').value="-1";
                                document.getElementById('txtcashierversion').value="";
                                $('#txtcashierversion').attr('disabled', 'disabled');
                                alert(data);                           
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
    <div id="pagetitle"><?php echo "$pagetitle";?></div>
        <br />
        <form method="post" action="" id="frmapps" name="frmapps">
            <input type="hidden" name="paginate" id="paginate" value="UpCashierVersion" />
            <input type="hidden" name="txtoldcashierversion" id="txtoldcashierversion" value="" />
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
                    <td>Cashier Version</td>
                    <td>
                        <input type="text" size="5" id="txtcashierversion" name="txtcashierversion" value="" maxlength="5" size="5" onkeypress="return numberonly(event);" />
                    </td>
                </tr>
            </table>
            <div id="loading"></div>
            
            <div id="submitarea"> 
                <input type="button" value="Submit" id="btnOK"/>
            </div>
            
            <div id="light2" class="white_content" style="width: 370px; height:120px;">
            <br />
            <div align="center">
              Are you sure you want to replace
              the existing cashier version for this site ?
            </div>
            <br />
            <br />
            <div align="right">
                <input type="button" id="btnSubmit" value="YES" onclick="document.getElementById('light2').style.display='none';document.getElementById('fade2').style.display='none'" />
                
                <input type="button" id="btnCancel" value=" NO" onclick="document.getElementById('light2').style.display='none';document.getElementById('fade2').style.display='none'" />
            </div>        
            </div>
            <div id="fade2" class="black_overlay"></div>
        </form>     
</div>
<?php  
    }
}
include "footer.php"; ?>

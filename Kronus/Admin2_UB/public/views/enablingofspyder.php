<?php 
$pagetitle = "Enabling of Spyder";  
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
        $('input:radio').attr('disabled', 'disabled');
        
        $("input[name='spyderyes']").click(function()
        {
            $('#spyderno').attr('checked',false);
            $('#txtspyder').val('1');
        });
        
        $("input[name='spyderno']").click(function()
        {
            $('#spyderyes').attr('checked',false);
            $('#txtspyder').val('0');
        });     
            
        $('#cmbsite').live('change', function(){
            $('#spyderno').attr('checked',false);
            $('#spyderyes').attr('checked',false);
            
            if($('#cmbsite').val() == '-1'){
               $('input:radio').attr('disabled', 'disabled');
               jQuery("#txtsitename").text(" ");
            }
            else
            {
                jQuery.ajax({
                        url: url,
                        type: 'post',
                        data: {
                            cmbsites: function(){return jQuery("#cmbsite").val();}},
                        dataType: 'json',
                        success: function(data){
                            $.each(data, function(i,user)
                            {
                            $('#txtoldspyder').val(data);
                            $('#txtspyder').val(data);
                            if(jQuery('#txtoldspyder').val() > 0){
                                $('#spyderno').attr('checked',false);
                                $('#spyderyes').attr('checked',true);    
                            }
                            else{
                                $('#spyderno').attr('checked',true);    
                                $('#spyderyes').attr('checked',false);    
                            }
                            });
                            
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
                        },
                        error: function(XMLHttpRequest, e){
                            alert(XMLHttpRequest.responseText);
                            if(XMLHttpRequest.status == 401)
                            {
                                window.location.reload();
                            }
                        }
                });
                $('input:radio').removeAttr('disabled');
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
                            txtoldspyder: function(){return jQuery("#txtoldspyder").val();},
                            txtspyder: function(){return jQuery("#txtspyder").val();}},
                        dataType: 'json',
                        success: function(data){
                                $('input:radio').attr('disabled', 'disabled');
                                $('#txtoldspyder').val('');
                                $('#txtspyder').val('');
                                $('#spyderno').attr('checked',false);    
                                $('#spyderyes').attr('checked',false);
                                jQuery("#txtsitename").text(" ");
                                document.getElementById('cmbsite').value="-1";
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
            <input type="hidden" name="paginate" id="paginate" value="SpyderEnable" />
            <input type="hidden" name="txtoldspyder" id="txtoldspyder" value="" />
            <input type="hidden" name="txtspyder" id="txtspyder" value="" />
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
                    <td>Spyder Enabled</td>
                    <td>
                            Yes<input type="radio" id="spyderyes" name="spyderyes" value="1"/>
                            No<input type="radio" id="spyderno" name="spyderno" value="0"  />
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
              Do you want to proceed ?
            </div>
            <br />  
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

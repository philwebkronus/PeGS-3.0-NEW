<?php 
$pagetitle = "Cashier Menu Management";  
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
             var isChecked1=false;
             var isChecked2=false;
             $("#txtTMTab").val(0);
             $("#txtSRRTab").val(0);
             
             var oldTMVal=0;
             var oldSRRVal=0;
             
             $('input:radio').attr('disabled', 'disabled');
             
                $("input[name='tmTab']").click(function(){
                    
                    if(isChecked1==false)
                    {
                        $('#tmTab').attr('checked',true);
                        isChecked1=true;
                        $("#txtTMTab").val(1);
                    }
                    else
                    {
                        $('#tmTab').attr('checked',false);
                        isChecked1=false;
                        $("#txtTMTab").val(0);
                    }
                    
                });
                
                $("input[name='srrTab']").click(function(){
                    
                    if(isChecked2==false)
                    {
                        $('#srrTab').attr('checked',true);
                        isChecked2=true;
                        $("#txtSRRTab").val(1);
                    }
                    else
                    {
                        $('#srrTab').attr('checked',false);
                        isChecked2=false;
                        $("#txtSRRTab").val(0);
                    }
                    
                 
                });
                
    $("#cmbsite").change(function(){
                
                $('#tmTab').attr('checked',false);
                $('#srrTab').attr('checked',false);

                    if($('#cmbsite').val() == '-1')
                    {
                       $('input:radio').attr('disabled', 'disabled');
                       jQuery("#txtsitename").text(" ");
                    }else
                    {
                        jQuery.ajax({
                           url:url,
                           type: 'post',
                           dataType: 'json',
                           data:{cashierTab:function(){return $("#cmbsite").val();}},
                           success:function(data){
                              
                              if(data['TMTab']==1)
                              {
                                   $('#tmTab').attr('checked',true);
                                   $("#txtTMTab").val(1);
                                   isChecked1=true; 
                                   oldTMVal = 1;
                              }
                              if(data['SRRTab']==1)
                              {
                                  $('#srrTab').attr('checked',true);
                                  $("#SRRTab").val(1);
                                  isChecked2=true;
                                  oldSRRVal = 1;
                              }
                              
                              $('input:radio').removeAttr('disabled'); 
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
               }        
        });
        
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
            
            $('#btnSubmit').click(function()
            {
                    
                    jQuery.ajax({
                        url: url,
                        type: 'post',
                        data: {
                            page2: function(){return jQuery("#paginate").val();},
                            cmbsite: function(){return jQuery("#cmbsite").val();},
                            tmTab: function(){return jQuery("#txtTMTab").val();},
                            srrTab: function(){return jQuery("#txtSRRTab").val();},
                            oldTM:function(){return oldTMVal;},
                            oldSRR:function(){return oldSRRVal;}},
                        dataType: 'json',
                        success: function(data){
                                $('input:radio').attr('disabled', 'disabled');
                                $('#txtTMTab').val(0);
                                $('#txtSRRTab').val(0);
                                $('#tmTab').attr('checked',false);    
                                $('#srrTab').attr('checked',false);
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
            <input type="hidden" name="paginate" id="paginate" value="UpdateCashierTab" />
            <input type="hidden" name="txtTMTab" id="txtTMTab" value="" />
            <input type="hidden" name="txtSRRTab" id="txtSRRTab" value="" />
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
                    <td>Menu</td>
                    <td>
                            TM<input type="radio" id="tmTab" name="tmTab" value="1"/>
                            STAND ALONE<input type="radio" id="srrTab" name="srrTab" value="0"  />
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

<?php  
$pagetitle = "Update Terminal Classification";  
include 'process/ProcessTerminalMgmt.php';
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

<div id="workarea">

    <script type="text/javascript"> 
        $(document).ready(function(){
            var url = 'process/ProcessTerminalMgmt.php';
            
            $('input:radio').attr('disabled', 'disabled');
        
            $("input[name='terminaltype2']").click(function()
            {
                $('#terminaltype1, #terminaltype0').attr('checked',false);
                $('#terminaltype').val('2');
            });    
        
            $("input[name='terminaltype1']").click(function()
            {
                $('#terminaltype0, #terminaltype2').attr('checked',false);
                $('#terminaltype').val('1');
            });

            $("input[name='terminaltype0']").click(function()
            {
                $('#terminaltype1, #terminaltype2').attr('checked',false);
                $('#terminaltype').val('0');
            });
            
        
            $('#cmbsitename').live('change', function()
            {
                var site = document.getElementById('cmbsitename').value;
                if(site > 0)
                {
                    $("#cmbterminals").empty();
                    $("#cmbterminals").append($("<option />").val("-1").text("Please Select"));
                    
                    //this will display terminalcode
                jQuery.ajax({
                    url: 'process/ProcessAppSupport.php',
                    type: 'post',
                    data: {sendSiteID2: function(){return jQuery("#cmbsitename").val();}},
                    dataType: 'json',
                    success: function(data){
                        var terminal = $("#cmbterminals");
                        jQuery.each(data, function(){
                            terminal.append($("<option />").val(this.TerminalID).text(this.TerminalCode));
                        });
                        //terminal.append($("<option />").val("All").text("All"));
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
                else
                {
                    $('input:radio').attr('disabled', 'disabled');
                    $('#cmbterminals').empty();
                    $('#cmbterminals').append($("<option />").val("-1").text("Please Select"));
                }
                
                jQuery("#txttermname").text("");
                
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
                      error: function(XMLHttpRequest, e){
                        alert(XMLHttpRequest.responseText);
                        jQuery("#txtusername").val(" ");
                        jQuery("#userdata").hide();
                        if(XMLHttpRequest.status == 401)
                        {
                            window.location.reload();
                        }
                      }
                 });
            });

            $('#cmbterminals').live('change', function(){
                jQuery("#txttermname").text("");
                $("#userdata tbody").html("");
                $('#terminaltype0').attr('checked',false);
                $('#terminaltype1').attr('checked',false);
                $('#terminaltype2').attr('checked',false);
            
                var terminal = document.getElementById('cmbterminals').value;
                
                if(terminal > 0)
                {
                    $.ajax({
                       url : url,
                       type : 'post',
                       data : {paginate: function(){return 'GetTerminalType';},
                       cmbsitename: function(){return jQuery("#cmbsitename").val();},
                       cmbterminals: function(){return jQuery("#cmbterminals").val();}
                       },
                       dataType : 'json',
                       success : function(data){
                           $.each(data, function(i,user){
                               
                            $('#oldterminaltype').val(this.TerminalType);
                            $('#terminaltype').val(this.TerminalType);
                            
                            if (this.TerminalType == 0){
                                $('#terminaltype0').attr('checked',true);
                                $('#terminaltype1, #terminaltype2').attr('checked',false);
                            }
                            else if (this.TerminalType == 1){
                                $('#terminaltype0, #terminaltype2').attr('checked',false);
                                $('#terminaltype1').attr('checked',true);
                            }
                            else {
                                $('#terminaltype0, #terminaltype1').attr('checked',false);
                                $('#terminaltype2').attr('checked',true);
                            }
                           }
                           )
                       },
                       error : function(e) {
                           alert("All records are selected");
                       }
                    });
                    $('input:radio').removeAttr('disabled');
                }
                else{
                    $('input:radio').attr('disabled', 'disabled');
                    jQuery("#txtsitename").text(" ");
                }
                
                //this part is for displaying terminal name
                jQuery.ajax({
                        url: url,
                        type: 'post',
                        data: {cmbterminal: function(){return jQuery("#cmbterminals").val();}},
                        dataType: 'json',
                        success: function(data){
                            jQuery("#txttermname").text(data.TerminalName);
                        }
                });
            });
            
            
             //On Click function of Submit button    
            $('#btnSubmit').click(function()
            {
                if(document.getElementById('cmbsitename').value == "-1")
                {
                    alert("Please select site");
                    document.getElementById('cmbsitename').focus();
                    return false;
                }
                else if(document.getElementById('cmbterminals').value == "-1"){
                    alert("Please select terminal");
                    document.getElementById('cmbterminals').focus();
                    return false;
                }
                else
                {
                    jQuery.ajax({
                            url: url,
                            type: 'post',
                            data: {
                                paginate: function(){return jQuery("#paginate").val();},
                                cmbterminals: function(){return jQuery("#cmbterminals").val();},
                                oldterminaltype: function(){return jQuery("#oldterminaltype").val();},
                                terminaltype: function(){return jQuery("#terminaltype").val();}},
                            dataType: 'json',
                            success: function(data){
                                    $('input:radio').attr('disabled', 'disabled');
                                    $('#oldterminaltype').val('');
                                    $('#terminaltype').val('');
                                    $('#terminaltype0').attr('checked',false);    
                                    $('#terminaltype1').attr('checked',false);
                                    jQuery("#txtsitename").text(" ");
                                    jQuery("#txtposaccno").text(" ");
                                    jQuery("#txttermname").text(" ");
                                    document.getElementById('cmbsitename').value="-1";
                                    $('#cmbterminals').empty();
                                    $('#cmbterminals').append($("<option />").val("-1").text("Please Select"));
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
                }
            });
            
         });
    </script>
    
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        
        <br/><br/>
        <input type="hidden" name="paginate" id="paginate" value="UpdateTerminalType"/>
            <form method="post" id="frmterminals" action="#">
                <input type="hidden" name="page" value="TerminalViews"/>
            <input type="hidden" name="oldterminaltype" id="oldterminaltype" value="" />
            <input type="hidden" name="terminaltype" id="terminaltype" value="" />
                <table>
                    <tr>
                        <td width="130px">Site / PEGS </td>
                        <td>
                            <?php

                                array_key_exists("siteids", $_SESSION) ? $sitesList = $_SESSION['siteids'] : $siteList = array();
                                $vsiteID = $sitesList;
                                echo "<select id=\"cmbsitename\" name=\"cmbsitename\">";
                                echo "<option value=\"-1\">Please Select</option>";

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
                        <td>Terminals</td>
                        <td>
                            <select id="cmbterminals" name="cmbterminals">
                                <option value="-1">Please Select</option>
                            </select>
                            <label id="txttermname"></label>
                        </td>
                    </tr>
                    <tr>
                    <td>Terminal Type</td>
                    <td>
                            Regular<input type="radio" id="terminaltype0" name="terminaltype0" value="0"/>
                            EGM<input type="radio" id="terminaltype1" name="terminaltype1" value="1"  />
                            e-SAFE<input type="radio" id="terminaltype2" name="terminaltype2" value="2"  />
                    </td>
                </tr>
                </table>
                <div id="submitarea"> 
                <input type="button" value="Submit" id="btnSubmit"/>
                </div>
            </form>
        
</div>
<?php  
    }
}
include "footer.php"; ?>

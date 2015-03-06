<?php  
$pagetitle = "Update Terminal Profile";  
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
            $('#cmbsitename').live('change', function()
            {
                var site = document.getElementById('cmbsitename').value;
                if(site > 0)
                {
                    $('#pagination').show();
                    loadterminals(url);
                    sendSiteID1($(this).val());
                    $('#results').hide();
                    $('#cmbterminals').empty();
                    $('#cmbterminals').append($("<option />").val("-1").text("All"));
                }
                else
                {
                    $('#pagination').hide();
                    $('#results').hide();
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
                var terminal = document.getElementById('cmbterminals').value;

                if(terminal > 0)
                {
                    var data = $('#frmterminals').serialize();
                    $('#pagination').hide();
                    $('#results').show();
                    $.ajax({
                       url : url,
                       type : 'post',
                       data : data,
                       dataType : 'json',
                       success : function(data){
                               var tblRow = "<thead>"
                                            +"<tr>"
                                            +"<th colspan='4' class='header'>Terminals</th>"
                                            +"</tr>"
                                            +"<tr>"
                                            +"<th>Terminal Name</th>"
                                            +"<th>Terminal Code</th>"
                                            +"<th>Status</th>"
                                            +"<th>Action</th>"
                                            +"</tr>"
                                            +"</thead>";

                            $.each(data, function(i,user){
                               var rstatus = this.Status;
                               var rmessage;

                                if(rstatus == 1)
                                    rmessage = "Active";
                                else
                                    rmessage = "Inactive";
                                tblRow +=
                                            "<tbody>"
                                            +"<tr>"
                                            +"<td>"+this.TerminalName+"</td>"
                                            +"<td>"+this.TerminalCode+"</td>"
                                            +"<td align='center'>"+rmessage+"</td>"
                                            +"<td align='center' style='width: 50px;'><input type=\"button\" value=\"Edit Details\" onclick=\"window.location.href='process/ProcessTerminalMgmt.php?termid="+terminal+"&page1='+'ViewTerminal'\"/></td>"
                                            +"</tr>"
                                            +"</tbody>";

                                            $('#userdata').html(tblRow);
                            });
                       },
                       error : function(e) {
                           alert("All records are selected");
                       }
                    });
                }
                else
                {
                    loadterminals(url);
                    $('#pagination').show();
                    $('#results').hide();
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
         });
         function loadterminals(url)
         {
             $('#useraccs').GridUnload();
             jQuery("#useraccs").jqGrid({
                            url: url,
                            mtype: 'post',
                            postData: {
                                    paginate: function() {return $("#paginate").val();},
                                    cmbsitename: function() {return $('#cmbsitename').val();}
                                      },
                            datatype: "json",
                            colNames:['Terminal Name','Terminal Code', 'Status', 'Action'],
                            colModel:[
                                    {name:'TerminalName',index:'TerminalName', width:170, align: 'center'},
                                    {name:'TerminalCode',index:'TerminalCode', width:200, align: 'center'},
                                    {name:'Status',index:'Status', width:100, align: 'center'},
                                    {name:'button', index: 'button', width:200, align: 'center'}
                            ],

                            rowNum:10,
                            rowList:[10,20,30],
                            height: 280,
                            width: 1200,
                            pager: '#pager2',
                            sortname: 'TerminalName',
                            viewrecords: true,
                            sortorder: "asc",
                            caption:"Terminals"
                    });
                jQuery("#useraccs").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false, view: false});
                $('#useraccs').trigger("reloadGrid");
         }
    </script>
    
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        
        <br/><br/>
        <input type="hidden" name="paginate" id="paginate" value="TerminalsPage"/>
            <form method="post" id="frmterminals" action="#">
                <input type="hidden" name="page" value="TerminalViews"/>
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
                </table>
            </form>
        
        <div align="center" id="pagination">
            <table border="1" id="useraccs">

            </table>
            <div id="pager2"></div>
        </div>

        <div id="results" style="display: none;">
              <table id="userdata" class="tablesorter">

              </table>
        </div>
        
</div>
<?php  
    }
}
include "footer.php"; ?>

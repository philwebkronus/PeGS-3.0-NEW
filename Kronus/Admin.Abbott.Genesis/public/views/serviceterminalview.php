<?php  
$pagetitle = "Update MG OC Account Status";  
include "process/ProcessTerminalMgmt.php";
include "header.php";
$vviewagents = $_SESSION['serviceterminals'];
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

<script type="text/javascript">
        $(document).ready(function(){
            var url = 'process/ProcessTerminalMgmt.php';
            $('#cmbusername').live('change', function(){
                $("#userdata tbody").html("");
                var terminal = document.getElementById('cmbusername').value;
                if(terminal > 0)
                    {
                        $('#pagination').hide();
                        $('#results').show();
                    }
                else{
                        $('#pagination').show();
                        $('#results').hide();
                    }

                var data = $('#frmusername').serialize();
                $.ajax({
                   url : url,
                   type : 'post',
                   data : data,
                   dataType : 'json',
                   success : function(data){
                           var tblRow = "<thead>"
                                        +"<tr>"
                                        +"<th colspan='5' class='header'>Provider Terminals</th>"
                                        +"</tr>"
                                        +"<tr>"
                                        +"<th>Service Terminal ID</th>"
                                        +"<th>User Name</th>"
                                        +"<th>Agent</th>"
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
                                        +"<td>"+this.ServiceTerminalID+"</td>"
                                        +"<td>"+this.ServiceTerminalAccount+"</td>"
                                        +"<td>"+this.Username+"</td>"
                                        +"<td align='center'>"+rmessage+"</td>"
                                        +"<td align='center' style='width: 50px;'><input type=\"button\" value=\"Change Status\" onclick=\"window.location.href='process/ProcessTerminalMgmt.php?stermid="+terminal+"&updtermpage=UpdateSTerminalStatus'\"/></td>"
                                        +"</tr>"
                                        +"</tbody>";
                                        $('#userdata').html(tblRow);
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
            });
            jQuery("#useraccs").jqGrid({
                            url: url,
                            mtype: 'post',
                            postData: {
                                    paginate: function() {return $("#paginate").val();}
                                      },
                            datatype: "json",
                            colNames:['Service Terminal ID','User Name', 'Agent', 'Status', 'Action'],
                            colModel:[
                                    {name:'ServiceTerminalID',index:'ServiceTerminalID', width:100, align: 'center'},
                                    {name:'ServiceTerminalAccount',index:'Username', width:150, align: 'left'},
                                    {name:'Agent',index:'Agent', width:150, align: 'left'},
                                    {name:'Status',index:'Status', width:100, align: 'center'},
                                    {name:'button', index: 'button', width:70, align: 'center'}
                            ],

                            rowNum:10,
                            rowList:[10,20,30],
                            height: 280,
                            width: 1200,
                            pager: '#pager2',
                            sortname: 'ServiceTerminalAccount',
                            viewrecords: true,
                            sortorder: "asc",
                            caption:"Service Terminals"
                    });
             jQuery("#useraccs").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false});
         });
</script>
<div id="workarea">

        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <input type="hidden" name="paginate" id="paginate" value="ViewServiceAccount"/>
        <form method="post" id="frmusername" action="#">
            <input type="hidden" name="page" id="page" value="ViewServiceAccount"/>
             <table>
                <tr>
                   <td width="120px">MG OC Account</td>
                   <td>
                    <?php
                        echo "<select id=\"cmbusername\" name=\"cmbusername\">";
                        echo "<option value=\"-1\">All</option>";
                        foreach($vviewagents as $resultviews)
                        {
                            $vstermID = $resultviews['ServiceTerminalID'];
                            $vname = $resultviews['ServiceTerminalAccount'];
                            echo "<option value=\"".$vstermID."\">".$vname."</option>";
                        }

                        echo "</select>";
                    ?>
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

<?php 
$pagetitle = "Update Site / PEGS Profile";  
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
                $("#userdata tbody").html("");
                
                //load/unload grid upon change of combo box
                if(site > 0)
                    {
                        $('#pagination').hide();
                        $('#useraccs').trigger("GridUnload");
                        $('#results').show();
                    }
                else{
                        $('#pagination').show();
                        $('#useraccs').trigger("reloadGrid");
                        $('#results').hide();
                    }
                
                //for displaying single information of a particular site
                $.ajax(
                {
                   url : url,
                   type : 'post',
                   data : data,
                   dataType : 'json',
                   success : function(data)
                   {
                         var tblRow = "<thead>"
                                    +"<tr>"
                                    +"<th colspan='7' class='header'>Sites / PEGS </th>"
                                    +"</tr>"
                                    +"<tr>"
                                    +"<th>SiteID</th>"
                                    +"<th>Site / PEGS Name</th>"
                                    +"<th>Site / PEGS Code</th>"
                                    +"<th>Site / PEGS Description</th>"
                                    +"<th>Site / PEGS Address</th>"
                                    +"<th>Status</th>"
                                    +"<th>Action</th>"
                                    +"</tr>"
                                    +"</thead>";
                                        
                         $.each(data, function(i,user){
                            var rstatus = this.Status;
                            var rmessage;
                            switch(rstatus){
                                case '1':
                                     rmessage = "Active";
                                break;
                                case '0':
                                     rmessage = "Inactive";
                                break;
                                case '2':
                                    rmessage = "Suspended";
                                break;
                                case '3':
                                    rmessage = "Deactivated";
                                break;
                                default:
                                     rmessage = "Invalid Status";
                                break;
                            }
                                
                            tblRow +=
                                        "<tbody>"
                                        +"<tr>"
                                        +"<td>"+this.SiteID+"</td>"
                                        +"<td>"+this.SiteName+"</td>"
                                        +"<td>"+this.SiteCode+"</td>"
                                        +"<td>"+this.SiteDescription+"</td>"
                                        +"<td>"+this.SiteAddress+"</td>"
                                        +"<td align='center'>"+rmessage+"</td>"
                                        +"<td align='center' style='width: 100px;'><input type=\"button\" value=\"Update Details\" onclick=\"window.location.href='process/ProcessSiteManagement.php?siteid="+site+"&page=ViewSite'\"/></td>"
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
            jQuery("#useraccs").jqGrid(
            {
                            url:url,
                            mtype: 'post',
                            postData: {
                                    sitepage: function() {return $("#sitepage").val();}
                                      },
                            datatype: "json",
                            colNames:['SiteID','Site / PEGS Name','Site / PEGS Code', 'POSAccountNo', 'Site / PEGS Description', 'Site / PEGS Address', 'Status', 'Action'],
                            colModel:[
                                    {name:'SiteID', index:'SiteID', align:'left'},
                                    {name:'SiteName',index:'SiteName', width:170, align: 'left'},
                                    {name:'SiteCode',index:'SiteCode', width:140, align: 'left'},
                                    {name:'POS', index:'POS', width:140, align: 'center'},
                                    {name:'SiteDescription',index:'SiteDescription', width:230, align: 'left'},
                                    {name:'SiteAddress',index:'SiteAddress', width:250, align: 'left'},
                                    {name:'Status',index:'Status', width:75, align: 'center'},
                                    {name:'button', index: 'button', width:120, align: 'center'}
                            ],

                            rowNum:10,
                            rowList:[10,20,30],
                            height: 280,
                            width: 1200,
                            pager: '#pager2',
                            sortname: 'SiteID',
                            viewrecords: true,
                            sortorder: "asc",
                            caption:"Site / PEGS"
            });
            jQuery("#useraccs").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false});
         });
    </script>
        
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <input type="hidden" name="sitepage" id="sitepage" value="Paginate"/>
        <form method="post" id="frmsites" action="#">
            <input type="hidden" name="page" value="SiteView"/>
            <table>
                <tr>
                    <td width="100px">Site / PEGS</td>
                    <td>
                        <?php 
                        echo "<select id=\"cmbsite\" name=\"cmbsite\">";
                        echo "<option value=\"-1\">All</option>";

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
                    <td>
                        <label id="txtsitename"></label> <label id="txtposaccno"></label>
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

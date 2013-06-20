<?php 
$pagetitle = "Casino Services Profile Management";  
include 'process/ProcessCasinoMgmt.php';
include "header.php";
$vviewservcgrp = $_SESSION['servicegrpid'];
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
            var url = 'process/ProcessCasinoMgmt.php';
            $('#cmbservicegrp').live('change', function()
            {
                var site = document.getElementById('cmbservicegrp').value;
                var data = $('#frmsites').serialize();
                $("#userdata tbody").html("");
                
                //load/unload grid upon change of combo box
                if(site > 0)
                    {
                        $('#pagination').show();
                        $('#useraccs').trigger("reloadGrid");
                        jQuery.ajax({
                                    url: url,
                                    type: 'post',
                                    data: {page : function (){ return 'sendServiceGroupID';},
                                    cmbservicegrp: function() {return $('#cmbservicegrp').val();}},
                                    dataType : 'json',
                                    success : function(data){
                                        jQuery.each(data, function(){
                                           var service = $("#cmbservice");
                                           service.append($("<option />").val(this.ServiceID).text(this.ServiceName));
                                        });
                                    },
                                    error : function(XMLHttpRequest, e)
                                    {
                                        alert(XMLHttpRequest.responseText);
                                        if(XMLHttpRequest.status == 401)
                                        {
                                            window.location.reload();
                                        }
                                    }
                               });
                        $('#cmbservice').empty();
                        $('#cmbservice').append($("<option />").val("-1").text("All"));
                        $('#results').hide();
                        
                    }
                  
                else{
                        $('#pagination').show();
                        $('#useraccs').trigger("reloadGrid");
                        $('#results').hide();
                        $('#cmbservice').empty();
                        $('#cmbservice').append($("<option />").val("-1").text("All"));
                    }
                
                //for displaying single information of a particular casino service
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
                                    +"<th colspan='10' class='header'>Services </th>"
                                    +"</tr>"
                                    +"<tr>"
                                    +"<th>Service ID</th>"
                                    +"<th>Service Name</th>"
                                    +"<th>Service Alias</th>"
                                    +"<th>Service Code</th>"
                                    +"<th>Service Description</th>"
                                    +"<th>Mode</th>"
                                    +"<th>Status</th>"
                                    +"<th>Casino</th>"
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
                            }
                                
                            tblRow +=
                                        "<tbody>"
                                        +"<tr>"
                                        +"<td>"+this.ServiceID+"</td>"   
                                        +"<td>"+this.ServiceName+"</td>"
                                        +"<td>"+this.Alias+"</td>"
                                        +"<td>"+this.Code+"</td>"
                                        +"<td>"+this.ServiceDescription+"</td>"
                                        +"<td>"+this.UserMode+"</td>"
                                        +"<td>"+this.ServiceGroupName+"</td>"
                                        +"<td>"+rmessage+"</td>"
                                        +"<td align='center' style='width: 100px;'><input type=\"button\" value=\"Edit Details\" onclick=\"window.location.href='process/ProcessCasinoMgmt.php?serviceid="+this.ServiceID+"&page=ViewService'\"/></td>"
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
                })
                });
                
                $('#cmbservice').live('change', function(){
                $("#userdata tbody").html("");
                var terminal = document.getElementById('cmbservice').value;

                if(terminal > 0)
                {
                    var data = $('#frmsites').serialize();
                    $('#pagination').hide();
                    $('#results').show();            
                }
               
                else
                {
                    var data = $('#frmsites').serialize();
                    $('#pagination').hide();
                    $('#results').show();                         
                }
                
                $.ajax({
                       url : url,
                       type : 'post',
                       data : data,
                       dataType : 'json',
                       success : function(data){
                               var tblRow = "<thead>"
                                    +"<tr>"
                                    +"<th colspan='10' class='header'>Services </th>"
                                    +"</tr>"
                                    +"<tr>"
                                    +"<th>Service ID</th>"
                                    +"<th>Service Name</th>"
                                    +"<th>Service Alias</th>"
                                    +"<th>Service Code</th>"
                                    +"<th>Service Description</th>"
                                    +"<th>User Mode</th>"
                                    +"<th>Status</th>"
                                    +"<th>Casino</th>"
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
                                        +"<td>"+this.ServiceID+"</td>"   
                                        +"<td>"+this.ServiceName+"</td>"
                                        +"<td>"+this.Alias+"</td>"
                                        +"<td>"+this.Code+"</td>"
                                        +"<td>"+this.ServiceDescription+"</td>"
                                        +"<td>"+this.UserMode+"</td>"
                                        +"<td>"+rmessage+"</td>"
                                        +"<td>"+this.ServiceGroupName+"</td>"
                                        +"<td align='center' style='width: 100px;'><input type=\"button\" value=\"Edit Details\" onclick=\"window.location.href='process/ProcessCasinoMgmt.php?serviceid="+this.ServiceID+"&page=ViewService'\"/></td>"
                                        +"</tr>"
                                        +"</tbody>";

                                        $('#userdata').html(tblRow);
                            });
                       },
                       error : function(e) {
                           alert("All records are selected");
                       }
                    })
          });
            
            
            jQuery("#useraccs").jqGrid(
            {
                            url:url,
                            mtype: 'post',
                            postData: {
                                    servicepage: function() {return $("#servicepage").val();},
                                    cmbservicegrp: function() {return $('#cmbservicegrp').val();}
                                      },
                            datatype: "json",
                            colNames:['ServiceID','Service Name', 'Service Alias', 'Service Code', 'Service Description','Mode' ,'Status','Casino', 'Action'],
                            colModel:[
                                    {name:'ServiceID', index:'ServiceID', align:'center'},
                                    {name:'ServiceName',index:'ServiceName', width:140, align: 'left'},
                                    {name:'Alias', index:'Alias', width:140, align: 'center'},
                                    {name:'Code',index:'Code', width:120, align: 'center'},
                                    {name:'ServiceDescription',index:'ServiceDescription', width:300, align: 'left'},
                                    {name:'UserMode', index:'UserMode', width:140, align: 'center'},
                                    {name:'Status',index:'Status', width:75, align: 'center'},
                                    {name:'ServiceGroupName',index:'ServiceGroupName', width:120, align: 'center'},
                                    {name:'button', index: 'button', width:150, align: 'center'}
                            ],

                            rowNum:10,
                            rowList:[10,20,30],
                            height: 280,
                            width: 1200,
                            pager: '#pager2',
                            sortname: 'ServiceID',
                            viewrecords: true,
                            sortorder: "asc",
                            caption:"Services"
            });
            jQuery("#useraccs").jqGrid('navGrid','#pager2',{edit:false,add:false,del:false, search:false, view: false});
                $('#useraccs').trigger("reloadGrid");
         });
    </script>
        
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        <input type="hidden" name="servicepage" id="servicepage" value="Paginate"/>
        <form method="post" id="frmsites" action="#">
            <input type="hidden" name="page" value="ServiceView"/>
            <table>
                <tr>
                    <td width="100px">Casino</td>
                    <td>
                        <?php 
                        
                        echo "<select id=\"cmbservicegrp\" name=\"cmbservicegrp\">";
                                echo '<option value=\"-1\">All</option>';

                        
                       
                                
                        foreach($vviewservcgrp as $resultviews)
                        {
                            $vserviceID = $resultviews['ServiceGroupID'];      
                            $vserviceName = $resultviews['ServiceGroupName'];


                        
                              echo "<option value=\"".$vserviceID."\">".$vserviceName."</option>";
                            
                        }
                        echo "</select>";
                        ?>
                    </td>
                    
                </tr>
                <tr>
                        <td>Services</td>
                        <td>
                            <select id="cmbservice" name="cmbservice">
                                <option value="-1"> All </option>
                            </select>
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

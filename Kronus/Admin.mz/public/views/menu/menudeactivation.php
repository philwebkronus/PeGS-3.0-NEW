<?php
$pagetitle = 'Menu Deactivation';
//include '../process/ProcessMenuMaintenance.php';
include 'menuheader.php';

if(isset ($_SESSION['menudet']))
{
    $vmenudet = $_SESSION['menudet'];
    echo "<script type=\"text/javascript\">
          jQuery(document).ready(function(){
          document.getElementById('light').style.display='block';
          document.getElementById('fade').style.display='block';
          });
          </script>";
    unset($_SESSION['menudet']);
}
?>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var url = '../process/ProcessMenuMaintenance.php';
            //ajax: menu dropdown box
           jQuery.ajax({
                url: url,
                type: 'post',
                data: {page : function (){ return 'LoadMenu';}},
                dataType : 'json',
                success : function(data){
                    jQuery.each(data, function(){
                       var menu = jQuery("#cmbmenu");
                       menu.append(jQuery("<option />").val(this.MenuID).text(this.Name));
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
           
           //this part displays the menu details
           jQuery("#menu").jqGrid({
                url: url,
                mtype: 'post',
                postData: {
                            paginate: function(){return 'MenuDeactivation';}
                          },
                datatype: "json",
                colNames:['Menu Name','DefaultPage','Description','Status','Action'],
                colModel:[
                           {name:'Menu', index:'Name', align:'center'},
                           {name:'DefaultPage',index:'Link', align: 'left'},
                           {name:'Description',index:'Description', align: 'left'},
                           {name:'Status',index:'Status',align:'center'},
                           {name:'button', index: 'button', width:120, align: 'center'}
                         ],
                rowNum:10,
                rowList:[10,20,30],
                height: 280,
                width: 1000,
                pager: '#pager1',
                viewrecords: true,
                sortorder: "asc",
                caption: "Menu Deactivation",
                gridview: true
           });
           jQuery("#menu").jqGrid('navGrid','#pager1',{edit:false,add:false,del:false, search:false});
           
           jQuery("#cmbmenu").live('change', function(){
                var menuID = jQuery("#cmbmenu").val();
                if(menuID > 0)
                {
                    jQuery.ajax({
                           url : url,
                           type : 'post',
                           data : {page : function(){return 'GetMenu';},
                                   menuid: function(){ return jQuery("#cmbmenu").val();}
                                  },
                           dataType : 'json',
                           success : function(data){
                                   var tblRow = "<thead>"
                                                +"<tr>"
                                                +"<th colspan='6' class='header'>Menu Deactivation</th>"
                                                +"</tr>"
                                                +"<tr>"
                                                +"<th>Menu Name</th>"
                                                +"<th>Default Page</th>"
                                                +"<th>Description</th>"
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
                                                +"<td align='left'>"+this.Name+"</td>"
                                                +"<td align='left'>"+this.Link+"</td>"
                                                +"<td align='left'>"+this.Description+"</td>"
                                                +"<td align='center'>"+rmessage+"</td>"
                                                +"<td align='center' style='width: 50px;'><input type=\"button\" value=\"Deactivate Menu\" onclick=\"window.location.href='../process/ProcessMenuMaintenance.php?menuid="+menuID+"&name="+this.Name+"&getpage=DeactivateMenu'\" /></td>"
                                                +"</tr>"
                                                +"</tbody>";

                                                $('#results').show();
                                                $('#details').hide();
                                                $('#userdata').html(tblRow);
                                });
                           }
                     });
                     jQuery("#menu").trigger("GridUnload");
                     $('#pagination').hide();
                }
                else{
                        $('#pagination').show();
                        $('#results').hide();
                    }
           });
    });
</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br />
    <form method="post" action="../process/ProcessMenuMaintenance.php">
        <input type="hidden" name="page" value="DeactivateMenu" />
        <table>
            <tr>
                <td>Menu</td>
                <td>
                    <select id="cmbmenu" name="cmbmenu">
                        <option value="0">All</option>
                    </select>
                </td>
            </tr>
        </table>
        <div align="center" id="pagination">
          <!-- for viewing of menus-->
          <table border="1" id="menu"></table>
          <div id="pager1"></div>
        </div>
        <div id="results" style="display: none;">
              <table id="userdata" class="tablesorter">

              </table>
        </div>
        <div id="light" class="white_confirm">
            <div class="close_popup" id="btnClose" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"></div>
            <input type="hidden" id="txtmenuid" name="txtmenuid" value="<?php echo  $vmenudet["MenuID"]; ?>" />
            <input type="hidden" name="txtmenu" id="txtmenu" value="<?php echo $vmenudet['MenuName']; ?>"/>
            <br />
            <p>Are you sure you want to deactivate this menu?</p>
            <p align="center"><?php echo $vmenudet['MenuName']; ?></p>
            <input type="submit" value="OK" style="float: left;"/>
            <input type="button" id="btnCancel" value="Cancel" style="float: right;" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none'" />
        </div>
        <div id="fade" class="black_overlay"></div>
    </form>
</div>
<?php include 'menufooter.php'; ?>
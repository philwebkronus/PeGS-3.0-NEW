<?php
$pagetitle = 'View Menu';
include 'process/ProcessMenuMaintenance.php';
include 'header.php';

$vaccesspages = array('1');
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
    jQuery(document).ready(function(){
        var url = 'process/ProcessMenuMaintenance.php';
           //this part displays the menu details
           jQuery("#menu").jqGrid({
                url: url,
                mtype: 'post',
                postData: {
                            paginate: function(){return 'MenuView';}
                          },
                datatype: "json",
                colNames:['Menu Name','Default Page','Description','Status','Action'],
                colModel:[
                           {name:'Menu', index:'Name', align:'center'},
                           {name:'DefaultPage',index:'Link', align: 'left'},
                           {name:'Description',index:'Description', align: 'left'},
                           {name:'Status',index:'Status',align:'center'},
                           {name:'button', index: 'button', width:120, align: 'center',sortable:false}
                         ],
                rowNum:10,
                rowList:[10,20,30],
                height: 280,
                width: 1000,
                pager: '#pager1',
                viewrecords: true,
                sortorder: "asc",
                caption: "Menu",
                gridview: true
           });
           jQuery("#menu").jqGrid('navGrid','#pager1',{edit:false,add:false,del:false, search:false});
           
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
                                                +"<th colspan='6' class='header'>Menu</th>"
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
                                                +"<td align='center' style='width: 50px;'><input type=\"button\" value=\"Update Details\" onclick=\"window.location.href='process/ProcessMenuMaintenance.php?menuid="+menuID+"&getpage=MenuDetails'\" /></td>"
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
    <form method="post" action="#">
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
        <br />
        <div align="center" id="pagination">
          <!-- for viewing of menus-->
          <table border="1" id="menu"></table>
          <div id="pager1"></div>
        </div>
        <div id="results" style="display: none;">
              <table id="userdata" class="tablesorter">

              </table>
        </div>
    </form>
</div>
<?php  
    }
}
include 'footer.php'; 
?>
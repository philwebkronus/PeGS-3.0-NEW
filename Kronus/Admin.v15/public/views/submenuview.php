<?php
$pagetitle = "Sub-menu View";
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
           //this part displays the sub-menu details
           viewsubmenu(url, 0);
           
           //ajax: get active menus only
           jQuery.ajax({
                url: url,
                type: 'post',
                data: {page: function(){return 'GetActiveMenu';},
                       menuid: function(){return -1;}
                      },
                dataType: 'json',
                success: function(data){
                    var menu = jQuery("#cmbmenu");
                    jQuery.each(data, function(){
                        menu.append(jQuery("<option />").val(this.MenuID).text(this.Name));
                    });
                },
                error: function(e){
                    alert(e.responseText);
                }
            });
            
            //ajax: onchange event load submenus and grid
            jQuery("#cmbmenu").live('change', function(){
                var menuID = jQuery("#cmbmenu").val();
                viewsubmenu(url, menuID);
                if(menuID > 0)
                {
                    jQuery("#cmbsubmenu").empty();
                    jQuery("#cmbsubmenu").append(jQuery("<option />").val("-1").text("All"));
                    //ajax: get all sub menus
                    jQuery.ajax({
                       url : url,
                       type : 'post',
                       data : {page: function(){return 'GetSubmenu';},
                              menuid: function(){return jQuery("#cmbmenu").val();},
                              submenuid: function(){return 0;}
                       },
                       dataType : 'json',
                       success: function(data)
                       {
                           var submenu = jQuery("#cmbsubmenu");
                           jQuery.each(data, function(){
                              submenu.append(jQuery("<option />").val(this.SubMenuID).text(this.Name)); 
                           });
                       }
                    });
                }
                else
                {
                    jQuery("#cmbsubmenu").empty();
                    jQuery("#cmbsubmenu").append(jQuery("<option />").val("-1").text("All"));
                    $('#pagination').show();
                    $('#results').hide();
                }
            });
            
            //ajax: onchange event of submenu load specific data
            jQuery("#cmbsubmenu").live('change', function(){
                var submenuID = jQuery("#cmbsubmenu").val();
                if(submenuID > 0)
                {
                    jQuery.ajax({
                           url : url,
                           type : 'post',
                           data : {page: function(){return 'GetSubmenu';},
                                  menuid: function(){return 0;},
                                  submenuid: function(){return submenuID;}
                           },
                           dataType : 'json',
                           success : function(data){
                                   var tblRow = "<thead>"
                                                +"<tr>"
                                                +"<th colspan='6' class='header'>Sub-menu</th>"
                                                +"</tr>"
                                                +"<tr>"
                                                +"<th>Menu Name</th>"
                                                +"<th>Sub-menu Name</th>"
                                                +"<th>Group</th>"
                                                +"<th>Description</th>"
                                                +"<th>Action</th>"
                                                +"</tr>"
                                                +"</thead>";

                                $.each(data, function(i,user){
                                    tblRow +=
                                                "<tbody>"
                                                +"<tr>"
                                                +"<td align='left'>"+this.MenuName+"</td>"
                                                +"<td align='left'>"+this.SubMenuName+"</td>"
                                                +"<td align='left'>"+this.Group+"</td>"
                                                +"<td align='left'>"+this.Description+"</td>"
                                                +"<td align='center' style='width: 50px;'><input type=\"button\" value=\"Update Details\" onclick=\"window.location.href='process/ProcessMenuMaintenance.php?submenuid="+submenuID+"&getpage=SubMenuDetails'\" /></td>"
                                                +"</tr>"
                                                +"</tbody>";

                                                $('#results').show();
                                                $('#details').hide();
                                                $('#userdata').html(tblRow);
                                });
                           }
                     });
                     jQuery("#submenu").GridUnload();
                     $('#pagination').hide();
                }
                else
                {
                     var menuID = jQuery("#cmbmenu").val();
                     viewsubmenu(url, menuID);
                     $('#pagination').show();
                     $('#results').hide();
                }
            });
    });
    
    //display all submenus on the grid
    function viewsubmenu(url, menuID)
    {
        jQuery("#submenu").GridUnload();
        jQuery("#submenu").jqGrid({
                url: url,
                mtype: 'post',
                postData: {
                            paginate: function(){return 'SubMenuView';},
                            menuid : function(){return menuID;}
                          },
                datatype: "json",
                colNames:['Menu Name','Sub-Menu Name','Group','Description','Action'],
                colModel:[
                           {name:'MenuName', index:'MenuName', align:'center'},
                           {name:'SubMenuName', index:'SubMenuName', align:'left'},
                           {name:'Group', index:'Group', align: 'left',sortable:false},
                           {name:'Description',index:'Description', align: 'left'},
                           {name:'button', index: 'button', width:120, align: 'center',sortable:false}
                         ],
                rowNum:10,
                rowList:[10,20,30],
                height: 280,
                width: 1000,
                pager: '#pager1',
                viewrecords: true,
                sortorder: "asc",
                caption: "Sub-Menu",
                gridview: true
           });
           jQuery("#submenu").jqGrid('navGrid','#pager1',{edit:false,add:false,del:false, search:false});
    }
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
            <tr>
                <td>Sub Menu</td>
                <td>
                    <select id="cmbsubmenu" name="cmbsubmenu">
                        <option value="-1">All</option>
                    </select>
                </td>
            </tr>
        </table>
        <div align="center" id="pagination">
          <!-- for viewing of menus-->
          <table border="1" id="submenu"></table>
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
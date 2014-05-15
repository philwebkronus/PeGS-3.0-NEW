<?php
$pagetitle = 'Sub-menu Deactivation';
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
        $vsubmenuID = '';
        $vmenuID = '';
        $vsubmenuname = '';
        $vstatus = '';
        if(isset ($_SESSION['submenudet']))
        {
            $vsubmenudet = $_SESSION['submenudet'];
            $vsubmenuID = $vsubmenudet["SubMenuID"];
            $vmenuID = $vsubmenudet['MenuID'];
            $vsubmenuname = $vsubmenudet['SubMenuName'];
            $vstatus = $vsubmenudet['Status'];
            echo "<script type=\"text/javascript\">
                  jQuery(document).ready(function(){
                  document.getElementById('light').style.display='block';
                  document.getElementById('fade').style.display='block';
                  });
                  </script>";
            unset($_SESSION['submenudet']);
        }
?>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var url = 'process/ProcessMenuMaintenance.php';
        
            //this part displays the sub-menu details
           viewsubmenu(url, 0, 0);
           
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
           
           jQuery("#cmbsubmenu").live('change', function(){
               var submenuID = jQuery("#cmbsubmenu").find("option:selected").val();
               var menuID = jQuery("#cmbmenu").find("option:selected").val();
               viewsubmenu(url, menuID, submenuID);
           });
           
            jQuery("#cmbmenu").live('change', function(){
                jQuery("#cmbsubmenu").empty();
                jQuery("#cmbsubmenu").append(jQuery("<option />").val("-1").text("Please Select"));
                
                var menuID = jQuery("#cmbmenu").val();
                var submenuID = jQuery("#cmbsubmenu").find("option:selected").val();
                viewsubmenu(url, menuID, submenuID);
                if(menuID > 0)
                {
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
                       }, 
                       error: function(XMLHttpRequest, e)
                       {
                           alert(XMLHttpRequest.responseText);
                           if(XMLHttpRequest.status == 401)
                           {
                                window.location.reload();
                           }
                       }
                    });
                }
                else{
                    jQuery("#cmbsubmenu").empty();
                    jQuery("#cmbsubmenu").append(jQuery("<option />").val("-1").text("All"));
                    $('#pagination').show();
                    $('#results').hide();
                }
                
            });            
    });
    
    //display all submenus on the grid
    function viewsubmenu(url, menuID, submenuID)
    {
        jQuery("#submenu").GridUnload();
        jQuery("#submenu").jqGrid({
                url: url,
                mtype: 'post',
                postData: {
                            paginate: function(){return 'SubMenuDeactivation';},
                            menuid : function(){return menuID;},
                            submenuid : function(){return submenuID;}
                          },
                datatype: "json",
                colNames:['Menu Name','Sub-Menu Name','Group','Description','Status','Action'],
                colModel:[
                           {name:'MenuName', index:'MenuName', align:'center'},
                           {name:'SubMenuName', index:'SubMenuName', align:'left'},
                           {name:'Group', index:'Group', align: 'left',sortable:false},
                           {name:'Description',index:'Description', align: 'left'},
                           {name:'Status',index:'Status', align:'center'},
                           {name:'button', index: 'button', width:120, align: 'center',sortable:false}
                         ],
                rowNum:10,
                rowList:[10,20,30],
                height: 280,
                width: 1000,
                pager: '#pager1',
                viewrecords: true,
                sortorder: "asc",
                caption: "Sub-Menu Deactivation",
                gridview: true
           });
           jQuery("#submenu").jqGrid('navGrid','#pager1',{edit:false,add:false,del:false, search:false});
    }
</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br />
    <form method="post" action="process/ProcessMenuMaintenance.php">
        <input type="hidden" name="page" value="DeactivateSubMenu" />
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
                <td>Sub-menu</td>
                <td>
                    <select id="cmbsubmenu" name="cmbsubmenu">
                        <option value="-1">Please Select</option>
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
        <div id="light" class="white_confirm">
            <div class="close_popup" id="btnClose" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none';"></div>
            <input type="hidden" id="txtsubmenuid" name="txtsubmenuid" value="<?php echo  $vsubmenuID; ?>" />
            <input type="hidden" id="txtmenuid" name="txtmenuid" value="<?php echo  $vmenuID; ?>" />
            <input type="hidden" name="txtsubmenu" id="txtsubmenu" value="<?php echo $vsubmenuname; ?>"/>
            <input type="hidden" name="txtstatus" id="txtstatus" value="<?php echo $vstatus; ?>"/>
            <br />
            <p>Are you sure you want to deactivate/activate this sub-menu?</p>
            <p align="center"><?php echo $vsubmenuname; ?></p>
            <input type="submit" value="OK" style="float: left;"/>
            <input type="button" id="btnCancel" value="Cancel" style="float: right;" onclick="document.getElementById('light').style.display='none';document.getElementById('fade').style.display='none'" />
        </div>
        <div id="fade" class="black_overlay"></div>
    </form>
</div>
<?php  
    }
}
include 'footer.php'; 
?>
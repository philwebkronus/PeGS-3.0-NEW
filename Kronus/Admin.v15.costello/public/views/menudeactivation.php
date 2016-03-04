<?php
$pagetitle = 'Menu Deactivation';
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
        $vmenuID = '';
        $vmenuname = '';
        $vstatus = '';
        if(isset ($_SESSION['menudet']))
        {
            $vmenudet = $_SESSION['menudet'];
            $vmenuID = $vmenudet["MenuID"];
            $vmenuname = $vmenudet['MenuName'];
            $vstatus = $vmenudet['Status'];
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
           var url = 'process/ProcessMenuMaintenance.php';
            
           showmenugrid(url, 0);
           
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
                showmenugrid(url, menuID);
           });
    });
    
    function showmenugrid(url, menuid){
           jQuery("#menu").GridUnload();
           //this part displays the menu details
           jQuery("#menu").jqGrid({
                url: url,
                mtype: 'post',
                postData: {
                            paginate: function(){return 'MenuDeactivation';},
                            menuid : function(){return menuid;}
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
    }
</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br />
    <form method="post" action="process/ProcessMenuMaintenance.php">
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
            <input type="hidden" id="txtmenuid" name="txtmenuid" value="<?php echo  $vmenuID; ?>" />
            <input type="hidden" name="txtmenu" id="txtmenu" value="<?php echo $vmenuname; ?>"/>
            <input type="hidden" name="txtstatus" id="txtstatus" value="<?php echo $vstatus; ?>"/>
            <br />
            <p>Are you sure you want to deactivate/activate this menu?</p>
            <p align="center"><?php echo $vmenuname; ?></p>
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
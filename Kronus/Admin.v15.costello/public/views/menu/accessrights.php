<?php
$pagetitle = "Access Rights";
//include '../process/ProcessMenuMaintenance.php';
include 'menuheader.php';
?>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle;?></div>
    <form method="post" action="../../process/ProcessMenuMaintenance.php">
        <input type="hidden" name="page" value="CreateAccessRights" />
        <br />
        <table>
            <tr>
                <td>Account Type</td>
                <td>
                    <select id="cmbacctype" name="cmbacctype">
                        <option value="-1">Please Select</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Menu</td>
                <td>
                    <select id="cmbmenu" name="cmbmenu">
                        <option value="-1">Please Select</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Sub-Menu</td>
                <td>
                    <select id="cmbsubmenu" name="cmbsubmenu">
                        <option value="-1">Please Select</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Order</td>
                <td>
                    <input type="text" id="txtorderid" name="txtorderid" onkeypress="return numberonly(event);" />
                </td>
            </tr>
            <tr>
                <td>Default URL</td>
                <td>
                    <input type="text" name="txturl" id="txturl" onkeypress="return urlvalidation(event);" />
                </td>
            </tr>
            <tr>
                <td>Default URL2</td>
                <td>
                    <input type="text" name="txturl2" id="txturl2" onkeypress="return urlvalidation(event);" />
                </td>
            </tr>
        </table>
        <div id="submitarea">
            <input type="submit" value="Submit" onclick="return validatearights();" />
        </div>
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var url = '../process/ProcessMenuMaintenance.php';
        
        //ajax: get all account types
        jQuery.ajax({
            url : url,
            type: 'post',
            data: {page: function(){ return 'GetAccountTypes'}},
            dataType: 'json',
            success: function(data){
                var acct = jQuery("#cmbacctype");
                jQuery.each(data, function(){
                    acct.append(jQuery("<option />").val(this.AccountTypeID).text(this.Name));
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
        
        //ajax: get all menus
        jQuery.ajax({
            url: url,
            type: 'post',
            data: {page: function(){return 'GetMenu';},
                   menuid: function(){return -1;}
                  },
            dataType: 'json',
            success: function(data){
                var menu = jQuery("#cmbmenu");
                jQuery.each(data, function(){
                    menu.append(jQuery("<option />").val(this.MenuID).text(this.Name));
                });
            }
        });
        
        jQuery("#cmbmenu").live('change', function(){
            jQuery("#cmbsubmenu").empty();
            jQuery("#cmbsubmenu").append(jQuery("<option />").val("-1").text("Please Select"));
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
        });
    });
</script>
<?php include 'menufooter.php'; ?>
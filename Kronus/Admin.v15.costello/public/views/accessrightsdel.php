<?php
$pagetitle = "Deactivate Access Rights";
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
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle;?></div>
    <form method="post" action="process/ProcessMenuMaintenance.php">
        <input type="hidden" name="page" value="DeactivateAccessRights" />
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
                <td>Sub-menu</td>
                <td>
                    <select id="cmbsubmenu" name="cmbsubmenu">
                        <option value="-1">Please Select</option>
                    </select>
                </td>
            </tr>
        </table>
        <div id="submitarea">
            <input type="submit" value="Submit" onclick="return delAccessRights();" />
        </div>
    </form>
</div>
<script type="text/javascript">
    jQuery(document).ready(function(){
        var url = 'process/ProcessMenuMaintenance.php';
        
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
            
            jQuery.ajax({
                url : url,
                type : 'post',
                data : {page: function(){ return 'GetAccessOrder';},
                        acctypeid : function(){ return $("#cmbacctype").find("option:selected").val();},
                        menuid : function(){return $("#cmbmenu").find("option:selected").val();}
                       },
                dataType : 'json',
                success : function(data){
                    $("#txtorderid").val(data.orderID);
                }
            });
        });
    });
</script>
<?php  
    }
}
include 'footer.php'; 
?>
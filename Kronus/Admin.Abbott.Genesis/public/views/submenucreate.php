<?php
$pagetitle = "Create Sub-Menu";
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
    });
</script>
<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle;?></div>
    <form method="post" action="process/ProcessMenuMaintenance.php">
        <input type="hidden" name="page" value="CreateSubMenu" />
        <br />
        <table>
            <tr>
                <td>Menu</td>
                <td>
                    <select id="cmbmenu" name="cmbmenu">
                        <option value="-1">Please Select</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Sub-menu Name</td>
                <td>
                    <input type="text" name="txtsubname" id="txtsubname" onkeypress="return letter(event);"/>
                </td>
            </tr>
            <tr>
                <td>Description</td>
                <td>
                    <input type="text" id="txtdescription" name="txtdescription" onkeypress="return letter(event);" />
                </td>
            </tr>
            <tr>
                <td>Group</td>
                <td>
                    <input type="text" name="txtgroup" id="txtgroup" onkeypress="return letter(event);" />
                </td>
            </tr>
        </table>
        <div id="submitarea">
            <input type="submit" value="Submit" onclick="return validatesubmenu();" />
        </div>
    </form>
</div>
    
<?php  
    }
}
include 'footer.php'; 
?>
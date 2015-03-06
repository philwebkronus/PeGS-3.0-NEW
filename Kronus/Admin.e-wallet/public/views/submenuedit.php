<?php
$pagetitle = 'Update Sub-Menu';
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
        $vsubdetails = $_SESSION['submenu'];
        foreach($vsubdetails as $row)
        {
            $vsubmenuID = $row['SubMenuID'];
            $vsubmenuname = $row['SubMenuName'];
            $vmenuname = $row['MenuName'];
            $vgroup = $row['Group'];
            $vdescription = $row['Description'];
            $vmenuID = $row['MenuID'];
        }
        $arrolddetails = array($vmenuID,$vsubmenuname,$vdescription,$vgroup);
        $oldetails = implode(",",$arrolddetails);
        unset($arrolddetails);
?>

<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle;?></div>
    <form method="post" action="process/ProcessMenuMaintenance.php">
        <input type="hidden" name="page" value="SubMenuUpdate" />
        <input type="hidden" name="txtsubmenuID" value="<?php echo $vsubmenuID; ?>" />
        <input type="hidden" name="txtolddetails" value="<?php echo $oldetails; ?>" />
        <br />
        <table>
            <tr>
                <td>Menu</td>
                <td>
                    <select id="cmbmenu" name="cmbmenu">
                        <option value="-1">Please Select</option>
                        <?php 
                        $vmenudetails = $_SESSION['menus'];
                        foreach ($vmenudetails as $row)
                        {
                            $vmenuID = $row['MenuID'];
                            $rmenuName = $row['Name'];
                            if($rmenuName == $vmenuname)
                            {
                                echo "<option value='$vmenuID' selected='selected'>$vmenuname</option>";
                            }
                            else
                            {
                                echo "<option value='$vmenuID'>$rmenuName</option>";
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Sub-Menu Name</td>
                <td>
                    <input type="text" name="txtsubname" id="txtsubname" maxlength="40" size="40" onkeypress="return letter(event);" value="<?php echo $vsubmenuname; ?>"/>
                </td>
            </tr>
            <tr>
                <td>Description</td>
                <td>
                    <input type="text" id="txtdescription" name="txtdescription" maxlength="100" size="100" onkeypress="return letter(event);" value="<?php echo $vdescription; ?>"/>
                </td>
            </tr>
            <tr>
                <td>Group</td>
                <td>
                    <input type="text" name="txtgroup" id="txtgroup" maxlength="50" size="50" onkeypress="return letter(event);" value="<?php echo $vgroup;?>"/>
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

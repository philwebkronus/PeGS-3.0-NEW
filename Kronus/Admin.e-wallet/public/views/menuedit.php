<?php
 $pagetitle = "Update Menu";
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
         if(!isset($_SESSION['menu'])){
             echo "<script type='text/javascript'>
                        alert('Session is not set');
                        window.location.href = 'menuview.php';
                   </script>";
         }
         
         $vmenudet = $_SESSION['menu'];
         foreach ($vmenudet as $row)
         {
             $vmenuID = $row['MenuID'];
             $vmenuname = $row['Name'];
             $vmenulink = $row['Link'];
             $vmenudesc = $row['Description'];
         }
         $arrolddetails = array($vmenuname,$vmenudesc,$vmenuID);
         $olddetails = implode(",", $arrolddetails);
         unset($arrolddetails);
?>

<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br /> 
    <form method="post" action="process/ProcessMenuMaintenance.php">
        <input type="hidden" name="page" id="page" value="MenuUpdate" />
        <input type="hidden" name="txtmenuID" id="txtmenuID" value ="<?php echo $vmenuID; ?>" />
        <input type="hidden" name="txtolddetails" value="<?php echo $olddetails; ?>" />
        <table>
            <tr>
                <td>Menu Name</td>
                <td>
                    <input type="text" id="txtmenuname" name="txtmenuname" onkeypress="return letter(event);" value="<?php echo $vmenuname; ?>"/> 
                </td>
            </tr>
            <tr>
                <td>Default Page</td>
                <td>
                    <input type="text" id="txtdefault" name="txtdefault" readonly="readonly" onkeypress="return alphanumeric(event);" value="<?php echo $vmenulink; ?>"/>
                </td>
            </tr>
            <tr>
                <td>Description</td>
                <td>
                    <input type="text" id="txtdescription" name="txtdescription" onkeypress="return letter(event);" value="<?php echo $vmenudesc; ?>"/>
                </td>
            </tr>
        </table>
        <div id="submitarea">
            <input type="submit" value="Submit" onclick="return validatemenu();" />
        </div>
    </form>
</div>
<?php  
    }
}
include 'footer.php'; 
?>
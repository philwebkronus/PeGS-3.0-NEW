<?php
 $pagetitle = "Create Menu";
 //include '../process/ProcessMenuMaintenance.php';
 include 'menuheader.php';
?>

<div id="workarea">
    <div id="pagetitle"><?php echo $pagetitle; ?></div>
    <br /> 
    <form method="post" action="../process/ProcessMenuMaintenance.php">
        <input type="hidden" name="page" id="page" value="MenuCreation" />
        <table>
            <tr>
                <td>Menu Name</td>
                <td>
                    <input type="text" id="txtmenuname" name="txtmenuname" onkeypress="return letter(event);"/> 
                </td>
            </tr>
            <tr>
                <td>Default Page</td>
                <td>
                    <input type="text" id="txtdefault" name="txtdefault" onkeypress="return urlvalidation(event);" />
                </td>
            </tr>
            <tr>
                <td>Description</td>
                <td>
                    <input type="text" id="txtdescription" name="txtdescription" onkeypress="return letter(event);" />
                </td>
            </tr>
        </table>
        <div id="submitarea">
            <input type="submit" value="Submit" onclick="return validatemenu();" />
        </div>
    </form>
</div>

<?php include 'menufooter.php'; ?>
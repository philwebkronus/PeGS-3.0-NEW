<?php $pagetitle = "Site/PEGS Group Creation";  ?>

<?php
include '../process/ProcessSiteManagement.php';
//ini_set('display_errors',true);
//ini_set('log_errors',true);
?>

<?php  include "header.php"; ?>

<div id="workarea">
  <div id="pagetitle"><?php echo $pagetitle; ?></div>
  <br />
  
  <form method="post" action="../process/ProcessSiteGroup.php">
    <input type="hidden"   name="page" value="SiteGroupCreation" />
    <table>
      <tr>
        <td>Site / PEGS Group Name</td>
        <td>
          <input type="text" name="txtgrpname" id="txtgrpname" maxlength="30" size="30" onkeypress="return letterexceptspace(event);"/>
        </td>
      </tr>
      <tr>
          <td>Site / PEGS Group Description</td>
          <td>
              <input type="text" name="txtgrpdesc" id="txtgrpdesc" maxlength="50" size="50" onkeypress="return letter(event);" />
          </td>
      </tr>
    </table>
    <div id="submitarea"> 
      <input type="submit" value="Submit" onclick="return chksitegrp();" />
    </div>
  </form>
</div>
<?php  include "footer.php"; ?>

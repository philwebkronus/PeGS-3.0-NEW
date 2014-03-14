<?php
include '../process/ProcessSiteGroup.php';
$pagetitle = "Site/PEGS Group Update";
$rdetails = $_SESSION['grpdetails'];
foreach ($rdetails as $result)
{
    $rgrpname = $result['SiteGroupsName'];
    $rgrpdescription = $result['Description'];
    $rgrpID = $result['SiteGroupID'];
}
ini_set('display_errors',true);
ini_set('log_errors',true);

include 'header.php';
?>

<div id="workarea">
  <div id="pagetitle"><?php echo $pagetitle; ?></div>
  <br />
  <form method="post" action="../process/ProcessSiteGroup.php">
      <input type="hidden" name="page" value="SiteGroupUpdate" />
      <input type="hidden" name="txtgrpid" value="<?php echo $rgrpID;?>" />
      <table>
          <tr>
              <td>Site / PEGS Group Name</td>
              <td>
                  <input type="text" name="txtgrpname" id="txtgrpname" maxlength="30" size="30" value="<?php echo $rgrpname;?>" onkeypress="return  letterexceptspace(event);"/>
              </td>
          </tr>
          <tr>
              <td>Site / PEGS Group Description</td>
              <td>
                  <input type="text" name="txtgrpdesc" id="txtgrpdesc" maxlength="50" size="50" value="<?php echo $rgrpdescription;?>"  onkeypress="return letter(event);" />
              </td>
          </tr>
      </table>
      <div id="submitarea"> 
        <input type="submit" value="Submit" onclick="return chksitegrp();" />
      </div>
  </form>
</div>

<?php include 'footer.php'; ?>

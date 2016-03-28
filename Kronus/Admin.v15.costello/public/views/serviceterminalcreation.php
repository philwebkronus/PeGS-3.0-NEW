<?php  
$pagetitle = "MG OC Account Creation";  
include "process/ProcessTerminalMgmt.php";
include "header.php";
$vaccesspages = array('8');
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
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
         <form method="post" action="process/ProcessTerminalMgmt.php">
                <input type="hidden" name="page" value="ServiceTerminalCreation" />
                <table>
                    <tr>
                        <td width="130px">Username</td>
                        <td>
                            <input type="text" id="txtusername" name="txtusername" maxlength="20" onkeypress="javascript: return numberandletter(event);"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Password</td>
                        <td>
                            <input type="password" id="txtpassword" name="txtpassword" maxlength="50" onkeypress="javascript: return numberandletter(event);" />
                        </td>
                    </tr>
                    <tr>
                        <td>Service Agents</td>
                        <td>
                         <?php
                            $vagentID = $_SESSION['agents'];
                            echo "<select id=\"cmbagents\" name=\"cmbagents\">";
                            echo "<option value=\"-1\">Please Select</option>";
                            foreach ($vagentID as $result)
                            {
                                       $ragentID = $result['ServiceAgentID'];
                                       $vname = $result['Username'];
                                       echo "<option value=\"".$ragentID."\">".$vname."</option>";
                            }
                            echo "</select>";
                            ?>
                        </td>
                    </tr>
                </table>
                <br />
            
                <div id="submitarea"> 
                    <input type="submit" value="Create OC Account" onclick="return chkterminalcreation();"/>
                </div>
         </form>
</div>
<?php  
    }
}
include "footer.php"; ?>

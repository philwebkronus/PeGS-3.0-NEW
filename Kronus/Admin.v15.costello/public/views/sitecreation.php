<?php
$pagetitle = "Site / PEGS Creation";
include 'process/ProcessSiteManagement.php';
include "header.php";

$vaccesspages = array('8');
$vctr = 0;
if (isset($_SESSION['acctype']))
{
    foreach ($vaccesspages as $val)
    {
        if ($_SESSION['acctype'] == $val)
        {
            break;
        }
        else
        {
            $vctr = $vctr + 1;
        }
    }

    if (count($vaccesspages) == $vctr)
    {
        echo "<script type='text/javascript'>document.getElementById('blockl').style.display='block';
                         document.getElementById('blockf').style.display='block';</script>";
    }
    else
    {
        ?>

        <div id="workarea">
            <script type="text/javascript">
                $(document).ready(function() {
                    $('#cmbislands').live('change', function() {
                        sendIslandID($(this).val());
                        $('#cmbregions').empty();
                        $('#cmbregions').append($("<option />").val("-1").text("Please Select"));
                        $('#cmbprovinces').empty();
                        $('#cmbprovinces').append($("<option />").val("-1").text("Please Select"));
                        $('#cmbcity').empty();
                        $('#cmbcity').append($("<option />").val("-1").text("Please Select"));
                        $('#cmbbrgy').empty();
                        $('#cmbbrgy').append($("<option />").val("-1").text("Please Select"));
                    });

                    $('#cmbregions').live('change', function() {
                        sendRegionID($(this).val());
                        $('#cmbprovinces').empty();
                        $('#cmbprovinces').append($("<option />").val("-1").text("Please Select"));
                        $('#cmbcity').empty();
                        $('#cmbcity').append($("<option />").val("-1").text("Please Select"));
                        $('#cmbbrgy').empty();
                        $('#cmbbrgy').append($("<option />").val("-1").text("Please Select"));
                    });

                    $('#cmbprovinces').live('change', function() {
                        sendProvID($(this).val());
                        $('#cmbcity').empty();
                        $('#cmbcity').append($("<option />").val("-1").text("Please Select"));
                        $('#cmbbrgy').empty();
                        $('#cmbbrgy').append($("<option />").val("-1").text("Please Select"));
                    });

                    $('#cmbcity').live('change', function() {
                        sendCityID($(this).val());
                        $('#cmbbrgy').empty();
                        $('#cmbbrgy').append($("<option />").val("-1").text("Please Select"));
                    });
                });
            </script>


            <div id="pagetitle"><?php echo $pagetitle; ?></div>
            <br />
            <form method="post" action="process/ProcessSiteManagement.php">
                <input type="hidden" name="page" value="SiteCreation">
                <table>
                    <tr>
                        <td>Island</td>
                        <td>
                            <?php
                            //display all islands
                            $vislands = $_SESSION['resislands'];
                            echo "<select id=\"cmbislands\" name=\"cmbislands\">";
                            echo "<option value=\"-1\">Please Select</option>";


                            foreach ($vislands as $result)
                            {
                                $vname = $result['IslandName'];
                                $vIslandId = $result['IslandID'];
                                echo "<option value=\"" . $vIslandId . "\">" . $vname . "</option>";
                            }
                            echo "</select>";
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Region</td>
                        <td>
                            <div id="regions">
                                <select id="cmbregions" name="cmbregions">
                                    <option value="-1">Please Select</option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Province</td>
                        <td>
                            <div id="provinces">
                                <select id="cmbprovinces" name="cmbprovinces">
                                    <option value="-1">Please Select</option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>City</td>
                        <td>
                            <select id="cmbcity" name="cmbcity">
                                <option value="-1">Please Select</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Barangay</td>
                        <td>
                            <select id="cmbbrgy" name="cmbbrgy" >
                                <option value="-1">Please Select</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Site / PEGS Name</td>
                        <td>
                            <input type="text" name="txtsitename" id="txtsitename" maxlength="50" size="50"  onkeypress="return letter(event);"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Site / PEGS Code</td>
                        <td>
                            <input type="text" name="txtsitecode" id="txtsitecode" maxlength="5" size="5" onkeypress="return alphanumeric1(event);"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Site / PEGS Description</td>
                        <td>
                            <input type="text" name="txtsitedesc" id="txtsitedesc" maxlength="50"  size ="50" onkeypress="return letter(event);"/>
                        </td>
                    </tr>
        <!--                <tr>
                        <td>Site / PEGS Alias</td>
                        <td>
                            <input type="text" name="txtsitealias" id="txtsitealias" maxlength="20" size ="20" onkeypress="return numberandletter1(event);"/>
                        </td>
                    </tr>-->
                    <tr>
                        <td>Address (bldg., Street)</td>
                        <td>
                            <input type="text" name="txtsiteaddress" id="txtsiteaddress" maxlength="150" size="80" onkeypress="return numberandletter1(event);"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Contact Number</td>
                        <td colspan="4">
                            <label>Country Code </label>
                            <input type="text" id="txtctrycode" name="txtctrycode" maxlength="5" size="5"  onkeypress="return numberonly(event);"/>
                            <label>Area Code </label>
                            <input type="text" id="txtareacode" name="txtareacode" maxlength="5" size="5"  onkeypress="return numberonly(event);"/>
                            <input type="text" id="txtphone" name="txtphone" maxlength="7" size="7"  onkeypress="return numberonly(event);"/></td>
                    </tr>
                    <tr>
                        <td title="Certificate To Operate">CTO</td>
                        <td>
                            <input type="text" name="txtcto" id="txtcto" maxlength="50" size="50" onkeypress="return numberandletter1(event);"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Passcode</td>
                        <td>
                            <input type="text" name="txtpasscode" id="txtpasscode" maxlength="<?php echo $gpasscode_len; ?>" size="<?php echo $gpasscode_len; ?>" onkeypress="return numberonly(event);" />
                        </td>
                    </tr>
                    <!--Site Tagging 1 for Hybrid Site, 2 for Platinum Site and 3 for e-Bingo-->
                    <tr>
                        <td>Site Classfication</td>
                        <td>
                            <?php
                            //display all site classifications
                            $vsiteclass = $_SESSION['siteclass'];
                            echo "<select id='cmbsiteclass' name='cmbsiteclass' >";
                            echo "<option value='-1'>Please Select</option>";


                            foreach ($vsiteclass as $result)
                            {
                                $vname = $result['Description'];
                                $vSiteId = $result['SiteClassificationID'];
                                echo "<option value=\"" . $vSiteId . "\">" . $vname . "</option>";
                            }
                            echo "</select>";
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>isTestSite</td>
                        <td>
                            Yes<input type="radio" id="opttestyes" name="opttest" value="1" />
                            No<input type="radio" id="opttestno" name="opttest" value="0" checked />
                        </td>
                    </tr>
                </table>
                <br>

                <!-- e-Bingo Site Creation -->
                <div class="site">
                    <table >
                        <tr>
                            <td>
                                Tab
                            </td>
                            <td >
                                Show
                            </td>
                            <td>
                                Hide
                            </td>
                        </tr>
                        <tr>
                            <td >
                                Terminal Monitoring
                            </td>
                            <td>
                        <center><input type="radio" id="tmtab" name="tmtab" value="1" checked /></center>
                        </td>
                        <td>
                        <center><input type="radio" id="tmtab" name="tmtab" value="0" /></center>
                        </td>
                        </tr>
                        <tr>
                            <td >
                                Start Session/ Reload Session/ Redemption
                            </td>
                            <td>
                        <center><input type="radio" id="srrtab" name="srrtab" value="1" checked /></center>
                        </td>
                        <td>
                        <center><input type="radio" id="srrtab" name="srrtab" value="0" /></center>
                        </td>
                        </tr>
                        <tr>
                            <td >
                                e-SAFE Load & Withdraw
                            </td>
                            <td>
                        <center><input type="radio" id="esafetab" name="esafetab" value="1" checked /></center>
                        </td>
                        <td >
                        <center><input type="radio" id="esafetab" name="esafetab" value="0" /></center>
                        </td>
                        </tr>
                    </table>
                </div>
                <!-- End e-Bingo site creation -->

                <div id="submitarea">
                    <input type="submit" value="Submit" onclick="return chkcreatesites();"/>
                </div>

            </form>
        </div>
        <?php
    }
}
include "footer.php";
?>

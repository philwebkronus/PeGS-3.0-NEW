<?php
$pagetitle = "Terminal Provider Assignment";
include 'process/ProcessTerminalMgmt.php';
include "header.php";
$vaccesspages = array('8', '19');
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
                $(document).ready(function()
                {
                    var url = 'process/ProcessTerminalMgmt.php';
                    $('#cmbsitename').live('change', function() {
                        jQuery("#submit").attr("disabled", true);
                        document.getElementById('loading').style.display='block';
                        document.getElementById('fade2').style.display='block';
                        
                        jQuery("#txttermname").text(" ");
                        sendSiteID1($(this).val());
                        jQuery("#cmbterminals").empty();
                        jQuery("#cmbterminals").append(jQuery("<option />").val("-1").text("Please Select"));

                        //this part is for displaying site name
                        jQuery.ajax({
                            url: url,
                            type: 'post',
                            data: {cmbsitename: function() {
                                    return jQuery("#cmbsitename").val();
                                }},
                            dataType: 'json',
                            success: function(data) {
                                if (jQuery("#cmbsitename").val() > 0)
                                {
                                    jQuery("#txtsitename").text(data.SiteName + " / ");
                                    jQuery("#txtposaccno").text(data.POSAccNo);
                                }
                                else
                                {
                                    jQuery("#txtsitename").text(" ");
                                    jQuery("#txtposaccno").text(" ");
                                }
                                    $('#loading').hide();
                                    document.getElementById('loading').style.display='none';
                                    document.getElementById('fade2').style.display='none';
                            }
                        });
                    });

                    jQuery("#cmbterminals").live('change', function()
                    {
                        document.getElementById('loading').style.display='block';
                        document.getElementById('fade2').style.display='block';
                        
                        //to get the site code and passcode
                        jQuery.ajax({
                            url: url,
                            type: 'post',
                            data: {page: function() {
                                    return jQuery("#page").val();
                                },
                                cmbterminals: function() {
                                    return jQuery("#cmbterminals").val();
                                },
                                cmbsitename: function() {
                                    return jQuery("#cmbsitename").val();
                                }
                            },
                            dataType: 'json',
                            success: function(data) {
                                jQuery.each(data, function() {
                                    jQuery("#termcode").val(data.terminalcode);
                                    jQuery("#passcode").val(data.passcode);
                                });
                            }
                        });

                        //this part is for displaying terminal name                   
                        jQuery.ajax({
                            url: url,
                            type: 'post',
                            data: {cmbterminal: function() {
                                    return jQuery("#cmbterminals").val();
                                }},
                            dataType: 'json',
                            success: function(data) {
                                jQuery("#txttermname").text(data.TerminalName);
                                $('#loading').hide();
                                    document.getElementById('loading').style.display='none';
                                    document.getElementById('fade2').style.display='none';
                            },
                            error: function(XMLHttpRequest, e) {
                                alert(XMLHttpRequest.responseText);
                                if (XMLHttpRequest.status == 401)
                                {
                                    window.location.reload();
                                }
                            }
                        });
                    });

                    jQuery("#cmbservices").live('change', function() {
                        var url = 'process/ProcessTerminalMgmt.php';
                        var servicename = ($(this).find("option:selected").text());
                        jQuery("#txtprovider").val(servicename);
                        document.getElementById('loading').style.display='block';
                        document.getElementById('fade2').style.display='block';
                        $.ajax({
                            url: url,
                            type: 'post',
                            data: {page: function() {
                                    return 'GetServiceGroup';
                                },
                                serviceid: function() {
                                    return $('#cmbservices').find("option:selected").val();
                                }
                            },
                            dataType: 'json',
                            success: function(data) {
                                $.each(data, function() {
                                    $('#txtservicegrp').val(this.ServiceGroupID);
                                });
                                document.getElementById('loading').style.display='none';
                                document.getElementById('fade2').style.display='none';
                                jQuery("#submit").attr("disabled", false);
                            },
                            error: function(XMLHttpRequest, e) {
                                alert(XMLHttpRequest.responseText);
                                if (XMLHttpRequest.status == 401)
                                {
                                    window.location.reload();
                                }
                            }
                        });
                    });
                });
            </script>

            <div id="pagetitle"><?php echo $pagetitle; ?></div>
            <br />
            <input type="hidden" id="page" value="GetTerminalCode" />
            <form method="post" action="process/ProcessTerminalMgmt.php">
                <input type="hidden" name="page" value="ServiceAssignment" />
                <input type="hidden" name="txtprovider" id="txtprovider"/>
                <input type="hidden" name="txtservicegrp" id="txtservicegrp" />
                <table>
                    <tr>
                        <td>Site / PEGS </td>
                        <td>
                            <?php
                            $vsiteID = $_SESSION['siteids'];
                            echo "<select id=\"cmbsitename\" name=\"cmbsitename\">";
                            echo "<option value=\"-1\">Please Select </option>";
                            foreach ($vsiteID as $result)
                            {
                                $rsiteID = $result['SiteID'];
                                $vorigcode = $result['SiteCode'];

                                //search if the sitecode was found on the terminalcode
                                if (strstr($vorigcode, $terminalcode) == false)
                                {
                                    $vcode = $vorigcode;
                                }
                                else
                                {
                                    //removes the "icsa-"
                                    $vcode = substr($vorigcode, strlen($terminalcode));
                                }
                                if ($rsiteID <> 1)
                                {
                                    echo "<option value=\"" . $rsiteID . "\">" . $vcode . "</option>";
                                }
                            }
                            echo "</select>";
                            ?>
                            <label id="txtsitename"></label><label id="txtposaccno"></label>
                        </td>
                    </tr>
                    <tr>
                        <td>Terminals</td>
                        <td>
                            <select id="cmbterminals" name="cmbterminals">
                                <option value="-1">Please Select</option>
                            </select>
                            <label id="txttermname"></label>
                        </td>
                    </tr>
                    <tr>
                        <td>Provider</td>
                        <td>
                            <?php
                            echo "<select id=\"cmbservices\" name=\"cmbservices\">";
                            echo "<option value=\"-1\">Please Select</option>";
                            $rgetservices = $_SESSION['getservices'];
                            foreach ($rgetservices as $result)
                            {
                                $vservicegroup = $result['ServiceGroupID'];
                                $vserviceID = $result['ServiceID'];
                                $vname = $result['ServiceName'];
                                //if "RTG" (regardless of case sensetivity) was found on the menugroup, then display only RTG servers
                                if (preg_match("/RTG/i", $menugroup))
                                {
                                    //if "RTG2" (regardless of case sensetivity) was found on the menugroup, then display only RTG servers
                                    if (preg_match("/RTG2/i", $menugroup))
                                    {
                                        //select only RTG2 Servers from the DB 
                                        if ($vservicegroup == "4")
                                        {
                                            echo "<option value=\"" . $vserviceID . "\">" . $vname . "</option>";
                                        }
                                    }
                                    else
                                    {
                                        //select only RTG Servers from the DB 
                                        if ($vservicegroup == "1")
                                        {
                                            echo "<option value=\"" . $vserviceID . "\">" . $vname . "</option>";
                                        }
                                    }
                                }
                                //if "MG" (regardless of case sensetivity) was found on the menugroup, then display only MG server/s
                                if (preg_match("/MG/i", $menugroup))
                                {
                                    //select only MG Servers from the DB 
                                    if ($vservicegroup == "2")
                                    {
                                        echo "<option value=\"" . $vserviceID . "\">" . $vname . "</option>";
                                    }
                                }
                                if (preg_match("/PT/i", $menugroup))
                                {
                                    //select only MG Servers from the DB 
                                    if ($vservicegroup == "3")
                                    {
                                        echo "<option value=\"" . $vserviceID . "\">" . $vname . "</option>";
                                    }
                                }
                            }
                            echo "</select>";
                            ?>
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="txttermcode" id="termcode"/>
                <input type="hidden" name="txtpasscode" id="passcode" />
                <div id="submitarea">
                    <input id="submit" type="submit" value="Add Provider" onclick="return chkserviceass();"/>
                </div>
            <div id="loading" style="position: fixed; z-index: 5000; background: url('images/Please_wait.gif') no-repeat; height: 162px; width: 260px; margin: 50px 0 0 400px; display: none;"></div>
            <div id="fade2" class="black_overlay" oncontextmenu="return false"></div>
            </form>
        </div>
        <?php
    }
}
include "footer.php";
?>

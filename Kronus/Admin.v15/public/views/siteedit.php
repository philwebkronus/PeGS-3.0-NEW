<?php
$pagetitle = "Edit Site / PEGS Profile";
include 'process/ProcessSiteManagement.php';
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
            if((!isset($_SESSION['ressitedet'])))
            {
                echo "<script type='text/javascript'>window.location.href='siteview.php';</script>";
            }
            else
            {
                $rdemogs = $_SESSION['demographics'];
                $rregions = $rdemogs['Regions'];
                $rprovinces = $rdemogs['Provinces'];
                $rcities = $rdemogs['Cities'];
                $rbarangays = $rdemogs['Barangays'];
                $rsitedet = $_SESSION['ressitedet'];
                foreach ($rsitedet as $resultviews)
                {
                    $rsiteid = $resultviews['SiteID'];
                    $vsiteName = $resultviews['SiteName'];
                    $arrsiteCode = strstr($resultviews['SiteCode'], '-');
                    if($arrsiteCode)
                        $vsiteCode = explode('-',$arrsiteCode);
                    else
                        $vsiteCode[1] = $resultviews['SiteCode'];

                    $vownerAID = $resultviews['OwnerAID'];
                    $vsiteDesc = $resultviews['SiteDescription'];
                    $vsiteAlias = $resultviews['SiteAlias'];
                    $vsiteAddress = $resultviews['SiteAddress']; 
                    //$vgrpID = $resultviews['SiteGroupID'];
                    $vislandName =$resultviews['IslandName'];
                    $vregionName = $resultviews['RegionName'];
                    $vprovinceName = $resultviews['ProvinceName'];
                    $vcityName = $resultviews['CityName'];
                    $vbrgyName = $resultviews['BarangayName'];
                    $vIslandID = $resultviews['IslandID'];
                    $vregionID = $resultviews['RegionID'];
                    $vprovID =$resultviews['ProvinceID'];
                    $vcityID = $resultviews['CityID'];
                    $vbrgyID = $resultviews['BarangayID'];
                    $vCTO = $resultviews['CTO'];
                    $vpasscode = $resultviews['PassCode'];
                    $vposaccno = $resultviews['POS'];
                    $vistestsite = $resultviews['isTestSite'];
                    $vsiteClassification = $resultviews['SiteClassificationID'];
                    $vcontactno = explode("-", $resultviews['ContactNumber']);
                }
                
                if(count($vcontactno) == 1)
                {
                    $vcountryLine = $vcontactno[0];
                    $vareaLine = "";
                    $vnumLine = "";
                }
                elseif(count($vcontactno) == 2)
                {
                    $vcountryLine = $vcontactno[0];
                    $vareaLine = $vcontactno[1];
                    $vnumLine = "";
                }
                else{
                    list($vcountryLine, $vareaLine, $vnumLine) = $vcontactno;
                }
                $arrolddetails = array($vownerAID, $vIslandID, $vregionID, $vprovID, $vcityID, $vbrgyID, $vsiteName, $vsiteCode[1], $vsiteDesc, $vCTO, $vsiteAddress, $vpasscode, $resultviews['ContactNumber'], $vistestsite);
                $olddetails = implode("-", $arrolddetails);
            }

?>

<div id="workarea">    
        <script type="text/javascript">
          $(document).ready(function(){
              var display ;
                $('#cmbislands').live('change', function(){                    
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

                $('#cmbregions').live('change', function(){
                    sendRegionID($(this).val());
                    $('#cmbprovinces').empty();
                    $('#cmbprovinces').append($("<option />").val("-1").text("Please Select"));
                    $('#cmbcity').empty();
                    $('#cmbcity').append($("<option />").val("-1").text("Please Select"));
                    $('#cmbbrgy').empty();
                    $('#cmbbrgy').append($("<option />").val("-1").text("Please Select"));
                });

                $('#cmbprovinces').live('change', function(){
                    sendProvID($(this).val());
                    $('#cmbcity').empty();
                    $('#cmbcity').append($("<option />").val("-1").text("Please Select"));
                    $('#cmbbrgy').empty();
                    $('#cmbbrgy').append($("<option />").val("-1").text("Please Select"));
                });

                $('#cmbcity').live('change', function(){
                    sendCityID($(this).val());
                    $('#cmbbrgy').empty();
                    $('#cmbbrgy').append($("<option />").val("-1").text("Please Select"));
                });
                
                $('#cmbsiteowner').live('change', function(){
                    if(document.getElementById('cmbsiteowner').value != '-1'){
                            $('#displayStatus').empty();
                            var url = 'process/ProcessSiteManagement.php';
                            $.ajax({
                                url: url,
                                type: 'POST',
                                data:"sendOwnerID="+$(this).val(),
                                success: function(data){
                                    jQuery('#displayStatus').text(data);
                                }
                            });
                    } else {
                        $('#displayStatus').empty();
                    }
                });
  
            });
        </script>
        
        <div id="pagetitle"><?php echo $pagetitle; ?></div>
        <br />
        
        <form method="post" action="process/ProcessSiteManagement.php" onsubmit="return chktrailingsitespaces();">
            <input type="hidden" name="page" value='SiteUpdate'>
            <input type="hidden" name="txtsiteid" value="<?php echo $rsiteid; ?>" />
            <input type="hidden" name="txtolddetails" value="<?php echo $olddetails; ?>" />
            <table>
                <tr>
                    <td width="130px;">Owner</td>
                    <td>
                        <?php
                            $vowner = $_SESSION['owner'];
                            $status = "";
                            echo "<select id=\"cmbsiteowner\" name=\"cmbsiteowner\" >";
                            echo "<option value=\"-1\">Please Select</option>";
                            foreach ($vowner as $resultdet){
                               $vaccID = $resultdet['AID'];
                               $vaccname = $resultdet['UserName'];
                               if($vownerAID == $vaccID)
                               {
                                    echo "<option value=\"".$vownerAID."\" selected=\"selected\">".$vaccname."</option>";
                                    $vstatus = $resultdet['Status'];
                                    switch ($vstatus){
                                        case 1:
                                            $status = 'Active';
                                            break;
                                        case 6;
                                            $status = 'Password Expired';
                                            break;
                                    }
                               }
                             else 
                               {
                                    echo "<option value=\"".$vaccID."\">".$vaccname."</option>";
                               }
                            }
                             echo "</select>";
                        ?>
                        <label id="displayStatus" ><?php echo $status;?></label>
                    </td>
                    <input type="hidden" name="txtoldowner" value="<?php echo $vownerAID; ?>" />
                </tr>
                 <tr>
                    <td>Island</td>
                    <td>
                        <?php
                            $vislands = $_SESSION['resislands'];
                            echo "<select id=\"cmbislands\" name=\"cmbislands\">";
                            echo "<option value=\"-1\">Please Select</option>";
                            foreach ($vislands as $resultdet){
                               $vname = $resultdet['IslandName'];
                               $rIslandId = $resultdet['IslandID'];
                                   if($vIslandID == $rIslandId)
                                   {
                                     echo "<option value=\"".$vIslandID."\" selected=\"selected\">".$vname."</option>";
                                   }
                                   else{
                                    echo "<option value=\"".$rIslandId."\">".$vname."</option>";   
                                   }
                            }

                             echo "</select>";
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>Region</td>
                    <td>
                        <div id="regions">
                            <?php
                                echo "<select id=\"cmbregions\" name=\"cmbregions\">";
                                echo "<option value=\"-1\">Please Select</option>";
                                foreach ($rregions as $resultdet)
                                {
                                   $vname = $resultdet['RegionName'];
                                   $rregionID = $resultdet['RegionID'];
                                       if($vregionID == $rregionID)
                                       {
                                         echo "<option value=\"".$vregionID."\" selected=\"selected\">".$vname."</option>";
                                       }
                                       else{
                                        echo "<option value=\"".$rregionID."\">".$vname."</option>";   
                                       }
                                }
                                echo "</select>";
                            ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>Province</td>
                    <td>
                        <div id="provinces">
                            <?php
                                echo "<select id=\"cmbprovinces\" name=\"cmbprovinces\">";
                                echo "<option value=\"-1\">Please Select</option>";
                                foreach ($rprovinces as $resultdet)
                                {
                                   $vname = $resultdet['ProvinceName'];
                                   $rprovinceID = $resultdet['ProvinceID'];
                                       if($vprovID == $rprovinceID)
                                       {
                                         echo "<option value=\"".$vprovID."\" selected=\"selected\">".$vname."</option>";
                                       }
                                       else{
                                        echo "<option value=\"".$rprovinceID."\">".$vname."</option>";   
                                       }
                                }
                                echo "</select>";
                            ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>City</td>
                    <td>
                        <?php
                            echo "<select id=\"cmbcity\" name=\"cmbcity\">";
                            echo "<option value=\"-1\">Please Select</option>";
                            foreach ($rcities as $resultdet)
                            {
                               $vname = $resultdet['CityName'];
                               $rcityID = $resultdet['CityID'];
                                   if($vcityID == $rcityID)
                                   {
                                     echo "<option value=\"".$vcityID."\" selected=\"selected\">".$vname."</option>";
                                   }
                                   else{
                                     echo "<option value=\"".$rcityID."\">".$vname."</option>";   
                                   }
                            }
                            echo "</select>";
                        ?>
                    </td>
                </tr>
                <tr>
                    <td>Barangay</td>
                    <td>
                        <?php
                            echo "<select id=\"cmbbrgy\" name=\"cmbbrgy\">";
                            echo "<option value=\"-1\">Please Select</option>";
                            foreach ($rbarangays as $resultdet)
                            {
                               $vname = $resultdet['BarangayName'];
                               $rbrgyID = $resultdet['BarangayID'];
                                   if($vbrgyID == $rbrgyID)
                                   {
                                     echo "<option value=\"".$vbrgyID."\" selected=\"selected\">".$vname."</option>";
                                   }
                                   else{
                                     echo "<option value=\"".$rbrgyID."\">".$vname."</option>";   
                                   }
                            }
                            echo "</select>";
                        ?>
                    </td>
                </tr>

                <tr>
                    <td>Site / PEGS Name</td>
                    <td>
                        <input type="text" name="txtsitename1" id="txtsitename" value="<?php echo $vsiteName; ?>" maxlength="50" size="50" onkeypress="return letter(event);" />
                    </td>
                </tr>

                <tr>
                    <td>Site / PEGS Code</td>
                    <td>
                        <input type="text" name="txtsitecode" id="txtsitecode" value="<?php echo $vsiteCode[1]; ?>" maxlength="5" size="5" onkeypress="return alphanumeric1(event);" />
                        <input type="hidden" name="chksitecode" id="chksitecode" value="<?php echo $vsiteCode[1]; ?>" maxlength="5" size="5" onkeypress="return alphanumeric1(event);" />
                        <label style="padding-left: 20px;">POS Account No: <?php echo $vposaccno; ?></label>
                    </td>
                </tr>

                <tr>
                    <td>Site / PEGS Description</td>
                    <td>
                        <input type="text" name="txtsitedesc" id="txtsitedesc" value="<?php echo $vsiteDesc; ?>" maxlength="50" size="50" onkeypress="return letter(event);"/>
                    </td>
                </tr>
<!--                <tr>
                    <td>Site / PEGS Alias</td>
                    <td>
                        <input type="text" name="txtsitealias" id="txtsitealias" value="<?php echo $vsiteAlias; ?>" maxlength="20" size="20" onkeypress="return numberandletter1(event);"/>
                    </td>
                </tr>-->
                <tr>
                    <td title="Certificate To Operate">CTO</td>
                    <td>
                        <input type="text" name="txtcto" id="txtcto" value="<?php echo $vCTO; ?>" maxlength="20" size="20" onkeypress="return numberandletter1(event);"/>
                    </td>
                </tr>
                <tr>
                    <td>Address (bldg., Street)</td>
                    <td>
                        <input type="text" name="txtsiteaddress" id="txtsiteaddress" value ="<?php echo $vsiteAddress; ?>" maxlength="150" size="80" onkeypress="return numberandletter1(event); "/>
                    </td>
                </tr>
                <tr>
                    <td>Contact Number</td>
                    <td>
                           <label>Country Code </label>
                           <input type="text" id="txtctrycode" name="txtctrycode" value="<?php echo $vcountryLine; ?>"  maxlength="5" size="5"  onkeypress="return numberonly(event);"/>
                           <label>Area Code </label>
                           <input type="text" id="txtareacode" name="txtareacode" value="<?php echo $vareaLine; ?>" maxlength="5" size="5"  onkeypress="return numberonly(event);"/>
                           <input type="text" id="txtphone" name="txtphone" value="<?php echo $vnumLine; ?>" maxlength="7" size="7"  onkeypress="return numberonly(event);"/>
                    </td>
                </tr>
                <tr>
                    <td>Passcode</td>
                    <td>
<!--                        <input type="text" name="txtpasscode" id="txtpasscode" maxlength="7" size="7" value="<?php //$siteID = str_pad($rsiteid, 3, '0', STR_PAD_LEFT);if(strlen($vpasscode) > 4){echo $vpasscode;}else{echo $vpasscode.$siteID;} ?>" onkeypress="return numberonly(event);" />-->
                        <input type="text" readonly="readonly" name="txtpasscode" id="txtpasscode" maxlength="7" size="7" value="<?php echo $vpasscode; ?>" onkeypress="return numberonly(event);" />
                    </td>
                </tr>
                <tr>
                    <td>Site Classification</td>
                    <td>
                        <?php if($vsiteClassification == 1) { ?>
                             Platinum<input type="radio" id="siteClass" name="siteClass" value="2" />
                       Non - Platinum<input type="radio" id="siteClass" name="siteClass" value="1" checked />
                        <?php }else{ ?>
                             Platinum<input type="radio" id="siteClass" name="siteClass" value="2"  checked //>
                       Non - Platinum<input type="radio" id="siteClass" name="siteClass" value="1">
                        <?php }?>
                    </td>
                </tr>
                <tr>
                    <td>isTestSite</td>
                    <td>
                        <?php if($vistestsite == 0) { ?>
                            Yes<input type="radio" id="opttestyes" name="opttest" value="1" />
                            No<input type="radio" id="opttestno" name="opttest" value="0"  checked/>
                        <?php }else{ ?>
                            Yes<input type="radio" id="opttestyes" name="opttest" value="1" checked/>
                            No<input type="radio" id="opttestno" name="opttest" value="0"  />
                        <?php }?>
                    </td>
                </tr>
            </table>   
            
            <div id="submitarea">
                <input type="button" value="Change Status" onclick="window.location.href='process/ProcessSiteManagement.php?siteid=<?php echo $rsiteid; ?>'+'&statuspage='+'UpdateStatus'"/>
                <input type="submit" value="Submit" onclick="return chkeditsites();"/>
            </div>
        </form>
        
</div>
<?php  
    }
}
include "footer.php"; ?>

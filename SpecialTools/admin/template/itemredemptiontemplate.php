<div id="itemmessagebody" class="itemmessagebody" style="background-color: #FFFFFF; display:none;" align="center">
    <?php
        $counter = count($_SESSION['RewardOfferCopy']["SerialNumber"]);
        for($itr = 0; $itr < $counter; $itr++){
            $serialcode = $_SESSION['RewardOfferCopy']["SerialNumber"][$itr]; 
            $securitycode = $_SESSION['RewardOfferCopy']["SecurityCode"][$itr]; 
    ?>
            <hr>
            <br>
            <table cellpadding="0" cellspacing="0" width="812" style="text-align: left;font-family: arial; font-size: 9pt;background-color: #FFFFFF;">
                <tr>
                    <td style="vertical-align:top;" align="center" colspan ="2">
                        <img src="<?php echo $newheader; ?>" width="812" height="80"/>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align:top;" colspan ="1" width="43%">
                        <br/>
                        <img src="<?php echo $itemimage; ?>" style="height:auto; width:320px; max-width:320px; max-height:350px;"/>
                    </td>
                    <td style="vertical-align:top;" align="center" colspan ="1" style="font-size: 14pt;">
                        <br/>
                        <table width="100%" cellpadding="2" style="text-align: left;">
                            <tr>
                                <td colspan="2" style="font-size: 22px;"><b><?php echo $itemname; ?></b></td>
                            </tr>
                            <tr>
                                <td colspan="2">E-GAMES PARTNER: &nbsp; <?php echo $partnername; ?></td>
                            </tr>
                            <tr>
                                <td colspan="2">COUPON OWNER: </td>
                            </tr>
                            <tr>
                                <td colspan="2"><?php echo $playername; ?></td>
                            </tr>
                            <tr>
                                <td colspan="2">MEMBERSHIP CARD NUMBER: &nbsp;<?php echo $cardnumber; ?></td>
                            </tr>
                            <tr>
                                <td colspan="2">DATE OF REDEMPTION: &nbsp;<?php echo $redemptiondate; ?></td>
                            </tr>
                            <tr>
                                <td colspan="2"><br/><hr/></td>
                            </tr>
                            <tr>
                                <td style="vertical-align:text-top;">E-COUPON SERIAL CODE: &nbsp;&nbsp;&nbsp;&nbsp;<br/>
                                        <?php echo $serialcode; ?> <br/>
                                        E-COUPON SECURITY CODE: &nbsp;&nbsp;&nbsp;&nbsp;<br/>
                                        <?php echo $securitycode; ?> <br/>
                                        AVAIL REWARD UNTIL: &nbsp;&nbsp;&nbsp;&nbsp;<br/>
                                        <?php echo $enddate; ?>
                                </td>
                                <td style="vertical-align:text-top; width: 200px; text-wrap: normal;"> <b ><span style="font-size: 14px;"><?php echo $partnername; ?></span></b><br/>
                                        <span style="font-size: 10px;"><?php echo $companyaddress; ?> <br/>
                                        Tel. Nos: <?php echo $companyphone; ?> <br/>
                                        Website: <?php echo $companywebsite; ?></span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr><td colspan="2" style="height: 15px;"></td></tr>
                <tr>
                    <td colspan="2">
                        <img src="<?php echo $importantreminder; ?>" style="height:40px; width:812px;"/>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align:top;" colspan ="2" align="justify" style=" font-size: 9pt;">
                        <br/>
                        <p style="font-size: 14px;"><b>ABOUT THIS REWARD</b></p>
                        <?php echo $about ?> <br/>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align:top;" colspan ="2" align="justify" style=" font-size: 9pt;">
                        <hr/>
                        <p style="font-size: 14px;"><b>TERMS OF REWARD AVAILMENT</b></p>
                        <?php echo $term; ?>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align:top;" align="center" colspan ="2">
                        <img src="<?php echo $newfooter; ?>" width="812" height="40" />
                    </td>
                </tr>
            </table>
    <?php
        }
        unset($_SESSION['RewardOfferCopy']); 
    ?>
</div>

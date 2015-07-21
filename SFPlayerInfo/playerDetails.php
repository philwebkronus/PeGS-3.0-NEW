<?php
include("init.inc.php");
include_once("playerDetailscontroller.php");
?>
<html>
    <head>
        <style>
            table, th, td{
                border: 1px solid black;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12px;
                text-align: left;
            }
        </style>
    </head>
    <body oncontextmenu="return false" onselectstart="return false" ondragstart="return false">
        <form method="GET" name="frmPlayerDtls" id="frmPlayerDtls">
            <table cellspacing="0" width="100%" height="200px">
                <tr align="left" style="background-color: black; color:white; width: 100%;">
                    <th colspan ="4" >
                        Player Details:
                    </th>
                </tr>
                <tr>
                    <th>
                        Name:
                    </th>
                    <td>
                        <?php echo $rowEnc['FirstName'] . " " . $rowEnc['MiddleName'] . " " . $rowEnc['LastName']; ?>
                    </td>
                    <th>
                        Card Number
                    </th>
                    <td>
                        <?php
                        echo $cardNumber;
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Birthdate:
                    </th>
                    <td>
                        <?php echo date('d-M-y', strtotime($rowNonEnc['Birthdate'])); ?>
                    </td>
                    <th>
                        Membership Type:
                    </th>
                    <td>
                        <?php
                        if (strlen($cardNumber > 8))
                            echo 'Migrated';
                        else
                            echo 'New';
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Address:
                    </th>
                    <td>
                        <?php echo $rowEnc['Address1']; ?>
                    </td>
                    <th>
                        Member Status:
                    </th>
                    <td>
                        <?php echo $rowEnc['Status']; ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Email:
                    </th>
                    <td>
                        <?php echo $rowEnc['Email']; ?>
                    </td>
                    <th>
                        Date of Membership (Red):
                    </th>
                    <td>
                        <?php
                        if (!$isnew)
                        {
                            if (strlen($cardNumber > 8))
                                echo 'N/A';
                            else
                                echo date('d-M-y', strtotime($cardDateCreated));
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        ID Presented:
                    </th>
                    <td>
                        <?php echo $rowIDPresented; ?>
                    </td>
                    <th>
                        Date of Membership (Green):
                    </th>
                    <td>
                        <?php
                        if (!$isnew)
                        {
                            if (strlen($cardNumber > 8))
                                echo date('d-M-y', strtotime($cardDateCreated));
                            else
                                echo 'N/A';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        ID Number:
                    </th>
                    <td>
                        <?php echo $rowEnc['IdentificationNumber']; ?>
                    </td>
                    <th>
                        Mobile Number:
                    </th>
                    <td>
                        <?php echo $rowEnc['MobileNumber']; ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Current Points:
                    </th>
                    <td>
                        <?php echo $isnew ? 0 : number_format($currentPoints); ?>
                    </td>
                    <th>
                        Last Site Played:
                    </th>
                    <td>
                        <?php echo $isnew ? '' : $rowSite['SiteName']; ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Lifetime Points:
                    </th>
                    <td>
                        <?php echo $isnew ? 0 : number_format($lifetimePoints); ?>
                    </td>
                    <th>
                        Last Date Played:
                    </th>
                    <td>
                        <?php
                        echo $isnew ? '' : (isset($rowLastTransaction['TransactionDate']) ? date('M d, Y ', strtotime($rowLastTransaction['TransactionDate'])) : '');
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        Redeemed Points:
                    </th>
                    <td>

                        <?php echo $isnew ? 0 : number_format($redeemedPoints); ?>
                    </td>
                    <th>
                        Last Reload Amount:
                    </th>
                    <td>
                        <?php echo $isnew ? 0.00 : number_format($rowLastReloadTransaction['Amount'], 2); ?>
                    </td>

                </tr>
                <tr>
                    <th></th>
                    <td></td>
                    <th>
                        e-SAFE Playable Balance:
                    </th>
                    <td>
                        <?php echo $playableBalance; ?>
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>
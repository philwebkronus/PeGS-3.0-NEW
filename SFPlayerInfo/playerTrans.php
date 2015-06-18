<?php
include("init.inc.php");
include_once("playerTranscontroller.php");
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
        <form method="GET" name="frmPlayerTrans" id="frmPlayerTrans">
            <table cellspacing="0" width="100%">
                <tr align="left" style="background-color: black; color:white; width: 100%;">
                    <th colspan="4">
                        Player's Transaction Summary:
                    </th>
                </tr>
                <tr>
                    <th>
                        Site Played
                    </th>
                    <th>
                        Total Load
                    </th>
                    <th>
                        Total Redemption
                    </th>
                    <th>
                        Casino Winnings
                    </th>
                </tr>
                <?php
                $arrTotalLoad[] = '';
                $arrTotalWithdrawal[] = '';
                $arrTotalCasinoWinnings[] = '';
                $casinoWinnings = 0;
                foreach ($PTS as $singlePtsResult)
                {
                    $siteID = $singlePtsResult['SiteID'];
                    $siteIDResult = $_Sites->getSite($siteID);
                    $siteName = $siteIDResult[0]['SiteName'];
                    $totalDeposit = $singlePtsResult['TotalDeposit'];
                    $totalReload = $singlePtsResult['TotalReload'];
                    $totalWithdrawal = $singlePtsResult['TotalWithdrawal'];
                    $totalLoad = $totalDeposit + $totalReload;

                    $casinoWinnings = (float) $totalLoad - (float) $totalWithdrawal;
                    if ($casinoWinnings < 0)
                    {
                        echo "<tr><td>" . $siteName . "</td><td>" . number_format($totalLoad, 2) . "</td><td>" . number_format($totalWithdrawal, 2) . "</td><td>(" . number_format(abs($casinoWinnings), 2) . ")</td></tr>";
                    }
                    else
                    {
                        echo "<tr><td>" . $siteName . "</td><td>" . number_format($totalLoad, 2) . "</td><td>" . number_format($totalWithdrawal, 2) . "</td><td>" . number_format($casinoWinnings, 2) . "</td></tr>";
                    }
                    $arrTotalLoad[] = $totalLoad;
                    $arrTotalWithdrawal[] = $totalWithdrawal;
                    $arrTotalCasinoWinnings[] = $casinoWinnings;
                }
                ?>
                <tr>
                    <th>
                        Total
                    </th>
                    <th>
                        <?php echo number_format((float) array_sum($arrTotalLoad), 2, '.', ','); ?>
                    </th>
                    <th>
                        <?php echo number_format((float) array_sum($arrTotalWithdrawal), 2, '.', ','); ?>
                    </th>
                    <th>
                        <?php
                        if ($casinoWinnings < 0)
                        {
                            echo '(' . number_format((float) abs(array_sum($arrTotalCasinoWinnings)), 2, '.', ',') . ')';
                        }
                        else
                        {
                            echo number_format((float) array_sum($arrTotalCasinoWinnings), 2, '.', ',');
                        }
                        ?>
                    </th>
                </tr>
            </table>
        </form>
    </body>
</html>
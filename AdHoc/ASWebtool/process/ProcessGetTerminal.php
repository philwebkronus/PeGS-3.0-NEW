<?php

include '../PDOhandler.php';
$PDO = new PDOhandler($svrname, $dbType, $dbPort, $dbName, $dbUsername, $dbPassword);

if (isset($_GET['SiteID'])) {

    $siteID = $_GET['SiteID'];

    $getTerminalsPerSite = $PDO->getTerminals($siteID);

    if ($getTerminalsPerSite) {

        echo '<tr>
              <td>
                <select multiple id="terminalLists" name="terminalLists" size=30 style="width = 200%; height: 100%;">';

        foreach ($getTerminalsPerSite as $rowTerminals) {
            $TerminalID = $rowTerminals['TerminalID'];
            $TerminalCode = $rowTerminals['TerminalCode'];

            echo '<option value = "' . $TerminalID . '">' . str_replace('ICSA-', '', strtoupper($TerminalCode)) . '</option>';
        }
        echo '</td></select></tr>';
    } else {
        echo "No Terminal/s Created!";
    }
} else {
    echo "error";
}

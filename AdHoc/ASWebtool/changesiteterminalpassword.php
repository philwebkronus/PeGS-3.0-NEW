<?php
ini_set('display_errors', true);
ini_set('log_errors', true);

include 'PDOhandler.php';
include 'config/config.php';
$PDO = new PDOhandler($svrname, $dbType, $dbPort, $dbName, $dbUsername, $dbPassword);

if ($_SERVER['REMOTE_ADDR']) {
    if (in_array(htmlspecialchars(trim($_SERVER['REMOTE_ADDR'])), $IPrestriction)) {
        session_start();
        if (!empty($_SESSION['loggedin'])) {
//get all sites
            $getAllSites = $PDO->getSites();

            $getActiveCasinos = $PDO->getActiveCasinos();
            ?>

            <html>
                <head>
                    <title>Change Site Terminal Password Web Tool</title>
                    <script src="js/jquery.min.js"></script>
                    <link rel="stylesheet" type="text/css" href="css/styles.css">
                </head>

                <body style="position: static;">
                    <div class="ajax-loader">
                        <img src="images/Please_wait.gif" class="img-responsive"  />
                    </div>
                    <div class="navbar">
                        <?php include('navbar.php'); ?>
                    </div>
                    <div style="width: 100%; margin-top : 50px;">
                        <div style="float:left; width: 45%; border: 1px solid black;">
                            <legend>
                                <center><h1>Change Site Terminal Password Web Tool</h1></center>
                                <hr><br>
                                <table>
                                    <tr>
                                        <td><b>*Site : </b></td>
                                        <td>
                                            <select name="sites" id="sites">
                                                <option value="">-------------------Select Site---------------</option>
                                                <?php
                                                //get All Sites Active
                                                foreach ($getAllSites as $rowSites) {
                                                    echo "<option value='" . $rowSites['SiteID'] . "'>" . str_replace('ICSA-', '', strtoupper($rowSites['SiteCode'])) . "</option>";
                                                }
                                                ?>
                                            </select> 
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>*Terminals :</b></td>
                                        <td>
                                            <div id="terminals">Loading....</div>
                                        </td>
                                    </tr>
                                    <tr id ="active-casino" style="visibility: hidden;">
                                        <td><b>*Casino :</b></td>
                                        <td>
                                            <select name="casino" id="casino">
                                                <option value="">-------------------Select Casino---------------</option>
                                                <?php
                                                //get All Sites Active
                                                foreach ($getActiveCasinos as $rowCasinos) {
                                                    echo "<option value='" . $rowCasinos['ServiceID'] . "'>" . str_replace('ICSA-', '', strtoupper($rowCasinos['ServiceName'])) . "</option>";
                                                }
                                                ?>
                                            </select> 
                                        </td>
                                    </tr>
                                    <tr id ="submit" style="visibility: hidden;">
                                        <td><b>Submit : </b></td>
                                        <td>
                                            <button class="submit-button">Submit Query</button>
                                        </td>
                                    </tr>
                                    <tr id ="retry" style="visibility: hidden;">
                                        <td><b></b></td>
                                        <td>
                                            <button class="retry-button" value="Refresh Page" onClick="location.href = location.href">Retry</button>
                                        </td>
                                    </tr>
                                </table>
                                <p id="max-terminal" value="<?php echo $max_terminal_process; ?>" style="visibility: hidden;"><?php echo $max_terminal_process; ?></p>
                                <div id="result"></div>
                            </legend>
                        </div>
                        <div style="float:right;width: 54% ;border: 1px solid black;">
                            <center><h1>Logs</h1></center>
                            <hr><br>
                            <div id="contents" style="height : 600px;overflow-y:scroll; font-size : 8px;"></div> 
                        </div>
                    </div>
                    <div style="clear:both"></div>
                    <div id="footer">
                        <p><b>&copy; Phil<span style='color: red;'>Web</span></b>  <?php echo date('Y') ?>
                            <span style='font-size : 8px; display: block; margin-top : -15px;'><i>coded by John Aaron Vida</i></span></p>
                    </div>

                </body>
            </html>

            <script type="text/javascript">
                $(document).ready(function() {

                    get_contents();
                    $('#terminalLists').val('');
                    $('#terminalLists :selected').text('');

                    function get_contents() {
                        $.get('FileReadChangePassword.php?tail', function(data) {
                            $('#contents').html(data);
                        });
                        //setTimeout(get_contents, 2000);
                    }


                    $('.submit-button').click(function() {

                        $('.submit-button').attr('disabled', true);
                        var TerminalIDs = $('#terminalLists').val();
                        var TCodes = $.map($("#terminalLists option:selected"), function(el, i) {
                            return $(el).text();
                        });
                        var TerminalCodes = TCodes.join(", ");
                        var SiteID = $('#sites :selected').val();
                        var SiteCode = $('#sites :selected').text();
                        var CasinoID = $('#casino :selected').val();
                        var CasinoCode = $('#casino :selected').text();

                        var ret = TerminalCodes.replace(/,/g, '\n');
                        var max_terminal = $('#max-terminal').text();

                        if (SiteID !== null && SiteCode !== null && TerminalIDs !== null && TerminalCodes !== null && CasinoID !== null && CasinoID !== '') {

                            if (TerminalIDs.length > max_terminal) {
                                alert("Exceeds Maximum Terminals that can process. Please limit it to " + max_terminal + " pairs of Regular/VIP terminals only!");
                                //                                $('#retry').css("visibility", "visible");
                                $('.submit-button').attr('disabled', false);
                                exit;
                            } else {
                                var answer = confirm("Are you sure you want to change the password of " + CasinoCode + " on the following terminals? \n" + ret);
                                if (answer) {
                                    $('.ajax-loader').css("visibility", "visible");
                                    $.ajax({
                                        url: 'process/ProcessChangeTerminalPassword.php',
                                        method: "POST",
                                        data: {
                                            TerminalIDs: TerminalIDs,
                                            TerminalCodes: TerminalCodes,
                                            SiteID: SiteID,
                                            SiteCode: SiteCode,
                                            CasinoID: CasinoID,
                                            CasinoCode: CasinoCode
                                        },
                                        dataType: 'html',
                                        success: function(data) {
                                            get_contents();
                                            $('#retry').css("visibility", "visible");
                                            $('.retry-button').attr('disabled', false);
                                            $('.ajax-loader').css("visibility", "hidden");
                                            $('#result').html(data);
                                        }
                                    });
                                }
                                else {
                                    $('#retry').css("visibility", "visible");
                                    $('.retry-button').attr('disabled', false);
                                }
                            }

                        } else {
                            $('.submit-button').attr('disabled', false);
                            alert("Please complete needed information!!");
                        }

                    });

                    $('#sites').change(function() {
                        $('.submit-button').attr('disabled', false);
                        var SiteID = $("#sites").val();

                        $('.ajax-loader').css("visibility", "visible");
                        $('#active-casino').css("visibility", "hidden");
                        $.ajax({
                            url: "process/ProcessGetTerminal.php",
                            method: "POST",
                            data: {SiteID: SiteID},
                            datatype: "html",
                            success: function(data) {
                                $('#terminals').html(data);
                                if (data !== "No Terminal/s Created!") {
                                    $('#submit').css("visibility", "visible");
                                    $('#active-casino').css("visibility", "visible");
                                } else {
                                    $('#active-casino').css("visibility", "hidden");
                                }
                                $('.ajax-loader').css("visibility", "hidden");
                            }
                        });
                    });
                });

            </script>


            <?php
        } else {
            header("Location: login.php");
            die();
        }
    } else {
        header("Location: forbidden.php");
        die();
    }
}
?>

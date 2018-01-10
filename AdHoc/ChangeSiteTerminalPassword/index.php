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
                    <style>
                        body{
                            margin-top : 50px;
                            font-family: helvetica, sans-serif;
                        }
                        .ajax-loader {
                            visibility: hidden;
                            background: black;
                            opacity: .75;
                            filter: alpha(opacity=75);
                            position: absolute;
                            z-index: +100 !important;
                            width: 100%;
                            height: 100%;
                        }
                        .ajax-loader img {
                            position: relative;
                            top:50%;
                            left:50%;
                        }

                        /* The navigation bar */
                        .navbar {
                            overflow: hidden;
                            background-color: #333;
                            position: fixed; /* Set the navbar to fixed position */
                            top: 0; /* Position the navbar at the top of the page */
                            width: 100%; /* Full width */
                        }

                        /* Links inside the navbar */
                        .navbar a {
                            float: left;
                            display: block;
                            color: #f2f2f2;
                            text-align: center;
                            padding: 14px 16px;
                            text-decoration: none;
                        }

                        /* Change background on mouse-over */
                        .navbar a:hover {
                            background: #ddd;
                            color: black;
                        }

                        /* Main content */
                        .main {
                            margin-top: 30px; /* Add a top margin to avoid content overlay */
                        } 
                        #footer {
                            /*                    background: #ccc;
                                                border-top: 4px solid red;*/
                            clear: both;
                            height: 70px;
                            margin-top: 100px;
                            position: relative;
                            width: 100%;
                            text-align: center;
                            line-height:  30px;
                        }
                    </style>
                </head>

                <body style="position: static;">
                    <div class="ajax-loader">
                        <img src="images/Please_wait.gif" class="img-responsive"  />
                    </div>
                    <div class="navbar">
                        <a href="index.php">Home</a>
                        <a href="log/runtime.txt" target="_blank">View Full Logs</a>
                        <div style="float:right">
                            <a href="logout.php" title="Logout"><span>LOGOUT AS <?php echo strtoupper($_SESSION['loggedin']); ?></span></a>
                        </div>
                    </div>
                    <div style="width: 100%;">
                        <div style="float:left; width: 49%; border: 1px solid black;">
                            <legend>
                                <h1>Change Site Terminal Password Web Tool</h1>
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

                                <div id="result"></div>
                            </legend>
                        </div>
                        <div style="float:right;width: 49% ;border: 1px solid black;">
                            <h1>Logs</h1>
                            <hr><br>
                            <div id="contents" style="height : 600px;overflow-y:scroll;font-size : 8px;"></div> 
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
                        $.get('FileRead.php?tail', function(data) {
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

                        if (SiteID !== null && SiteCode !== null && TerminalIDs !== null && TerminalCodes !== null && CasinoID !== null && CasinoID !== '') {

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
                                //some code
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

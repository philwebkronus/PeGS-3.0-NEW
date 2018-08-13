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
            ?>


            <html>
                <head>
                    <title> Habanero Userbased Accounts Creation Web Tool </title>
                    <script src="js/jquery.min.js"></script>
                    <link rel="stylesheet" type="text/css" href="css/styles.css">
                </head>

                <body style="position: static;">
                    <div class="ajax-loader">
                        <img src="images/Please_wait.gif" class="img-responsive"  />
                    </div>
                    <div class="navbar">
                        <?php include('navbar.php') ?>
                    </div>
                    <div style="width: 100%; margin-top : 50px;">
                        <div style="float:left; width: 49%; border: 1px solid black;">
                            <legend>
                                <center><h1>Habanero Userbased Accounts Creation Web Tool</h1></center>
                                <hr><br>
                                <table>
                                    <tr>
                                        <td><b>*No. of Accounts to Create : </b></td>
                                        <td>
                                            <input type="text" id="count">
                                        </td>
                                        <td>
                                            <button class="submit-button">Submit Query</button>
                                        </td>
                                    </tr>
                                </table>
                                <p id="max-terminal" value="<?php echo $max_terminal_process_creation; ?>" style="visibility: hidden;"><?php echo $max_terminal_process_creation; ?></p>
                                <div id="result"></div>
                            </legend>
                        </div>
                        <div style="float:right;width: 49% ;border: 1px solid black;">
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
                    function get_contents() {
                        $.get('FileReadHabaneroUserbasedAccountCreation.php?tail', function(data) {
                            $('#contents').html(data);
                        });
                        //setTimeout(get_contents, 2000);
                    }


                    $('.submit-button').click(function() {

                        //$('.submit-button').attr('disabled', true);

                        var Count = $('#count').val();
                        var max_terminal = $('#max-terminal').text();

                        if (Count !== '') {
                            if (parseInt(Count) > parseInt(max_terminal)) {
                                alert("Exceeds Maximum Accounts that can process. Please limit it to " + max_terminal + " Accounts only!");
                                $('.submit-button').attr('disabled', false);
                                exit;
                            } else {
                                var answer = confirm("Are you sure you want to create habanero user account?");
                                if (answer) {
                                    $('.ajax-loader').css("visibility", "visible");
                                    $.ajax({
                                        url: 'process/ProcessCreateHabaneroUserBasedAccount.php',
                                        method: "POST",
                                        data: {
                                            Count: Count,
                                        },
                                        dataType: 'html',
                                        success: function(data) {
                                            get_contents();
                                            $('.ajax-loader').css("visibility", "hidden");
                                            $('#result').html(data);
                                        }
                                    });
                                }
                            }

                        } else {
                            $('.submit-button').attr('disabled', false);
                            alert("Please complete needed information!!");
                        }

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

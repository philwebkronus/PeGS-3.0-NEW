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
                    <title>Welcome to ASWebTools | Home</title>
                    <script src="js/jquery.min.js"></script>
                    <link rel="stylesheet" type="text/css" href="css/styles.css">
                </head>

                <body style="position: static;">
                    <div class="navbar">
                        <?php include('navbar.php') ?>
                    </div>

                    <div style='font-weight: 800;;margin-top:  250px;font-size: 70px;font-family: helvetica, sans-serif;'>
                        <CENTER >
                            <hr style='width : 50%;'>
                            <span style='color: #8b0000;'>WELCOME!</span><br>
                            <hr style='width : 50%;'>
                        </CENTER>
                    </div>
                    <div id="footer">
                        <p><b>&copy; Phil<span style='color: red;'>Web</span></b>  <?php echo date('Y') ?>
                            <span style='font-size : 8px; display: block; margin-top : -15px;'><i>coded by John Aaron Vida</i></span></p>
                    </div>
                </body>
            </html>



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
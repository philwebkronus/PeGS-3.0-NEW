<?php
//ini_set('display_errors', true);
//ini_set('log_errors', true);

include 'config/config.php';
include 'PDOhandler.php';

$PDO = new PDOhandler($svrname, $dbType, $dbPort, $dbName, $dbUsername, $dbPassword);

if ($_SERVER['REMOTE_ADDR']) {
    if (in_array(htmlspecialchars(trim($_SERVER['REMOTE_ADDR'])), $IPrestriction)) {

        session_start();
        if (empty($_SESSION)) {

            if (!empty($_POST)) {
                if ($_POST['txtusername'] !== '' && $_POST['txtpassword'] !== '') {
                    $uname = strip_tags(trim($_POST['txtusername']));
                    $pwd = strip_tags(trim($_POST['txtpassword']));

                    if (array_key_exists($uname, $credentials)) {
                        if (isset($credentials[$uname]) && $credentials[$uname] == $pwd) {
                            session_start();
                            $_SESSION['loggedin'] = $uname;
                            $_SESSION['IP_Adddress'] = $_SERVER['REMOTE_ADDR'];

                            $title = "Login";
                            $errormessage = "Username : " . $uname . " | IP Address " . $_SERVER['REMOTE_ADDR'] . " | User Successfuly Logged-in!.";
                            $PDO->InsertLogs($title, $errormessage);
                            header("Location: index.php");
                            die();
                        } else {
                            echo "<script>alert('Invalid Username or Password!');</script>";

                            $title = "Login";
                            $errormessage = "Username : " . $uname . " | IP Address " . $_SERVER['REMOTE_ADDR'] . " | Invalid Username or Password!.";
                            $PDO->InsertLogs($title, $errormessage);
                        }
                    } else {
                        echo "<script>alert('Invalid Username or Password!');</script>";
                        $title = "Login";
                        $errormessage = "Username : " . $uname . " | IP Address " . $_SERVER['REMOTE_ADDR'] . " | Invalid Username or Password!.";
                        $PDO->InsertLogs($title, $errormessage);
                    }
                }
            }
            ?>

            <html>
                <head>
                    <title>Change Site Terminal Password Web Tool | Login</title>
                    <style>
                        body {
                            font-family: helvetica, sans-serif;
                        }

                        div, input {
                            box-sizing: border-box;
                        }

                        .container {
                            width: 500px;
                            height: 350px;
                            margin: auto;
                            background: -webkit-linear-gradient(left, #C2DD9A, #F5FBF0 50%, #C2DD9A); 
                        }


                        .header {
                            height: 44px;
                            line-height: 44px;
                            padding-left: 30px;
                            font-size: 20px;

                        }

                        form {
                            margin: 0 50px;
                        }

                        input[type=text], input[type=password] {
                            display: block;
                            width: 100%;
                            height: 48px;
                            padding-left: 38px;
                            color: #000;
                            border: 1px solid black;
                            transition: box-shadow .3s;
                            border-radius: 10px;
                            font-size: 20px;
                        }

                        input[type=text]:focus, input[type=password]:focus {
                            outline: 0;
                            box-shadow: inset 0 0 8px rgba(226,239,207,.7);
                        }

                        input[type=text] { margin-top: 10px; margin-bottom: 10px; }

                        input[type=password] { margin-top: 10px; margin-bottom: 10px; }

                        input[type=submit] { margin-top: 5px; margin-bottom: 20px; }

                        input[type=checkbox] {
                            -webkit-appearance: none;
                            width: 11px; height: 11px;
                            border-radius: 10px;
                            background: rgba(0,0,0,.5);
                            margin: 0 5px;
                        }

                        input[type=checkbox]:checked {
                            background: -webkit-radial-gradient(center center, circle, white 0%, white 30%, rgba(0,0,0,.5) 50%);
                        }
                        input[type=checkbox] + label {
                            color: white;
                            font-size: 15px;
                            padding-bottom: 3px;
                        }

                        input[type=submit] {
                            width: 100%; 
                            height: 51px;
                            border: 1px solid #B7DA7C;
                            background: skyblue;
                            font-weight: bold;
                            color: white;
                            border-radius: 10px;
                        }

                        input[type=submit]:active {
                            box-shadow: inset 0 -3 5px rgba(0,0,0,.5);
                            background: -webkit-linear-gradient(top, #649126, #7DB731);
                        }
                        #parent {
                            display: table;
                            width: 100%;
                            padding : 20px;
                        }
                        #form_login {
                            display: table-cell;
                            text-align: center;
                            vertical-align: middle;
                            background: rgba(0,0,0,.23);
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
                    <script>
 
                       function startTime() {
                            var today = new Date();
                            var h = today.getHours();
                            var m = today.getMinutes();
                            var s = today.getSeconds();
                            m = checkTime(m);
                            s = checkTime(s);
                            document.getElementById('txt').innerHTML =
                                    h + ":" + m + ":" + s;
                            var t = setTimeout(startTime, 500);
                        }
                        function checkTime(i) {
                            if (i < 10) {
                                i = "0" + i
                            }
                            ;  // add zero in front of numbers < 10
                            return i;
                        }
                    </script>
                </head>
                <body  onload="startTime()">
                    <br><br><br>
                <center> <h1>Change Site Terminal Password Web Tool </h1>
		    <hr style = 'width : 50%;'>
                    <div id="txt"></div>
		    <div><?php echo date('l - F d, Y'); ?></div>
                    <div style="width: 500px; margin: 50px auto 0 auto; border: 2px seagreen dashed">
                        <center>
                            <h1>Web Tool Login</h1>
                            <form name="bizLoginForm" method="post" action="" >
                                Username: <input type="text" id="txtusername" name="txtusername" required=""/><br />
                                Password: <input type="password" id="txtpassword" name="txtpassword" required=""/><br />
                                <input type="submit" value="Login" />
                            </form>
                        </center>
                    </div>
                    <br><br>
                    <span><i>Your IP Address is <?php echo $_SERVER['REMOTE_ADDR']; ?></i></span>
		<hr style = 'width : 50%;'>
                </center>
                <div id="footer">
                    <p><b>&copy; Phil<span style='color: red;'>Web</span>  <?php echo date('Y') ?></b>
                        <span style='font-size : 8px; display: block; margin-top : -15px;'><i>coded by John Aaron Vida</i></span></p>
                </div>
            </body>
            </html>

            <?php
        } else {
            header("Location: index.php");
            die();
        }
    } else {
        header("Location: forbidden.php");
        die();
    }
}
?>


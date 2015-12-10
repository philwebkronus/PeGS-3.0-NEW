<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

        <title>e-Games</title>
        <script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js?ver=1.8'></script>


        <link rel="pingback" href="https://membership.egamescasino.ph/xmlrpc.php" />
        <link rel="shortcut icon" href="https://membership.egamescasino.ph/wp-content/themes/e-games1/images/egames.ico" type="image/x-icon" />
        <link rel="icon" href="https://membership.egamescasino.ph/wp-content/themes/e-games1/images/egames.ico" type="image/x-icon"/>


        <link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,400,700,300,600' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" media="all" href="css/norm.css"/>

        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        <script>
            $(function() {
                $("#dialog").dialog({
                    modal: true,
                    resizable: false,
                    draggable: false,
                    closeOnEscape: false,
                    width: 400,
                    buttons: {
                        OK: function() {
                            $(this).dialog("close");
                        }
                    }
                });
            });
        </script>
        <script type="text/javascript">
            $(document).ready(function() {
                $("#clear").bind("click", function() {
                    $("input[type=text]").val("");
                });
            });
            $(document).on('keypress', '#pname', function(event) {
                var regex = new RegExp("^[a-zA-Z ]+$");
                var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
                if (!regex.test(key)) {
                    event.preventDefault();
                    return false;
                }
            });
            function isNumber(evt) {
                var iKeyCode = (evt.which) ? evt.which : evt.keyCode
                if (iKeyCode != 46 && iKeyCode > 31 && (iKeyCode < 48 || iKeyCode > 57))
                    return false;
                return true;
            }
        </script>
    </head>
    <body>

        <!-----content-------->

        <div class="content-wrapper"> 
            <div id="membership" class="content inner-page">

                <header id="header">
                    <div class="page-heading-wrapper">
                        <div class="container">
                            <h1 id ="logo"><a></a></h1>
                        </div>
                    </div>
                </header>
            </div>
        </div>

        <div id="main-content">
            <div class="container">

                <div id="membership-1" class="terms-content membership-content">
                    <div class="registration-form">
                        <h2>Registration</h2>
                        <form method="post">
                            <p><label>Mobile Number*</label><input required type="text" name="mobileNo" id="mobilenum" maxlength="11" placeholder="09XXXXXXXXX" onkeypress="javascript:return isNumber(event)"></p>
                            <p><label>Player Name*</label><input required type="text"  placeholder="Player Name" name="Name" id="pname" ></p>
                            <p><input type="button" value="Clear" id="clear">
                                <input type="submit" name="submit" value="Submit" id="submit"></p>
                            <div class="clearfix"></div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <?php if ($return == null) {
            
        } else {
            ?>
            <div id="dialog">
                <center><?php echo $return; ?></center>
            </div>
            <?php
        }
        ?>

    </body>
</html>
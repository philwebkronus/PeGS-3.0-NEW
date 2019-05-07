<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php include_once '../models/LPConfig.php'; ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="language" content="en" />
        <meta http-equiv="X-UA-Compatible" content="IE=9" />

        <link rel="stylesheet" type="text/css" href="../css/screen.css" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="../css/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
        <link rel="stylesheet" type="text/css" href="../css/lp.css" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="../css/reset.css" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="../css/reset2.css" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="../css/style.css" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="../css/font-awesome/css/font-awesome.min.css" />

        <script type="text/javascript" src="../js/jquery-1.7.1.min.js"></script>
        <script src="//code.jquery.com/ui/1.11.3/jquery-ui.js"></script>
        <script type="text/javascript" src="../js/Globals.js"></script>
        <script type="text/javascript" src="../js/resolution.js"></script>
        <script type="text/javascript" src="../js/functionJS.js"></script>
        <script type="text/javascript" src="../js/functionJS2.js"></script>
        <script type="text/javascript" src="../js/PinScript.js"></script>
        <script type="text/javascript" src="../js/keyBoardCreate.js"></script>
        <script type="text/javascript" src="../js/keyBoardDynamicCSS.js"></script>
        <script type="text/javascript" src="../js/changePin.js"></script>
        <script type="text/javascript" src="../js/buttonFunctions.js"></script>
        <script type="text/javascript" src="../js/prompt.js"></script>
        <script type="text/javascript" src="../js/launchGame.js"></script>
        <script type="text/javascript" src="../css/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
        <script type="text/javascript" src="../css/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
        <script type="text/javascript" src="../js/lightbox.js?path=<?php echo LPConfig::app()->params['projectDIR']; ?>"></script>
        <script type="text/javascript" src="../js/jquery.vticker.js"></script>
        <script type="text/javascript" src="../js/disable_selection.js"></script>

        <link rel="shortcut icon" href="http://pj.pagcoregames.com/favicon.ico" type="image/x-icon" />
        <title>Screen Saver</title>
        <link rel="stylesheet" type="text/css" href="../css/menu.css" media="screen, projection" />

        <script type="text/javascript">
            try
            {
                var Shell = new ActiveXObject('WScript.Shell');
                var regpath = '<?php echo LPConfig::app()->params["registry_path"]["terminalCode"] ?>';
                var terminalCode = Shell.RegRead(regpath);
                var userMode = "";
                var serviceCode = "";
                var terminalType = "";
                var siteID = "";
                var siteClassID = "";
                var isesafe = "";
                var LPewalletURL = '<?php echo LPConfig::app()->params["LPewalletURL"]; ?>';
                var Switch = '<?php echo LPConfig::app()->params["isAllowed"]; ?>';
                var IsDetectTerminalType = '<?php echo LPConfig::app()->params["isDetectTerminalType"]; ?>';
                var IsALLeSAFE = '<?php echo LPConfig::app()->params["IsALLeSAFE"]; ?>';
                var serviceid = '';
                var vvserviceid = '';
                var mmserviceid = '';
                var sysversion = '<?php echo LPConfig::app()->params["sysversionname"]; ?>';

                var LasVegasHome = '<?php echo json_encode(LPConfig::app()->params["vvhome"]); ?>';
                var Game1 = '<?php echo json_encode(LPConfig::app()->params["game1"]); ?>';
                var Game2 = '<?php echo json_encode(LPConfig::app()->params["game2"]); ?>';
                var Game3 = '<?php echo json_encode(LPConfig::app()->params["game3"]); ?>';
                var Game4 = '<?php echo json_encode(LPConfig::app()->params["game4"]); ?>';
                var Game5 = '<?php echo json_encode(LPConfig::app()->params["game5"]); ?>';
                var Game6 = '<?php echo json_encode(LPConfig::app()->params["game6"]); ?>';
                var Game7 = '<?php echo json_encode(LPConfig::app()->params["game7"]); ?>';
                var Game8 = '<?php echo json_encode(LPConfig::app()->params["game8"]); ?>';
                var Game9 = '<?php echo json_encode(LPConfig::app()->params["game9"]); ?>';
                var Game10 = '<?php echo json_encode(LPConfig::app()->params["game10"]); ?>';
                var Game11 = '<?php echo json_encode(LPConfig::app()->params["game11"]); ?>';
                var Game12 = '<?php echo json_encode(LPConfig::app()->params["game12"]); ?>';
                var Game13 = '<?php echo json_encode(LPConfig::app()->params["game13"]); ?>';
                var Game14 = '<?php echo json_encode(LPConfig::app()->params["game14"]); ?>';
                var Game15 = '<?php echo json_encode(LPConfig::app()->params["game15"]); ?>';

                $("#tdvv").hide();
                $("#tdmm").hide();
                $("#tdvv").css("display", "none");
                $("#tdmm").css("display", "none");

                /*               
                 $.checkSpyderConnection = function()
                 {
                 
                 $.ajax(
                 {
                 url: '../Helper/connector.php',
                 type: 'post',
                 dataType: 'json',
                 async: false,
                 data: {fn: function() {
                 return 'checkSpyderConnection';
                 },
                 TerminalCode: function() {
                 return terminalCode;
                 }},
                 success: function(data)
                 {
                 if (data['state'] != "Connected")
                 {
                 alert("Please Activate Spyder Connection on this Terminal!");
                 window.open("index.php", '_self');
                 }
                 }
                 });
                 };
                 */

                $.checkTerminalType = function()
                {
                    $.ajax(
                            {
                                url: '../Helper/connector.php',
                                type: 'post',
                                dataType: 'json',
                                async: false,
                                data: {fn: function() {
                                        return 'getTerminalType';
                                    },
                                    TerminalCode: function() {
                                        return terminalCode;
                                    }},
                                success: function(data)
                                {
                                    terminalType = JSON.stringify(data['Terminaltype']);
                                    siteID = JSON.stringify(data['SiteID']);
                                    siteClassID = JSON.stringify(data['SiteClassID']);
                                    if (JSON.stringify(data['Terminaltype']) == JSON.stringify(data['TerminaltypeVIP']))
                                    {
                                        if (terminalType == 0)
                                        {
                                            $.prompt("Invalid terminal. Terminal should be setup as e-SAFE");
                                        }
                                        else if (terminalType == 1)
                                        {
                                            $.prompt("Invalid terminal. Terminal should be setup as genesis.");
                                        }
                                    }
                                    else
                                    {
                                        $.prompt("Invalid terminal. Terminal is not properly mapped.");
                                    }
                                }
                            });
                };
                $.getTerminalUserMode = function()
                {
                    $.ajax(
                            {
                                url: '../Helper/connector.php',
                                type: 'post',
                                dataType: 'json',
                                async: true,
                                data: {fn: function() {
                                        return 'getTerminalUserMode';
                                    },
                                    TerminalCode: function() {
                                        return terminalCode;
                                    }},
                                success: function(data)
                                {
                                    if (data != null)
                                    {
                                        userMode = JSON.stringify(data['UserMode']).replace(/"/g, "");
                                        serviceCode = JSON.stringify(data['Code']).replace(/"/g, "");
                                        siteClassID = JSON.stringify(data['SiteClassID']).replace(/"/g, "");
                                        $.ajax(
                                                {
                                                    url: '../Helper/connector.php',
                                                    type: 'post',
                                                    dataType: 'json',
                                                    async: false,
                                                    data: {fn: function() {
                                                            return 'checkForExistingSession';
                                                        },
                                                        TerminalCode: function() {
                                                            return terminalCode;
                                                        }},
                                                    success: function(data) {
                                                        SessionType = JSON.stringify(data['SessionType']);
                                                        isesafe = JSON.stringify(data['IsEwallet']);
                                                        serviceid = data['ServiceID'];
                                                        TransactionSummaryID = JSON.stringify(data['TransactionSummaryID']);
                                                        if (SessionType == 1)
                                                        { //terminal based session
                                                            $.showRegular(true);
                                                        }
                                                        else if (isesafe == 1 && SessionType == 2 && !(TransactionSummaryID === undefined || TransactionSummaryID === null || TransactionSummaryID === 'undefined' || TransactionSummaryID === 'null'))
                                                        { //e-safe, user based session
                                                            $.showLobby2(true);
                                                        }
                                                        else if (isesafe == 0 && SessionType == 2 && !(TransactionSummaryID === undefined || TransactionSummaryID === null || TransactionSummaryID === 'undefined' || TransactionSummaryID === 'null'))
                                                        { //non e-safe, user based session
                                                            $.showRegular(true);
                                                        }
                                                        else
                                                        { //return to the default home page if there's no active session with valid login start
                                                            if (terminalType == 2 && (userMode == 0 || userMode == 2 || userMode == 3))
                                                            {
                                                                $.showRegular(true);
                                                            }
                                                            else if (terminalType == 2 && userMode == 1)
                                                            {
                                                                $.showRegular(false);
                                                            }
                                                            else
                                                            {
                                                                $.prompt("Invalid terminal. Terminal is not properly mapped.");
                                                            }
                                                        }
                                                    }
                                                });
                                    }
                                    else
                                    {
                                        $.prompt("Invalid terminal. Can't get terminal details.");
                                    }
                                }
                            });
                };
                $.countServices = function()
                {
                    $.ajax(
                            {
                                url: '../Helper/connector.php',
                                type: 'post',
                                dataType: 'json',
                                async: true,
                                data: {fn: function() {
                                        return 'countServices';
                                    },
                                    TerminalCode: function() {
                                        return terminalCode;
                                    }},
                                success: function(data)
                                {
                                    if (JSON.stringify(data['Count']).replace(/\"/g, "") != 2)
                                        $.prompt("Invalid terminal. Terminal is not properly mapped"); //casino != 2
                                }
                            });
                }

                $.showRegular = function(bool)
                {
                    if (bool)
                    {

                        $("#tdvv").hide();
                        $("#tdmm").hide();

                        $("#copyright1").hide();
                        $("#copyright2").hide();
                        $("#contentt").hide();
                        $("#lobby2").hide();
                        $("#instantPlay").show();
                        $("#ipfooter").html("");
                        $("#loginfooter").html("");
                        $("#lobby2footer").html("");
                        $("#ipfooter").html($("#getfooter").html());
                        $("#copyright2").show();
                        /* CCT BEGIN added */
                        $("#tdvv").css("display", "block");

                        $("#vvimg").css(
                                {
                                    /*"width":lobby2imgW+"px",*/
                                    /*"height":lobby2imgH+"px"*/
                                    "width": "266px",
                                    "height": "266px",
                                    "list-style-type": "none",
                                    "background-size": "100%",
                                    /*"padding": "20px",
                                     "border": "2px solid transparent",
                                     "border-radius": "50px",*/
                                });

                        $("#tdmm").css("display", "block");
                        /*$("#link-mm").css("margin-right","50px");*/
                        /*$("#mmimg").css("margin-right","50px");*/

                        $("#mmimg").css(
                                {
                                    /*"width":lobby2imgW+"px",*/
                                    /*"height":lobby2imgH+"px"*/
                                    "width": "266px",
                                    "height": "266px",
                                    "list-style-type": "none",
                                    "background-size": "100%",
                                    /*"padding": "20px",
                                     "border": "2px solid transparent",
                                     "border-radius": "50px",*/
                                });

                        $("#games-holder").css("display", "block");
                        $("#games").css(
                                {
                                    /*"width":lobby2imgW+"px",*/
                                    /*"height":lobby2imgH+"px"*/
                                    "width": "126",
                                    "height": "135",
                                    "list-style-type": "none",
                                    "background-size": "100%",
                                    /*"padding": "20px",
                                     "border": "2px solid transparent",
                                     "border-radius": "50px",*/
                                });
                        $.ajax(
                                {
                                    url: '../Helper/connector.php',
                                    type: 'post',
                                    dataType: 'json',
                                    data: {fn: function() {
                                            return 'getServiceID';
                                        },
                                        TerminalCode: function() {
                                            return terminalCode;
                                        }},
                                    success: function(data)
                                    {
                                        var array = data.split(", ");



                                        if (array.length > 1) {
                                            for (i = 0; i < array.length; i++) {

                                                if (array[i] === '22' || array[i] === '25') {
                                                    alert("Error : Invalid casino mapping.");
                                                    window.external.ScreenBlocker(true);
                                                }

                                                if (array[i] === '28') {
                                                    mmserviceid = array[i];

                                                    $("#tdvv2").show();
                                                    $("#tdmm2").show();
                                                    $("#tdss2").show();
                                                    $("#tdvv2").css("display", "block");
                                                    $("#tdss2").css("display", "block");
                                                    $("#tdmm2").css("display", "block");
                                                }

                                                if (array[i] === '29') {
                                                    vvserviceid = array[i];

                                                    $("#tdvv").show();
                                                    $("#tdmm").show();
                                                    $("#tdss").show();
                                                    $("#tdvv").css("display", "block");
                                                    $("#tdss").css("display", "block");
                                                    $("#tdmm").css("display", "block");

                                                }
                                            }
                                        } else {
                                            alert("Error : Only one casino was mapped on this terminal.");
                                            window.external.ScreenBlocker(true);
                                        }
                                    },
                                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                                        alert("Status: " + textStatus);
                                        alert("Error: " + errorThrown);
                                    }
                                });
                    }
                    else
                    {
                        $("#copyright1").hide();
                        $("#copyright2").hide();
                        $("#lobby2").hide();
                        $("#instantPlay").hide();
                        $("#contentt").show();
                        $("#ipfooter").html("");
                        $("#loginfooter").html("");
                        $("#lobby2footer").html("");
                        $("#loginfooter").html($("#getfooter").html());
                        $("#copyright1").show();
                        if (IsALLeSAFE)
                        {
                            $("#signupforfreetd").css("display", "none");
                            $("#InstantPlaytbl").css("display", "none");
                        }
                        else
                        {
                            $("#signupforfreetd").css("display", "block");
                            $("#InstantPlaytbl").css("display", "block");
                        }
                    }
                };
                $.showLobby2 = function(bool)
                {
                    if (bool)
                    {
                        $("#copyright1").hide();
                        $("#copyright2").hide();
                        $("#contentt").hide();
                        $("#instantPlay").hide();
                        $("#lobby2").show();
                        $("#ipfooter").html("");
                        $("#loginfooter").html("");
                        $("#lobby2footer").html("");
                        $("#lobby2footer").html($("#getfooter").html());
                        $("#copyright1").show();
                        $("#endSession").css("display", "block");
                        if (siteClassID == "1")
                        {
                            $("#link-platinum-nonactive").css("display", "block");
                            $("#link-platinum-active").css("display", "none");
                        }
                        else
                        {
                            if (serviceid != 19)
                            {
                                $("#link-platinum-nonactive").css("display", "none");
                                $("#link-platinum-active").css("display", "block");
                            }
                            else
                            {
                                $("#link-platinum-nonactive").css("display", "block");
                                $("#link-platinum-active").css("display", "none");
                            }
                        }
                        $("#signupforfreetd").css("display", "none");
                        $("#InstantPlaytbl").css("display", "none");
                    }
                    else
                    {
                        $("#copyright1").hide();
                        $("#copyright2").hide();
                        $("#lobby2").hide();
                        $("#instantPlay").hide();
                        $("#contentt").show();
                        $("#ipfooter").html("");
                        $("#loginfooter").html("");
                        $("#lobby2footer").html("");
                        $("#loginfooter").html($("#getfooter").html());
                        $("#copyright1").show();
                        if (IsALLeSAFE)
                        {
                            $("#signupforfreetd").css("display", "none");
                            $("#InstantPlaytbl").css("display", "none");
                        }
                        else
                        {
                            $("#signupforfreetd").css("display", "block");
                            $("#InstantPlaytbl").css("display", "block");
                        }
                    }
                }

                showLightbox = function(onSuccess)
                {
                    jQuery.fancybox(
                            {
                                padding: 0,
                                margin: 0,
                                opacity: true,
                                overlayOpacity: 1,
                                overlayColor: '#000',
                                height: 190,
                                content: '<img src="../images/loading_big.gif" />',
                                scrolling: 'no',
                                modal: true,
                                onComplete: function()
                                {
                                    $.fancybox.resize();
                                    onSuccess();
                                },
                                onClosed: function()
                                {
                                    if (xhr != null)
                                        xhr.abort();
                                    xhr = null;
                                }

                            });
                };
                $(document).ready(function()
                {
                    $('#system-version').html(sysversion);
                    localStorage.clear();
                    $.checkTerminalType();
                    $.getTerminalUserMode();
                    $.countServices();
                    xhr = null;
                    try
                    {
                        window.external.ScreenBlocker(false);
                        function tempAlert(msg, duration)
                        {
                            if ($('#sessionendedalert').length <= 0)
                            {
                                var el = document.createElement("div");
                                el.setAttribute("style", "text-align: center; margin-top: 0; margin-right:auto; margin-bottom:0; margin-left:auto;font-size: 30px;background-color:white;width: 306px;height: 45px;");
                                el.setAttribute("id", "sessionendedalert");
                                el.innerHTML = msg;
                                setTimeout(function()
                                {
                                    el.parentNode.removeChild(el);
                                }, duration);
                                document.body.appendChild(el);
                            }
                        }

                        var lobbyURL = "lobby.php";
                        var currPage = 'lobby';
                        var terminalCode;
                        var login = '';
                        var terminalPass = '';
                        $(this).bind('contextmenu', function(e)
                        {
                            e.preventDefault();
                        });
                        $(':not(input,select,textarea)').disableSelection();
                        try
                        {
                            var Shell = new ActiveXObject('WScript.Shell');
                            var regpath = '<?php echo LPConfig::app()->params["registry_path"]["terminalCode"] ?>';
                            var login = '';
                            var terminalPass = '';
                            try
                            {
                                terminalCode = Shell.RegRead(regpath);
                                if (terminalCode != '')
                                {
                                    $('#casino').attr("TerminalCode", terminalCode);
                                    $('#casinoo').attr("TerminalCode", terminalCode);
                                }
                            }
                            catch (e)
                            {
                                alert("Please setup the registry");
                                return false;
                            }

                            $('#casino0').live('click', function()
                            {
                                var currServiceID = '';
                                $.ajax({
                                    url: '../Helper/connector.php',
                                    type: 'post',
                                    dataType: 'json',
                                    data: {fn: function() {
                                            return "getServiceID";
                                        },
                                        TerminalCode: function() {
                                            return terminalCode;
                                        }},
                                    success: function(data)
                                    {
                                        currServiceID = data;
                                        if (currServiceID)
                                        {
                                            var url = '../Helper/connector.php';
                                            $.ajax(
                                                    {
                                                        url: url,
                                                        type: 'post',
                                                        dataType: 'json',
                                                        data: {fn: function() {
                                                                return "casinoServiceClick";
                                                            }},
                                                        success: function(data)
                                                        {
                                                            var currentbal = data.currentbal;
                                                            try
                                                            {
                                                                if (data.html == 'not ok')
                                                                {
                                                                    try
                                                                    {
                                                                        window.external.ScreenBlocker(true);
                                                                    }
                                                                    catch (e)
                                                                    { //do nothing
                                                                    }
                                                                    tempAlert("Session has been ended!", 3000);
                                                                    return false;
                                                                }
                                                                else
                                                                {
                                                                    try
                                                                    {
                                                                        if (terminalCode !== '')
                                                                        {
                                                                            showLightbox(function()
                                                                            {
                                                                                $.ajax(
                                                                                        {
                                                                                            url: '../Helper/connector.php',
                                                                                            type: 'post',
                                                                                            dataType: 'json',
                                                                                            data: {fn: function() {
                                                                                                    return "getUserBaseLogin";
                                                                                                },
                                                                                                TerminalCode: function() {
                                                                                                    return terminalCode;
                                                                                                }},
                                                                                            success: function(data)
                                                                                            {

                                                                                                if (data.UserMode == '1' || data.UserMode == '3')
                                                                                                {
                                                                                                    login = data.UBServiceLogin;
                                                                                                    if (data.Code == "MM")
                                                                                                    {
                                                                                                        terminalPass = data.UBHashedServicePassword;
                                                                                                    }
                                                                                                    else
                                                                                                    {
                                                                                                        terminalPass = data.UBServicePassword;
                                                                                                    }
                                                                                                    currServiceID = data.ServiceID;
                                                                                                    if (login != '' && terminalPass != '')
                                                                                                    {
                                                                                                        window.external.OpenGameClient(currServiceID, login, terminalPass);
                                                                                                        jQuery.fancybox.close();
                                                                                                    }
                                                                                                    else
                                                                                                    {
                                                                                                        alert("Please try again.");
                                                                                                        return false;
                                                                                                    }
                                                                                                }
                                                                                                else
                                                                                                {
                                                                                                    $.ajax(
                                                                                                            {
                                                                                                                url: '../Helper/connector.php',
                                                                                                                type: 'post',
                                                                                                                dataType: 'json',
                                                                                                                data: {fn: function() {
                                                                                                                        return "getTerminalBaseLogin";
                                                                                                                    },
                                                                                                                    TerminalCode: function() {
                                                                                                                        return data.TerminalCode;
                                                                                                                    },
                                                                                                                    ServiceID: function() {
                                                                                                                        return data.ServiceID;
                                                                                                                    }},
                                                                                                                success: function(data)
                                                                                                                {
                                                                                                                    if (data.TerminalCode != '')
                                                                                                                    {
                                                                                                                        login = data.TerminalCode;
                                                                                                                        if (data.Code == "MM")
                                                                                                                        {
                                                                                                                            terminalPass = data.HashedServicePassword;
                                                                                                                        }
                                                                                                                        else
                                                                                                                        {
                                                                                                                            terminalPass = data.ServicePassword;
                                                                                                                        }
                                                                                                                        currServiceID = data.ServiceID;
                                                                                                                        if (login != '' && terminalPass != '')
                                                                                                                        {
                                                                                                                            window.external.OpenGameClient(currServiceID, login, terminalPass);
                                                                                                                            jQuery.fancybox.close();
                                                                                                                        }
                                                                                                                        else
                                                                                                                        {
                                                                                                                            alert("Please try again.");
                                                                                                                            return false;
                                                                                                                        }
                                                                                                                    }
                                                                                                                },
                                                                                                                error: function(XMLHttpRequest, e)
                                                                                                                {
                                                                                                                    alert(XMLHttpRequest.responseText);
                                                                                                                    if (XMLHttpRequest.status == 401)
                                                                                                                    {
                                                                                                                        window.location.reload();
                                                                                                                    }
                                                                                                                }
                                                                                                            });
                                                                                                }
                                                                                            },
                                                                                            error: function(XMLHttpRequest, e)
                                                                                            {
                                                                                                alert(XMLHttpRequest.responseText);
                                                                                                if (XMLHttpRequest.status == 401)
                                                                                                {
                                                                                                    window.location.reload();
                                                                                                }
                                                                                            }
                                                                                        });
                                                                            });
                                                                        }
                                                                    }
                                                                    catch (e)
                                                                    {
                                                                        alert("Game client not found.");
                                                                        setTimeout($(function() {
                                                                            location.reload();
                                                                        }), 3000);
                                                                    }
                                                                }
                                                            }
                                                            catch (e)
                                                            {
                                                                alert("Parse error");
                                                                setTimeout($(function() {
                                                                    location.reload();
                                                                }), 3000);
                                                            }
                                                        },
                                                        error: function(e)
                                                        {
                                                            alert(e.responseText);
                                                            setTimeout($(function() {
                                                                location.reload();
                                                            }), 3000);
                                                        }
                                                    });
                                        }
                                        else
                                        {
                                            try
                                            {
                                                window.external.ScreenBlocker(true);
                                            } catch (e)
                                            {  //do nothing
                                            }
                                            tempAlert("Session has been ended!", 3000);
                                        }
                                    },
                                    error: function(XMLHttpRequest, e)
                                    {
                                        alert(XMLHttpRequest.responseText);
                                        if (XMLHttpRequest.status == 401)
                                        {
                                            window.location.reload();
                                        }
                                    }
                                });
                                return false;
                            });
                        } catch (e)
                        {
                            alert('There is a problem in activex');
                            window.open("index.php", '_self');
                        }
                    } catch (e)
                    {
                        alert("Access Denied! Unauthorized browser.");
                        window.open("index.php", '_self');

                    }
                });
            }
            catch (e)
            {
                alert('There is a problem in activex');
                window.open("index.php", '_self');
            }
        </script>
        <script type="text/javascript">
            function toggle_visibility(id) {

                var e = document.getElementById(id);

                var pgames = document.getElementById('iptable pgames');
                var ngames = document.getElementById('iptable ngames');
                var fgames = document.getElementById('iptable fgames');

                var pgamesLink = document.getElementById('popular-games-link');
                var ngamesLink = document.getElementById('new-games-link');
                var fgamesLink = document.getElementById('featured-games-link');

                if (id == 'iptable pgames') {
                    e.style.display = 'block';
                    ngames.style.display = 'none';
                    fgames.style.display = 'none';

                    pgamesLink.className = pgamesLink.className + ' active';
                    ngamesLink.className = ngamesLink.className.replace(/(?:^|\s)active(?!\S)/g, '');
                    fgamesLink.className = fgames.className.replace(/(?:^|\s)active(?!\S)/g, '');
                }
                else if (id == 'iptable ngames') {
                    e.style.display = 'block';
                    pgames.style.display = 'none';
                    fgames.style.display = 'none';

                    ngamesLink.className = ngamesLink.className + ' active';
                    pgamesLink.className = pgamesLink.className.replace(/(?:^|\s)active(?!\S)/g, '');
                    fgamesLink.className = fgames.className.replace(/(?:^|\s)active(?!\S)/g, '');
                }
                else {
                    e.style.display = 'block';
                    pgames.style.display = 'none';
                    ngames.style.display = 'none';

                    fgamesLink.className = fgamesLink.className + ' active';
                    ngamesLink.className = ngamesLink.className.replace(/(?:^|\s)active(?!\S)/g, '');
                    pgamesLink.className = pgamesLink.className.replace(/(?:^|\s)active(?!\S)/g, '');
                }

            }
        </script>
    </head>
    <body>
        <div id="blackwrapper"></div>

        <!-- page begin -->
        <div id="page"> 
            <!-- wrapper begin -->
            <div class="wrapper"> 
                <!-- eGames Logo  -->
                <div class="banner"></div>

                <div class="games-logo-container"> 

                    <!-- Magic Macau && Viva Las Vegas  -->
                    <table id="iptable" style="margin-top: 35px;" align="center">
                        <tr>
                            <td id="tdvv" align="center" style="padding: 17px;">
                                <a id='casinovv' ><img id="vvimg" src="../images/viva-lasvegas.png" /></a>
                            </td>
                            <td id="tdmm" align="center" style="padding: 17px;">
                                <a id='casinomm' ><img id="mmimg" src="../images/magic-macau.png" /></a>
                            </td>    
                        </tr>
                    </table>

                    <!-- NAVIGATION BAR --->
                    <div id="moveCenter" style="margin-top: 10px;">
                        <ul class="menu">
                            <li class="featured-games" onclick="toggle_visibility('iptable fgames');"><a id ="featured-games-link" href="#"></a></li>
                            <li class="new-games" onclick="toggle_visibility('iptable ngames');"><a id ="new-games-link" href="#"></a></li>
                            <li class="popular-games" onclick="toggle_visibility('iptable pgames');"><a class="active" id="popular-games-link" href="#"></a></li>
                        </ul>
                    </div>

                    <!-- NAVIGATION GAMES --->
                    <table id="iptable pgames" style="margin-top: 10px; width : 500px;" align="center">
                        <tr>
                            <td id="games-holder" align="center" style="padding: 9px;">
                                <a id='casinovvgame1' ><img id="games" src="../images/image-1.png" /></a>
                            </td>
                            <td id="games-holder" align="center" style="padding: 9px;">
                                <a id='casinovvgame2' ><img id="games" src="../images/image-2.png" /></a>
                            </td>    
                            <td id="games-holder" align="center" style="padding: 9px;">
                                <a id='casinovvgame3' ><img id="games" src="../images/image-3.png" /></a>
                            </td> 
                            <td id="games-holder" align="center" style="padding: 9px;">
                                <a id='casinovvgame4' ><img id="games" src="../images/image-4.png" /></a>
                            </td>
                            <td id="games-holder" align="center" style="padding: 9px;">
                                <a id='casinovvgame5' ><img id="games" src="../images/image-5.png" /></a>
                            </td>
                        </tr>
                    </table>

                    <!-- NEW GAMES --->
                    <table id="iptable ngames" style="margin-top: 10px; display: none;" align="center">
                        <tr>
                            <td id="games-holder" align="center" style="padding: 9px;">
                                <a id='casinovvgame6' ><img id="games" src="../images/image-6.png" /></a>
                            </td>
                            <td id="games-holder" align="center" style="padding: 9px;">
                                <a id='casinovvgame7' ><img id="games" src="../images/image-7.png" /></a>
                            </td>    
                            <td id="games-holder" align="center" style="padding: 9px;">
                                <a id='casinovvgame8' ><img id="games" src="../images/image-8.png" /></a>
                            </td> 
                            <td id="games-holder" align="center" style="padding: 9px;">
                                <a id='casinovvgame9' ><img id="games" src="../images/image-9.png" /></a>
                            </td>                             
                            <td id="games-holder" align="center" style="padding: 9px;">
                                <a id='casinovvgame10' ><img id="games" src="../images/image-10.png" /></a>
                            </td> 
                        </tr>
                    </table>

                    <!-- FEATURED GAMES --->
                    <table id="iptable fgames" style="margin-top: 10px; display: none;" align="center">
                        <tr>
                            <td id="games-holder" align="center" style="padding: 9px;">
                                <a id='casinovvgame11' ><img id="games" src="../images/image-11.png" /></a>
                            </td>
                            <td id="games-holder" align="center" style="padding: 9px;">
                                <a id='casinovvgame12' ><img id="games" src="../images/image-12.png" /></a>
                            </td>    
                            <td id="games-holder" align="center" style="padding: 9px;">
                                <a id='casinovvgame13' ><img id="games" src="../images/image-13.png" /></a>
                            </td> 
                            <td id="games-holder" align="center" style="padding: 9px;">
                                <a id='casinovvgame14' ><img id="games" src="../images/image-14.png" /></a>
                            </td>                             
                            <td id="games-holder" align="center" style="padding: 9px;">
                                <a id='casinovvgame15' ><img id="games" src="../images/image-15.png" /></a>
                            </td> 
                        </tr>
                    </table>
                    <div style='margin-bottom: 5px'></div>
                    <div id="ipfooter" style="margin-top: 30px;"></div>
                </div> 
                <div id="footer_logos" class="footer-logos"></div>
                <div id="system-version" class="sysversion" onClick="window.location.reload()"></div>
            </div>  
        </div>     
        <div id="blackOut"></div>
        <div id="blocker"></div>
        <div id="whiteBox"></div>    
        <div id="loadingBox"></div>  
        <div id="prompt"></div>    
        <div class="clear"></div>
    </body>
</html>

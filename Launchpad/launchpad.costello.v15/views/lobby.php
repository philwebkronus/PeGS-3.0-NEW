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
                var sysversion = '<?php echo LPConfig::app()->params["sysversionname"]; ?>';

                $.checkSpyderConnection = function()
                {
                    $("#tdnew").hide();

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
                        $("#tdnew").hide();
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
                        $("#tdnew").css("display", "block");

                        $("#vvnew").css(
                                {
                                    "width": "700px",
                                    "height": "250px",
                                    "list-style-type": "none",
                                    "background-size": "100%"

                                });
                        $("#link-new").css(
                                {
                                    "width": "700px",
                                    "height": "250px",
                                    "list-style-type": "none",
                                    "background-size": "100%"
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
                                        $("#tdnew").show();

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
                    $.checkSpyderConnection();
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
                        } catch (e)
                        {
                            alert('There is a problem in activex');
                            window.open("index.php", '_self');
                        }
                    } catch (e)
                    {
                        alert("Access Denied! Unauthorized browser.");
                        //window.close();
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
    </head>
    <body>
        <div id="blackwrapper"></div>
        <a id='casinonew' >
            <!-- page begin -->
            <div id="page"> 
                <!-- wrapper begin -->
                <div class="wrapper"> 
                    <!--<div class="banner"></div>  banner  -->
                    <div class="games-logo-container"> 
                        <table id="iptable" style="margin-top: 10px;" align="center">
                            <tr>
                                <td id="tdnew" align="center">
					<img id="newimg" src="../images/start-logo.png" />
                                </td>        
                            </tr>
                        </table>
                        <div style='margin-bottom: 5px'></div>
                        <div id="ipfooter" style="margin-top: 30px;"></div>
                    </div> 
                    <div id="footer_logos" class="footer-logos"></div>
                    <div id="system-version" class="sysversion" onClick="window.location.href = window.location.href"></div>
                </div>  
            </div>    
        </a>
        <div id="blackOut"></div>
        <div id="blocker"></div>
        <div id="whiteBox"></div>    
        <div id="loadingBox"></div>  
        <div id="prompt"></div>    
        <div class="clear"></div>
    </body>
</html>

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
                                                                $.prompt("ERROR 002: Invalid terminal. Terminal is not properly mapped.");
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
                                        $.prompt("ERROR 003: Invalid terminal. Terminal is not properly mapped"); //casino != 2
                                }
                            });
                }

                $.showRegular = function(bool)
                {
                    if (bool)
                    {
                        $("#tdvv").hide();
                        $("#tdmm").hide();
                        $("#tdss").hide();
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
			$("#tdvv").css("display", "block");

                        $("#vvimg").css(
                                {
                                    "width": "360px",
                                    "height": "340px",
                                    "list-style-type": "none",
                                    "background-size": "100%"

                                });
                        $("#link-vv").css(
                                {
                                    "width": "360px",
                                    "height": "340px",
                                    "list-style-type": "none",
                                    "background-size": "100%"
                                });
                        $("#tdmm").css("display", "block");
	    
                        $("#mmimg").css(
                                {
                                    "width": "360px",
                                    "height": "340px",
                                    "list-style-type": "none",
                                    "background-size": "100%"
                                });
                        $("#link-mm").css(
                                {
                                    "width": "360px",
                                    "height": "340px",
                                    "list-style-type": "none",
                                    "background-size": "100%"
                                });
                        $("#tdss").css("display", "block");

                        $("#ssimg").css(
                                {
                                    "width": "360px",
                                    "height": "340px",
                                    "list-style-type": "none",
                                    "background-size": "100%"
                                });
                        $("#link-ss").css(
                                {
				    "width": "360px",
				    "height": "340px",
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
                                        if (data === '25' || data === '29') {

                                            $("#tdvv").show();
                                            $("#tdmm").hide();
                                            $("#tdss").hide();
                                            $("#tdss").css("display", "block");
                                            $("#tdmm").css("display", "block");
                                            $("#link-mm").css("display", "none");
                                            $("#link-ss").css("display", "none");

                                        }
                                        else {
                                            $("#tdmm").show();
                                            $("#tdvv").hide();
                                            $("#tdss").hide();
                                            $("#tdss").css("display", "block");
                                            $("#tdvv").css("display", "block");
                                            $("#link-vv").css("display", "none");
                                            $("#link-ss").css("display", "none");
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

        <!-- page begin -->
        <div id="page"> 
            <!-- wrapper begin -->
            <div class="wrapper"> 
                <div class="banner"></div> <!-- banner  -->

                <div class="games-logo-container"> 


                    <table id="iptable" style="margin-top: 10px;" align="center">

                        <tr>
                            <td id="tdvv" align="center">
                                <div id="link-vv" class="link-container">
                                    <a id='casinovv' ><img id="vvimg" src="../images/viva-lasvegas.png" /></a>
                                </div>
                            </td>
                            <td id="tdmm" align="center">
                                <div id="link-mm" class="link-container">
                                    <a id='casinomm' ><img id="mmimg" src="../images/magic-macau.png" /></a>
                                </div>
                            </td>
                            <td id="tdss" align="center">
                                <div id="link-ss" class="link-container">
                                    <a id=''><img id="ssimg" src="../images/swinging-singapore.png" /></a>
                                </div>
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
        <div id="blackOut"></div>
        <div id="blocker"></div>
        <div id="whiteBox"></div>    
        <div id="loadingBox"></div>  
        <div id="prompt"></div>    
        <div class="clear"></div>
    </body>
</html>
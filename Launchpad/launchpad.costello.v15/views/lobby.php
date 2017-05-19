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
    <script type="text/javascript" src="../js/lightbox.js?path=<?php echo LPConfig::app()->params['projectDIR'];  ?>"></script>
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
        var terminalType="";
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
                url:'../Helper/connector.php',
                type: 'post',
                dataType: 'json',
                async:false,
                data:{fn:function(){return 'getTerminalType';},
                TerminalCode:function(){return terminalCode;}},
                success:function(data)
                { 
                    terminalType = JSON.stringify(data['Terminaltype']);
                    siteID = JSON.stringify(data['SiteID']);
                    siteClassID = JSON.stringify(data['SiteClassID']);

                    if(JSON.stringify(data['Terminaltype']) == JSON.stringify(data['TerminaltypeVIP']))
                    {
                        if(terminalType==0)
                        {
                            $.prompt("Invalid terminal. Terminal should be setup as e-SAFE");
                        }
                        else if(terminalType==1)
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
                url:'../Helper/connector.php',
                type: 'post',
                dataType: 'json',
                async: true,
                data:{fn:function(){return 'getTerminalUserMode';},
                TerminalCode:function(){return terminalCode;}},
                success:function(data)
                {  
                    if(data != null)
                    {
                        userMode = JSON.stringify(data['UserMode']).replace(/"/g,""); 
                        serviceCode = JSON.stringify(data['Code']).replace(/"/g,""); 
                        siteClassID = JSON.stringify(data['SiteClassID']).replace(/"/g,""); 
                        $.ajax(
                        {
                            url:'../Helper/connector.php',
                            type: 'post',
                            dataType: 'json',
                            async:false,
                            data:{fn:function(){return 'checkForExistingSession';},
                            TerminalCode:function(){return terminalCode;}},
                            success:function(data){ 
                                SessionType = JSON.stringify(data['SessionType']);
                                isesafe = JSON.stringify(data['IsEwallet']);
                                serviceid = data['ServiceID'];
                                TransactionSummaryID = JSON.stringify(data['TransactionSummaryID']);

                                if(SessionType == 1) 
                                { //terminal based session
                                   $.showRegular(true); 
                                } 
                                else if(isesafe == 1 && SessionType == 2 && !(TransactionSummaryID === undefined || TransactionSummaryID === null || TransactionSummaryID === 'undefined' || TransactionSummaryID === 'null')) 
                                { //e-safe, user based session
                                    $.showLobby2(true);
                                } 
                                else if(isesafe == 0 && SessionType == 2 && !(TransactionSummaryID === undefined || TransactionSummaryID === null || TransactionSummaryID === 'undefined' || TransactionSummaryID === 'null')) 
                                { //non e-safe, user based session
                                    $.showRegular(true);
                                } 
                                else 
                                { //return to the default home page if there's no active session with valid login start
                                    if(terminalType==2&&(userMode==0||userMode==2))
                                    {
                                        $.showRegular(true);
                                    } 
                                    else if(terminalType==2&&userMode==1)
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
                url:'../Helper/connector.php',
                type: 'post',
                dataType: 'json',
                async: true,
                data:{fn:function(){return 'countServices';},
                TerminalCode:function(){return terminalCode;}},
                success:function(data)
                {
                    if(JSON.stringify(data['Count']).replace(/\"/g, "")!=2)
                        $.prompt("Invalid terminal. Terminal is not properly mapped"); //casino != 2
                }
            });
        }

        $.showRegular = function(bool)
        {
            if(bool)
            {
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
                /* CCT BEGIN comment */
                /*
                if(serviceCode == "MM")
                {
                    $("#tdvv").css("display","none");
                    $("#tdmm").css("display","block");
                    $("#link-mm").css("margin-right","25px");
                    $("#mmimg").css("margin-right","25px");
                } 
                else if(serviceCode == "VV")
                {
                    $("#tdvv").css("display","block");
                    $("#tdmm").css("display","none");
                    $("#link-vv").css("margin-right","25px");
                    $("#vvimg").css("margin-right","25px");
                }
                              
                $("#vvimg").css(
                {
                    "width":lobby2imgW+"px",
                    "height":lobby2imgH+"px"
                });
                $("#link-vv").css(
                {
                    "width":lobby2imgW+"px",
                    "height":lobby2imgH+"px",
                    "background-size":lobby2imgW+"px "+lobby2imgH+"px"
                });
                 */
                /* CCT END comment */
                
                /* CCT BEGIN added */
                $("#tdvv").css("display","block");
                /*$("#link-vv").css("margin-right","25px");*/
                /*$("#vvimg").css("margin-right","25px");*/

                $("#vvimg").css(
                {
                    /*"width":lobby2imgW+"px",*/
                    /*"height":lobby2imgH+"px"*/
                    "width":"244px",
                    "height":"277px"
                });
                $("#link-vv").css(
                {
                    /*"width":lobby2imgW+"px",*/
                    /*"height":lobby2imgH+"px",*/
                    /*"background-size":lobby2imgW+"px "+lobby2imgH+"px"*/
                    "width":"244px",
                    "height":"277px",
                    "background-size":"244px 277px"
                });
                
                $("#tdmm").css("display","block");
                /*$("#link-mm").css("margin-right","25px");*/
                /*$("#mmimg").css("margin-right","25px");*/

                $("#mmimg").css(
                {
                    /*"width":lobby2imgW+"px",*/
                    /*"height":lobby2imgH+"px"*/
                    "width":"244px",
                    "height":"277px"
                });
                $("#link-mm").css(
                {
                    /*"width":lobby2imgW+"px",*/
                    /*"height":lobby2imgH+"px",*/
                    /*"background-size":lobby2imgW+"px "+lobby2imgH+"px"*/
                    "width":"244px",
                    "height":"277px",
                    "background-size":"244px 277px"
                });
                
                $("#tdss").css("display","block");
                /*$("#link-ss").css("margin-right","25px");*/
                /*$("#ssimg").css("margin-right","25px");*/
                
                $("#ssimg").css(
                {
                    /*"width":lobby2imgW+"px",*/
                    /*"height":lobby2imgH+"px"*/
                    "width":"244px",
                    "height":"277px"
                });
                $("#link-ss").css(
                {
                    /*"width":lobby2imgW+"px",*/
                    /*"height":lobby2imgH+"px",*/
                    /*"background-size":lobby2imgW+"px "+lobby2imgH+"px"*/
                    "width":"244px",
                    "height":"277px",
                    "background-size":"244px 277px"
                });
                /*
                $("footer_logos").css(
                {
                    "margin-left":((screen_width - 1024) /2)+"px",
                });
                alert(screen_width);
                */
                /* CCT END added */
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
                if(IsALLeSAFE)
                {
                    $("#signupforfreetd").css("display","none");
                    $("#InstantPlaytbl").css("display","none");
                } 
                else 
                {
                    $("#signupforfreetd").css("display","block");
                    $("#InstantPlaytbl").css("display","block");
                }
            }
        };

        $.showLobby2 = function(bool)
        {
            if(bool)
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
                $("#endSession").css("display","block");
                if(siteClassID == "1")
                {
                    $("#link-platinum-nonactive").css("display","block");
                    $("#link-platinum-active").css("display","none");
                } 
                else 
                {
                    if(serviceid != 19)
                    {
                        $("#link-platinum-nonactive").css("display","none");
                        $("#link-platinum-active").css("display","block");
                    } 
                    else 
                    {
                        $("#link-platinum-nonactive").css("display","block");
                        $("#link-platinum-active").css("display","none");
                    }
                }
                $("#signupforfreetd").css("display","none");
                $("#InstantPlaytbl").css("display","none");
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

                if(IsALLeSAFE)
                {
                    $("#signupforfreetd").css("display","none");
                    $("#InstantPlaytbl").css("display","none"); 
                } 
                else 
                {
                    $("#signupforfreetd").css("display","block");
                    $("#InstantPlaytbl").css("display","block");
                }
            }
        }

        showLightbox = function(onSuccess)
        {
            jQuery.fancybox(
            {
                padding:0,
                margin:0,
                opacity:true,
                overlayOpacity:1,
                overlayColor:'#000',
                height:190,
                content:'<img src="../images/loading_big.gif" />',
                scrolling:'no',
                modal:true,
                onComplete:function()
                {
                    $.fancybox.resize();
                    onSuccess();
                },
                onClosed: function()
                {
                    if(xhr != null)
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

                function tempAlert(msg,duration) 
                {
                    if($('#sessionendedalert').length <= 0)
                    {
                        var el = document.createElement("div");
                        el.setAttribute("style","text-align: center; margin-top: 0; margin-right:auto; margin-bottom:0; margin-left:auto;font-size: 30px;background-color:white;width: 306px;height: 45px;");
                        el.setAttribute("id","sessionendedalert");
                        el.innerHTML = msg;
                        setTimeout(function()
                        {
                         el.parentNode.removeChild(el);
                        },duration);
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
                        if(terminalCode != '')
                        {
                            $('#casino').attr("TerminalCode", terminalCode);
                            $('#casinoo').attr("TerminalCode", terminalCode);
                        }
                    } 
                    catch(e) 
                    {
                        alert("Please setup the registry");
                        return false;
                    }

                    $('#casino0').live('click',function()
                    {
                        var currServiceID = '';
                        $.ajax({
                            url:'../Helper/connector.php',
                            type: 'post',
                            dataType: 'json',
                            data: {fn: function(){return "getServiceID";},
                            TerminalCode: function(){return terminalCode;}},
                            success:function(data) 
                            {
                                currServiceID = data;

                                if(currServiceID) 
                                { 
                                   var url = '../Helper/connector.php';
                                   $.ajax(
                                   {
                                        url:url,
                                        type: 'post',
                                        dataType:'json',
                                        data: {fn: function(){return "casinoServiceClick";}},
                                        success:function(data)
                                        {
                                            var currentbal = data.currentbal;

                                            try 
                                            {
                                                if(data.html == 'not ok') 
                                                {
                                                    try 
                                                    {
                                                        window.external.ScreenBlocker(true);
                                                    } 
                                                    catch(e) 
                                                    { //do nothing
                                                    }
                                                    tempAlert("Session has been ended!",3000);
                                                    return false;
                                                }
                                                else 
                                                {
                                                    try 
                                                    {
                                                        if(terminalCode !== '')
                                                        {
                                                            showLightbox(function()
                                                            {
                                                                $.ajax(
                                                                {
                                                                    url:'../Helper/connector.php',
                                                                    type: 'post',
                                                                    dataType: 'json',
                                                                    data: {fn: function(){return "getUserBaseLogin";},
                                                                    TerminalCode: function(){return terminalCode;}},
                                                                    success:function(data) 
                                                                    {
                                                                        if(data.UserMode == '1')
                                                                        {
                                                                            login = data.UBServiceLogin;
                                                                            if(data.Code == "MM")
                                                                            {
                                                                                terminalPass = data.UBHashedServicePassword;
                                                                            } 
                                                                            else 
                                                                            { 
                                                                                terminalPass = data.UBServicePassword; 
                                                                            }
                                                                            currServiceID = data.ServiceID;
                                                                            if(login != '' && terminalPass != '')
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
                                                                                url:'../Helper/connector.php',
                                                                                type: 'post',
                                                                                dataType: 'json',
                                                                                data: {fn: function(){return "getTerminalBaseLogin";},
                                                                                TerminalCode: function(){return data.TerminalCode;},
                                                                                ServiceID: function(){return data.ServiceID;}},
                                                                                success:function(data) 
                                                                                {
                                                                                    if(data.TerminalCode != '')
                                                                                    {
                                                                                        login = data.TerminalCode;
                                                                                        if(data.Code == "MM")
                                                                                        {
                                                                                            terminalPass = data.HashedServicePassword;
                                                                                        } 
                                                                                        else 
                                                                                        { 
                                                                                            terminalPass = data.ServicePassword; 
                                                                                        }
                                                                                        currServiceID = data.ServiceID;

                                                                                        if(login != '' && terminalPass != '')
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
                                                                                    if(XMLHttpRequest.status == 401)
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
                                                                        if(XMLHttpRequest.status == 401)
                                                                        {
                                                                            window.location.reload();
                                                                        }
                                                                    }
                                                                });
                                                            });
                                                        }
                                                    } 
                                                    catch(e) 
                                                    {
                                                        alert("Game client not found.");
                                                        setTimeout($(function(){location.reload();}),3000);
                                                    }
                                                }
                                            } 
                                            catch(e) 
                                            {
                                                alert("Parse error");
                                                setTimeout($(function(){location.reload();}),3000);
                                            }
                                        },
                                        error:function(e) 
                                        {
                                            alert(e.responseText);
                                            setTimeout($(function(){location.reload();}),3000);
                                        }
                                    }); 
                                } 
                                else 
                                {
                                    try 
                                    {
                                        window.external.ScreenBlocker(true);
                                    } catch(e) 
                                    {  //do nothing
                                    }
                                    tempAlert("Session has been ended!",3000);
                                }
                            },
                            error: function(XMLHttpRequest, e)
                            {
                                alert(XMLHttpRequest.responseText);
                                if(XMLHttpRequest.status == 401)
                                {
                                    window.location.reload();
                                }
                            }
                        });
                        return false;
                    });
                }catch(e) 
                {
                    alert('There is a problem in activex');
                }  
            }catch(e)
            {
                alert("Access Denied! Unauthorized browser.");
                window.close();
            }
        });
    }
    catch(e)
    {
        alert('There is a problem in activex');
    }
</script>
</head>
<body>
    <div id="blackwrapper"></div>
    <!-- CCT BEGIN ADDED -->    
    <!-- page begin -->
    <div id="page"> 
        <!-- wrapper begin -->
	<div class="wrapper"> 
            <div class="banner"></div> <!-- banner  -->
            <!-- games-logo-container begin -->
            <div class="games-logo-container"> 
    <!-- CCT END ADDED -->   
                <!-- CCT BEGIN COMMENT -->  
                <!-- e-SAFE Enter PIN, Change PIN page-->
                <!--
                <center>
                <table id="contentt">
                    <tr>
                        <td>
                            <div id="left">
                                <div id='cont1'>
                                    <center>
                                    <table style="margin-top:40px; ">
                                        <tr>
                                            <td><img src="../images/eSafelogo.png" /></td>
                                        </tr>
                                        <tr>
                                            <td id="tdpad"></td>
                                        </tr>
                                        <tr>
                                            <td><p style="color: white;"><b>PIN</b></p><input type='password' id='pinfield' value="000000" readonly /></td>
                                        </tr>
                                        <tr> 
                                            <td id="tdpad"></td> 
                                        </tr>
                                        <tr>
                                            <td><input type='button' id='Login' value='Login' /></td>
                                        </tr>
                                        <tr>
                                            <td id="tdpad"></td>
                                        </tr>
                                        <tr>
                                            <td id="signupforfreetd" style="display: none; color: white;">No account yet?&nbsp;<a id='signUP'>Sign up for free</a></td>
                                        </tr> 
                                        <tr>
                                           <td>
                                               <a id='changeuserPin'>Change PIN</a>
                                               <table id="InstantPlaytbl" style="display: none;">
                                                   <tr>
                                                       <td id="tdpad"></td>
                                                   </tr>
                                                   <tr>
                                                       <td><input type='button' id='casino' value='Instant Play' /></td>
                                                   </tr>
                                               </table>
                                           </td> 
                                       </tr>
                                    </table>
                                    </center>
                                </div>
                                <div id='hdncont1' style="display: none;">
                                    <center>
                                    <table style="margin-top:30px;">
                                        <tr>
                                            <td><p><b>Enter New PIN</b></p><input type='password' id='newPinField'/></td>
                                        </tr>
                                        <tr>
                                            <td><p><b>Re-Enter New PIN</b></p><input type='password' id='rnewPinField'/></td>
                                        </tr>   
                                        <tr><td>&nbsp;</td></tr>
                                         <tr>
                                             <td><input type='button' id='Login' value='Login' /></td>
                                         </tr>
                                    </table>
                                    </center>
                                </div>
                                <center>
                                <div id='buttcont' style="display:none"></div>
                                </center> 
                            </div>   
                        </td>
                    </tr>
                    <tr>
                        <td id="loginfooter"></td>
                    </tr>           
                </table>
                </center>
                -->
                <!-- CCT END COMMENT -->  
                
                <!-- CCT BEGIN COMMENT -->  
                <!-- Instant Play - terminal based landing page-->
                <!--
                <center>
                <div id='instantPlay' >
                    <div id="virtual-ecity-logo-container" style="visibility: hidden; margin-top: 40px;">
                        <img src="../images/virtual_entertainment_city_logo.png" >
                    </div>
                -->
                <!-- CCT END COMMENT -->   
                <table id="iptable" style="margin-top: 10px;">
                    <!-- CCT BEGIN COMMENT and ADDED -->  
                    <tr>
                        <td id="tdvv">
                            <div id="link-vv" class="link-container">
                                <!-- <a id='casinovv' ><img id="vvimg" src="../images/vv_v15_normal.png" /></a> -->
                                <a id='casinovv' ><img id="vvimg" src="../images/viva-lasvegas.png" /></a>
                            </div>
                        </td>
                        <td id="tdmm">
                            <div id="link-mm" class="link-container">
                                <!-- <a id='casinomm' ><img id="mmimg" src="../images/mm_v15_normal.png" /></a> -->
                                <a id='casinomm' ><img id="mmimg" src="../images/magic-macau.png" /></a>
                            </div>
                        </td>
                        <!-- ADDED BEGIN -->
                        <td id="tdss">
                            <div id="link-ss" class="link-container">
                                <a id='casinoss'><img id="ssimg" src="../images/swinging-singapore.png" /></a>
                            </div>
                        </td>                
                        <!-- ADDED END -->
                        <!--    remove this comment to display SS casino image  -----------------------
                        <td id="tdss">
                            <div id="link-ss" >
                                    <img id="ssimg" src="../images/ss_unavailable.png" />
                            </div>
                        </td>
                        -->
                    <!--CCT END COMMENT and ADDED -->  
                    </tr>
                </table>
                <div style='margin-bottom: 5px'></div>
                <!--CCT BEGIN COMMENT -->  
                <!-- </div> -->
                <div id="ipfooter" style="margin-top: 30px;"></div>
                <!-- </center> -->
                <!--CCT END COMMENT -->    
            <!-- CCT BEGIN ADDED -->  
            <!-- games-logo-container end -->
            </div> 
            <div id="footer_logos" class="footer-logos"></div>
            <div id="system-version" class="sysversion" ></div>
            <!-- CCT END ADDED -->      
            <!--CCT BEGIN COMMENT -->  
            <!--Lobby 2 - e-SAFE landing page-->
            <!--
            <center>
            <div id='lobby2'>
                <table id="lobby2table" style="margin-top: 190px; ">
                    <tr>
                        <td>
                            <div id="link-non-platinum" style="margin-right: 25px;" class="link-container">
                                <a class='btnnonplatinum' id='nonPlatinum' value='Classic'>
                                    <img id="classicimg" style="margin-right: 25px;" src="../images/mm_v15_normal.png" /></a>
                            </div>
                        </td>
                        <td>
                            <div id="link-platinum-nonactive" style="margin-top: -10px;" class="link-container">
                                <a class='btnplatinum' value='Modern' >
                                    <img id="modernimgnonact" src="../images/ss_unavailable.png" /></a>
                            </div>
                            <div id="link-platinum-active" style="margin-top: -10px;" class="link-container">
                                <a id='platinum' class='btnplatinum' value='Modern' >
                                    <img id="modernimgact" src="../images/ss_v15_normal.png" /></a>
                            </div>
                        </td>
                    </tr>
                </table>
                <div style='margin-bottom: 5px'>
                    <div id="link-endsession" class="link-container">
                        <a id='endSession' class='myButtonStyle' value='END SESSION' >
                            <img id="endsessionimg" src="../images/end_session.png" /></a>
                    </div>
                </div>
            </div>
            <div id="lobby2footer" style="margin-top: 10px;"></div>
            </center>
            <center>
            <div id="getfooter" style="display:none;">
                <div id="copyright1">
                    <div id="divcopyright1" style="margin-top: 10px; width: 100%; float:left">
                        <img src="../images/footer_with_esafe.png" />
                    </div>
                </div>
                <div id="copyright2">
                    <div id="divcopyright2" style="margin-top: 80px; width: 100%; float:left">
                        <img src="../images/footer_without_esafe.png" />
                    </div>
                </div>
            </div>
            </center>
            -->
            <!--CCT END COMMENT -->  
        <!-- CCT BEGIN ADDED -->      
        <!--wrapper end -->
        </div>  
    <!-- page end -->   
    </div> 
        <!-- CCT END ADDED -->      
    <div id="blackOut"></div>
    <div id="blocker"></div>
    <div id="whiteBox"></div>    
    <div id="loadingBox"></div>  
    <div id="prompt"></div>    
    <div class="clear"></div>
    <!--CCT BEGIN COMMENT -->  
    <!-- <div id="footer"></div> --> 
    <!--<span id="system-version"></span>-->
    <!--CCT END COMMENT -->  
</body>
</html>
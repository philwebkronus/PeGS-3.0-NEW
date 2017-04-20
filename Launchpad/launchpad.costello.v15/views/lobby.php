<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php include_once '../models/LPConfig.php'; ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />
        <meta http-equiv="X-UA-Compatible" content="IE=9" />
        <!--
        <meta http-equiv="cache-control" content="max-age=0" />
        <meta http-equiv="cache-control" content="no-cache" />
        <meta http-equiv="expires" content="0" />
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <meta http-equiv="pragma" content="no-cache" />
        -->
	<!-- blueprint CSS framework -->
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
         
                        
        <!--<script type="text/javascript" src="https://getfirebug.com/firebug-lite.js"></script>-->
            
        <link rel="shortcut icon" href="http://pj.pagcoregames.com/favicon.ico" type="image/x-icon" />
	<title>Screen Saver</title>
        <script type="text/javascript">
            
            try
            {
                var Shell = new ActiveXObject('WScript.Shell');
                var regpath = '<?php echo LPConfig::app()->params["registry_path"]["terminalCode"] ?>';
                var terminalCode = Shell.RegRead(regpath);
//              var terminalCode = 'ICSA-TSTID06';
//              var terminalCode = '<?php echo LPConfig::app()->params["TerminalTstCode"]; ?>';
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
                      $.ajax({
                                url:'../Helper/connector.php',
                                type: 'post',
                                dataType: 'json',
                                async:false,
                                data:{fn:function(){return 'getTerminalType';},
                                     TerminalCode:function(){return terminalCode;}},
                                success:function(data){ 
                                    terminalType = JSON.stringify(data['Terminaltype']);
                                    siteID = JSON.stringify(data['SiteID']);
                                    siteClassID = JSON.stringify(data['SiteClassID']);
                                                                        
//                                    if(IsDetectTerminalType){
//                                        if(siteClassID == 1 || siteClassID == 2){ // Platinum/NonPlatinum site
//                                            window.location.href = LPewalletURL;
//                                        }
//                                    }
                                    
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
                                    }else
                                    {
                                        $.prompt("Invalid terminal. Terminal is not properly mapped.");
                                    }
                                }
                            });
                    };
                    
                    $.getTerminalUserMode = function(){
                        $.ajax({
                            url:'../Helper/connector.php',
                            type: 'post',
                            dataType: 'json',
                            async: true,
                            data:{fn:function(){return 'getTerminalUserMode';},
                                 TerminalCode:function(){return terminalCode;}},
                            success:function(data){  
                                if(data != null){
                                    userMode = JSON.stringify(data['UserMode']).replace(/"/g,""); 
                                    serviceCode = JSON.stringify(data['Code']).replace(/"/g,""); 
                                    siteClassID = JSON.stringify(data['SiteClassID']).replace(/"/g,""); 
                                    $.ajax({
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

                                                if(SessionType == 1) { //terminal based session
                                                   $.showRegular(true); 
                                                } else if(isesafe == 1 && SessionType == 2 && !(TransactionSummaryID === undefined || TransactionSummaryID === null || TransactionSummaryID === 'undefined' || TransactionSummaryID === 'null')) { //e-safe, user based session
                                                    $.showLobby2(true);
                                                } else if(isesafe == 0 && SessionType == 2 && !(TransactionSummaryID === undefined || TransactionSummaryID === null || TransactionSummaryID === 'undefined' || TransactionSummaryID === 'null')) { //non e-safe, user based session
                                                    $.showRegular(true);
                                                } else { //return to the default home page if there's no active session with valid login start
                                                    if(terminalType==2&&(userMode==0||userMode==2)){
                                                        $.showRegular(true);
                                                    } else if(terminalType==2&&userMode==1){ 
                                                        $.showRegular(false);
                                                    } else {
                                                        $.prompt("Invalid terminal. Terminal is not properly mapped.");
                                                    }
                                                }
                                            }
                                        });   
                                } else {
                                    $.prompt("Invalid terminal. Can't get terminal details.");
                                }
                                
                                
                            }
                        });
                    };
                    
                    $.countServices = function(){
                        $.ajax({
                            url:'../Helper/connector.php',
                            type: 'post',
                            dataType: 'json',
                            async: true,
                            data:{fn:function(){return 'countServices';},
                                 TerminalCode:function(){return terminalCode;}},
                            success:function(data){
                                if(JSON.stringify(data['Count']).replace(/\"/g, "")!=2)
                                    $.prompt("Invalid terminal. Terminal is not properly mapped"); //casino != 2
                            }
                        });
                    }

                    $.showRegular = function(bool){
                        if(bool){
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
                            if(serviceCode == "MM"){
                                $("#tdvv").css("display","none");
                                $("#tdmm").css("display","block");
                                $("#link-mm").css("margin-right","25px");
                                $("#mmimg").css("margin-right","25px");
                            } else if(serviceCode == "VV"){
                                $("#tdvv").css("display","block");
                                $("#tdmm").css("display","none");
                                $("#link-vv").css("margin-right","25px");
                                $("#vvimg").css("margin-right","25px");
                            }
                            $("#vvimg").css({
                                "width":lobby2imgW+"px",
                                "height":lobby2imgH+"px"
                            });
                            $("#link-vv").css({
                                "width":lobby2imgW+"px",
                                "height":lobby2imgH+"px",
                                "background-size":lobby2imgW+"px "+lobby2imgH+"px"
                            });
                        }
                        else{
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
                            if(IsALLeSAFE){
                                $("#signupforfreetd").css("display","none");
                                $("#InstantPlaytbl").css("display","none");
                            } else {
                                $("#signupforfreetd").css("display","block");
                                $("#InstantPlaytbl").css("display","block");
                            }
                        }
                    };
                    
                    $.showLobby2 = function(bool){
                        if(bool){
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
                            if(siteClassID == "1"){
                                $("#link-platinum-nonactive").css("display","block");
                                $("#link-platinum-active").css("display","none");
                            } else {
                                if(serviceid != 19){
                                    $("#link-platinum-nonactive").css("display","none");
                                    $("#link-platinum-active").css("display","block");
                                } else {
                                    $("#link-platinum-nonactive").css("display","block");
                                    $("#link-platinum-active").css("display","none");
                                }
                            } 
                            $("#signupforfreetd").css("display","none");
                            $("#InstantPlaytbl").css("display","none");
                        }
                        else{
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
                            
                            if(IsALLeSAFE){
                                $("#signupforfreetd").css("display","none");
                                $("#InstantPlaytbl").css("display","none"); 
                            } else {
                                $("#signupforfreetd").css("display","block");
                                $("#InstantPlaytbl").css("display","block");
                            }
                        }
                    }

                    showLightbox = function(onSuccess){
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
                        onComplete:function(){
                            $.fancybox.resize();
                            onSuccess();
                        },
                        onClosed: function(){
                            if(xhr != null)
                                xhr.abort();
                            xhr = null;
                        }
                        
                    });
                };
            
            
            
            $(document).ready(function(){
                //alert(localStorage.getItem("PIN") + "\n" + typeof localStorage.getItem("PIN")); 
                $('#system-version').html(sysversion);                
                localStorage.clear();
                $.checkTerminalType();
                $.getTerminalUserMode();
                $.countServices();
                xhr = null;
               try{
                   window.external.ScreenBlocker(false);
               
                    function tempAlert(msg,duration) {
                        if($('#sessionendedalert').length <= 0){
                            var el = document.createElement("div");
                            el.setAttribute("style","text-align: center; margin-top: 0; margin-right:auto; margin-bottom:0; margin-left:auto;font-size: 30px;background-color:white;width: 306px;height: 45px;");
                            el.setAttribute("id","sessionendedalert");
                            el.innerHTML = msg;
                            setTimeout(function(){
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

                    $(this).bind('contextmenu', function(e) {
                        e.preventDefault();
                    });
                    $(':not(input,select,textarea)').disableSelection();    

                    try {

                        var Shell = new ActiveXObject('WScript.Shell');
                        var regpath = '<?php echo LPConfig::app()->params["registry_path"]["terminalCode"] ?>';
//                        var terminalCode = '<?php echo LPConfig::app()->params["TerminalTstCode"]; ?>';
                        var login = '';
                        var terminalPass = '';

                        try {
                            terminalCode = Shell.RegRead(regpath);
                            if(terminalCode != ''){
                                $('#casino').attr("TerminalCode", terminalCode);
                                $('#casinoo').attr("TerminalCode", terminalCode);
                            }
                        } catch(e) {
    //                        displayMessageLightbox('Please setup the registry',function(){
    //                        });
                            alert("Please setup the registry");
                            return false;
                        }

    //                    $('.btnClose').live('click',function(){
    //                        jQuery.fancybox.close();
    //                    });

                        $('#casino0').live('click',function(){
    //                    $('#casino').click(function(){

                            var currServiceID = '';
    //                        var bot = $(this).attr('botpath');
    //                        var casinopath = $(this).attr('casinopath');
    //                        showLightbox(function(){
                            $.ajax({
                                url:'../Helper/connector.php',
                                type: 'post',
                                dataType: 'json',
                                data: {fn: function(){return "getServiceID";},
                                            TerminalCode: function(){return terminalCode;}},
                                success:function(data) {
                                    currServiceID = data;

                                    if(currServiceID) { 
                                        var url = '../Helper/connector.php';
                                        //var serviceType = $(this).attr('serviceType');

    //                                    showLightbox(function(){
                                           $.ajax({
                                                url:url,
                                                type: 'post',
                                                dataType:'json',
                                                data: {fn: function(){return "casinoServiceClick";}},
                                                success:function(data){
                                                    var currentbal = data.currentbal;

                                                    try {
                                                        if(data.html == 'not ok') {
    //                                                        displayMessageLightbox('<b style=\"width:125px\">Session Ended</b>',function(){
    //                                                            location.reload();
    //                                                            return false;
    //                                                        })
                                                            try {
                                                                window.external.ScreenBlocker(true);
                                                            } catch(e) {
                                                                //alert(e);
                                                                //do nothing
                                                            }
    //                                                        location.reload();
                                                            tempAlert("Session has been ended!",3000);
                                                            return false;
                                                        } else {
    //                                                        if(currentbal <= 0){
    //                                                            displayMessageLightbox('<b style=\"width:125px\">Can\'t open Game Client: Balance is insufficient.</b>',function(){                                   
    //                                                                setTimeout(lobbyURL,3000);
    //                                                            })
    //                                                            alert("Can\'t open Game Client: Balance is insufficient.");
    //                                                            setTimeout(lobbyURL,3000);
    //                                                        } else {

                                                                try {
    //                                                                if(<?php // echo LPConfig::app()->params["enable_blocker"]; ?>) {
                                                                        if(terminalCode !== ''){
                                                                            showLightbox(function(){
                                                                            $.ajax({
                                                                                url:'../Helper/connector.php',
                                                                                type: 'post',
                                                                                dataType: 'json',
                                                                                data: {fn: function(){return "getUserBaseLogin";},
                                                                                            TerminalCode: function(){return terminalCode;}},
                                                                                success:function(data) {

                                                                                    if(data.UserMode == '1'){
                                                                                        login = data.UBServiceLogin;
                                                                                        if(data.Code == "MM"){
                                                                                            terminalPass = data.UBHashedServicePassword;
                                                                                        } else { terminalPass = data.UBServicePassword; }
                                                                                        currServiceID = data.ServiceID;
                                                                                        if(login != '' && terminalPass != ''){
                                                                                                window.external.OpenGameClient(currServiceID, login, terminalPass);
                                                                                                jQuery.fancybox.close();
                                                                                        } else {
                                                                                            alert("Please try again.");
                                                                                            return false;
                                                                                        }
                                                                                    } else {
    //                                                                                    jQuery.fancybox.close();
    //                                                                                    showLightbox(function(){
                                                                                        $.ajax({
                                                                                            url:'../Helper/connector.php',
                                                                                            type: 'post',
                                                                                            dataType: 'json',
                                                                                            data: {fn: function(){return "getTerminalBaseLogin";},
                                                                                                        TerminalCode: function(){return data.TerminalCode;},
                                                                                                        ServiceID: function(){return data.ServiceID;}
                                                                                            },
                                                                                            success:function(data) {
                                                                                                if(data.TerminalCode != ''){
                                                                                                    login = data.TerminalCode;
                                                                                                    if(data.Code == "MM"){
                                                                                                        terminalPass = data.HashedServicePassword;
                                                                                                    } else { terminalPass = data.ServicePassword; }
                                                                                                    currServiceID = data.ServiceID;

                                                                                                    if(login != '' && terminalPass != ''){
                                                                                                        window.external.OpenGameClient(currServiceID, login, terminalPass);
                                                                                                        jQuery.fancybox.close();
                                                                                                    } else {
                                                                                                        alert("Please try again.");
                                                                                                        return false;
                                                                                                    }
                                                                                                }
                                                                                            },
                                                                                            error: function(XMLHttpRequest, e){
                                                                                                    alert(XMLHttpRequest.responseText);
                                                                                                    if(XMLHttpRequest.status == 401)
                                                                                                    {
                                                                                                        window.location.reload();
                                                                                                    }
                                                                                                }
                                                                                        });
    //                                                                                });
                                                                                    }
                                                                                },
                                                                                error: function(XMLHttpRequest, e){
                                                                                        alert(XMLHttpRequest.responseText);
                                                                                        if(XMLHttpRequest.status == 401)
                                                                                        {
                                                                                            window.location.reload();
                                                                                        }
                                                                                    }
                                                                            });
                                                                            });
                                                                        }
    //                                                                } else {
    //                                                                    window.external.OpenGameClient(currServiceID, login, terminalPass);
    //                                                                }
                                                                } catch(e) {
    //                                                                displayMessageLightbox('<b style=\"width:125px\">Game client not found</b>',function(){
    //                                                                    setTimeout(lobbyURL,3000);
    //                                                                });
                                                                alert("Game client not found.");
                                                                setTimeout($(function(){location.reload();}),3000);
                                                                }

    //                                                        }
                                                        }
                                                    } catch(e) {
    //                                                    displayMessageLightbox('<b style=\"width:125px\">Parse error</b>',function(){
    //                                                        setTimeout(lobbyURL,3000);
    //                                                    });
                                                            alert("Parse error");
                                                            setTimeout($(function(){location.reload();}),3000);
                                                    }
                                                },
                                                error:function(e) {
    //                                                displayMessageLightbox('<b style=\"width:125px\">'+e.responseText+'</b>',function(){
    //                                                    setTimeout(lobbyURL,3000);
    //                                                });
                                                    alert(e.responseText);
                                                    setTimeout($(function(){location.reload();}),3000);
                                                }
                                            }); 
    //                                    });
                                    } else {
                                        try {
                                            window.external.ScreenBlocker(true);
                                        } catch(e) {
                                            //alert(e);
                                            //do nothing
                                        }

                                        tempAlert("Session has been ended!",3000);
                                    }

                                },
                                error: function(XMLHttpRequest, e){
                                        alert(XMLHttpRequest.responseText);
                                        if(XMLHttpRequest.status == 401)
                                        {
                                            window.location.reload();
                                        }
                                    }
                            });
    //                        });

                            return false;
                        });
                    }catch(e) {
                        alert('There is a problem in activex');
                    }  
                
                }catch(e){
                    //do nothing
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
    <center><table id="contentt">
        <tr>
            <td>
                <div id="left">
                    <div id='cont1'>
                         <center><table style="margin-top:40px; ">
                            <tr><td><img src="../images/eSafelogo.png" /></td></tr>
                            <tr> <td id="tdpad"></td> </tr>
                            <tr>
                                <td><p style="color: white;"><b>PIN</b></p>
                                    <input type='password' id='pinfield' value="000000" readonly /></td>
                             </tr>
                            <tr> <td id="tdpad"></td> </tr>
                                 <tr>
                                     <td><input type='button' id='Login' value='Login' /></td>
                                 </tr>
                                  <tr> <td id="tdpad"></td> </tr>
                                  <tr>
                                     <td id="signupforfreetd" style="display: none; color: white;">No account yet?&nbsp;<a id='signUP'>Sign up for free</a></td>
                                 </tr> 
                                 <tr>
                                    <td>
                                        <a id='changeuserPin'>Change PIN</a>
                                        <table id="InstantPlaytbl" style="display: none;">
                                            <tr> <td id="tdpad"></td> </tr>
                                            <tr>
                                                <td>
                                                    <input type='button' id='casino' value='Instant Play' />
                                                </td>
                                            </tr>
                                        </table>
                                    </td> 
                                </tr>
                                  
                            </table></center>
                    </div>
                          
                    <div id='hdncont1' style="display: none;">
                        
                         <center><table style="margin-top:30px;">
                           <tr>
                                <td><p><b>Enter New PIN</b></p>
                                        <input type='password' id='newPinField'/>
                                </td>
                            </tr>
                            <tr>
                                <td><p><b>Re-Enter New PIN</b></p>
                                    <input type='password' id='rnewPinField'/></td>
                             </tr>   
                            <tr><td>&nbsp;</td></tr>
                                 <tr>
                                     <td><input type='button' id='Login' value='Login' /></td>
                                 </tr>
                            </table></center>
                    </div>
                    
                    <center><div id='buttcont' style="display:none"></div></center> 
                </div>   
            </td>
            </tr>
            <tr><td id="loginfooter"></td></tr>           
        </table></center>
        
        <!--Instant Play - terminal based landing page-->
        <center>
            <div id='instantPlay' >
                <div id="virtual-ecity-logo-container" style="visibility: hidden; margin-top: 40px;">
                    <img src="../images/virtual_entertainment_city_logo.png" >
                </div>
                <!-- previous code ------------------------------------------------
                <table id="iptable" style="margin-top: 10px; margin-left: -12.5px;">
                -->
                <table id="iptable" style="margin-top: 10px;">
                    <tr>
                        <td id="tdmm">
                            <div id="link-mm" class="link-container">
                                <a id='casinomm' >
                                    <img id="mmimg" src="../images/mm_v15_normal.png" />
                                </a>
                            </div>
                        </td>
                        <td id="tdvv">
                            <div id="link-vv" class="link-container">
                                <a id='casinovv' >
                                    <img id="vvimg" src="../images/vv_v15_normal.png" />
                                </a>
                            </div>
                        </td>
                        <!--    remove this comment to display SS casino image  -----------------------
                        <td id="tdss">
                            <div id="link-ss" >
                                    <img id="ssimg" src="../images/ss_unavailable.png" />
                            </div>
                        </td>
                        -->
                    </tr></table>
                <div style='margin-bottom: 5px'></div>
            </div>
            <div id="ipfooter" style="margin-top: 30px;"></div>
        </center>

        <!--Lobby 2 - e-SAFE landing page-->
        <center>
            <div id='lobby2'>
                <table id="lobby2table" style="margin-top: 190px; "><tr>
                        <td>
                            <div id="link-non-platinum" style="margin-right: 25px;" class="link-container">
                                <a class='btnnonplatinum' id='nonPlatinum' value='Classic'>
                                    <img id="classicimg" style="margin-right: 25px;" src="../images/mm_v15_normal.png" />
                                </a>
                            </div>
                        </td>
                        <td>
                            <div id="link-platinum-nonactive" style="margin-top: -10px;" class="link-container">
                                <a class='btnplatinum' value='Modern' >
                                    <img id="modernimgnonact" src="../images/ss_unavailable.png" />
                                </a>
                            </div>
                            <div id="link-platinum-active" style="margin-top: -10px;" class="link-container">
                                <a id='platinum' class='btnplatinum' value='Modern' >
                                    <img id="modernimgact" src="../images/ss_v15_normal.png" />
                                </a>
                            </div>
                        </td>
                    </tr></table>
                <div style='margin-bottom: 5px'>
    <!--                <input type='button' class='myButtonStyle' style='width:407px; height: 45px;' id='endSession' value='END SESSION'>-->
                    <div id="link-endsession" class="link-container">
                        <a id='endSession' class='myButtonStyle' value='END SESSION' >
                            <img id="endsessionimg" src="../images/end_session.png" />
                        </a>
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
        

        <!--<div id="copies-container">-->
<!--        <div id="wood-background">
            <div id="bars-container">
                <div id="progressive-jackpot-container">

                    <div id="prog-text">PROGRESSIVE JACKPOT</div>
                    <div id="prog-text2"></div>
                     <div id="prog-amount"></div>
                    <div class="clearer"></div>

                </div>

                <div id="main-casino-game-container">
                    <div id="casino-game-container">
                        <div id="casino-text">CASINO</div>
                        <div id="casino-text2"><?php // echo isset($currentCasino)?$currentCasino:''; ?></div>
                        <div id="casino-balance-text">BALANCE</div>                    
                        <div id="casino-balance"><?php // echo isset($currentBalance)?$currentBalance:''; ?></div>                    
                        <div class="clearer"></div>
                    </div>
                    <div id="refresh-button">
                        <a href="" id="refresh"><img src="<?php // echo Yii::app()->getModule('launchpad')->baseAssets; ?>/images/refresh_button.png"></a>
                    </div> #refresh-button     

                    <div class="clearer"></div>
                </div>
            </div>
        </div>-->
        <!--</div>-->

        <script type="text/javascript">
        $(document).ready(function(){
//            var xhrbalance = null;

//            $('#prog-text2').vTicker({
//                speed: 500,
//                pause: 3000,
//                showItems: 1,
//                animation: 'fade',
//                mousePause: false,
//                height: 0,
//                direction: 'up'
//        //        onLoad:function(obj){
//        //           var title = obj.children('li:first').children('input').val();
//        //           $('#messages-and-announcement-container').html('<h2 class="messages">'+title+'</h2>');
//        //        },
//        //        onChange:function(obj){
//        //           if(obj != undefined) {
//        //                var title = obj.children('li:first').children('input').val();
//        //                $('#messages-and-announcement-container').html('<h2 class="messages">'+title+'</h2>');
//        //           }
//        //        }
//            });



            // document hover
//           $(document).live('hover',function(){
//              if(xhrbalance == null) {
//                 //url = '<?php //echo $this->createUrl('getBalance',array('serviceID'=>Yii::app()->user->getState('currServiceID'),'format'=>'yes')); ?>';
//                 url = '<?php // echo $this->createUrl('getcasinoandabalance',array('format'=>'yes')); ?>';
//                 jQuery('#casino-balance').html('Loading ...');
//                 jQuery('#casino-text2').html('Loading ...');
//                 xhrbalance = jQuery.ajax({
//                    url : url,
//                    type : 'post',
//                    dataType : 'json',
//                    success : function(data){
//                       if(data.balance == undefined) {
//                           jQuery('#casino-balance').html('<span syle="color:red">N/A</span>');
//                           jQuery('#casino-text2').html('<span syle="color:red">N/A</span>');
//                       } else {
//                           jQuery('#casino-balance').html(data.balance);
//                           jQuery('#casino-text2').html(data.casino);
//                       }
//                       xhrbalance = null;
//                    },
//                    error : function(e){
//                       jQuery('#casino-balance').html('<span syle="color:red">N/A</span>');
//                       xhrbalance = null;
//                    }
//                 });
//              }
//
//           });

//            $('#refresh').click(function(){
//                location.reload();
//                showLightbox();
//            });
//
//            var heartbeat = null;
//            setInterval(function(){
//                if(heartbeat == null) {
//                    heartbeat = $.ajax({
//                        url:'<?php // echo Yii::app()->createUrl('launchpad/lobby/ping'); ?>',
//                        success:function(){
//                            heartbeat = null;
//                        },
//                        error:function(){
//                            heartbeat = null;
//                        }
//                    })
//                }
//                },'<?php // echo LPConfig::app()->params['heart_beat'] ?>')
        })
        </script>
        
    
    <div id="blackOut"></div>
    <div id="blocker"></div>
    <div id="whiteBox">   
    </div>    
    <div id="loadingBox">   
    </div>  
    <div id="prompt">   
    </div>    
            
    <div class="clear"></div>

    <div id="footer">
        
    </div><!-- footer -->
    
    </div><!-- page -->
<span id="system-version"></span>
</body>
</html>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php include_once '../models/LPConfig.php'; ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />

	<!-- blueprint CSS framework -->
	<link rel="stylesheet" type="text/css" href="../css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="../css/lp.css" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="../css/ie.css" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="../css/reset.css" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="../css/reset2.css" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="../css/style.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="../fancybox/jquery.fancybox-1.3.4.css" media="screen" />
            
                        <script type="text/javascript" src="../js/jquery-1.7.1.min.js"></script>
                        <script type="text/javascript" src="../css/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
                        <script type="text/javascript" src="../css/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
                        <script type="text/javascript" src="../js/jquery.vticker.js"></script>
                        <script type="text/javascript" src="../js/disable_selection.js"></script>
                        <!--<script type="text/javascript" src="https://getfirebug.com/firebug-lite.js"></script>-->
            
        <link rel="shortcut icon" href="http://pj.pagcoregames.com/favicon.ico" type="image/x-icon" />
	<title>Lobby</title>
        <script type="text/javascript">
            $(document).ready(function(){

                
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
                    var terminalCode;
                    var login = '';
                    var terminalPass = '';
                                
                    try {
                        terminalCode = Shell.RegRead(regpath);
                        if(terminalCode != ''){
                            $('#casino').attr("TerminalCode", terminalCode);
                        }
                    } catch(e) {
//                        displayMessageLightbox('Please setup the registry',function(){
//                        });
                        alert("Please setup the registry");
                        return false;
                    }

                    $('.btnClose').live('click',function(){
                        jQuery.fancybox.close();
                    });
                    
                    $('#casino').live('click',function(){
//                    $('#casino').click(function(){
                        var currServiceID = '';
//                        var bot = $(this).attr('botpath');
//                        var casinopath = $(this).attr('casinopath');

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
                                                                                    } else {
                                                                                        alert("Please try again.");
                                                                                        return false;
                                                                                    }
                                                                                } else {
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

                        
                        return false;
                    });
                }catch(e) {
                    alert('There is a problem in activex');
                }

            });
        </script>
</head>

<body>
    <div class="container" id="page">
<center>
        <div id="virtual-ecity-logo-container">
            <img src="../images/virtual_entertainment_city_logo.png" >
        </div>
</center>
        <div id="casino-games-wrapper" align="center">

            <div align="center" id="casino-games-container">

                <div id="magic-macau-container">
                    <div id="link-magic-macau" class="link-container">
                        <a id="casino" href="javascript: void();" onClick="">
                            <img id="mmcasino" src="../images/magic_macau.jpg" />
                        </a>
                    </div>
                </div>
                
                <div class="clearer"></div>

            </div><!-- #casino-games-container -->

        </div><!-- #casino-games-wrapper -->

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
            
    <div class="clear"></div>

    <div id="footer">

    </div><!-- footer -->
    </div><!-- page -->
</body>
</html>

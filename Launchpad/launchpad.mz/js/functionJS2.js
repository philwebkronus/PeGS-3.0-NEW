$(document).ready(function() {

    $.resetVal();
    $.resetVal(0);
    $.resetVal(1);


    $.checkSessionMM = function()
    {
        ServiceID = mmserviceid;
        $.transferWallet();
    };

    $.checkSessionVV = function()
    {
        ServiceID = vvserviceid;
        TabID = JSON.stringify(LasVegasHome);
        $.transferWallet();
    };

    /*START OF GAME*/

    $.transferGame1 = function()
    {
        ServiceID = vvserviceid;
        TabID = JSON.stringify(Game1);
        $.transferWallet();
    };

    $.transferGame2 = function()
    {
        ServiceID = vvserviceid;
        TabID = JSON.stringify(Game2);
        $.transferWallet();
    };

    $.transferGame3 = function()
    {
        ServiceID = vvserviceid;
        TabID = JSON.stringify(Game3);
        $.transferWallet();
    };

    $.transferGame4 = function()
    {
        ServiceID = vvserviceid;
        TabID = JSON.stringify(Game4);
        $.transferWallet();
    };

    $.transferGame5 = function()
    {
        ServiceID = vvserviceid;
        TabID = JSON.stringify(Game5);
        $.transferWallet();
    };


    $.transferGame6 = function()
    {
        ServiceID = vvserviceid;
        TabID = JSON.stringify(Game6);
        $.transferWallet();
    };

    $.transferGame7 = function()
    {
        ServiceID = vvserviceid;
        TabID = JSON.stringify(Game7);
        $.transferWallet();
    };

    $.transferGame8 = function()
    {
        ServiceID = vvserviceid;
        TabID = JSON.stringify(Game8);
        $.transferWallet();
    };

    $.transferGame9 = function()
    {
        ServiceID = vvserviceid;
        TabID = JSON.stringify(Game9);
        $.transferWallet();
    };

    $.transferGame10 = function()
    {
        ServiceID = vvserviceid;
        TabID = JSON.stringify(Game10);
        $.transferWallet();
    };

    $.transferGame11 = function()
    {
        ServiceID = vvserviceid;
        TabID = JSON.stringify(Game11);
        $.transferWallet();
    };


    $.transferGame12 = function()
    {
        ServiceID = vvserviceid;
        TabID = JSON.stringify(Game12);
        $.transferWallet();
    };

    $.transferGame13 = function()
    {
        ServiceID = vvserviceid;
        TabID = JSON.stringify(Game13);
        $.transferWallet();
    };

    $.transferGame14 = function()
    {
        ServiceID = vvserviceid;
        TabID = JSON.stringify(Game14);
        $.transferWallet();
    };

    $.transferGame15 = function()
    {
        ServiceID = vvserviceid;
        TabID = JSON.stringify(Game15);
        $.transferWallet();
    };


    /*END OF GAME*/


  /*START OF TRANSFERWALLET*/

   $.transferWallet = function()
    {
        window.external.ScreenBlocker(true);
        
        showLightbox(function() {

            $.post("../Helper/lock.php",
                    {
                        data: 'countSession',
                        terminalCode: terminalCode,
                    }, function(data) {

                var json = $.parseJSON(data);

                if (json.Count != undefined) {
                    if (json.Count != 0) {
                        if (json.Count == 1) {

                            $.post("../Helper/lock.php",
                                    {
                                        data: 'transferWallet',
                                        terminalCode: terminalCode,
                                        ServiceID: ServiceID,
                                        UserMode: 3,
                                    }, function(datatw) {

                                var jsonTW = $.parseJSON(datatw);
                                if (jsonTW.ErrorCode == 0) {
                                    $.post("../Helper/lock.php",
                                            {
                                                data: 'checkIfTerminalSessionLobby',
                                                terminalCode: terminalCode,
                                                ServiceID: ServiceID,
                                            }, function(dataDetails) {

                                        var jsonDetails = $.parseJSON(dataDetails);

                                        if (jsonDetails.Count != undefined) {
                                            if (jsonDetails.Count != 0) {
                                                if (jsonDetails.Count == 1) {

                                                    ServiceUsername = jsonDetails.ServiceUsername;
                                                    ServicePassword = jsonDetails.ServicePassword;
                                                    isVIP = jsonDetails.isVIP;
                                                    ServicePassword = ServicePassword.replace(/\"/g, "");
                                                    HabaneroPath = jsonDetails.HabaneroPath;

                                                    $.launchGame(ServiceID, ServiceUsername, ServicePassword, isVIP, HabaneroPath, terminalCode);


                                                } else {
                                                    window.external.ScreenBlocker(false);
                                                    jQuery.fancybox.close();
                                                    $.prompt("[ERROR 007] Terminal has more than One (1) active session.");
                                                }
                                            } else {
                                                window.external.ScreenBlocker(false);
                                                jQuery.fancybox.close();
                                                $.prompt("[ERROR 006] Terminal has no valid session");
                                            }
                                        }
                                        else {
                                            window.external.ScreenBlocker(false);
                                            jQuery.fancybox.close();
                                            $.prompt(JSON.stringify(jsonTW.ReturnMessage));
                                        }

                                    });
                                }
                                else {
                                    window.external.ScreenBlocker(false);
                                    jQuery.fancybox.close();

                                    if (jsonTW.ErrorCode == 2 || jsonTW.ErrorCode == 8 || jsonTW.ErrorCode == 25 || jsonTW.ErrorCode == 40 || jsonTW.ErrorCode == 41 || jsonTW.ErrorCode == 42 || jsonTW.ErrorCode == 45) {
                                        $.prompt(JSON.stringify(jsonTW.ReturnMessage).replace(/\"/g, ""));
                                    } else {
                                        $.prompt("[ERROR #" + jsonTW.ErrorCode + "] An error was encountered. Please try again.");
                                    }
                                }

                            });

                        } else {
                            window.external.ScreenBlocker(false);
                            jQuery.fancybox.close();
                            $.prompt("[ERROR 003] Terminal has more than One (1) active session.");
                        }
                    } else {
                        window.external.ScreenBlocker(false);
                        jQuery.fancybox.close();
                        $.prompt("[ERROR 002] Terminal has no valid session");
                    }
                }
                else {
                    window.external.ScreenBlocker(false);
                    jQuery.fancybox.close();
                    $.prompt("[ERROR 001] Error was encountered. Kindly retry.");
                }

            });
        });
    };


  /*END OF TRANSFERWALLET*/


    $.checkTerminalBasedSession = function()
    {
        showLightbox(function() {

            $.post("../Helper/lock.php",
                    {
                        data: 'checkIfTerminalSessionLobby',
                        terminalCode: terminalCode,
                        ServiceID: ServiceID, }, function(data) {
                var json = $.parseJSON(data);

                if (json.Count != undefined) {
                    if (json.Count != 0) {
                        if (json.Count == 1) {

                            ServiceUsername = json.ServiceUsername;
                            ServicePassword = json.ServicePassword;
                            isVIP = json.isVIP;
                            ServicePassword = ServicePassword.replace(/\"/g, "");
                            HabaneroPath = json.HabaneroPath;

                            $.launchGame(ServiceID, ServiceUsername, ServicePassword, isVIP, HabaneroPath, terminalCode);

                        } else {
                            jQuery.fancybox.close();
                            $.prompt("[ERROR 005] Terminal has more than One (1) active session.");
                        }
                    } else {
                        jQuery.fancybox.close();
                        $.prompt("[ERROR 004] Terminal has no valid session");
                    }
                }
                else {
                    jQuery.fancybox.close();
                    $.prompt("[ERROR 003] Error was encountered. Kindly retry.");
                }

            });
        });

    };

    $.checkEwalletSession2 = function()
    {

        var userMode = "";
        var serviceGID = "";
        showLightbox(function() {


            $.post("../Helper/lock.php",
                    {
                        data: "checkEwalletSession",
                        terminalCode: terminalCode,
                        option: 0
                    }, function(data) {
                if (JSON.stringify(data['IsEwallet']) != -1)
                {
                    if (JSON.stringify(data['IsEwallet']) == 0)
                    {
                        if (JSON.stringify(data['UBServiceLogin']) != 0)
                        {

                            userMode = JSON.stringify(data['UserMode']);
                            serviceGID = JSON.stringify(data['ServiceGroupID']);

                            if (userMode == 1 && serviceGID == 4)
                            {
                                tmpUBServicePassword = JSON.stringify(data['UBHashedServicePassword']);
                            }
                            if (userMode == 1 && serviceGID != 4)
                            {
                                tmpUBServicePassword = JSON.stringify(data['UBServicePassword']);
                            }

                            tmpServiceID = JSON.stringify(data['ServiceID']);
                            tmpUBserviceLogin = JSON.stringify(data['UBServiceLogin']);
                            tmpUBServicePassword = tmpUBServicePassword.replace(/\"/g, "");

                            $.launchGame(tmpServiceID, tmpUBserviceLogin, tmpUBServicePassword, null, null);
                        }
                        else
                        {
                            jQuery.fancybox.close();
                            $.prompt("There is no existing session");
                        }
                    }
                    else
                    {
                        jQuery.fancybox.close();
                        $.prompt("There is an existing terminal session in e-SAFE. Please enter your credentials to login");
                    }
                }
                else
                {
                    jQuery.fancybox.close();
                    $.prompt("Terminal has no valid session");
                }
            }, 'json');

        });

    };


});



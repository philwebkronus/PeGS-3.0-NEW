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


    /*START OF TRANSFER WALLET*/

    $.transferWallet = function()
    {
        window.external.ScreenBlocker(true);

        showLightbox(function() {

            var err = false;

            $.post("../Helper/connector.php",
                    {
                        fn: 'getServiceID',
                        TerminalCode: terminalCode,
                    }, function(dataID) {

                var array = dataID.split(", ");

                if (array.length > 1) {
                    for (i = 0; i < array.length; i++) {
                        if (array[i] === '22' || array[i] === '25') {
                            err = true;
                            alert("Error : Invalid casino mapping.");
                            jQuery.fancybox.close();
                            window.external.ScreenBlocker(true);
                        }
                    }
                } else {
                    err = true;
                    alert("Error : Only one casino was mapped on this terminal.");
                    window.external.ScreenBlocker(true);
                    jQuery.fancybox.close();
                }


                if (err == false) {

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
                                                UserMode: userMode,
                                            }, function(datatw) {

                                        var jsonTW = $.parseJSON(datatw);

                                        if (jsonTW.ErrorCode == '0') {
                                            $.post("../Helper/lock.php",
                                                    {
                                                        data: 'checkIfTerminalSessionLobby',
                                                        terminalCode: terminalCode,
                                                        ServiceID: ServiceID,
                                                    }, function() {
                                            })
                                                    .done(function(dataDetails) {

                                                        var jsonDetails = $.parseJSON(dataDetails);

                                                        if (jsonDetails.Count != undefined) {
                                                            if (jsonDetails.Count == 1) {

                                                                ServiceUsername = jsonDetails.ServiceUsername;
                                                                ServicePassword = jsonDetails.ServicePassword;
                                                                isVIP = jsonDetails.isVIP;
                                                                ServicePassword = ServicePassword.replace(/\"/g, "");
                                                                HabaneroPath = jsonDetails.HabaneroPath;
                                                                TsServiceID = jsonDetails.ServiceID;

                                                                $.launchGame(TsServiceID, ServiceUsername, ServicePassword, isVIP, HabaneroPath, terminalCode);

                                                            }
                                                        }
                                                        else {
                                                            window.external.ScreenBlocker(false);
                                                            jQuery.fancybox.close();
                                                            $.prompt("[LP.05] An error was encountered. Please try again.");
                                                        }
                                                    })
                                                    .fail(function() {
                                                        window.external.ScreenBlocker(false);
                                                        jQuery.fancybox.close();
                                                        $.prompt("[LP.04] An error was encountered. Please try again.");
                                                    });
                                        }
                                        else {
                                            window.external.ScreenBlocker(false);
                                            jQuery.fancybox.close();

                                            if (jsonTW.ErrorCode == 1000) {
                                                $.prompt("[LP #" + jsonTW.ErrorCode + "] An error was encountered transferring to this casino. Please try the other casino.");
                                            }
                                            else if (jsonTW.ErrorCode == '05') {                                            
												$.prompt("[LP #" + jsonTW.ErrorCode + "] Unable to transfer to new casino. Please call customer service.");
                                            }
                                            else if (jsonTW.ErrorCode == 50 || jsonTW.ErrorCode == 51 || jsonTW.ErrorCode == 52 || jsonTW.ErrorCode == 53) {
                                                $.prompt("[LP #" + jsonTW.ErrorCode + "] An error was encountered transferring to this casino. Please call customer service.");
                                            }
                                            else if (jsonTW.ErrorCode == '02' || jsonTW.ErrorCode == '05' || jsonTW.ErrorCode == '08' || jsonTW.ErrorCode == 25 || jsonTW.ErrorCode == 40 || jsonTW.ErrorCode == 41 || jsonTW.ErrorCode == 42 || jsonTW.ErrorCode == 45 || jsonTW.ErrorCode == 54) {
                                                $.prompt(JSON.stringify(jsonTW.ReturnMessage).replace(/\"/g, ""));
                                            }
                                            else {
                                                $.prompt("[LP #" + jsonTW.ErrorCode + "] An error was encountered. Please try again.");
                                            }
                                        }

                                    });

                                } else {
                                    window.external.ScreenBlocker(false);
                                    jQuery.fancybox.close();
                                    $.prompt("[LP.03] The terminal has more than one (1) active session.");
                                }
                            } else {
                                window.external.ScreenBlocker(false);
                                jQuery.fancybox.close();
                                $.prompt("[LP.02] The terminal has no valid session.");
                            }
                        }
                        else {
                            window.external.ScreenBlocker(false);
                            jQuery.fancybox.close();
                            $.prompt("[LP.01] An error was encountered. Kindly retry.");
                        }

                    });
                }
            });
        });
    };

    /*END OF TRANSFER WALLET*/

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

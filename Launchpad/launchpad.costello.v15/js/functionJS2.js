$(document).ready(function() {

    $.resetVal();
    $.resetVal(0);
    $.resetVal(1);

    $.checkSession2 = function() {
        $.post("../Helper/lock.php",
                {
                    data: 'countMappedCasinos',
                    TerminalID: terminalCode,
                    TerminalIDVIP: "",
                    option: 1
                }, function(data) {

            if (data > 0)
            {
                $.post("../Helper/lock.php",
                        {
                            data: 'checkIfTerminalSession',
                            terminalCode: terminalCode,
                        }, function(data) {

                    if (JSON.stringify(data['TerminalID']) > 0)
                    {
                        ServiceID = JSON.stringify(data['ServiceID']);

                        if (JSON.stringify(data['Usermode']) != 0 || JSON.stringify(data['Usermode']) != 3)
                        {

                            $.checkTerminalBasedSession();

                        }
                        else
                        {

                            $.checkEwalletSession2();
                        }
                    }
                    else
                    {
                        $.prompt("[ERROR 002] Terminal has no valid session");
                    }

                }, 'json');
            }
            else
            {
                $.prompt("[ERROR 001] Casino not available");
                $.resetVal(0);
                $.resetVal(1);
                $.resetVal();
            }

        }, 'json');

    };

    $.checkTerminalBasedSession = function()
    {
        showLightbox(function() {

            $.post("../Helper/lock.php",
                    {
                        data: 'checkIfTerminalSessionLobby',
                        terminalCode: terminalCode,
                        ServiceID: ServiceID,
                    }, function(data) {
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


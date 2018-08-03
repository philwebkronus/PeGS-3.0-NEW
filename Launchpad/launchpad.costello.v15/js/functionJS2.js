$(document).ready(function() {

    $.resetVal();
    $.resetVal(0);
    $.resetVal(1);

    $.checkSession2 = function() {
        var termID = "";
        var termIDVIP = "";

        $.post("../Helper/lock.php",
                {
                    data: 'countMappedCasinos',
                    TerminalID: terminalCode,
                    TerminalIDVIP: "",
                    option: 1
                }, function(data) {

            if (data == 1)
            {
                $.post("../Helper/lock.php",
                        {
                            data: 'checkTerminaServicesSession',
                            terminalCode: terminalCode,
                            option: 0
                        }, function(data) {

                    if (JSON.stringify(data['Count']) > 0)
                    {
                        tmpServiceID = JSON.stringify(data['ServiceID']);
                        tmpUserMode = JSON.stringify(data['UserMode']);
                        tmpServiceGroupID = JSON.stringify(data['ServiceGroupID']);

                        if (tmpServiceID == JSON.stringify(data['ServiceIDVIP']))
                        {

//                                      if(tmpUserMode==0&&tmpServiceGroupID==4)
//                                      {
//                                          ServicePassword = JSON.stringify(data['HashedServicePassword']);
//                                      }
//                                      if(tmpUserMode==0&&tmpServiceGroupID!=4)
//                                      {
//                                          ServicePassword = JSON.stringify(data['ServicePassword']);
//                                      }

                            ServicePassword = JSON.stringify(data['HashedServicePassword']);

                            termID = JSON.stringify(data['TerminalID']);
                            termIDVIP = JSON.stringify(data['TerminalIDVIP']);

                            if (tmpUserMode != 1 && tmpServiceGroupID != 4)
                            {

                                $.checkTerminalBasedSession(termID, termIDVIP);

                            }
                            else if (tmpUserMode == 3 && (tmpServiceGroupID == 4 || tmpServiceGroupID == 6))
                            {

                                $.checkTerminalBasedSession(termID, termIDVIP);

                            }
                            else
                            {

                                $.checkEwalletSession2();
                            }
                        }
                        else
                        {
                           $.prompt("ERROR 007: Invalid Terminal. Terminal is not properly mapped");
                        }
                    }
                    else
                    {
                        //check session regular
                        $.checkEwalletSession2();
                    }

                }, 'json');
            }
            else if (data > 1)
            {
                $.prompt("Invalid terminal. Terminal has more than one(1) casino​");
                $.resetVal(0);
                $.resetVal(1);
                $.resetVal();
            }
            else
            {
                $.prompt("Casino not available");
                $.resetVal(0);
                $.resetVal(1);
                $.resetVal();
            }

        }, 'json');

    };

    $.checkTerminalBasedSession = function(TerminalID, TerminalIDVIP)
    {


        showLightbox(function() {

            $.post("../Helper/lock.php",
                    {
                        data: 'checkIfTerminalSessionLobby',
                        terminalCode: terminalCode,
                        terminalID: TerminalID,
                        terminalIDVIP: TerminalIDVIP,
                        ServiceID: tmpServiceID
                    }, function(data) {
                var json = $.parseJSON(data);
                if (json.Count != undefined) {
                    if (json.Count != 0) {
                        if (json.Count == 1) {

                            ServiceUsername = json.ServiceUsername;
                            ServicePassword = json.HashedServicePassword;
                            isVIP = json.isVIP;
                            ServicePassword = ServicePassword.replace(/\"/g, "");
                            HabaneroPath = json.HabaneroPath

                            $.launchGame(tmpServiceID, ServiceUsername, ServicePassword, isVIP, HabaneroPath, terminalCode);

                        } else {
                            jQuery.fancybox.close();
                            $.prompt("Terminal has more than One (1) active session.");
                        }
                    } else {
                        jQuery.fancybox.close();
                        $.prompt(" Terminal has no valid session");
                    }
                }
                else {
                    jQuery.fancybox.close();
                    $.prompt("Error was encountered. Kindly retry.");
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
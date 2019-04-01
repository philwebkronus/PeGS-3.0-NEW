$.launchGame = function(currServiceID, login, terminalPass, isVIP, HBPath, terminalCode)
{

    if (currServiceID == 25 || currServiceID == 29) {

        try {
            var shell = new ActiveXObject("WScript.Shell");
            var cred = " /u " + login + " /p " + terminalPass + " /v " + isVIP + " /t " + terminalCode;
            var path = '"' + HBPath + '"' + cred;
            shell.Run(path);
        } catch (e) {
            $.prompt("Game client not found");
        }

    }
    else {

        try {
            var shell = new ActiveXObject("WScript.Shell");
            var cred = " -l " + login + " -p " + terminalPass;
            var path = '"' + HBPath + '"' + cred;
            shell.Run(path);
        } catch (e) {
            $.prompt("Game client not found");
        }

    }

    jQuery.fancybox.close();

};




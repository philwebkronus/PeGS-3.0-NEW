$.launchGame = function(currServiceID, login, terminalPass, isVIP, HBPath, terminalCode)
{

    if (currServiceID == 25 || currServiceID == 29) {

        try {
            var shell = new ActiveXObject("WScript.Shell");
            var cred = " /u " + login + " /p " + terminalPass + " /v " + isVIP + " /t " + terminalCode;
            var custom = " /custom " + TabID;
            var path = '"' + HBPath + '"' + cred + custom;
            try {
                window.external.ScreenBlocker(false);
                shell.Run(path);
            } catch (e) {
                window.external.ScreenBlocker(false);
                $.prompt("Ooops! Kindly retry.");
            }
        } catch (e) {
            window.external.ScreenBlocker(false);
            $.prompt("Game client not found");
        }

    }
    else {

        try {
            var shell = new ActiveXObject("WScript.Shell");
            var cred = " -l " + login + " -p " + terminalPass;
            var path = '"' + HBPath + '"' + cred;

            try {
                window.external.ScreenBlocker(false);
                shell.Run(path);
            } catch (e) {
                window.external.ScreenBlocker(false);
                $.prompt("Ooops! Kindly retry.");
            }

        } catch (e) {
            window.external.ScreenBlocker(false);
            $.prompt("Game client not found");

        }

    }

    jQuery.fancybox.close();

};

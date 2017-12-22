
$.launchGame = function(currServiceID, login, terminalPass, isVIP, HBPath)
{

    /*
     * For Habanero no calling of Spyder 
     * Added John Aaron Vida
     * 12/21/2017
     */

    if (currServiceID == 25) {

        //var HabaneroPath = 'C:/HABA/ChromiumLauncher.exe';

        var shell = new ActiveXObject("WScript.Shell");
        var cred = " /u " + login + " /p " + terminalPass + " /v " + isVIP;
        var path = HBPath + cred;
        shell.Run(path);
        jQuery.fancybox.close();
    }
    else {
        try {
            window.external.OpenGameClient(currServiceID, login, terminalPass);
            jQuery.fancybox.close();
        }
        catch (e)
        {
            $.prompt("Game client not found");
            jQuery.fancybox.close();
        }
    }


};
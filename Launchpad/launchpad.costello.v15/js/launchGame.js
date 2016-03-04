
$.launchGame = function(currServiceID,login,terminalPass)
{
   
    try{
        //alert(currServiceID+" : "+login+" : "+terminalPass);
        window.external.OpenGameClient(currServiceID, login,terminalPass);
        jQuery.fancybox.close();
    }
    catch(e)
    {
         $.prompt("Game client not found");
         jQuery.fancybox.close();
    }

};
$(document).ready(function(){
    xhr = null;
    
    showLightbox = function(onSuccess){
        jQuery.fancybox(
        {
            padding:0,
            margin:0,
            opacity:true,
            overlayOpacity:1,
            overlayColor:'#000',
            height:190,
            content:'<img src="'+lpAssetsPath+'/images/loading_big.gif" />',
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
    
    redirectPage = function(url) {
        jQuery.fancybox({
            padding:0,
            margin:0,
            opacity:true,
            overlayOpacity:1,
            overlayColor:'#000',            
            content:'<img src="'+lpAssetsPath+'/images/loading_big.gif" />',
            scrolling:'no',
            modal:true,
            onComplete:function(){
                window.location.href = url;
            }     
        });
    }
    
    displayMessageLightbox = function(message,callBack){
        jQuery.fancybox({
            padding:0,
            margin:0,
            opacity:true,
            overlayOpacity:1,
            overlayColor:'#000',            
            content:message,
            scrolling:'no',
            modal:true,
            onComplete:function(){
                callBack();
            }    
        })
    }
    
    displayRefresh = function() {
        jQuery.fancybox({
            padding:0,
            margin:0,
            opacity:true,
            overlayOpacity:1,
            overlayColor:'#000',            
            content:'Refresh',
            scrolling:'no',
            modal:true
        });
    }
    
    runCasino = function(bot,user,pass,casinopath,formTitle,Shell) {
        jQuery.fancybox('<b>Please wait...</b>',
        {
            padding:0,
            margin:0,
            opacity:true,
            overlayOpacity:1,
            overlayColor:'#000',            
            scrolling:'no',
            modal:true,
            onComplete:function(){
                Shell.Run(bot + ' ' + user +' ' + pass + ' ' + casinopath + ' ' + formTitle);
                jQuery.fancybox.close();
            }
        });        
    }
    
    updateLightbox = function(data){
        jQuery.fancybox({padding:0,margin:0,opacity:true,overlayOpacity:1,overlayColor:'#000',content:data,scrolling:'no'});
    }
    
    updateLightboxModal = function(data) {
        jQuery.fancybox({padding:0,margin:0,opacity:true,overlayOpacity:1,overlayColor:'#000',width:400,content:data,modal:true,scrolling:'no'});
    }
});
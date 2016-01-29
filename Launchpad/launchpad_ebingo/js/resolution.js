

$(document).ready(function(){
    
    windowResize();
     
     
function windowResize(){
     
    if(screen_width >= 800 && screen_width <= 1000)
    {
        
        var width = (512*screen_width)/screen_defaultWidth;
        var tfield = (300*screen_width)/screen_defaultWidth;
        var ipboxH = ((ipboxH*screen_height)/screen_defaultHeight);
 
        $("#content").css({
           "height":"600px"  
        });
        
        
        $("#left").css({
            "width":width+"px"
        });
        
        $("#ubfield").css({
           
            "width":tfield+"px"
        });
        
        $("#pinfield").css({
           
            "width":tfield+"px"
        });
        
        $("#newPinField").css({
           
            "width":tfield+"px"
        });
        
        $("#rnewPinField").css({
           
            "width":tfield+"px"
        });
        
        $("#buttcont").css({
            "width":width+"px" 
        });
        

        $("#Login").css({
            "width":tfield+"px"
        });
        
        $("#casino").css({
            "width":tfield+"px"
        });
        
        $("#cont1").css({
            "width":width+"px"
        });
        
        $("#hdncont1").css({
            "width":width+"px"
        });
                
        $("#instantPlay").css({
            "width":width+"px",
            "height":ipboxH+"px"
        });
        
        $("#lobby2").css({
            "width":width+"px"
        });
              
        contenttmtopW = ((contenttmtopW*screen_height)/screen_defaultHeight);
        ipmtopW = ((ipmtopW*screen_height)/screen_defaultHeight);
        ipfootermtopW = ((ipfootermtopW*screen_height)/screen_defaultHeight);
        lobby2imgW = ((lobby2imgW*screen_width)/screen_defaultWidth);
        lobby2imgH = ((lobby2imgH*screen_height)/screen_defaultHeight);
        lobby2tableH = ((lobby2tableH*screen_height)/screen_defaultHeight);
        
        $("#contentt").css({
            "margin-top":contenttmtopW+"px"
        });
        
        //lobby 2
        $("#modernimgnonact").css({
            "width":lobby2imgW+"px",
            "height":lobby2imgH+"px"
        });
        $("#modernimgact").css({
            "width":lobby2imgW+"px",
            "height":lobby2imgH+"px"
        });
        $("#classicimg").css({
            "width":lobby2imgW+"px",
            "height":lobby2imgH+"px"
        });
        $("#link-platinum-nonactive").css({
            "width":lobby2imgW+"px",
            "height":lobby2imgH+"px",
            "background-size":lobby2imgW+"px "+lobby2imgH+"px"
        });
        $("#link-platinum-active").css({
            "width":lobby2imgW+"px",
            "height":lobby2imgH+"px",
            "background-size":lobby2imgW+"px "+lobby2imgH+"px"
        });
        $("#link-non-platinum").css({
            "width":lobby2imgW+"px",
            "height":lobby2imgH+"px",
            "background-size":lobby2imgW+"px "+lobby2imgH+"px"
        });
        
        $("#lobby2table").css({
            "margin-top":lobby2tableH+"px"
        });
        
        //Instant Play
        $("#mmimg").css({
            "width":lobby2imgW+"px",
            "height":lobby2imgH+"px"
        });
        $("#vvimg").css({
            "width":lobby2imgW+"px",
            "height":lobby2imgH+"px"
        });
        $("#ssimg").css({
            "width":lobby2imgW+"px",
            "height":lobby2imgH+"px"
        });
        $("#link-mm").css({
            "width":lobby2imgW+"px",
            "height":lobby2imgH+"px",
            "background-size":lobby2imgW+"px "+lobby2imgH+"px"
        });
        $("#link-vv").css({
            "width":lobby2imgW+"px",
            "height":lobby2imgH+"px",
            "background-size":lobby2imgW+"px "+lobby2imgH+"px"
        });
        $("#link-ss").css({
            "width":lobby2imgW+"px",
            "height":lobby2imgH+"px",
            "background-size":lobby2imgW+"px "+lobby2imgH+"px"
        });
        $("#virtual-ecity-logo-container").css({
            "margin-top":ipmtopW+"px"
        });
        $("#ipfooter").css({
            "margin-top":ipfootermtopW+"px"
        });
        
        changePinContH =((changePinContH*screen_height)/screen_defaultHeight);//this is for lytbox function, no need to append "px"
        changePinContW = ((changePinContW*screen_width)/screen_defaultWidth);//this is for lytbox function, no need to append "px"
        signupContW = ((signupContW*screen_width)/screen_defaultWidth);//this is for lytbox function, no need to append "px"
        
        confirmButtonsW = ((confirmButtonsW*screen_width)/screen_defaultWidth)+"px";
        confirmButtonsMarginTop =((confirmButtonsMarginTop*screen_height)/screen_defaultHeight)-20+"px";
        
        keyBoardContainerWidth = ((keyBoardContainerWidth*screen_width)/screen_defaultWidth)+"px";
        
        numPadContainerWidth = ((numPadContainerWidth*screen_width)/screen_defaultWidth)+"px"; 
        keyBoardContainerHeight = ((keyBoardContainerHeight*screen_height)/screen_defaultHeight)+"px";
        
        marginThirdRow = ((marginThirdRow*screen_height)/screen_defaultHeight)+"px";
        
        keyBoardW = ((keyBoardW*screen_width)/screen_defaultWidth)+"px"; 
        keyBoardH = ((keyBoardH*screen_height)/screen_defaultHeight)+"px"; 
        
        numPadW = ((numPadW*screen_width)/screen_defaultWidth)+"px"; 
        numPadH = ((numPadH*screen_height)/screen_defaultHeight)+"px"; 
        
        lytBoxW = ((lytBoxW*screen_width)/screen_defaultWidth); 
        lytBoxH = ((lytBoxH*screen_height)/screen_defaultHeight); 
        
        kewIDW = ((kewIDW*screen_width)/screen_defaultWidth)+"px"; 
        kewIDH = ((kewIDH*screen_height)/screen_defaultHeight)+"px"; 
        
        kewContW = ((kewContW*screen_width)/screen_defaultWidth)+"px"; 
        infoButW = ((infoButW*screen_width)/screen_defaultWidth)+"px"; 
        formUIW = ((formUIW*screen_width)/screen_defaultWidth)+"px"; 
        eSafetcbodyW = ((eSafetcbodyW*screen_width)/screen_defaultWidth)+"px"; 
        
        
        
        
        keyBoardContainerMargin2+="px";
        numPadContainerMargin2+="px";
        
        keyBoardContainerMargin1+="px";
        

    }
    else
    {
        
        changePinContH =default_changePinContH;
        changePinContW = default_changePinContW;
        signupContW = default_signupContW;
        
        confirmButtonsW+="px";
        confirmButtonsMarginTop=confirmButtonsMarginTop/2+"px";
        keyBoardContainerMargin2+="px";
        numPadContainerMargin2+="px";
        keyBoardContainerMargin1+="px";
        
        
        
        if(keyBoardContainerWidth<default_keyBoardContainerWidth){
            
            keyBoardW = ((default_keyBoardW*keyBoardContainerWidth)/default_keyBoardContainerWidth)+"px";
            keyBoardH = ((default_keyBoardH*keyBoardContainerHeight)/default_keyBoardContainerHeight)+"px";
            numPadW =((default_numPadW*numPadContainerWidth)/default_numPadContainerWidth)+"px";
            marginThirdRow = ((50*keyBoardContainerHeight)/default_keyBoardContainerHeight)+"px";
        }
        else
        {
            keyBoardW = ((default_keyBoardW*default_keyBoardContainerWidth)/default_keyBoardContainerWidth)+"px";
            keyBoardH = ((default_keyBoardH*default_keyBoardContainerHeight)/default_keyBoardContainerHeight)+"px";
            numPadW =((default_numPadW*default_numPadContainerWidth)/default_numPadContainerWidth)+"px";
            marginThirdRow="35px";
        }
              
        numPadH +="px";
       
        kewIDW = default_kewIDW + "px";
        kewIDH += "px";

        infoButW += "px";
        kewContW = default_kewContW + "px";

        formUIW = default_formUIW + "px";
        eSafetcbodyW = default_eSafetcbodyW + "px";
        
        keyBoardContainerWidth =default_keyBoardContainerWidth+"px";
        keyBoardContainerHeight +="px";
        numPadContainerWidth +="px";

        
        
    }
 }
 
 
});


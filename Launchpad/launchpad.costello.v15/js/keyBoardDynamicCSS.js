

$.keyBoardCss1 = function()
{
    //this is for the function $.populatepads ( conversion keyboard)
    
    $("#infoBut").css('margin-top',keyBoardContainerMargin1);
  
    
    $("button").css("width",keyBoardW);
    $("button").css("height",keyBoardH);

    $("button[value=-2]").attr("disabled",set);
    $("button[value=-4]").attr("disabled",true);  
    $("button[value=-4]").css("border-color","black");
    
    
};

$.keyBoardCss2 = function()
{
    //this is for the function $.populatepads2 ( conversion numpad)
    
    $("#infoBut").css('margin-top',keyBoardContainerMargin1);

    $("button").css("width",numPadW);
    $("button").css("height",numPadH);
    $("button[value=-2]").attr("disabled",set);//ok button
};


$.keyBoardCss3 = function()
{
    //this is for the function $.populatepads3 ( login keyboard)
    
    $('#buttcont').css("margin-top",keyBoardContainerMargin2);
    
    $('#buttcont').css("height",keyBoardContainerHeight);
    $('#buttcont').css("width",keyBoardContainerWidth);

    $("button").css("width",keyBoardW);//variable is set on resolution.js
    $("button").css("height",keyBoardH);
    $(".third").css("margin-top",marginThirdRow);
    
    $("button[value=-2]").attr("disabled",true);
    $("button[value=-4]").attr("disabled",true);
    $("button[value=-4]").css("border-color","black");
    
    
};

$.keyBoardCss4 = function()
{
    //this is for the function $.populatepads4 ( login numpad)
    
    //$('#buttcont').css("margin-top",numPadContainerMargin2);
    $('#buttcont').css("margin-top",187);
    $('#buttcont').css("height","auto");
    $('#buttcont').css("width",numPadContainerWidth);
    
    $("button").css("width",numPadW);
    $("button").css("height",numPadH);
    
    
};

$.keyBoardCss5 = function()
{
    //this is for the function $.populatepads5 (change pin keyboard)
    if(screen_width >= 800 && screen_width <= 1000) {
        $('#buttcont2').css("margin-top","-127px");
    } else {
        $('#buttcont2').css("margin-top","-135px");
    }

    $('#buttcont2').css("height",keyBoardContainerHeight);
    $('#buttcont2').css("width",keyBoardContainerWidth);
    
    $("button").css("width",keyBoardW);
    $("button").css("height",keyBoardH);
    $(".third").css("margin-top",marginThirdRow);
    
    $("button[value=-2]").attr("disabled",true);
    $("button[value=-4]").attr("disabled",true);      
    $("button[value=-4]").css("padding-top",'2px'); 
    $("button[value=-4]").css("border-color","black");
    
};

$.keyBoardCss6 = function()
{
    //this is for the function $.populatepads6 ( change pin keyboard - numpad)
    if(screen_width >= 800 && screen_width <= 1000) {
        $('#buttcont2').css("margin-top","-47px");
    } else {
        $('#buttcont2').css("margin-top","-54px");
    }
    $('#buttcont2').css("height","auto");
    $('#buttcont2').css("width",numPadContainerWidth);

    $("button").css("width",numPadW);
    $("button").css("height",numPadH);
    
};
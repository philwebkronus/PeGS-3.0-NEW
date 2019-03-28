
$.center = function () {
    var top, left;

    top = Math.max($(window).height() - $("div#prompt").outerHeight(), 0) / 2;
    left = Math.max($(window).width() - $("div#prompt").outerWidth(), 0) / 2;

    $("div#prompt").css({
        top:top + $(window).scrollTop(), 
        left:left + $(window).scrollLeft()
    });
};

$.prompt = function(msg)
{

    var contetnt = "<a class='boxclose'></a>";
    
    contetnt+=msg;
    $("#prompt").html(contetnt);
    
    $("div#prompt").css({
        width: msg.width || 'auto', 
        height: msg.height || 'auto'
    });
    
    $.center();

//    $('#blackwrapper').css('filter','alpha(opacity=70)');   
    $("div#blackwrapper").show();
    $("div#prompt").show();

};

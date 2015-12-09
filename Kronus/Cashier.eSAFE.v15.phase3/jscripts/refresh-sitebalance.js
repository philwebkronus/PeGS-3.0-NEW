$(document).ready(function(){
    var ajax_hander = null;
    
    $('#head-bcf').click(function(){
        showLightbox();
        var url = $(this).attr('url');
        ajax_hander = $.ajax({
            url : url,
            success : function(data) {
                $('#head-bcf > div').children('span').html(data);
                hideLightbox();
                ajax_hander = null;
            },
            error : function(e) {
                $('#innerLightbox').html('<h1 style="color:red">'+e.responseText+'</h1>');
                setTimeout(function(){hideLightbox()}, 1000);
            }
        })
    });
    
    refresher = function() {
        var url = $('#head-bcf').attr('url');
        if(ajax_hander == null) {
            ajax_hander = $.ajax({
                url : url,
                success : function(data) {
                    $('#head-bcf > div').children('span').html(data);
                    ajax_hander = null;
                }
            });
        }
    }
    
//    setInterval('refresher()',5000);
});
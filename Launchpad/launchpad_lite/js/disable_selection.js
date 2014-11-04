(function($){

$.fn.ctrl = function(key, callback) {
    if(typeof key != 'object') key = [key];
    callback = callback || function(){ return false; }
    return $(this).keydown(function(e) {
        var ret = true;
        $.each(key,function(i,k){
            if(e.keyCode == k.toUpperCase().charCodeAt(0) && e.ctrlKey) {
                ret = callback(e);
            }
        });
        return ret;
    });
};


$.fn.disableSelection = function() {
    $(window).ctrl(['a','s','c']);
    return this.each(function() {           
        $(this).attr('unselectable', 'on')
               .css({'-moz-user-select':'none',
                    '-o-user-select':'none',
                    '-khtml-user-select':'none',
                    '-webkit-user-select':'none',
                    '-ms-user-select':'none',
                    'user-select':'none'})
               .each(function() {
                    $(this).attr('unselectable','on')
                    .bind('selectstart',function(){ return false; });
               });
    });
};

})(jQuery);
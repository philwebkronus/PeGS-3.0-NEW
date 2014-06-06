$(document).ready(function(){
    // this is use in monitoring only
    setInterval(function(){
        $('#refresh_getbal').trigger('click');
    }, 60000);
})
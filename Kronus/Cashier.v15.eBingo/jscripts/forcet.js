$(document).ready(function(){  
   
    
    $('#ForceTFormModel_transaction_type').change(function(){
        if($(this).val() == '') {
            $('#containerForceT').html('');
            return false;
        }
        var url = $(this).val();
        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                success : function(data) {
                    $('#containerForceT').html(data);
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
    });
});



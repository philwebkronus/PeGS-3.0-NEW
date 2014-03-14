<script type="text/javascript">
$(document).ready(function(){
    $('#refresh_getbal').click(function(){
        showLightbox(function(){
            updateLightbox('', 'Please wait. This may take a while',function(){
                var url = '<?php echo Mirage::app()->createUrl('refresh') ?>';
                $.ajax({
                    url : url,
                    type : 'post',
                    success : function(data) {
                        if(data != 'Success') {
                            alert('Oops! Something went wrong');
                        }
                        location.reload(true);
                    },
                    error : function(e) {
                        displayError(e);
                    }
                });
            });
            
        });
        return false;
    })
});
</script>
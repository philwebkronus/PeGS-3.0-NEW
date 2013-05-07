/**
 * Lightbox
 * @author : Bryan Salazar
 */
$(document).ready(function(){
    xhr = null;
    
    showLightbox = function(onSuccess){
        jQuery.fancybox(
        {
            padding:0,
            content:'<img src="'+IMAGE_URL+'loading_big.gif" />',
            scrolling:'no',
            modal:true,
            onComplete:function(){
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
            content:'<img src="'+IMAGE_URL+'loading_big.gif" />',
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
            content:message,
            scrolling:'no',
            modal:true,
            onComplete:function(){
                callBack();
            }    
        })
    }
    
    displayPageLoading = function() {
        jQuery.fancybox({
            padding:0,
            content:'<img src="'+IMAGE_URL+'loading_big.gif" />',
            scrolling:'no',
            modal:true
        });
    }
    
    updateLightbox = function(data,onSuccess){
        jQuery.fancybox({padding:0,content:data,scrolling:'no',modal:true,onComplete:function(){onSuccess();}});
    }
    
    updateLightboxModal = function(data) {
        jQuery.fancybox({padding:0,width:400,content:data,modal:true,scrolling:'no'});
    }
    
    $('.btnCancel').live('click',function(){
        jQuery.fancybox.close();
    })
    
    $('.btnUpdate').live('click',function(){
        if($('.error').length != 0) {
            return false;
        }
        var dataForm = '';
        var type = 'get';
        
        if($(this).parents('form').attr('action')) {
            url = $(this).parents('form').attr('action');
            dataForm = $(this).parents('form').serialize();
            type='post';
            
            if($(this).parents('form').yiiactiveform.isValid == false) {
                $(this).parents('form').submit();
                return false;
            }
            
        } else {
            $(this).parents('form').yiiactiveform.isValid = false;
            var url = $(this).attr('href');
        }
        
        showLightbox(function(){
            xhr = $.ajax({
                type : type,
                url : url,
                data : dataForm,
                success : function(data){
                    try {
                        var json = jQuery.parseJSON(data);
                        if(json.status == 'ok') {
                            $("#grid1").trigger("reloadGrid")
                            // get first id of input text, textarea or select
                            updateLightbox(json.html,function(){});
                        } else {
                            updateLightbox('Failed to update',function(){
                                jQuery.fancybox.close();
                            });
                        }
                    }catch(e) {
                        // get first id of input text, textarea or select
                        firstElem = $(data).find('input[type=text],textarea,select').attr('id');

                        updateLightbox(data,function(){
                            $('#'+firstElem).focus();
                        });
                    }
                },
                error : function(e){
                    updateLightbox(e.responseText,function(){
                        location.reload();
                    });
                }
            });
        });
        return false;
    })
    
    $('.btnView').live('click',function(){
        var url = $(this).attr('href');
        showLightbox(function(){
            xhr = $.ajax({
                url : url,
                type:'get',
                modal:true,
                success : function(data){
                    updateLightbox(data,function(){});
                },
                error : function(e){
                    updateLightbox(e.responseText,function(){
                        location.reload();
                    });
                }
            });
        });
        return false;
    });
    
    $('.btnDelete').live('click',function(){
        if(!confirm('Are you sure you want to delete this?')) 
            return false;
        
        var url = $(this).attr('href');
        showLightbox(function(){
            xhr = $.ajax({
                url:url,
                success:function(data){
                    if(data == 'ok') {
                        $("#grid1").trigger("reloadGrid");
                        updateLightbox('<h1>Successfully deleted</h1>',function(){
                            jQuery.fancybox.close();
                        });
                    } else {
                        updateLightbox('Failed to delete',function(){
                            jQuery.fancybox.close();
                        });
                    }
                },
                error:function(e) {
                    updateLightbox(e.responseText,function(){
                        location.reload();
                    });
                }
            });
        });
    });
    
    $('.btnAdd').live('click',function(){
        if($('.error').length != 0) {
            return false;
        }
        var dataForm = '';
        var type = 'get';
        
        if($(this).parents('form').attr('action')) {
            url = $(this).parents('form').attr('action');
            dataForm = $(this).parents('form').serialize();
            type='post';
            
            if($(this).parents('form').yiiactiveform.isValid == false) {
                $(this).parents('form').submit();
                return false;
            }
        } else {
            $(this).parents('form').yiiactiveform.isValid = false;
            var url = $(this).attr('href');
        }
        showLightbox(function(){
            xhr = $.ajax({
                type : type,
                url : url,
                data : dataForm,
                modal:true,
                success : function(data){
                    try {
                        var json = jQuery.parseJSON(data);
                        if(json.status == 'ok') {
                            $("#grid1").trigger("reloadGrid");
                            // get first id of input text, textarea or select
                            updateLightbox(json.html,function(){});
                        } else {
                            updateLightbox('Failed to save',function(){
                                jQuery.fancybox.close();
                            });
                        }
                    }catch(e) {
                        // get first id of input text, textarea or select
                        firstElem = $(data).find('input[type=text],textarea,select').attr('id');

                        updateLightbox(data,function(){
                            $('#'+firstElem).focus();
                        });
                    }
                },
                error : function(e){
                    updateLightbox(e.responseText,function(){
                        location.reload();
                    });
                }
            });
        });
        return false;
    });
    
    
});
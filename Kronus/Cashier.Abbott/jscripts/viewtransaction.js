$(document).ready(function(){  
    $('#txtDate').change(function(){
        var url = $('#transhistorypage').attr('url');
        var limit = $('#transhistorypage option:selected').val();
        var d = $('#txtDate').val();
        var data = 'limit='+limit+'&date='+d;
        showLightbox(function(){
            viewTrans(url,data);
        });
    });
    
    $('#tbodyviewtrans > tr').live('mouseover',function(){
        $(this).attr('id','ishover');
    });
    $('#tbodyviewtrans > tr').live('mouseout',function(){
        $(this).removeAttr('id');
    });
    $('#tbodyviewtrans > tr').live('click',function(){
        if($(this).attr('class') == 'isClick') {
            $(this).removeClass('isClick');
            return false;
        }  
        $('.isClick').removeClass('isClick');
        $(this).addClass('isClick');
    });
    
    viewTrans = function(url,data) {
        $.ajax({
            url:url,
            data:data,
            success:function(data){
                try{
                    data = jQuery.parseJSON(data);
                    var html = '';
                    for(i=0;i < data.trans_details.length;i++) {
                        html+='<tr>';
                        var datetime = data.trans_details[i].DateCreated;
                        datetime = datetime.split('.');
                        html+='<td>'+formatDateAMPM(datetime[0])+'</td>';
                        var terminal_name = data.trans_details[i].TerminalCode;
                        terminal_name = terminal_name.replace(data.site_code,'');
                        html+='<td>'+terminal_name+'</td>';
                        html+='<td>'+getTransType(data.trans_details[i].TransactionType)+'</td>';
                        html+='<td class="right">'+toMoney(data.trans_details[i].Amount)+'</td>';
                        html+='</tr>';
                    }
                    $('#tbodyviewtrans').html(html);
                }catch(e) {
                    alert('Oops! Something went wrong. Please try again');
                }
                hideLightbox();
            },
            error:function(e) {
                alert('Oops! Something went wrong. Please try again');
                hideLightbox();
            }
        });
    }
    
    getTransType = function(type) {
        var trans_type = '';
        switch(type) {
            case 'D':
                trans_type = 'DEPOSIT';
                break;
            case 'R':
                trans_type = 'RELOAD';
                break;
            case 'W':
                trans_type = 'WITHDRAW';
                break;
        }
        return trans_type;
    }
    
    showLightbox(function(){
        var url = $('#transhistorypage').attr('url');
        var limit = $('#transhistorypage').val();
        var d = $('#txtDate').val();
        var data = 'limit='+limit+'&date='+d;
        viewTrans(url,data);
    });
    
    $('#transhistorypage').live('change',function(){
        var url = $(this).attr('url');
        var limit = $('#transhistorypage option:selected').val();
        var d = $('#txtDate').val();
        var data = 'limit='+limit+'&date='+d;
        showLightbox(function(){
            viewTrans(url,data);
        });
    });
});
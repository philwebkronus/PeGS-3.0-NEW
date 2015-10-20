$(document).ready(function(){  
   $('#ViewTransactionFormModel_history_type').change(function(){
        if($(this).val() == '') {
            $('#containerTransactionHistory').html('');
            return false;
        }
        var url = $(this).val();
        showLightbox(function(){
            $.ajax({
                url : url,
                type : 'post',
                success : function(data) {
                    $('#containerTransactionHistory').html(data);
                    hideLightbox();
                },
                error : function(e) {
                    displayError(e);
                }
            });
        });
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
};

function parseAndDisplayData(data){
    if(data!==''){
        data = jQuery.parseJSON(data);
        displayData(data);
    }
}

function displayData(data){
    var html = '';
    for(i=0;i < data.trans_details.length;i++) {
        html+='<tr>';
        var datetime = data.trans_details[i].DateCreated;
        datetime = datetime.split('.');
        html+='<td>'+formatDateAMPM(datetime[0])+'</td>';
        var terminal_name = data.trans_details[i].TerminalCode;

        terminal_name = terminal_name.replace(data.site_code,'');
        if(data.trans_details[i].TerminalType == 1){
            terminal_name = 'G'+terminal_name;
        }

        html+='<td>'+terminal_name+'</td>';
        html+='<td>'+getTransType(data.trans_details[i].TransactionType)+'</td>';
        html+='<td class="right">'+toMoney(data.trans_details[i].Amount)+'</td>';
        html+='</tr>';
    }
    $('#tbodyviewtrans').html(html);
}

function viewTrans(url,data) {
    $.ajax({
        url:url,
        data:data,
        success:function(data){
            try{
               parseAndDisplayData(data);
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

function getTransactionHistory(){
    var url = $('#transhistorypage').attr('url');
    var limit = $('#transhistorypage option:selected').val();
    var d = $('#txtDate').val();
    var data = 'limit='+limit+'&date='+d;
    showLightbox(function(){
        viewTrans(url,data);
    });
}



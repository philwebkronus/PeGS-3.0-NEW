$(document).ready(function(){
    playingDuration = function(date_time,server_date) {
        date1 = date_time;
        date2 = server_date;

        diff = Math.abs(strtotime(date2) - strtotime(date1));
        days = Math.floor(diff / (60*60*24));
        hours = Math.floor((diff - days * 60*60*24) / (60 * 60));
        mins = Math.floor((diff - days * 60*60*24  - hours * 60 * 60) / 60);
        
        if(days < 2) {
            days = days + ' day, ';
        } else {
            days = days + ' days, ';
        }

        if(hours < 2) {
            hours = hours + ' hr and ';
        } else {
            hours = hours + ' hrs and ';
        }
        
        return days + hours + mins + ' min';     
    }    
    
    /**
     * 
     */
    getTimePlaying = function(minutes) {
        hours = 0;
        if(minutes > 60) {
            hours = Math.floor(minutes/60);
            minutes = minutes - (hours*60);
        } else {
            hours = "0";
        }
        if(minutes<10) {minutes = '0' +minutes;}
        return hours+':'+minutes;
    }
    
    /******* this should be the same logic in terminal_overview_tpl.php ******************/
    $('#temrinalmonitoring-pager > input').click(function(){
        showLightbox();
        var url = $(this).attr('url');
        $.ajax({
            url : url,
            success : function(data){
                var data = jQuery.parseJSON(data);
                var cntr = 0;
                var html = '<tr>';
                for(var i = 0; i < data.terminals.length; i++) {
                    if(cntr != 0 && cntr % 10 == 0) {
                        html += '</tr><tr>';
                    }
                    if(cntr % 2 == 0) {
                        var checkbox = '';var button = '';var casino = '';var last_balance = '';var asof = '';
                        var code = data.terminals[i]['tc']; // always regular terminal code
                        var nonvipid = data.terminals[i]['TerminalID']; // terminal id non-vip
                        if(data.terminals[i + 1] != undefined) {
                            var vipid = data.terminals[i + 1]['TerminalID']; // terminal id vip
                            
                            var casinovip = data.services[vipid];casinononvip = data.services[nonvipid];  
                        }
                        var tcode = data.terminals[i]['tc']; // set default terminal code if not vip
                        // check if disabled GRAY   
                        if(data.terminals[i + 1] == undefined) {
                            cls='disable';
                            vipid = '';
                            casinovip = '';
                        // check if has last balance RED  
                        } else if(data.terminals[i + 1]['lastbalance'] != null || data.terminals[i]['lastbalance'] != null && (data.terminals[i + 1]['Status'] == 1 && data.terminals[i]['Status'] == 1)) {
//                            casino = getTimePlaying(data.terminals[i]['minutes'])+' - '+data.refservices[data.terminals[i]['ServiceID']];
                            casino = playingDuration(data.terminals[i]['DateStarted'],data.server_date)+' - '+((data.refservices[data.terminals[i]['usedServiceID']] != undefined)?data.refservices[data.terminals[i]['usedServiceID']]:'');
                            var cid = data.terminals[i]['ServiceID'];
                            var tid = data.terminals[i]['TerminalID'];
                            last_balance = 'PhP '+data.terminals[i]['lastbalance'];
                            asof = ' as of '+data.terminals[i]['ltd'];
                            // check if vip
                            if(data.terminals[i + 1]['lastbalance'] != null) {
                                tcode = data.terminals[i + 1]['tc'];
                                cid = data.terminals[i + 1]['ServiceID'];
                                tid = data.terminals[i + 1]['TerminalID'];
//                                casino = getTimePlaying(data.terminals[i + 1]['minutes'])+' - '+data.refservices[data.terminals[i + 1]['ServiceID']];
                                casino = playingDuration(data.terminals[i + 1]['DateStarted'],data.server_date)+' - '+((data.refservices[data.terminals[i + 1]['usedServiceID']] != undefined)?data.refservices[data.terminals[i + 1]['usedServiceID']]:'');
                                last_balance = 'PhP '+data.terminals[i + 1]['lastbalance'];
                                asof = ' as of '+data.terminals[i + 1]['ltd'];
                            }
                            cls = 'active';
                            button='<input tid="'+tid+'" cid="'+cid+'" type="button" value="Reload" class="reload"/><input tid="'+tid+'" cid="'+cid+'" type="button" value="Redeem" class="redeem"/>';
                            
                        // check if disabled GRAY    
                        } else if(data.terminals[i + 1]['Status'] == 2 || data.terminals[i]['Status'] == 2 || data.terminals[i + 1]['Status'] == 0 || data.terminals[i]['Status'] == 0 || data.terminals[i + 1]['Status'] == 3 || data.terminals[i]['Status'] == 3) {
                            cls='disable';
                        // not active GREEN    
                        } else {
                            checkbox = '<p> vip <input class="togglevip" type="checkbox" /></p>';
                            button = '<input class="startsession" type="button" value="START SESSION"/>';
                            cls = 'notactive';
                            casino = casinononvip;
                        }
                        html+='<td id="' + code + '" vipid="' + vipid + '" nonvipid="' + nonvipid + '" class="' + cls + '">';
                        html += '<div class="box tcode"><h1>'+ tcode + '</h1></div>';
                        html += '<div class="box chk"></div>';
                        html += '<div class="clear"></div>';
                        html += '<p>' + casino + '</p>';
                        html += '<p class="lastbalance">'+last_balance+'<span class="asof">'+asof+'</span></p>';
                        //html += button;
                        html += '</td>';
                    }
                    cntr++;
                }
                html += '</tr>';
                $('#tblterminalmonitoring > tbody').html(html);
                $('#temrinalmonitoring-pager > .current-page').removeClass('current-page');
                $('#page' + data.current_page).addClass('current-page');
                hideLightbox();
            },
            error : function(e) {
                displayError(e);
            }
        })
    });
});
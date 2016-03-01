$(document).ready(function(){
    ActionType = {Deposit:false}    

    /*************** check if its partner is already started **************/
    checkPartner = function(opt_lbl) {
        var optlbl = opt_lbl;
        var pos = strpos(optlbl,'VIP');
        var hasStarted = true;
        // check if has vip
        if(pos) {
            var notvip = optlbl.replace('VIP','');
            $('#StartSessionFormModel_terminal_id').children('option').each(function(k,v){
                if(v.innerHTML == notvip) {
                    hasStarted = false;
                }
            });
            if(hasStarted) {
                alert(notvip + ' already startsession');
                return false; 
            }
        } else {
            var vip = optlbl + 'VIP';
            $('#StartSessionFormModel_terminal_id').children('option').each(function(k,v){
                
                if(v.innerHTML == vip) {
                    
                    hasStarted = false;
                }
            });
            if(hasStarted) {
                alert(vip + ' already startsession');
                return false; 
            }
        }
        return true;
    };
    
    checkPartner2 = function(opt_lbl) {
        var optlbl = opt_lbl;
        var pos = strpos(optlbl,'VIP');
        var hasStarted = true;
        // check if has vip
        if(pos) {
            var notvip = optlbl.replace('VIP','');
            $('#UnlockTerminalFormModel_terminal_id').children('option').each(function(k,v){
                if(v.innerHTML == notvip) {
                    hasStarted = false;
                }
            });
            if(hasStarted) {
                alert(notvip + ' already startsession');
                return false; 
            }
        } else {
            var vip = optlbl + 'VIP';
            $('#UnlockTerminalFormModel_terminal_id').children('option').each(function(k,v){
                if(v.innerHTML == vip) {
                    hasStarted = false;
                }
            });
            if(hasStarted) {
                alert(vip + ' already startsession');
                return false; 
            }
        }
        return true;
    };
});
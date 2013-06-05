jQuery(document).ready(function(){
    // input with class auto will be money formatted on keyup and on blur
    $('input.auto').autoNumeric();
    
    /********************* menu description ***********************************/
    var desc = $('#main-menu > a.active').attr('desc');
    $('#menu-description').html(desc); 
    
    $('#main-menu > a').mouseover(function() {
       var desc = $(this).attr('desc'); 
       $('#menu-description').html(desc); 
    });
    
    $('#main-menu > a').mouseout(function() {
        $('#menu-description').html('');
        var desc = $('#main-menu > a.active').attr('desc');
        $('#menu-description').html(desc); 
    });
    // end of menu description
    
    $('.auto').live('keypress',function(e){
        code = (e.keyCode)?e.keyCode:e.which;
        if(code.toString() == '13') {
            return false;
        }
    });
});

/**
 * Description: remove milliseconds
 * Sample param: 2011-11-16 17:05:41.167051 or 17:05:41.167051
 */
function removeMillisec(date_time) {
    var dt = date_time.split('.');
    return dt[0];
}

/**
 * Description: convert military time to 12-hour AM PM
 * Sample param: 2011-08-01 13:20:30 convert to 2011-08-01 01:20:30 PM
 */
function formatDateAMPM(date) {
    d = date.split(' ');
    time = d[1];
    t = time.split(':');
    h = t[0];
    n = 'AM';
    if(h > 12) {
        h = h - 12;
        if(h.toString().length < 2) {
            h = '0' + h;
        }
        n = 'PM';
    } else if(h == 12) {
        n = 'PM';
    }
    return d[0] + ' ' + h + ':' + t[1] + ':' + t[2] + ' ' + n;
}

/**
 * Description: replicate the strpos of php
 * Return: int position of needle
 */
function strpos(haystack, needle, offset) {
    var i = (haystack + '').indexOf(needle, (offset || 0));
    return i === -1 ? false : i;
}

/**
 * Description: convert money formatted to int
 * Return: int 
 */
function toInt(val) {
    return val.replace(/\,/g,'');
}

/**
 * Description: convert int to money format
 * Requirements: accounting.min.js
 * Return: string money formatted value
 */
function toMoney(val,noprefix) {
    var pre = 'PhP ';
    if(noprefix != undefined) {
        pre = '';
    }
    
//    return accounting.formatMoney(val, pre, 2, ".", ",");
    return accounting.formatMoney(val, pre, 2, ",", ".");
}

//function toMoney2(val) {
//    return val.formatMoney(2, '.', ',');
//}
//
//Number.prototype.formatMoney = function(c, d, t){
//var n = this, c = isNaN(c = Math.abs(c)) ? 2 : c, d = d == undefined ? "," : d, t = t == undefined ? "." : t, s = n < 0 ? "-" : "", i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
//   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
// };


//function formatWithComma(number) {
//    var formattedNumberString = (number%1000).toString();
//    var x = parseInt(number/1000);
//    while(x > 0) {
//            formattedNumberString = (x%1000) + '','' + formattedNumberString;
//            x = parseInt(x/1000);
//    }
//    return formattedNumberString;
//}


/*@Param number
*Description: Format the number with comma. ex: 1,000
*/
function addCommas(number)
{
	number += '';
	var x = number.split('.');
	var x1 = x[0];
	var x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1;
}


/*@Param dateverified
*Description: Get the difference between the passed date parameter and the date today
*/
function getDateDiff(dateverified,servertime){
        var date2 = new Date(servertime);
        var oneDay = 24*60*60*1000;     // hours*minutes*seconds*milliseconds

        //check if the parameter value
        if(dateverified == null || dateverified == undefined || dateverified == ""){
            return "false";
        } else {
            var date1 = new Date(dateverified);
            var diffDays = Math.abs((date1.getTime() - date2.getTime())/(oneDay));
            //var diffDays = Math.abs((date1.getTime() - date2)/(oneDay));

            //check if the date difference between the date verified and the date now is greater than/equal to one day.
            if(diffDays >= 1) {
                     return "true";
             } else {
                 var actdate = date1.getTime() + oneDay;

                 //formatting date return
                 var activationdate = new Date(actdate);
                var a_p = "";
                var dateact = "";
                var act_mon = activationdate.getMonth() + 1;
                var act_day = activationdate.getDate();
                var act_year = activationdate.getFullYear().toString(10).substring(2, 4);
                var act_hour = activationdate.getHours();
                var act_min = activationdate.getMinutes();

                //condition for formatting month
                act_mon = act_mon < 10 ? act_mon = "0"+act_mon:act_mon=act_mon;
                
                //condition for formatting day
                act_day = act_day < 10 ? act_day = "0"+act_day:act_day=act_day;

                //conditions for formatting time
                a_p = act_hour < 12 ? "AM":"PM";
                act_hour = act_hour == 0 ? act_hour = 12:act_hour = act_hour;
                act_hour = act_hour > 12 ? act_hour = act_hour - 12:act_hour = act_hour;
                act_hour = act_hour < 10 ? act_hour = "0"+act_hour:act_hour = act_hour;
                act_min = act_min < 10 ? act_min = "0"+act_min:act_min=act_min;

                dateact = act_mon + "-" + act_day + "-" + act_year + " " + act_hour + ":" + act_min + a_p;
                 return dateact;
             }
        }

}


/*@Param cardstatus id
*Description: Return the corresponding status value of a give cardstatus id.
*/

function getStatusValue(cardstatus){
        var StatusValue = '';
        switch(cardstatus){
            case 0:
                StatusValue = 'Inactive';
                break;
            case 1:
                StatusValue = 'Active';
                break;
            case 2:
                StatusValue = 'Deactivated';
                break;
            case 3:
                StatusValue = 'Old';
                break;
            case 4:
                StatusValue = 'Old Migrated';
                break;
            case 5:
                StatusValue = 'Active Temporary';
                break;
            case 6:
                StatusValue = 'Inactive Temporary';
                break;
            case 7:
                StatusValue = 'New Migrated';
                break;
            case 8:
                StatusValue = 'Migrated Temporary';
                break;
            case 9:
                StatusValue = 'Banned Card';
                break;
            default:
                StatusValue = "Invalid";
                break;
        }
        return StatusValue;
}

/*@Param cardtype id
*Description: Return the corresponding status value of a give CardTypeStatus id.
*/

function getCardType(cardtype){
        var CardTypeStatus = '';
        switch(cardtype){
            case "1":
                CardTypeStatus = 'Gold';
                break;
            case "2":
                CardTypeStatus = 'Green';
                break;
            case "3":
                CardTypeStatus = 'Temporary';
                break;
        }
        return CardTypeStatus;
}
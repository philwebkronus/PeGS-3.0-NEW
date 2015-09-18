$(document).ready(function(){
    validateDateTopup = function(){
        var date = new Date();
        var cutoff;
        var curr_date = date.getDate();
        var curr_month = date.getMonth();
        curr_month = curr_month + 1;
        var curr_year = date.getFullYear();
        if(curr_month < 10)
        {
            curr_month = "0" + curr_month;
            if(curr_date < 10)
                {
                    curr_date = "0" + curr_date;
                    cutoff = curr_year + '-'+ curr_month + '-'+ ("0" + (parseInt(curr_date) + 1));
                }
            else
                {
                    cutoff = curr_year + '-'+ curr_month + '-'+ (parseInt(curr_date) + 1);
                }
        }
        var datenow = curr_year + '-'+ curr_month + '-'+ curr_date;
        
        
        if(datenow < $('#startdate').val()) {
            alert("Queried date must not be greater than today");
            return false;
        } else if(datenow < $('#enddate').val()) {
            alert("Queried date must not be greater than today");
            return false;
        } else if($('#enddate').val() < $('#startdate').val()) {
            alert("Start date must not greater than end date");
            return false;
        }
        return true;
    }
});


function date_time(id)
{
        date = new Date;
        year = date.getFullYear();
        month = date.getMonth();
        
        months = new Array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
        d = date.getDate();
        
        h = date.getTime();
        
       var mydate=new Date()
       var hours=mydate.getHours();

            var dn="AM";

            if (hours>=12)
            {
            dn="PM";
            }
            if (hours>12){
            hours=hours-12
            }
            if (hours==0)
            {
            hours=12;
            }
       
        if(h<10)
        {
                h = "0"+h;
        }
        m = date.getMinutes();
        if(m<10)
        {
                m = "0"+m;
        }
        
        
        result = ''+months[month]+'-'+d+'-'+year+' '+hours+':'+m+' '+dn;
        document.getElementById(id).innerHTML = result;
        setTimeout('date_time("'+id+'");','1000');
        return true;
}
  

/* 
 * Author: Roger Sanchez
 * Date Created: 2012-08-23
 * Company: Philweb
 */

function isDateRangeValid(cnt, period)
{
    var validdaterange = true;
    var maxdays = cnt+1;
    var validationduration = 10000;
    var validationcolor = '#FF6666';
    
    fromdate = parsedate($("#txtWhatDate").val());
    todate = parsedate($("#txtToDate").val());
    if (period == 'days')
    {
        mindate = parsedate($("#txtToDate").val());
        mindate.setDate(mindate.getDate() -cnt);
    }
    if (period == 'months')
    {
        mindate = parsedate($("#txtToDate").val());
        mindate.setMonth(mindate.getMonth() -cnt);
    }
    
    if ((fromdate < mindate) || (fromdate > todate))
    {
        if(fromdate > todate)
        {
            messagebox('Incorrect date range', 'Start date must be before or the same as end date');
        }
        else
        {
            messagebox('Incorrect date range', 'Please select a date range within ' + maxdays + ' ' + period);
        }
        
        $("#txtWhatDate").effect("highlight", {
            color: validationcolor
        }, validationduration);
        $("#txtToDate").effect("highlight", {
            color: validationcolor
        }, validationduration);
        validdaterange = false;
    }
    return validdaterange;
}

function isGraphOptionsComplete(period)
{
    var hasreportdays = false;
    var hasdimensions = false;
    var validationduration = 10000;
    var validationcolor = '#FF6666';
    
    if (period == 'days')
    {
        period = 'day';
    }
    if (period == 'months')
    {
        period = 'month';
    }
        
    $("input[name='chkReportDays[]']:checked").each(function ()
    {
        hasreportdays = true;
    });
        
    $("input[name='chkDimensions[]']:checked").each(function ()
    {
        hasdimensions = true;
    });
        
    if (!hasdimensions)
    {
        messagebox('Incomplete Graph Options', 'Please select a graph data')
        $("#dimensioncontainer").effect("highlight", {
            color: validationcolor
        }, validationduration);
    }
        
    if (!hasreportdays)
    {
        messagebox('Incomplete Graph Options', 'Please select a ' + period)
        $("#daycontainer").effect("highlight", {
            color: validationcolor
        }, validationduration);
    }
   
        
    return hasreportdays && hasdimensions;
                                        
}


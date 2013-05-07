/* 
 * Author: Roger Sanchez
 * Date Created: 2012-08-23
 * Company: Philweb
 */


function messagebox(boxtitle, message)
{
    if ($("#dashboard-messagebox").dialog( "isOpen" ) !== true)
    {
        $("#dialog:ui-dialog").dialog("destroy");
        $("#dashboard-messagebox-string").html(message);
        $("#dashboard-messagebox").dialog({
            modal: true,
            buttons: {
                Ok: function() {
                    $(this).dialog("close");
                }
            },
            title: boxtitle
        });
    }
}

function parsedate(datestring)
{
    yr = datestring.substring(0,4);
    mo = datestring.substring(5,7);
    if (datestring.length == 10)
    {
        dy = datestring.substring(8,10);
    }
    else
    {
        dy = '01';
    }
    return new Date(yr, mo - 1, dy);
}

function Highlight(elementid)
{
    var validationduration = 10000;
    var validationcolor = '#FF6666';
    $(elementid).effect("highlight", {
        color: validationcolor
    }, validationduration);
}


$(document).ready(function(){
    jQuery(':text').live("cut copy paste",function(e) {
        e.preventDefault();
    });

    jQuery(':password').live("cut copy paste",function(e) {
        e.preventDefault();
    });
});

function numberandletter(evt)
{
      var charCode = (evt.which) ? evt.which : evt.keyCode;
      if (charCode == 96 || charCode == 60 || charCode == 62 || charCode == 44 || charCode == 59 || charCode == 34)
      {
          return false;
      }
      else if (charCode > 31 && (charCode < 33 || charCode > 38) && (charCode < 42 || charCode > 63) && (charCode < 95 || charCode > 122)){
          return false;
      }
      else if(charCode == 9)
      {
          return true;
      }
      else
          return true;
}  

//validates input ; accepts number,small letter and special characters such as _%*+-!$=#.:?/& but not space
function numberandletter2(evt)
{
    var charCode = (evt.which) ? evt.which : evt.keyCode;    
    if((charCode > 47 && charCode < 58) || (charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123) 
        || charCode == 8 || charCode == 9 || charCode == 64 || charCode == 46 || charCode == 95 || charCode == 45)
        {
          return true;
        }
    else{
          return false;
    }
}


function numberonly(evt)
{
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57))
          return false;
    else if(charCode == 9)
      return true;
    else
      return true;
}

function echeck(str) {
    var at="@";
    var dot=".";
    var lat=str.indexOf(at);
    var lstr=str.length;
    var ldot=str.indexOf(dot);
    if (str.indexOf(at)==-1) {
        alert("Invalid E-mail Address");
        return false;
    }
    if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr) {
        alert("Invalid E-mail Address");
        return false;
    }
    if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr) {
        alert("Invalid E-mail Address");
        return false;
    }
    if (str.indexOf(at,(lat+1))!=-1) {
        alert("Invalid E-mail Address");
        return false;
    }
    if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot) {
        alert("Invalid E-mail Address");
        return false;
    }
    if (str.indexOf(dot,(lat+2))==-1) {
        alert("Invalid E-mail Address");
        return false;
    }
    if (str.indexOf(" ")!=-1) {
        alert("Invalid E-mail Address");
        return false;
    }
    else
         return true;
}
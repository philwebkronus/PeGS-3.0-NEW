function AlphaOnlyWithSpace(event)
{
    var char = (String.fromCharCode(event.which));
    if (char == "%" || char == "'")
    {
        return false;
    }
    else
    {
        var charCode = (event.which) ? event.which : event.keyCode;
        if ((charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123) || charCode == 32
             || charCode == 8 || charCode == 9)
        {
            return true;
        }
        else if (charCode == 37 || charCode == 39) //left and right arrow keys
             return true;
        else
        {
            return false;
        }
    }
}
function AlphaNumericOnlyWithSpace(event)
{
    var char = (String.fromCharCode(event.which));
    if (char == "%" || char == "'")
    {
        return false;
    }
    else
    {
        var charCode = (event.which) ? event.which : event.keyCode;
        if ((charCode > 47 && charCode < 58) || (charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123) || charCode == 32
             || charCode == 8 || charCode == 9 || charCode == 46 || charCode == 95 || charCode == 45)
        {
            return true;
        }
        else if (charCode == 37 || charCode == 39) //left and right arrow keys
             return true;
        else
        {
            return false;
        }
    }
}
function AlphaNumericOnly(event)
{
    var char = (String.fromCharCode(event.which));
    if (char == "%" || char == "'")
    {
        return false;
    }
    else
    {
        var charCode = (event.which) ? event.which : event.keyCode;
        if (/*DisableWhiteSpaces*/(charCode == 32) || /*DisableSpecialCharacters*/ (charCode > 32 && charCode < 48) || (charCode > 57 && charCode < 65) || (charCode > 90 && charCode < 97) || (charCode > 122 && charCode < 128))
            return false;
        else if (charCode == 37 || charCode == 39) //left and right arrow keys
            return true;
        else
        return true;
    }
}
        
        //letter and number with space and dash 
function alphanumeric4(event)
{
  
  var char = (String.fromCharCode(event.which));
  if (char == "%" || char == "'")
  {
    return false;
  }
  else
  {
  var charCode = (event.which) ? event.which : event.keyCode;
    if ((charCode > 64 && charCode < 91 ) || (charCode > 47 && charCode < 58) || (charCode > 96 && charCode < 123))
        return true;
    else if(charCode == 8 ||charCode == 32 || charCode == 9)
       return true;
    else if (charCode == 37 || charCode == 39) //left and right arrow keys
       return true;
    else
        return false;
  }
}

//validates input: accepts big letter and number only, no spaces
function alphanumeric1(event)
{
    var charCode;
    charCode = (event.which) ? event.which : event.keyCode;
    return ((charCode > 47 && charCode < 58) || (charCode > 64 && charCode < 91) || charCode == 8 || charCode == 9);
}
function numberonly(event)
{
    var char = (String.fromCharCode(event.which));
    if (char == "%" || char == "'")
    {
        return false;
    }
    else
    {
        var charCode = (event.which) ? event.which : event.keyCode;
        if (charCode == 37 || charCode == 39) //left and right arrow keys
             return true;
        else if (charCode > 31 && (charCode < 46 || ( charCode >= 47 && charCode < 48 ) || charCode > 57))
        {
            return false;
        }
        else if (charCode == 46)
        {
            return false;
        }
        else if(charCode == 9)
            return true;
        else
        {
            return true;
        }
    }
}

//validates input ; accepts number,small letter and special characters such as _%*+-!$=#.:?/&
function numberandletter(evt)
{
    var char = (String.fromCharCode(evt.which));
    if (char == "%" || char == "'")
    {
        return false;
    }
    else
    {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode == 37 || charCode == 39) //left and right arrow keys
           return true;
        else if (charCode == 96 || charCode == 60 || charCode == 62 || charCode == 44 || charCode == 59 || charCode == 34)
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
}

function AlphaNumericOnlyWithSpace(event)
{
    var char = (String.fromCharCode(event.which));
    if (char == "%" || char == "'")
    {
        return false;
    }
    else
    {
        var charCode = (event.which) ? event.which : event.keyCode;
        if ((charCode > 32 && charCode < 48) || (charCode > 57 && charCode < 65) || (charCode > 90 && charCode < 97) || (charCode > 122 && charCode < 128))
            return false;
        else if (charCode == 37 || charCode == 39) //left and right arrow keys
             return true;
        else
            return true;
    }
}
//removes spaces, special characters except (@, ., _) on email textboxes; modified 2012-02-14
function emailkeypress(event)
{
    var char = (String.fromCharCode(event.which));
    if (char == "%" || char == "'")
    {
        return false;
    }
    else
    {
        var charCode = (event.which) ? event.which : event.keyCode;  
        if((charCode > 47 && charCode < 58) || (charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123) 
            || charCode == 8 || charCode == 9 || charCode == 64 || charCode == 46 || charCode == 95 || charCode == 45)
            {
              return true;
            }
        else if (charCode == 37 || charCode == 39) //left and right arrow keys
             return true;
        else{
              return false;
        }
    }
}
function websitekeypress(event)
{
    var char = (String.fromCharCode(event.which));
    if (char == "%" || char == "'")
    {
        return false;
    }
    else
    {
        var charCode = (event.which) ? event.which : event.keyCode;
        if((charCode > 47 && charCode < 58) || (charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123) 
            || charCode == 8 || charCode == 9 || charCode == 46 || charCode == 95 || charCode == 45 || charCode == 58 || charCode == 47)
            {
              return true;
            }
        else if (charCode == 37 || charCode == 39) //left and right arrow keys
            return true;
        else{
              return false;
        }
    }
}
function telephonekeypress(event)
{
    var char = (String.fromCharCode(event.which));
    if (char == "%" || char == "'")
    {
        return false;
    }
    else
    {
        var charCode = (event.which) ? event.which : event.keyCode;
        if (charCode == 37 || charCode == 39) //left and right arrow keys
             return true;
        if (charCode > 31 && (charCode < 46 || ( charCode >= 47 && charCode < 48 ) || charCode > 57))
        {
            return false;
        }
        else if (charCode == 46)
        {
            return false;
        }
        else if(charCode == 9)
            return true;
        else
        {
            return true;
        }
    }
}
function addresskeypress(event)
{
  var char = (String.fromCharCode(event.which));
  if (char == "%" || char == "'")
  {
    return false;
  }
  else
  {
      var charCode = (event.which) ? event.which : event.keyCode;
      if ((charCode > 64 && charCode < 91 ) || (charCode > 47 && charCode < 58) || (charCode > 96 && charCode < 123))
          return true;
      else if(charCode == 8 ||charCode == 32 || charCode == 9)
         return true;
      else if (charCode == 45 || charCode == 44 || charCode == 47 || charCode == 46)
         return true;
      else if (charCode == 37 || charCode == 39) //left and right arrow keys
         return true;
      else
          return false;
   }
}
function usernamekeypress(event)
{
  
  var char = (String.fromCharCode(event.which));
  if (char == "%" || char == "'")
  {
    return false;
  }
  else
  {
  var charCode = (event.which) ? event.which : event.keyCode;
    if ((charCode > 64 && charCode < 91 ) || (charCode > 47 && charCode < 58) || (charCode > 96 && charCode < 123))
        return true;
    else if(charCode == 8 || charCode == 9)
       return true;
    else if (charCode == 37 || charCode == 39) //left and right arrow keys
       return true;
   else if (charCode == 95)
       return true;
    else
        return false;
  }
}


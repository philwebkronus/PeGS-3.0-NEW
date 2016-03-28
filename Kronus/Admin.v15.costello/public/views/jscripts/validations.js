/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//currency format
function CommaFormatted(num)
{
    num = num.toString().replace(/\$|\,/g, '');
    if (isNaN(num))
        num = "0";
    sign = (num == (num = Math.abs(num)));
    num = Math.floor(num * 100 + 0.50000000001);
    cents = num % 100;
    num = Math.floor(num / 100).toString();
    if (cents < 10)
        cents = "0" + cents;
    for (var i = 0; i < Math.floor((num.length - (1 + i)) / 3); i++)
        num = num.substring(0, num.length - (4 * i + 3)) + ',' + num.substring(num.length - (4 * i + 3));
    return (((sign) ? '' : '-') + num + '.' + cents);
}

//removes spaces, special characters except (@, ., _) on email textboxes; modified 2012-02-14
function emailkeypress(evt)
{
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if ((charCode > 47 && charCode < 58) || (charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123)
            || charCode == 8 || charCode == 9 || charCode == 64 || charCode == 46 || charCode == 95 || charCode == 45)
    {
        return true;
    }
    else {
        return false;
    }
}

//onkeypress valiation for corpo emails, removes spaces
function corpoemail(evt)
{
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    return ((charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123) || charCode == 8 || charCode == 9);
}
//validates input ; accepts number,small letter and special characters such as _%*+-!$=#.:?/&
function numberandletter(evt)
{
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode == 96 || charCode == 60 || charCode == 62 || charCode == 44 || charCode == 59 || charCode == 34)
    {
        return false;
    }
    else if (charCode > 31 && (charCode < 33 || charCode > 38) && (charCode < 42 || charCode > 63) && (charCode < 95 || charCode > 122)) {
        return false;
    }
    else if (charCode == 9)
    {
        return true;
    }
    else
        return true;
}

//validates input ; accepts number,small letter and special characters such as _-,'".
function numberandletterscore(evt)
{
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode == 42 || charCode == 37 || charCode == 33 || charCode == 43 || charCode == 61 || charCode == 35 || charCode == 96 || charCode == 60 || charCode == 62 || charCode == 63 || charCode == 44 || charCode == 58 || charCode == 59 || charCode == 47 || charCode == 38)
    {
        return false;
    }


    else if (charCode > 31 && (charCode < 33 || charCode > 38) && (charCode < 42 || charCode > 63) && (charCode < 95 || charCode > 122 || charCode == 47)) {
        return false;
    }

    else if (charCode == 9)
    {
        return true;
    }
    else
        return true;
}

//validates input for paths, url's : accepts . and / and (a-zA_Z0-9)
function urlvalidation(evt)
{
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    return ((charCode > 45 && charCode < 58) || (charCode > 96 && charCode < 123) ||
            (charCode > 64 && charCode < 91) || charCode == 8 || charCode == 9);
}

//validates input ; accepts number,small letter and special characters such as _%*+-!$=#.:?/& but not space
function numberandletter2(evt)
{
    var charCode = (evt.which) ? evt.which : evt.keyCode;

    if (charCode == 96)
    {
        return false;
    }

    else if (charCode > 31 && (charCode < 35 || charCode > 38) && (charCode < 42 || charCode > 63) && (charCode < 95 || charCode > 122)) {
        return false;
    }
    else if (charCode == 9)
    {
        return true;
    }
    else
        return true;
}

//validates input: accept numbers only
function numberonly(evt)
{
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
    else if (charCode == 9)
        return true;
    else
        return true;
}



//validates input: accept numbers and decimal only
function isNumberKey(evt, nStr) {
    var charCode = (evt.which) ? evt.which : evt.keyCode
    if (charCode > 31 && (charCode < 45 || charCode > 57)) {
        return false;
    }
    return true;


}

//comma separator
function addSeparator(nStr) {
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

//validates input: accept numbers only with decimal
function numberonlydecimal(evt)
{
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46)
        return false;
    else if (charCode == 9)
        return true;
    else
        return true;
}

//valiates input: accept all numbers but not 0
function nozero(evt)
{

    var charCode = (evt.which) ? evt.which : evt.keyCode;

    if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
    else if (charCode == 48)
        return false;
    else if (charCode == 9)
        return true;
    else
        return true;
}

//validates input: accept numbers only and -
function numberonlyanddash(evt)
{
    var charCode = (evt.which) ? evt.which : evt.keyCode;

    if (charCode == 45)
    {
        return true;
    }
    else if (charCode > 31 && (charCode < 45 || (charCode >= 47 && charCode < 48) || charCode > 57))
    {
        return false;
    }
    else if (charCode == 46)
    {
        return true;
    }
    else if (charCode == 9)
        return true;
    else
    {
        return true;
    }
}

//validates input: accepts letters and numbers only; all special characters are excluded
function numberandletter1(evt)
{
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 32 && (charCode < 44 || charCode > 57) && (charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122))
        return false;
    else if (charCode == 9)
        return true;
    else
        return true;
}

//validates input: accepts small letter and number only, no spaces
function alphanumeric(evt)
{
    var charCode;
    charCode = (evt.which) ? evt.which : evt.keyCode;
    return ((charCode > 47 && charCode < 58) || (charCode > 96 && charCode < 123) || charCode == 8 || charCode == 9);
}

//validates input: accepts big letter and number only, no spaces
function alphanumeric1(evt)
{
    var charCode;
    charCode = (evt.which) ? evt.which : evt.keyCode;
    return ((charCode > 47 && charCode < 58) || (charCode > 64 && charCode < 91) || charCode == 8 || charCode == 9);
}

//validates input: accepts big letter and number only, no spaces
function alphanumeric2(evt)
{
    var charCode;
    charCode = (evt.which) ? evt.which : evt.keyCode;
    return ((charCode > 47 && charCode < 58) || (charCode > 64 && charCode < 91) ||
            charCode == 8 || charCode == 9 || charCode == 45);
}

//for loyalty card number use only
function loyaltycardnumber(evt)
{
    var charCode;
    charCode = (evt.which) ? evt.which : evt.keyCode;
    return ((charCode > 47 && charCode < 58) || (charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123) ||
            charCode == 8 || charCode == 9 || charCode == 45);
}


//letter and number with space and dash 
function alphanumeric4(evt)
{
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if ((charCode > 64 && charCode < 91) || (charCode > 47 && charCode < 58) || (charCode > 96 && charCode < 123))
        return true;
    else if (charCode == 8 || charCode == 45 || charCode == 9 || charCode == 32)
        return true;
    else
        return false;
}

//letter and number with space
function alphanumeric3(evt)
{
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if ((charCode > 64 && charCode < 91) || (charCode > 47 && charCode < 58) || (charCode > 96 && charCode < 123))
        return true;
    else if (charCode == 8 || charCode == 32 || charCode == 9)
        return true;
    else
        return false;
}
//letter only small and big no space
function letteronly2(evt)
{
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if ((charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123))
        return true;
    else if (charCode == 8 || charCode == 9 || charCode == 32)
        return true;
    else
        return false;
}
//letter only small and big with space
function letteronly(evt)
{
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if ((charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123))
        return true;
    else if (charCode == 8 || charCode == 9)
        return true;
    else
        return false;
}

//validate input: accept letter only for account creation
function letter(k)
{
    var k;
    k = (k.which) ? k.which : k.keyCode;
    return ((k > 64 && k < 91) || (k > 96 && k < 123) || k == 8 || k == 32 || k == 9);
}

//validate input: accept letter only for account update
function letter2(k)
{
    var k;
    k = (k.which) ? k.which : k.keyCode;
    return ((k > 64 && k < 91) || (k > 96 && k < 123) || k == 8 || k == 9);
}

//validate input: accept letter only
function letterexceptspace(k)
{
    var k;
    k = (k.which) ? k.which : k.keyCode;
    return ((k > 64 && k < 91) || (k > 96 && k < 123) || k == 8 || k == 9);
}

//validate input: check if usename and password fields are empty
function chklogin()
{
    if (document.getElementById("txtusername").value.length == 0) {
        alert("Please enter your username");
        document.getElementById("txtusername").focus();
        return false;
    }
    else
        return true;
}

//validates email format entered
function echeck(str)
{
    var at = "@";
    var dot = ".";
    var lat = str.indexOf(at);
    var lstr = str.length;
    var ldot = str.indexOf(dot);
    if (str.indexOf(at) == -1)
    {
        alert("Invalid E-mail Address");
        return false;
    }
    if (str.indexOf(at) == -1 || str.indexOf(at) == 0 || str.indexOf(at) == lstr)
    {
        alert("Invalid E-mail Address");
        return false;
    }
    if (str.indexOf(dot) == -1 || str.indexOf(dot) == 0 || str.indexOf(dot) == lstr)
    {
        alert("Invalid E-mail Address");
        return false;
    }
    if (str.indexOf(at, (lat + 1)) != -1)
    {
        alert("Invalid E-mail Address");
        return false;
    }
    if (str.substring(lat - 1, lat) == dot || str.substring(lat + 1, lat + 2) == dot)
    {
        alert("Invalid E-mail Address");
        return false;
    }
    if (str.indexOf(dot, (lat + 2)) == -1)
    {
        alert("Invalid E-mail Address");
        return false;
    }
    if (str.indexOf(" ") != -1)
    {
        alert("Invalid E-mail Address");
        return false;
    }
    else
        return true;
}

//validates if email field is empty
function validateEmail()
{
    var emailID = document.getElementById('txtemail');
    if (document.getElementById('txtemail').value != "")
    {
        if ((emailID.value == null) || (emailID.value == ""))
        {
            alert("Please Enter your Email Address");
            emailID.focus();
            return false;
        }
        else if (echeck(emailID.value) == false)
        {
            emailID.value = "";
            emailID.focus();
            return false;
        }
        else
            return true;
    }
    else
    {
        alert("Please Enter your Email Address");
        emailID.focus();
        return false;
    }
}

function validateCorpEmail()
{
    var corpemailID = document.getElementById('txtcorpemail');
    if (document.getElementById('txtcorpemail').value != "")
    {
        if ((corpemailID.value == null) || (corpemailID.value == ""))
        {
            alert("Please Enter your Email Address");
            corpemailID.focus();
            return false;
        }
        else if (echeck(corpemailID.value) == false)
        {
            corpemailID.value = "";
            corpemailID.focus();
            return false;
        }
        else
            return true;
    }
    else
    {
        alert("Please Enter your Email Address");
        corpemailID.focus();
        return false;
    }
}

//validates email format entered (except numbers on the last part of the string)
function echeck1(str)
{
    var at = "@";
    var dot = ".";
    var lat = str.indexOf(at);
    var lstr = str.length;
    var ldot = str.indexOf(dot);
    if (str.indexOf(at) == -1)
    {
        alert("Invalid E-mail Address");
        return false;
    }
    if (str.indexOf(at) == -1 || str.indexOf(at) == 0 || str.indexOf(at) == lstr)
    {
        alert("Invalid E-mail Address");
        return false;
    }
    if (str.indexOf(dot) == -1 || str.indexOf(dot) == 0 || str.indexOf(dot) == lstr)
    {
        alert("Invalid E-mail Address");
        return false;
    }
    if (str.indexOf(at, (lat + 1)) != -1)
    {
        alert("Invalid E-mail Address");
        return false;
    }
    if (str.substring(lat - 1, lat) == dot || str.substring(lat + 1, lat + 2) == dot)
    {
        alert("Invalid E-mail Address");
        return false;
    }
    if (str.indexOf(dot, (lat + 2)) == -1)
    {
        alert("Invalid E-mail Address");
        return false;
    }
    else
        return true;
}

//allow input of number on the last part of the email
function validateEmail1()
{
    var emailID = document.getElementById('txtemail');
    if (document.getElementById('txtemail').value != "")
    {
        if ((emailID.value == null) || (emailID.value == ""))
        {
            alert("Please Enter your Email Address");
            emailID.focus();
            return false;
        }
        else if (echeck1(emailID.value) == false)
        {
            emailID.value = "";
            emailID.focus();
            return false;
        }
        else
            return true;
    }
    else
    {
        alert("Please Enter your Email Address");
        emailID.focus();
        return false;
    }
}

//used to what maintenance to be load
function loadWizard()
{
    if (document.getElementById('changepass').checked == true) {
        window.location.href = "changepass.php";
        return true;
    }
    else if (document.getElementById('forgotpass').checked == true) {
        window.location.href = "forgotpass.php";
        return true;
    }
    else if (document.getElementById('forgotuname').checked == true) {
        window.location.href = "forgotuser.php";
        return true;
    }
    else
    {
        alert("Please select on the radio button");
        return false;
    }
}

//validates if username and email field are empty
function chkChangePwd()
{
    if (document.getElementById('cmbsite').value == "-1")
    {
        alert("Please select Site/PEGS");
        document.getElementById('cmbsite').focus();
        return false;
    }
    else if ($('#chosen').val() == 'temp' || $('#chosen').text() == ''
            || $('#chosen').text() == 'Make your choice on the left')
    {
        alert("Please select terminal/s");
        document.getElementById('chosen').focus();
        return false;
    }
    else {
        return true;
    }
}

//validate if new password field is empty
function chkupdatepass()
{
    if (document.getElementById('txtnewpassword').value.length == 0)
    {
        alert("Please enter you new password");
        document.getElementById('txtnewpassword').focus();
        return false;
    }
    else if (document.getElementById('txtconfirmpass').value.length == 0)
    {
        alert("Please confirm your new password");
        document.getElementById('txtconfirmpass').focus();
        return false;
    }
    else if ((document.getElementById('txtnewpassword').value.length < 8) || (document.getElementById('txtnewpassword').value.length > 12))
    {
        alert("Password must not be less than 8 characters and not greater than 12 characters");
        document.getElementById('txtnewpassword').value = "";
        document.getElementById('txtconfirmpass').value = "";
        document.getElementById('txtnewpassword').focus();
        return false;
    }
    else if (document.getElementById('txtnewpassword').value != document.getElementById('txtconfirmpass').value)
    {
        alert("Password does not match");
        document.getElementById('txtnewpassword').focus();
        document.getElementById('txtnewpassword').value = "";
        document.getElementById('txtconfirmpass').value = "";
        return false;
    }
    else
        return true;
}

//validate passkey field if empty
function chkpasskey()
{
    if ((document.getElementById('txtpasskey').value.length == 0) || (document.getElementById("txtpasskey").value.indexOf(" ") == 0))
    {
        alert("Please enter you new passkey");
        document.getElementById('txtpasskey').focus();
        return false;
    }
    else
        return true;
}

//form validation in sitecreation.php
function chkcreatesites()
{

    if (document.getElementById('cmbislands').value == "-1")
    {
        alert("Please Select Island from List");
        document.getElementById('cmbislands').focus();
        return false;
    }
    else if (document.getElementById('cmbregions').value == "-1")
    {
        alert("Please Select Region from List");
        document.getElementById('cmbregions').focus();
        return false;
    }
    else if (document.getElementById('cmbprovinces').value == "-1")
    {
        alert("Please Select Province from List");
        document.getElementById('cmbprovinces').focus();
        return false;
    }
    else if (document.getElementById('cmbcity').value == "-1")
    {
        alert("Please Select City from List");
        document.getElementById('cmbcity').focus();
        return false;
    }
    else if (document.getElementById('cmbbrgy').value == "-1")
    {
        alert("Please Select Barangay from List");
        document.getElementById('cmbbrgy').focus();
        return false;
    }
    else if ((document.getElementById('txtsitename').value.length == 0) || (document.getElementById('txtsitename').value.indexOf(" ") == 0))
    {
        alert("Blank or Site/PEGS name with leading space/s is/are not allowed");
        document.getElementById('txtsitename').focus();
        return false;
    }
    else if ((document.getElementById('txtsitecode').value.length == 0) || (document.getElementById('txtsitecode').value.indexOf(" ") == 0))
    {
        alert("Blank or Site/PEGS code with leading space/s is/are not allowed");
        document.getElementById('txtsitecode').focus();
        return false;
    }
    else if (document.getElementById('txtsitecode').value.length < 3)
    {
        alert("Site/PEGS Code should be more than 2 digits.");
        document.getElementById('txtsitecode').focus();
        return false;
    }
//    else if((document.getElementById('txtsitedesc').value.length == 0) || (document.getElementById('txtsitedesc').value.indexOf(" ") == 0))
//        {
//            alert("Blank or site/pegs description with leading space/s is/are not allowed");
//            document.getElementById('txtsitedesc').focus();
//            return false;
//     }
//    else if((document.getElementById('txtsitealias').value.length == 0) || (document.getElementById('txtsitealias').value.indexOf(" ") == 0))
//        {
//            alert("Blank or site/pegs alias with leading space/s is/are not allowed");
//            document.getElementById('txtsitealias').focus();
//            return false;
//        }
    else if ((document.getElementById('txtsiteaddress').value.length == 0) || (document.getElementById('txtsiteaddress').value.indexOf(" ") == 0))
    {
        alert("Blank or Site/PEGS address with leading space/s is/are not allowed");
        document.getElementById('txtsiteaddress').focus();
        return false;
    }
//     else if((document.getElementById('txtcto').value.length == 0) || (document.getElementById('txtcto').value.indexOf(" ") == 0))
//        {
//            alert("Blank or Certificate To Operate #(CTO) with leading space/s is/are not allowed");
//            document.getElementById('txtcto').focus();
//            return false;
//        }
    else if (document.getElementById('txtctrycode').value.length == 0)
    {
        alert("Please input your country code");
        document.getElementById('txtctrycode').focus();
        return false;
    }
    else if (document.getElementById('txtareacode').value.length == 0)
    {
        alert("Please input your area code");
        document.getElementById('txtareacode').focus();
        return false;
    }
    else if (document.getElementById('txtphone').value.length == 0)
    {
        alert("Please input Site/PEGS contact number");
        document.getElementById('txtphone').focus();
        return false;
    }
    else if (document.getElementById('txtphone').value.length < 7)
    {
        alert("Contact number must be equal to 7 digits");
        document.getElementById('txtphone').focus();
        return false;
    }
    else if ((document.getElementById('txtpasscode').value.length == 0) || (document.getElementById('txtpasscode').value.indexOf(" ") == 0))
    {
        alert("Blank Passcode");
        document.getElementById('txtpasscode').focus();
        return false;
    }
    else if ((document.getElementById('txtpasscode').value.length < 4))
    {
        alert("Passcode Must Be 4 Digits");
        document.getElementById('txtpasscode').focus();
        return false;
    }
    else if (document.getElementById('cmbsiteclass').value == "-1")
    {
        alert("Please Select Site Classification from List");
        document.getElementById('cmbsiteclass').focus();
        return false;
    }
    else
        return true;
}

//form validation in siteedit.php
function chkeditsites()
{
    if (document.getElementById('cmbsiteowner').value == "-1")
    {
        alert("Please select Owner");
        document.getElementById('cmbsiteowner').focus();
        return false;
    }

    if (document.getElementById('cmbislands').value == "-1")
    {
        alert("Please Select Island from List");
        document.getElementById('cmbislands').focus();
        return false;
    }
    else if (document.getElementById('cmbregions').value == "-1")
    {
        alert("Please Select Region from List");
        document.getElementById('cmbregions').focus();
        return false;
    }
    else if (document.getElementById('cmbprovinces').value == "-1")
    {
        alert("Please Select Province from List");
        document.getElementById('cmbprovinces').focus();
        return false;
    }
    else if (document.getElementById('cmbcity').value == "-1")
    {
        alert("Please Select City from List");
        document.getElementById('cmbcity').focus();
        return false;
    }
    else if (document.getElementById('cmbbrgy').value == "-1")
    {
        alert("Please Select Barangay from List");
        document.getElementById('cmbbrgy').focus();
        return false;
    }
    else if ((document.getElementById('txtsitename').value.length == 0) || (document.getElementById('txtsitename').value.indexOf(" ") == 0))
    {
        alert("Blank or Site/PEGS name with leading space/s is/are not allowed");
        document.getElementById('txtsitename').focus();
        return false;
    }
    else if ((document.getElementById('txtsitecode').value.length == 0) || (document.getElementById('txtsitecode').value.indexOf(" ") == 0))
    {
        alert("Blank or Site/PEGS code with leading space/s is/are not allowed");
        document.getElementById('txtsitecode').focus();
        return false;
    }
    else if (document.getElementById('txtsitecode').value.length < 3)
    {
        alert("Site/PEGS Code should be more than 2 digits.");
        document.getElementById('txtsitecode').focus();
        return false;
    }
//    else if((document.getElementById('txtsitedesc').value.length == 0) || (document.getElementById('txtsitedesc').value.indexOf(" ") == 0))
//        {
//            alert("Blank or site/pegs description with leading space/s is/are not allowed");
//            document.getElementById('txtsitedesc').focus();
//            return false;
//     }
//    else if((document.getElementById('txtsitealias').value.length == 0) || (document.getElementById('txtsitealias').value.indexOf(" ") == 0))
//        {
//            alert("Blank or site/pegs alias with leading space/s is/are not allowed");
//            document.getElementById('txtsitealias').focus();
//            return false;
//        }
    else if ((document.getElementById('txtsiteaddress').value.length == 0) || (document.getElementById('txtsiteaddress').value.indexOf(" ") == 0))
    {
        alert("Blank or Site/PEGS address with leading space/s is/are not allowed");
        document.getElementById('txtsiteaddress').focus();
        return false;
    }
//     else if((document.getElementById('txtcto').value.length == 0) || (document.getElementById('txtcto').value.indexOf(" ") == 0))
//        {
//            alert("Blank or Certificate To Operate #(CTO) with leading space/s is/are not allowed");
//            document.getElementById('txtcto').focus();
//            return false;
//        }
    else if (document.getElementById('txtctrycode').value.length == 0)
    {
        alert("Please input your country code");
        document.getElementById('txtctrycode').focus();
        return false;
    }
    else if (document.getElementById('txtareacode').value.length == 0)
    {
        alert("Please input your area code");
        document.getElementById('txtareacode').focus();
        return false;
    }
    else if (document.getElementById('txtphone').value.length == 0)
    {
        alert("Please input Site/PEGS contact number");
        document.getElementById('txtphone').focus();
        return false;
    }
    else if (document.getElementById('txtphone').value.length < 7)
    {
        alert("Contact number must be equal to 7 digits");
        document.getElementById('txtphone').focus();
        return false;
    }
    else if (document.getElementById('cmbsiteclass').value == "-1")
    {
        alert("Please Select Site Classification from List");
        document.getElementById('cmbsiteclass').focus();
        return false;
    }
    else
        return true;
}


function chkeditservices()
{
    if (document.getElementById("txtservicename").value.indexOf(" ") == 0 || document.getElementById("txtservicename").value == '')
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtservicename").focus();
        return false;
    }
    else if (document.getElementById("txtalias").value.indexOf(" ") == 0 || document.getElementById("txtalias").value == '')
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtalias").focus();
        return false;
    }
    else if (document.getElementById("txtservicecode").value.indexOf(" ") == 0 || document.getElementById("txtservicecode").value == '')
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtservicecode").focus();
        return false;
    }
    else if (document.getElementById('txtservicecode').value.length < 2)
    {
        alert("Service Code less than 2 character");
        document.getElementById('txtservicecode').focus();
        return false;
    }
    else if (document.getElementById('txtservcdesc').value.indexOf(" ") == 0 || document.getElementById("txtservcdesc").value == '')
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtservcdesc").focus();
        return false;
    }
    else if (document.getElementById('cmbservicegrp').value.indexOf(" ") == 0 || document.getElementById("cmbservicegrp").value == '')
    {
        alert("Please Select Service Group");
        document.getElementById("cmbservicegrp").focus();
        return false;
    }
    else if (document.getElementById('usermode').value.indexOf(" ") == 0 || document.getElementById("usermode").value == '')
    {
        alert("Please Select User Mode");
        document.getElementById("usermode").focus();
        return false;
    }
    else
    {
         alert('Update Successful');
        return true;
    }
}

//form validation in accountcreation.php
function chkcreateacc()
{
    if (document.getElementById('cmbacctype').value == "-1")
    {
        alert("Please select account type");
        document.getElementById('cmbacctype').focus();
        return false;
    }
    else if ((jQuery('#cmbsite').val()) == "-1")
    {
        alert("Please select Site/PEGS name");
        //document.getElementById('cmbsite').focus();
        return false;
    }
    else if ((document.getElementById('txtusername').value.length == 0) || (document.getElementById("txtusername").value.indexOf(" ") == 0))
    {
        alert("Blank or username with leading space/s is/are not allowed");
        document.getElementById('txtusername').focus();
        return false;
    }
    else if ((document.getElementById('txtname').value.length == 0) || (document.getElementById("txtname").value.indexOf(" ") == 0))
    {
        alert("Blank or name with leading space/s is/are not allowed");
        document.getElementById('txtname').focus();
        return false;
    }
    else if ((document.getElementById('txtemail').value.length == 0) || (document.getElementById("txtemail").value.indexOf(" ") == 0))
    {
        alert("Please input your Email Address");
        document.getElementById('txtemail').focus();
        return false;
    }
    else if ((document.getElementById('txtcorpemail').value.length == 0) || (document.getElementById("txtcorpemail").value.indexOf(" ") == 0))
    {
        alert("Please input your Email Address");
        document.getElementById('txtcorpemail').focus();
        return false;
    }
    else if (((document.getElementById('txtphone').value.length == 0) || (document.getElementById("txtphone").value.indexOf(" ") == 0))
            && ((document.getElementById('txtmobile').value.length == 0) || (document.getElementById("txtmobile").value.indexOf(" ") == 0)))
    {
        alert("Please input your phone number or mobile number");
        document.getElementById('txtphone').focus();
        return false;
    }
    else if ((document.getElementById('txtphone').value.length < 7) && (document.getElementById('txtmobile').value.length < 7))
    {
        alert("Phone number or mobile number must be equal to 7 digits");
        document.getElementById('txtphone').focus();
        return false;
    }
    else if (((document.getElementById('txtctrycode').value.length == 0) || (document.getElementById("txtctrycode").value.indexOf(" ") == 0)) && ((document.getElementById('txtctrycode2').value.length == 0) || (document.getElementById("txtctrycode2").value.indexOf(" ") == 0)))
    {
        alert("Please input your country code");
        //document.getElementById('txtctrycode').focus();
        return false;
    }
    else if (((document.getElementById('txtareacode').value.length == 0) || (document.getElementById("txtareacode").value.indexOf(" ") == 0)) && ((document.getElementById('txtareacode2').value.length == 0) || (document.getElementById("txtareacode2").value.indexOf(" ") == 0)))
    {
        alert("Please input your area code");
        //document.getElementById('txtareacode').focus();
        return false;
    }
    else if ((document.getElementById('txtaddress').value.length == 0) || (document.getElementById("txtaddress").value.indexOf(" ") == 0))
    {
        alert("Blank or address with leading space/s is/are not allowed");
        document.getElementById('txtaddress').focus();
        return false;
    }
    else if (document.getElementById('cmbdesignation').value == "-1")
    {
        alert("Please select your designation");
        document.getElementById('cmbdesignation').focus();
        return false;
    }
    else
        return true;
}

//form validation in accountcreation.php
function chkupdacc()
{
    if (document.getElementById('cmbacctype').value == "-1")
    {
        alert("Please select account type");
        document.getElementById('cmbacctype').focus();
        return false;
    }
//    else if(document.getElementById('txtusername').value.length == 0)
//        {
//            alert("Please input your username");
//            document.getElementById('txtusername').focus();
//            return false;
//        }
    else if (document.getElementById('txtname').value.length == 0)
    {
        alert("Blank or name with leading space/s is/are not allowed");
        document.getElementById('txtname').focus();
        return false;
    }

    else if (document.getElementById('txtemail').value.length == 0)
    {
        alert("Please input your Email Address");
        document.getElementById('txtemail').focus();
        return false;
    }

    else if (document.getElementById('txtcorpemail').value.length == 0)
    {
        alert("Please input your Email Address");
        document.getElementById('txtcorpemail').focus();
        return false;
    }
    else if ((document.getElementById('txtphone').value.length == 0) && (document.getElementById('txtmobile').value.length == 0))
    {
        alert("Please input your phone number or mobile number");
        document.getElementById('txtphone').focus();
        return false;
    }
    else if ((document.getElementById('txtphone').value.length < 7) && (document.getElementById('txtmobile').value.length < 7))
    {
        alert("Phone number or mobile number must be equal to 7 digits");
        document.getElementById('txtphone').focus();
        return false;
    }
    else if ((document.getElementById('txtctrycode').value.length == 0) && (document.getElementById('txtctrycode2').value.length == 0))
    {
        alert("Please input your country code");
        document.getElementById('txtctrycode').focus();
        return false;
    }
    else if ((document.getElementById('txtareacode').value.length == 0) && (document.getElementById('txtareacode2').value.length == 0))
    {
        alert("Please input your area code");
        document.getElementById('txtareacode').focus();
        return false;
    }
    else if (document.getElementById('txtaddress').value.length == 0)
    {
        alert("Blank or address with leading space/s is/are not allowed");
        document.getElementById('txtaddress').focus();
        return false;
    }
    else if (document.getElementById('cmbdesignation').value == "-1")
    {
        alert("Please select your designation");
        document.getElementById('cmbdesignation').focus();
        return false;
    }
    else
    {
        return true
    }
}

//check if status is selected
function checkRadio(frmName, rbGroupName)
{
    var radios = document[frmName].elements[rbGroupName];
    for (var i = 0; i < radios.length; i++)
    {
        if (radios[i].checked)
        {
            return true;
        }
    }
    return false;
}

function chkStatus()
{
    if (!checkRadio("frmstatus", "optstatus"))
    {
        alert("Please choose to activate/deactivate");
        return false;
    }
    else
        return true;
}

//form validation in terminalcreation.php
function chkcreateterminal()
{
//    if(document.getElementById('txttermname').value.length == 0)
//        {
//            alert("Please input terminal name");
//            document.getElementById('txttermname').focus();
//            return false;
//        }
//    if(document.getElementById('txttermcode').value.length == 0)
//        {
//            alert("Please input terminal code");
//            document.getElementById('txttermcode').focus();
//            return false;
//        }
    if (document.getElementById('cmbsitename').value == "-1")
    {
        alert("Please select Site/PEGS");
        document.getElementById('cmbsitename').focus();
        return false;
    }
//    else if (!checkRadio("frmterminal","optvip"))
//        {
//            alert("Please select if vip or not");
//            return false;
//        }
    else
        return true;
}

//topup manual: update site balance
function chktopupbal()
{
    var amount = document.getElementById('txtamount').value;
    var maxbal = document.getElementById('txtmaxbal').value;
    var minbal = document.getElementById('txtminbal').value;
    var prevbal = document.getElementById('txtprevbal').value;
    minbal = minbal.replace(/,/g, "");
    maxbal = maxbal.replace(/,/g, "");
    amount = amount.replace(/,/g, "");
    prevbal = prevbal.replace(/,/g, "");

    var total = eval(amount) + eval(prevbal);


    if (document.getElementById('txtamount').value.length == 0)
    {
        alert("Please input amount");
        document.getElementById('txtamount').focus();
        return false;
    }
    else if (eval(total) < eval(minbal))
    {
        alert("Amount must be greater than or equal to minimum balance");
        document.getElementById('txtamount').value = "";
        document.getElementById('txtamount').focus();
        return false;
    }
    else if (eval(total) > eval(maxbal))
    {
        alert("Amount must be lower than or equal to maximum balance");
        document.getElementById('txtamount').value = "";
        document.getElementById('txtamount').focus();
        return false;
    }
    else if (document.getElementById('txtamount').value.substr(0, 1) == '0')
    {
        document.getElementById('txtamount').value = "";
        alert("Please input an amount greater than 0");
        document.getElementById('txtamount').focus();
        return false;
    }
    else
        return true;
}

//topup manual: update site parameter
function chktopup()
{
    var minbal = document.getElementById('txtminbal').value;
    var maxbal = document.getElementById('txtmaxbal').value;
    var amount = document.getElementById('txtamount').value;
    minbal = minbal.replace(/,/g, "");
    maxbal = maxbal.replace(/,/g, "");
    amount = amount.replace(/,/g, "");

    if ((jQuery("#cmbsite").val() == "-1") && (jQuery("#txtposacc").val().length == 0))
    {
        alert("Please select Site/PEGS or input the pos account number");
        document.getElementById('cmbsite').focus();
        return false;
    }
    else if (document.getElementById('txtamount').value.length == 0)
    {
        alert("Please input amount");
        document.getElementById('txtamount').focus();
        return false;
    }
    else if (document.getElementById('txtminbal').value.length == 0)
    {
        alert("Please input minimum balance");
        document.getElementById('txtminbal').focus();
        return false;
    }
//    else if(document.getElementById('txttopupamt').value.length == 0)
//        {
//            alert("Please input top-up amount");
//            document.getElementById('txttopupamt').focus();
//            return false;
//        }
    else if (eval(minbal) > eval(amount))
    {
        alert("Minimum balance must be less than or equal to amount");
        document.getElementById('txtminbal').value = "";
        document.getElementById('txtminbal').focus();
        return false;
    }

    else if (maxbal.length == 0)
    {
        alert("Please input maximum balance");
        document.getElementById('txtmaxbal').value = "";
        document.getElementById('txtmaxbal').focus();
        return false;
    }
    else if (eval(maxbal) < eval(minbal))
    {
        alert("Maximum balance must be greater than or equal to minimum balance");
        document.getElementById('txtmaxbal').value = "";
        document.getElementById('txtmaxbal').focus();
        return false;
    }
    else if (eval(maxbal) < eval(amount))
    {
        alert("Maximum balance must be greater than or equal to amount");
        document.getElementById('txtmaxbal').value = "";
        document.getElementById('txtmaxbal').focus();
        return false;
    }
    else if (!checkRadio("frmmanual", "optpick"))
    {
        alert("Please select if pick up or not");
        return false;
    }
    else
        return true;
}


//topup update: update site parameter
function chkupdtopup()
{
    var minbal = document.getElementById('txtminbal').value;
    var maxbal = document.getElementById('txtmaxbal').value;
    var amount = document.getElementById('txtamount').value;
    //var topupamt = document.getElementById('txttopupamt').value;
    minbal = minbal.replace(/,/g, "");
    maxbal = maxbal.replace(/,/g, "");
    amount = amount.replace(/,/g, "");
    //topupamt = topupamt.replace(/,/g,"");

    if (document.getElementById('txtminbal').value.length == 0)
    {
        alert("Please input minimum balance");
        document.getElementById('txtminbal').focus();
        return false;
    }
//    else if( parseFloat(minbal) > parseFloat(amount) )    
//        {
//            alert("Minimum balance must be less than or equal to site balance");
//            document.getElementById('txtminbal').value = "";
//            document.getElementById('txtminbal').focus();
//            return false;
//        }

    else if (maxbal.length == 0)
    {
        alert("Please input maximum balance");
        document.getElementById('txtmaxbal').value = "";
        document.getElementById('txtmaxbal').focus();
        return false;
    }
//    else if(topupamt.length == 0)
//    {
//        alert("Please input top-up amount");
//        document.getElementById('txttopupamt').focus();
//        return false;
//    }
    else if (eval(maxbal) < eval(minbal))
    {
        alert("Maximum balance must be greater than or equal to minimum balance");
        document.getElementById('txtmaxbal').value = "";
        document.getElementById('txtmaxbal').focus();
        return false;
    }
    else if (eval(maxbal) < eval(amount))
    {
        alert("Maximum balance must be greater than site balance");
        document.getElementById('txtmaxbal').value = "";
        document.getElementById('txtmaxbal').focus();
        return false;
    }
    else if (!checkRadio("frmmanual", "optpick"))
    {
        alert("Please select if pick up or not");
        return false;
    }
    else
        return true;
}

//reversal: validates fields
function chkreversal()
{
    var amount = document.getElementById('txtamount').value;
    var maxbal = document.getElementById('txtmaxbal').value;
    var minbal = document.getElementById('txtminbal').value;
    var prevbal = document.getElementById('txtprevbal').value;

    minbal = minbal.replace(/,/g, "");
    maxbal = maxbal.replace(/,/g, "");
    amount = amount.replace(/,/g, "");
    prevbal = prevbal.replace(/,/g, "");

    //var total = eval(amount) + eval(prevbal);  // deactivated due to error 

    var total = eval(amount);

    if ((jQuery("#cmbsite").val() == "-1") && (jQuery("#txtposacc").val().length == 0))
    {
        alert("Please select Site/PEGS or input the pos account number");
        document.getElementById('cmbsite').focus();
        return false;
    }
    else if (document.getElementById('txtamount').value.length == 0)
    {
        alert("Please input amount");
        document.getElementById('txtamount').focus();
        return false;
    }
    else if (document.getElementById('txtamount').value.substr(0, 1) == '0')
    {
        document.getElementById('txtamount').value = "";
        alert("Please input an amount greater than 0");
        document.getElementById('txtamount').focus();
        return false;
    }
    else if ((eval(total)) > 0 && (eval(prevbal)) == 0) // lbt 06/26 added for existing balance EQ to 0 and amount entered GT 0
    {
        alert("No amount to be reversed");
        document.getElementById('txtamount').value = "";
        document.getElementById('txtamount').focus();
        return false;
    }
//    else if(eval(amount) < eval(minbal))
//        {
//            alert("Amount must be lower than or equal to existing balance");
//            document.getElementById('txtamount').value = "";
//            document.getElementById('txtamount').focus();
//            return false;
//        }
    else if ((eval(amount)) > (eval(prevbal)))
    {
        alert("Amount must be lower than or equal to the current balance");
        document.getElementById('txtamount').value = "";
        document.getElementById('txtamount').focus();
        return false;
    }
    else if ((eval(amount)) > (eval(maxbal)))
    {
        alert("Amount must be lower than the maximum balance");
        document.getElementById('txtamount').value = "";
        document.getElementById('txtamount').focus();
        return false;
    }
    else
    {
        // amount less than previous balance  and amount GT 0 is allowed
        //return true;
        document.getElementById('light').style.display = 'block';
        document.getElementById('fade').style.display = 'block';
    }
}

//validate cash on hand posting
function chkcshonhandposting()
{
    var amount = document.getElementById('txtAmount').value;
    var reason = document.getElementById('txtReason').value;

    if ((jQuery("#cmbsitename").val() == "-1") && (jQuery("#txtposacc").val().length == 0))
    {
        alert("Please select Site/PEGS or input the pos account number");
        document.getElementById('cmbsitename').focus();
        return false;
    }
    else if ((jQuery("#txtAmount").val() == ""))
    {
        alert("Please enter amount.");
        document.getElementById('cmbsitename').focus();
        return false;
    }
    else if (jQuery.trim(jQuery("#txtReason").val()).length == 0)
    {
        alert("Please enter reason.");
        document.getElementById('cmbsitename').focus();
        return false;
    }

    else
    {
        document.getElementById('light').style.display = 'block';
        document.getElementById('fade').style.display = 'block';

    }
}

//validate input: check if usename and password fields are empty (serviceterminal.php) MG
function chkterminalcreation()
{
    if ((document.getElementById("txtusername").value.length == 0) || (document.getElementById("txtusername").value.indexOf(" ") == 0))
    {
        alert("Please enter your username");
        document.getElementById("txtusername").focus();
        return false;
    }
    else if ((document.getElementById("txtpassword").value.length == 0) || (document.getElementById("txtpassword").value.indexOf(" ") == 0))
    {
        alert("Please enter your password");
        document.getElementById("txtpassword").focus();
        return false;
    }
    else if (document.getElementById('cmbagents').value == "-1")
    {
        alert("Please select service agents");
        document.getElementById('cmbagents').focus();
        return false;
    }
    else
        return true;
}

//terminal mapping: validates fields
function chkmapping()
{
    if (document.getElementById('cmbsitename').value == "-1")
    {
        alert("Please select sites / pegs");
        document.getElementById('cmbsitename').focus();
        return false;
    }
    if (document.getElementById('cmbterminals').value == "-1")
    {
        alert("Please select terminals");
        document.getElementById('cmbterminals').focus();
        return false;
    }
    else if (document.getElementById('cmbserviceterms').value == "-1")
    {
        alert("Please select service terminals");
        document.getElementById('cmbserviceterms').focus();
        return false;
    }
    else
        return true;
}

//terminal maintenance : validates fields
function chkserviceass()
{
    if (document.getElementById('cmbsitename').value == "-1")
    {
        alert("Please select Site/PEGS");
        document.getElementById('cmbsitename').focus();
        return false;
    }
    if (document.getElementById('cmbterminals').value == "-1")
    {
        alert("Please select terminals");
        document.getElementById('cmbterminals').focus();
        return false;
    }
    else if (document.getElementById('cmbservices').value == "-1")
    {
        alert("Please select services");
        document.getElementById('cmbservices').focus();
        return false;
    }
    else
        return true;
}

//validation for site groups
function chksitegrp()
{
    if ((document.getElementById('txtgrpname').value.length == 0) || (document.getElementById("txtgrpname").value.indexOf(" ") == 0))
    {
        alert("Please enter group name");
        document.getElementById('txtgrpname').focus();
        return false;
    }
    else if ((document.getElementById('txtgrpdesc').value.length == 0) || (document.getElementById("txtgrpdesc").value.indexOf(" ") == 0))
    {
        alert("Please enter group description");
        document.getElementById('txtgrpdesc').focus();
        return false;
    }
    else
    {
        return true;
    }
}

//checking of dates for topup reporting
function chkdateformat()
{
    var date = new Date();
    var curr_date = date.getDate();
    var curr_month = date.getMonth();
    curr_month = curr_month + 1;
    var curr_year = date.getFullYear();
    if (curr_month < 10)
    {
        curr_month = "0" + curr_month;
        if (curr_date < 10)
            curr_date = "0" + curr_date;
    }

    var datenow = curr_year + '-' + curr_month + '-' + curr_date;

    if ((datenow) < (document.getElementById('rptDate').value))
    {
        alert("Queried date must not be greater than today");
        document.getElementById('rptDate').value = datenow;
        return false;
    }
//          else if((datenow) < (document.getElementById('rptDate2').value))
//          {
//              alert("Queried date must not be greater than today");
//              document.getElementById('rptDate2').value = datenow;
//              return false;         
//          }          
//          else if((document.getElementById('rptDate2').value) < 
//                  (document.getElementById('rptDate').value))
//          {
//              alert("Start date must not greater than end date");
//              document.getElementById('rptDate2').value = datenow;
//              return false;         
//          }          
    else
    {
        return true;
    }
}

//if computername field is empty -->appdisableterminal.php
function chkcomputername()
{
    if (document.getElementById('cmbdisterminal').value == "-1")
    {
        alert("Please select machine credentials");
        return false;
    }
    else if (document.getElementById("txtpcname").value.length == 0 || (document.getElementById("txtpcname").value.indexOf(" ") == 0))
    {

        alert("Blank or credential information with leading space/s is/are not allowed ");
        document.getElementById("txtpcname").focus();
        return false;
    }
    else if (document.getElementById("txtremarks").value.length == 0 || (document.getElementById("txtremarks").value.indexOf(" ") == 0))
    {
        alert("Blank or remarks with leading space/s is/are not allowed");
        document.getElementById("txtremarks").focus();
        return false;
    }
    else {
        return true;
    }
}

//validations for batch terminal creation
function chkbatchterminal()
{
    var terminals = jQuery("#cmbterminals").val();
    var ctr;

    for (ctr = 1; ctr <= terminals; ctr++)
    {
        if (!(checkRadio('frmbatch', 'optserver[' + ctr + ']')) && !(checkRadio('frmbatch', 'optserver1[' + ctr + ']'))
                && getCheckedValue(document.getElementById("optserver2[" + ctr + "]")) == '')
        {
            alert("Please select server on terminal #" + ctr);
            return false;
        }
    }

    return true;
}

function getCheckedValue(radioObj) {
    if (!radioObj)
        return "";
    var radioLength = radioObj.length;
    if (radioLength == undefined)
        if (radioObj.checked)
            return radioObj.value;
        else
            return "";
    for (var i = 0; i < radioLength; i++) {
        if (radioObj[i].checked) {
            return radioObj[i].value;
        }
    }
    return "";
}


//validate agent creation
function chkagentcreation()
{
    if (document.getElementById('cmbsitename').value == "-1")
    {
        alert("Please select Site/PEGS name");
        document.getElementById('cmbsitename').focus();
        return false;
    }

    else if (document.getElementById('txtusername').value.length == 0 || document.getElementById('txtusername').value.indexOf(" ") == 0)
    {
        alert("Please input agent name");
        document.getElementById('txtusername').focus();
        return false;
    }

    else if (document.getElementById('txtpassword').value.length == 0 || document.getElementById('txtpassword').value.indexOf(" ") == 0)
    {
        alert("Please input agent password");
        document.getElementById('txtpassword').focus();
        return false;
    }

    else
    {
        return true;
    }
}

///validates leading space/s for accounts
function chktrailingaccspaces()
{
    if (document.getElementById("txtusername").value.indexOf(" ") == 0)
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtusername").focus();
        return false;
    }
    else if (document.getElementById("txtname").value.indexOf(" ") == 0)
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtname").focus();
        return false;
    }
    else if (document.getElementById("txtemail").value.indexOf(" ") == 0)
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtemail").focus();
        return false;
    }
    else if (document.getElementById("txtcorpemail").value.indexOf(" ") == 0)
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtcorpemail").focus();
        return false;
    }
    else if (document.getElementById("txtphone").value.indexOf(" ") == 0)
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtphone").focus();
        return false;
    }
    else if (document.getElementById("txtmobile").value.indexOf(" ") == 0)
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtmobile").focus();
        return false;
    }
    else if ((document.getElementById("txtctrycode").value.indexOf(" ") == 0) || (document.getElementById("txtctrycode2").value.indexOf(" ") == 0))
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtctrycode").focus();
        return false;
    }
    else if ((document.getElementById("txtareacode").value.indexOf(" ") == 0) || (document.getElementById("txtareacode2").value.indexOf(" ") == 0))
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtareacode").focus();
        return false;
    }

    else if (document.getElementById("txtaddress").value.indexOf(" ") == 0)
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtaddress").focus();
        return false;
    }
    else {
        return true;
    }
}

//validates leading space/s for site update
function chktrailingsitespaces()
{
    if (document.getElementById("txtsitename").value.indexOf(" ") == 0)
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtsitename").focus();
        return false;
    }
    else if (document.getElementById("txtsitecode").value.indexOf(" ") == 0)
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtsitecode").focus();
        return false;
    }
    else if (document.getElementById("txtsitedesc").value.indexOf(" ") == 0)
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtsitedesc").focus();
        return false;
    }
    else if (document.getElementById('txtsitealias').value.indexOf(" ") == 0)
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtsitealias").focus();
        return false;
    }
//    else if(document.getElementById('txtcto').value.indexOf(" ") == 0)
//    {
//       alert("Blank or spaces are not allowed");
//       document.getElementById("txtcto").focus();
//       return false; 
//    }
    else if (document.getElementById('txtsiteaddress').value.indexOf(" ") == 0)
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtsiteaddress").focus();
        return false;
    }
    else if (document.getElementById('txtpasscode').value.indexOf(" ") == 0)
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtpasscode").focus();
        return false;
    }
    else
    {
        return true;
    }
}

/**
 * validation for editing casino service profile
 */
function chktrailingservicespaces()
{
    if (document.getElementById("txtservicename").value.indexOf(" ") == 0 || document.getElementById("txtservicename").value == '')
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtservicename").focus();
        return false;
    }
    else if (document.getElementById("txtalias").value.indexOf(" ") == 0 || document.getElementById("txtalias").value == '')
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtalias").focus();
        return false;
    }
    else if (document.getElementById("txtservicecode").value.indexOf(" ") == 0 || document.getElementById("txtservicecode").value == '')
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtservicecode").focus();
        return false;
    }
    else if (document.getElementById('txtservicecode').value.length < 2)
    {
        alert("Service Code less than 2 character");
        document.getElementById('txtservicecode').focus();
        return false;
    }
    else if (document.getElementById('txtservcdesc').value.indexOf(" ") == 0 || document.getElementById("txtservcdesc").value == '')
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txtservcdesc").focus();
        return false;
    }
    else if (document.getElementById('cmbservicegrp').value.indexOf(" ") == 0 || document.getElementById("cmbservicegrp").value == '')
    {
        alert("Please Select Service Group");
        document.getElementById("cmbservicegrp").focus();
        return false;
    }
    else if (document.getElementById('usermode').value.indexOf(" ") == 0 || document.getElementById("usermode").value == '')
    {
        alert("Please Select User Mode");
        document.getElementById("usermode").focus();
        return false;
    }
    else
    {
        return true;
    }
}

//validates leading space/s for terminal update
function chkterminalspaces()
{
    if (document.getElementById('txttermname').value.indexOf(" ") == 0)
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txttermname").focus();
        return false;
    }
    else if (document.getElementById('txttermcode').value.indexOf(" ") == 0)
    {
        alert("Blank or spaces are not allowed");
        document.getElementById("txttermcode").focus();
        return false;
    }
    else
    {
        return true;
    }
}

//POSTING OF DEPOSIT checking
function checkdepositposting()
{
    var sRemitType = document.getElementById("ddlRemittanceType").options[document.getElementById("ddlRemittanceType").selectedIndex].value;
    var sBank = document.getElementById("ddlBank").options[document.getElementById("ddlBank").selectedIndex].value;
    var sBranch = document.getElementById("txtBranch");
    var sAmount = document.getElementById("txtAmount");
    var sBankTransID = document.getElementById("txtBankTransID");
    var sBankTransDate = document.getElementById("txtBankTransDate");
    var sChequeNo = document.getElementById("txtChequeNo");
    var sParticulars = document.getElementById("txtParticulars");
    var sCurrentDate = document.getElementById('hidden_date').value;
    sBranch = sBranch.value;
    sAmount = sAmount.value;
    sBankTransID = sBankTransID.value;
    sBankTransDate = sBankTransDate.value;
    sChequeNo = sChequeNo.value;
    sParticulars = sParticulars.value;

    var date = new Date();
    var curr_date = date.getDate();
    var curr_month = date.getMonth();
    curr_month = curr_month + 1;
    var curr_year = date.getFullYear();
    var curr_date1 = date.getDate();
    if (curr_month < 10)
    {
        curr_month = "0" + curr_month;
        if (curr_date < 10 && curr_date1 < 10)
            curr_date = "0" + curr_date;
        curr_date1 = "0" + (curr_date1 - 7);
    }
    var datenow = curr_year + '-' + curr_month + '-' + curr_date;
    var pastdate = curr_year + '-' + curr_month + '-' + (curr_date1);

    if ((jQuery("#cmbsitename").val() == "-1") && (jQuery("#txtposacc").val().length == 0))
    {
        alert("Please select Site/PEGS or input the POS account number");
        document.getElementById('cmbsitename').focus();
        return false;
    }
    else if (sRemitType == 0)
    {
        if (sAmount.length == 0 || sAmount.indexOf(" ") == 0)
        {
            alert("Please supply the amount");
            document.getElementById("txtAmount").focus();
            return false;
        }
        else {
            alert("Please select the remittance type.");
            document.getElementById("ddlRemittanceType").focus();
            return false;
        }
    }
    //if with bank
    if ((sRemitType == 1) || (sRemitType == 3) || (sRemitType == 4) || (sRemitType == 6))
    {
        if (sBank == 0)
        {
            alert("Please select a bank.");
            document.getElementById("ddlBank").focus();
            return false;
        }
//        if (sBranch.length == 0 || sBranch.indexOf(" ") == 0)
//        {
//              alert("Please supply the branch.");
//              document.getElementById("txtBranch").focus();
//              return false;
//        }
        if (sBankTransID.length == 0 || sBankTransID.indexOf(" ") == 0)
        {
            alert("Please supply the bank transaction id.");
            document.getElementById("txtBankTransID").focus();
            return false;
        }
    }
    if (sAmount <= 0)
    {
        alert("Please supply the correct amount");
        document.getElementById("txtAmount").focus();
        return false;
    }
    if ((datenow) < (sBankTransDate))
    {
        //alert("Invalid Bank transaction date. Bank transactions advanced than today are prohibited.");
        alert("Queried date must not be greater than today");
        document.getElementById("txtBankTransDate").focus();
        return false;
    }
    if (sBankTransDate <= pastdate)
    {
        alert("Invalid Bank transaction date.Bank transactions later than 7 days are prohibited.");
        document.getElementById("txtBankTransDate").focus();
        return false;
    }

//    function isWhiteSpace(x) {
//        var white = new RegExp(/^\s$/);
//        return white.test(x.charAt(0));
//    };

    if (sParticulars.length == 0 || /^\s+$/.test(sParticulars))
    {
        alert("Please supply particulars");
        document.getElementById("txtParticulars").focus();
        return false;
    }

    if (confirm("Are all the information supplied correct?"))
    {
        showBlockUI(null);
    }
    else
    {
        return false;
    }
}

//ONCHANGE remittance type
function onchange_remittancetype()
{
    var sRemitType = document.getElementById("ddlRemittanceType").options[document.getElementById("ddlRemittanceType").selectedIndex].value;

    //bank deposit
    if (sRemitType == 1) {
        document.getElementById("ddlBank").disabled = false;
        document.getElementById("txtBranch").disabled = false;
        document.getElementById("txtBankTransID").disabled = false;
        document.getElementById("txtBankTransDate").disabled = false;
        document.getElementById("cal").style.visibility = 'visible';
        document.getElementById("txtChequeNo").disabled = false;
    }
    //bancnet
    else if (sRemitType == 6) {
        document.getElementById("ddlBank").disabled = false;
        document.getElementById("txtBranch").disabled = false;
        document.getElementById("txtBankTransID").disabled = false;
        document.getElementById("txtBankTransDate").disabled = false;
        document.getElementById("cal").style.visibility = 'visible';
        document.getElementById("txtChequeNo").disabled = true;
    }
    //cash or voucher
    else if (sRemitType == 7 || sRemitType == 5) {
        document.getElementById("ddlBank").disabled = true;
        document.getElementById("txtBranch").disabled = true;
        document.getElementById("txtBankTransID").disabled = true;
        document.getElementById("txtBankTransDate").disabled = true;
        document.getElementById("cal").style.visibility = 'hidden';
        document.getElementById("txtChequeNo").disabled = true;
    }
}

function showBlockUI(msg)
{
    isBusy = 1;
    if (msg != null)
        $.blockUI({message: '<div style="font-size: 14px; padding: 5px;"><img src="../images/ajax-loader.gif" /><br />' + msg + '</div>'});
    else
        $.blockUI({message: '<div style="font-size: 14px; padding: 5px;"><img src="../images/ajax-loader.gif" /></div>'});
}

function hideBlockUI()
{
    $.unblockUI();
    isBusy = 0;
}

//validate the form for pegs / grosshold  confirmation
function chkconformation()
{
    var date = new Date();
    var curr_date = date.getDate();
    var curr_month = date.getMonth();
    curr_month = curr_month + 1;
    var curr_year = date.getFullYear();
    if (curr_month < 10)
    {
        curr_month = "0" + curr_month;
        if (curr_date < 10)
            curr_date = "0" + curr_date;
    }
    var datenow = curr_year + '-' + curr_month + '-' + curr_date;
    var sdate = document.getElementById('txtdate').value;
    var transdate = sdate.substr(0, 10);

    if ((datenow) < (transdate))
    {
        alert("Queried date must not be greater than today");
        document.getElementById("txtdate").focus();
        return false;
    }

    else if ((document.getElementById('txtwho').value.length == 0) || document.getElementById('txtwho').value.indexOf(" ") == 0)
    {
        alert("Please input the Site/PEGS representative");
        document.getElementById('txtwho').focus();
        return false;
    }
    else if ((document.getElementById('txtamount').value.length == 0) || document.getElementById('txtamount').value.indexOf(" ") == 0)
    {
        alert("Please input the amount confirmed");
        document.getElementById('txtamount').focus();
        return false;
    }
    else
    {
        return true;
    }
}

//validate the form for switching of server (AS)
function chkswitchserver()
{
    if (document.getElementById('cmbsite').value == "-1")
    {
        alert("Please select Site/PEGS");
        document.getElementById('cmbsite').focus();
        return false;
    }
    else if (document.getElementById('cmboldservice').value == "-1")
    {
        alert("Please select server");
        document.getElementById('cmbnewservice').focus();
        return false;
    }
    else if (document.getElementById('chosen').value == "temp")
    {
        alert("Please select terminal");
        document.getElementById('chosen').focus();
        return false;
    }
    else if (document.getElementById('cmbnewservice').value == "-1")
    {
        alert("Please select new server");
        document.getElementById('cmbnewservice').focus();
        return false;
    }
    else if (document.getElementById("txtremarks").value.length == 0 || (document.getElementById("txtremarks").value.indexOf(" ") == 0))
    {
        alert("Blank or remarks with leading space/s is/are not allowed");
        document.getElementById("txtremarks").focus();
        return false;
    }
    else
    {
        return true;
    }
}

//validate the form for switching of server (AS)
function chkremoveserver()
{
    if (document.getElementById('cmbsite').value == "-1")
    {
        alert("Please select Site/PEGS");
        document.getElementById('cmbsite').focus();
        return false;
    }
    else if (document.getElementById('cmbterm').value == "-1")
    {
        alert("Please select terminal");
        document.getElementById('cmbterm').focus();
        return false;
    }
    else if (document.getElementById('cmbnewservice').value == "-1")
    {
        alert("Please select current server");
        document.getElementById('cmbnewservice').focus();
        return false;
    }
    else if (document.getElementById("txtremarks").value.length == 0 || (document.getElementById("txtremarks").value.indexOf(" ") == 0))
    {
        alert("Blank or remarks with leading space/s is/are not allowed");
        document.getElementById("txtremarks").focus();
        return false;
    }
    else
    {
        return true;
    }
}

function copyToList(from, to)
{
    var i;
    //var fromList = eval('document.forms[1].' + from);
    //var toList = eval('document.forms[1].' + to);
    var fromList = eval(document.getElementById(from));
    var toList = eval(document.getElementById(to));
    if (toList.options.length > 0 && toList.options[0].value == 'temp')
    {
        toList.options.length = 0;
    }

    var sel = false;
    for (i = 0; i < fromList.options.length; i++)
    {
        var current = fromList.options[i];

        if (current.selected)
        {
            sel = true;
            if (current.value == 'temp')
            {
                alert('You cannot move this text!');
                return;
            }

            var txt = current.text;
            var val = current.value;
            toList.options[toList.length] = new Option(txt, val);
            fromList.options[i] = null;
            i--;
        }
    }
    if (!sel)
        alert('You haven\'t selected any options!');
}

function allSelect()
{
    var i;
    //List = document.forms[1].chosen;
    List = document.getElementById('chosen');
    if (List.length && List.options[0].value == 'temp')
        return;

    for (i = 0; i < List.length; i++)
    {
        List.options[i].selected = true;
    }
}

//validation of date without cut-off
function validatedate(txtdate)
{
    var date = new Date();
    var curr_date = date.getDate();
    var curr_month = date.getMonth();
    curr_month = curr_month + 1;
    var curr_year = date.getFullYear();
    var arr = txtdate.split("-");
    var year = (arr[0]);
    var month = (arr[1]);
    var day = (arr[2]);

    if (curr_month < 10)
    {
        curr_month = "0" + curr_month;
        if (curr_date < 10)
            curr_date = "0" + curr_date;
    }
    var datenow = curr_year + '-' + curr_month + '-' + curr_date;
    if ((datenow) < (txtdate))
    {
        alert("Queried date must not be greater than today");
        return false;
    }
    else if (curr_year >= year)
    {
        if ((curr_year <= year) && (curr_month >= month))
        {
            if ((curr_month <= month) && (curr_date < day))
            {
                alert("Queried date must not be greater than today");
                return false;
            }
            else
            {
                return true;
            }
        }
        else
        {
            return true;
        }
    }
    else
    {
        return true;
    }
}

function validatemenu()
{
    if ((document.getElementById('txtmenuname').value.length == 0) || (document.getElementById('txtmenuname').value.indexOf(" ") == 0))
    {
        alert("Please input menu name");
        document.getElementById('txtmenuname').focus();
        return false;
    }
    else if ((document.getElementById('txtdefault').value.length == 0) || (document.getElementById('txtdefault').value.indexOf(" ") == 0))
    {
        alert("Please input default page");
        document.getElementById('txtdefault').focus();
        return false;
    }
    else if ((document.getElementById('txtdescription').value.length == 0) || (document.getElementById('txtdescription').value.indexOf(" ") == 0))
    {
        alert("Please input menu description");
        document.getElementById('txtdescription').focus();
        return false;
    }
    else
    {
        return true;
    }
}

function validatesubmenu()
{
    if (document.getElementById('cmbmenu').value == "-1")
    {
        alert("Please select menu");
        return false;
    }
    else if ((document.getElementById('txtsubname').value.length == 0) || (document.getElementById('txtsubname').value.indexOf(" ") == 0))
    {
        alert("Please input sub-menu name");
        document.getElementById('txtsubname').focus();
        return false;
    }
    else if ((document.getElementById('txtdescription').value.length == 0) || (document.getElementById('txtdescription').value.indexOf(" ") == 0))
    {
        alert("Please input sub-menu description");
        document.getElementById('txtdescription').focus();
        return false;
    }
    else
    {
        return true;
    }
}

function validatearights()
{
    if (document.getElementById('cmbacctype').value == "-1")
    {
        alert("Please selecte account type");
        return false;
    }
    else if (document.getElementById('cmbmenu').value == "-1")
    {
        alert("Please select menu");
        return false;
    }
    else if (document.getElementById('cmbsubmenu').value == "-1")
    {
        alert("Please select sub-menu");
        return false;
    }
    else if (document.getElementById('txtorderid').value.length == 0)
    {
        alert("Please enter order number");
        document.getElementById('txtorderid').focus();
        return false;
    }
    else if ((document.getElementById('txturl').value.length == 0) || (document.getElementById('txturl').value.indexOf(" ") == 0))
    {
        alert("Please enter default URL");
        document.getElementById('txturl').focus();
        return false;
    }
    else if ((document.getElementById('txturl2').value.length == 0) || (document.getElementById('txturl2').value.indexOf(" ") == 0))
    {
        alert("Please enter default URL2");
        document.getElementById('txturl2').focus();
        return false;
    }
    else
    {
        return true;
    }
}

function delAccessRights() {
    if (document.getElementById('cmbacctype').value == "-1")
    {
        alert("Please selecte account type");
        return false;
    }
    else if (document.getElementById('cmbmenu').value == "-1")
    {
        alert("Please select menu");
        return false;
    }
    else if (document.getElementById('cmbsubmenu').value == "-1")
    {
        alert("Please select sub-menu");
        return false;
    }
    else
        return true;
}

function chkecitytrack()
{
    var date = new Date();
    var curr_date = date.getDate();
    var curr_month = date.getMonth();
    curr_month = curr_month + 1;
    var curr_year = date.getFullYear();

    if (curr_month < 10)
    {
        curr_month = "0" + curr_month;
        if (curr_date < 10)
            curr_date = "0" + curr_date;
    }

    var datenow = curr_year + '-' + curr_month + '-' + curr_date;

    if (document.getElementById('cmbsite').value == "-1")
    {
        alert("Please select Site/PEGS");
        document.getElementById('cmbsite').focus();
        return false;
    }
    else if (document.getElementById('cmbterm').value == "-1")
    {
        alert("Please select terminal");
        document.getElementById('cmbterm').focus();
        return false;
    }
    else if ((datenow) < (document.getElementById('popupDatepicker1').value))
    {
        alert("Queried date must not be greater than today");
        document.getElementById('popupDatepicker1').value = datenow;
        return false;
    }
    else
    {
        return true;
    }
}

//validation for topup replenishment form
function chkreplenishment()
{
    var date = new Date();
    var curr_date = date.getDate();
    var curr_month = date.getMonth();
    curr_month = curr_month + 1;
    var curr_year = date.getFullYear();
    if (curr_month < 10)
    {
        curr_month = "0" + curr_month;
        if (curr_date < 10)
            curr_date = "0" + curr_date;
    }
    var datenow = curr_year + '-' + curr_month + '-' + curr_date;
    var refnum = document.getElementById('txtrefnum').value;
    //var sdate = document.getElementById('txtdate').value;
    //var sitedate = sdate.substr(0,10);
    if (document.getElementById('cmbsitename').value == "-1" && document.getElementById('txtposacc').value.length == 0)
    {
        alert("Please select Site/PEGS or input the POS account number");
        document.getElementById('cmbsitename').focus();
        return false;
    }
    else if ((document.getElementById('txtamount').value.length == 0) || document.getElementById('txtamount').value.indexOf(" ") == 0)
    {
        alert("Please input amount");
        document.getElementById('txtamount').focus();
        return false;
    }
//    else if((datenow) < (sitedate))
//    {
//        alert("Queried date must not be greater than today");
//        document.getElementById("txtdate").focus();
//        return false;
//    }
    else if (document.getElementById('cmbreplenishment').value == "-1")
    {
        alert("Please select replenishment type");
        document.getElementById('cmbreplenishment').focus();
        return false;
    }
    else if ((document.getElementById('cmbreplenishment').value == "2" && document.getElementById('txtrefnum').value.length == 0) || (document.getElementById('cmbreplenishment').value == "3" && document.getElementById('txtrefnum').value.length == 0))
    {
        alert("Please input the reference number");
        document.getElementById('txtrefnum').focus();
        return false;
    }
    else if ((document.getElementById('cmbreplenishment').value == "2" && refnum.trim().length == 0) || (document.getElementById('cmbreplenishment').value == "3" && refnum.trim().length == 0))
    {
        alert("Please input the reference number");
        document.getElementById('txtrefnum').focus();
        return false;
    }

    if (confirm("Are all the information supplied correct?"))
    {
        showBlockUI(null);
    }
    else
    {
        return false;
    }
}

function chkdenomination()
{
    if (eval(jQuery("#cmbmininitial").val()) > eval(jQuery("#cmbmaxinitial").val()))
    {
        alert("Initial (Regular) minimum amount must be lower than the maximum amount");
        return false;
    }
    else if (eval(jQuery("#cmbminregular").val()) > eval(jQuery("#cmbmaxregular").val()))
    {
        alert("Reload (Regular) minimum amount must be lower than the maximum amount");
        return false;
    }
    else if (eval(jQuery("#cmbmininitvip").val()) > eval(jQuery("#cmbmaxinitvip").val()))
    {
        alert("Initial (VIP) minimum amount must be lower than the maximum amount");
        return false;
    }
    else if (eval(jQuery("#cmbminrelvip").val()) > eval(jQuery("#cmbmaxrelvip").val()))
    {
        alert("Reload (VIP) minimum amount must be lower than the maximum amount");
        return false;
    }
    else
    {
        return true;
    }
}

function chktransaction()
{
    var date = new Date();
    var curr_date = date.getDate();
    var curr_month = date.getMonth();
    curr_month = curr_month + 1;
    var curr_year = date.getFullYear();

    if (curr_month < 10)
    {
        curr_month = "0" + curr_month;
        if (curr_date < 10)
            curr_date = "0" + curr_date;
    }

    var datenow = curr_year + '-' + curr_month + '-' + curr_date;

    if ((datenow) < (document.getElementById('popupDatepicker1').value))
    {
        alert("Queried date must not be greater than today");
        document.getElementById('popupDatepicker1').value = datenow;
        return false;
    }
    else if ((datenow) < (document.getElementById('popupDatepicker2').value))
    {
        alert("Queried date must not be greater than today");
        document.getElementById('popupDatepicker2').value = datenow;
        return false;
    }
    else if ((document.getElementById('popupDatepicker2').value) <
            (document.getElementById('popupDatepicker1').value))
    {
        alert("Start date must not greater than end date");
        document.getElementById('popupDatepicker1').value = datenow;
        return false;
    }
    else if (document.getElementById('cmbservices').value == "-1")
    {
        alert("Please select service");
        return false;
    }
    else
    {
        return true;
    }
}

function chkcashierterminal()
{
    if (document.getElementById('cmbsite').value == "-1")
    {
        alert("Please select Site/PEGS");
        return false;
    }
    else if (document.getElementById('txtaddcashier').value.length == 0)
    {
        alert("Please enter the desired number of cashier");
        document.getElementById('txtaddcashier').focus();
        return false;
    }
    else
    {
        return true;
    }
}

function validateupdateloadamt()
{
    if (document.getElementById('cmbsite').value == "-1")
    {
        alert("Please select Site/PEGS");
        return false;
    }
    else if (document.getElementById('txtloadamt').value.length == 0 || document.getElementById('txtloadamt').value == "0" || document.getElementById('txtloadamt').value == "")
    {
        alert("Please enter the load amount");
        document.getElementById('txtloadamt').focus();
        return false;
    }
    else
    {
        return true;
    }
}
function chkoffpasskey()
{
    if (document.getElementById('cmbsite').value == "-1")
    {
        alert("Please select Site/PEGS");
        return false;
    }
    else if (document.getElementById('cmbcashier').value == "-1")
    {
        alert("Please select cashier");
        return false;
    }
    else
    {
        return true;
    }
}

function chkterminalpwd()
{
    if (document.getElementById('txtnewpwd').value.length < 6)
    {
        alert("Please enter you new password. Minimum of 6 characters.");
        document.getElementById('txtnewpwd').focus();
        return false;
    }
    else if (document.getElementById('txtconfirmpass').value.length == 0)
    {
        alert("Please confirm your new password");
        document.getElementById('txtconfirmpass').focus();
        return false;
    }
    else if (document.getElementById('txtnewpwd').value != document.getElementById('txtconfirmpass').value)
    {
        alert("Password does not match");
        document.getElementById('txtnewpwd').focus();
        document.getElementById('txtnewpwd').value = "";
        document.getElementById('txtconfirmpass').value = "";
        return false;
    }
    else if (jQuery("#cmbnewservice").val() == "-1")
    {
        alert("Please select service");
        jQuery("#cmbnewservice").focus();
        return false;
    }
    else
        return true;
}

function chksiteremove() {
    if ($("#cmboptr").val() == "-1") {
        alert("Please select operator account");
        $("#cmboptr").focus();
        return false;
    }
    else if ($("#cmbsite").val() == "-1") {
        alert("Please select Site/PEGS")
        $("#cmbsite").focus();
        return false;
    }
    else
        return true;
}

/**
 * validation for reset casino account
 */
function chkResetCasinoAcct() {
    if ($("#cmbsite").val() == "-1") {
        alert("Please select Site/PEGS")
        $("#cmbsite").focus();
        return false;
    }
    else if ($("#cmbterm").val() == "-1") {
        alert("Please select terminal");
        $("#cmbterm").focus();
        return false;
    }
    else if ($("#cmbnewservice").val() == "-1") {
        alert("Please select casino server");
        $("#cmbnewservice").focus();
        return false;
    }
    else
        return true;
}

function chkOverride() {
    if ($("#cmbsite").val() == "-1") {
        alert("Please select Site/PEGS")
        $("#cmbsite").focus();
        return false;
    }
    else if (!checkRadio("frmstatus", "optstatus"))
    {
        alert("Please choose to enable/disable auto Top-up");
        return false;
    }
    else
        return true;
}

//checks if transaction was within 24 hr. frame 
function validateDateTime(date) {

    var fromDateTime = $("#popupDatepicker1").val().split(" ");
    var toDateTime = $("#popupDatepicker2").val().split(" ");
    var fromTimeArray = fromDateTime[1].split(":");
    var fromTime = parseInt("".concat(fromTimeArray[0]).concat(fromTimeArray[1]).concat(fromTimeArray[2]), 10);
    var toTimeArray = toDateTime[1].split(":");
    var toTime = parseInt("".concat(toTimeArray[0]).concat(toTimeArray[1]).concat(toTimeArray[2]), 10);
    var fromDate = fromDateTime[0].split("-");
    var toDateArray = toDateTime[0].split("-");
    var toDate = parseInt("".concat(toDateArray[0]).concat(toDateArray[1]).concat(toDateArray[2]));
    var fromDateAsInt = parseInt("".concat(fromDate[0]).concat(fromDate[1]).concat(fromDate[2]));

    var year = parseInt(fromDate[0], 10);
    var month = parseInt(fromDate[1], 10);
    var day = parseInt(fromDate[2], 10);

    var theNextDate = "";
    var leadingZero = "0";

    var currentDate = date;

    /**
     * @Code Block to check validity of date and time parameters
     * 
     */
    if (month == 1 || month == 3 || month == 5 || month == 7 || month == 8 || month == 10 || month == 12) { //31 Days

        if (month == 12) {

            if (day == 31) {
                theNextDate = theNextDate.concat((year + 1), '01', '01');
            }
            else {
                theNextDate = theNextDate.concat(year, '12', (leadingZero.concat((day + 1).toString())).substr(-2));
            }

        }
        else {

            if (day == 31) {
                theNextDate = theNextDate.concat(year, (leadingZero.concat((month + 1).toString())).substr(-2), '01');
            }
            else {
                theNextDate = theNextDate.concat(year, (leadingZero.concat((month).toString())).substr(-2), (leadingZero.concat((day + 1).toString())).substr(-2));
            }

        }

    }
    else if (month == 4 || month == 6 || month == 9 || month == 11) { //30 Days

        if (day == 30) {
            theNextDate = theNextDate.concat(year, (leadingZero.concat((month + 1).toString())).substr(-2), '01');
        }
        else {
            theNextDate = theNextDate.concat(year, (leadingZero.concat((month).toString())).substr(-2), (leadingZero.concat((day + 1).toString())).substr(-2));
        }

    }
    else { //February

        if ((year % 4) == 0) {

            if (day == 29) {
                theNextDate = theNextDate.concat(year, '03', '01');
            }
            else {
                theNextDate = theNextDate.concat(year, '02', (leadingZero.concat((day + 1).toString())).substr(-2));
            }

        }
        else {

            if (day == 28) {
                theNextDate = theNextDate.concat(year, '03', '01');
            }
            else {
                theNextDate = theNextDate.concat(year, '02', (leadingZero.concat((day + 1).toString())).substr(-2));
            }

        }

    }

    if ((toDate == fromDateAsInt || toDate == theNextDate) && (toDate <= currentDate && fromDateAsInt <= currentDate)) {

        if (toDate == fromDateAsInt) {

            if (fromTime == 0) {

                return true;

            }
            else {

                if ((toTime >= fromTime) && (toTime <= 235959)) {
                    return true;
                }
                else {
                    alert("Your Starting and Ending Date and Time must be within 24-Hour Frame");
                    return false;
                }

            }


        }
        else {

            if (toTime <= fromTime) {
                return true;
            }
            else {
                alert("Your Starting and Ending Date and Time must be within 24-Hour Frame");
                return false;
            }

        }

    }
    else {

        if ((fromDateAsInt > toDate) || (toDate > currentDate || fromDateAsInt > currentDate)) {

            alert("Invalid Date");

        }
        else {

            alert("Your Starting and Ending Date and Time must be within 24-Hour Frame");

        }

        return false;
    }
}

function specialcharacter(elementvalue)
{
    var iChars = "!`@#$%^&*()+=[]\\\';,./{}|\":<>?~_";
    var data = elementvalue;
    for (var i = 0; i < data.length; i++)
    {
        if (iChars.indexOf(data.charAt(i)) != -1)
        {
            return false;
        }
    }
    return true;
}

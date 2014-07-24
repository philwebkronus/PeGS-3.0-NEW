function getBalance_validate(casinoName, username)
{
    if((casinoName != '#' && casinoName =='') || username ==''){
        return false;
    } else {
        return true;
    }
}

function createAccount_validate(casinoName,username,email,address)
{
    if((casinoName != '#' && casinoName =='') || username =='' || email =='' || address ==''){
        return false;
    } else {
        return true;
    }
}

function deposit_validate(casinoName,username,password,amount,tranID)
{
    switch(casinoName){
    case 'RTG':
        return (username != '' && amount != '') ? true:false;
        break;
    case 'MG':
        return (username != '' && password !='' && amount != '' && tranID != '' ) ? true:false;
        break;  
    case 'PT':
        return (username != '' && password !='' && amount != '' && tranID != '') ? true:false;
        break;  
    }
}

function withdrawal_validate(casinoName,username,password,tranID)
{
    switch(casinoName){
    case 'RTG':
        return (username != '') ? true:false;
        break;
    case 'MG':
        return (username != '' && password !=''  && tranID != '' ) ? true:false;
        break;  
    case 'PT':
        return (username != '' && password !='' && tranID != '') ? true:false;
        break;  
    }
}

function freeze_validate(casinoName,username,status)
{
    switch(casinoName){
        case 'MG':
            return (username != '') ? true:false;
            break;  
        case 'PT':
            return (username != '' && status !='') ? true:false;
            break;  
    }
}

function getInfo_validate(casinoName,username,password)
{
    switch(casinoName){
    case 'RTG':
        return (username != '') ? true:false;
        break;
    case 'MG':
        return (username != '') ? true:false;
        break;
    case 'PT':
        return (username != '' && password !='') ? true:false;
        break;  
    }
}

function loadGetBalPage(dest)
{
       window.setInterval(function(){
        $.ajax({
            url:  dest,
            type: "POST",
            data: "",
            cache: true,
            success: function(response){
                $("#main-body").html(response);
            }
        });
    });
}

function loadPage(dest)
{
      window.location.href = dest;
}

function loadInputPage(dest,htmlName)
{
       window.setInterval(function(){
        $.ajax({
            url:  dest,
            type: "POST",
            data: "",
            cache: true,
            success: function(response){
                $(htmlName).html(response);
            }
        });
    });
}

function getInputURL1()
{
    var casinoName = document.getElementById('casinoName').value;
    var url;
    var htmlName = '#depositInputs';
    var localhost = location.host;
    switch(casinoName){
        case 'RTG':
            url = 'http://'+ localhost +'/TestPTAPI/view/depoRTG.php';
            loadInputPage(url,htmlName);
            $("#responseResults").html('');
            break;
        case 'MG':
            url = 'http://'+ localhost +'/TestPTAPI/view/depoMG.php';
            loadInputPage(url,htmlName);
            $("#responseResults").html('');
            break;  
        case 'PT':
            url = 'http://'+ localhost +'/TestPTAPI/view/depoPT.php';
            loadInputPage(url,htmlName);
            $("#responseResults").html('');
            break;  
    }
}

function getInputURL2()
{
    var casinoName = document.getElementById('casinoName').value;
    var url;
    var htmlName = '#withdrawalInputs';
    var localhost = location.host;
    switch(casinoName){
        case 'RTG':
            url = 'http://'+ localhost +'/TestPTAPI/view/withRTG.php';
            loadInputPage(url,htmlName);
            $("#responseResults").html('');
            break;
        case 'MG':
            url = 'http://'+ localhost +'/TestPTAPI/view/withMG.php';
            loadInputPage(url,htmlName);
            $("#responseResults").html('');
            break;  
        case 'PT':
            url = 'http://'+ localhost +'/TestPTAPI/view/withPT.php';
            loadInputPage(url,htmlName);
            $("#responseResults").html('');
            break;  
    }
}

function getInputURL3() 
{
    var casinoName = document.getElementById('casinoName').value;
    var url;
    var htmlName = '#unlockInputs';
    var localhost = location.host;
    switch(casinoName){
        case 'MG':
            url = 'http://'+ localhost +'/TestPTAPI/view/UnlockMG.php';
            loadInputPage(url,htmlName);
            $("#title").html('&nbsp;&nbsp; >>UNLOCK ACCOUNT(MG)');
            $("#responseResults").html('');
            break;  
        case 'PT':
            url = 'http://'+ localhost +'/TestPTAPI/view/UnfreezePT.php';
            loadInputPage(url,htmlName);
            $("#title").html('&nbsp;&nbsp; >>FREEZE/UNFREEZE ACCOUNT(PT)');
            $("#responseResults").html('');
            break;  
    }
}

function getInputURL4()
{
    var casinoName = document.getElementById('casinoName').value;
    var url;
    var htmlName = '#getInfoInputs';
    var localhost = location.host;
    switch(casinoName){
        case 'RTG':
            url = 'http://'+ localhost +'/TestPTAPI/view/infoMG_RTG.php';
            loadInputPage(url,htmlName);
            $("#responseResults").html('');
            break;
        case 'MG':
            url = 'http://'+ localhost +'/TestPTAPI/view/infoMG_RTG.php';
            loadInputPage(url,htmlName);
            $("#responseResults").html('');
            break;
        case 'PT':
            url = 'http://'+ localhost +'/TestPTAPI/view/infoPT.php';
            loadInputPage(url,htmlName);
            $("#responseResults").html('');
            break;  
    }
}

function getInputURL5()
{
    var usermode = document.getElementById('usermode').value;
    var url;
    var htmlName = '#createInputs';
    var localhost = location.host;
    switch(usermode){
        case '0':    //terminal-based
            url = 'http://'+ localhost +'/TestPTAPI/view/createAccZero.php';
            loadInputPage(url,htmlName);
            $("#responseResults").html('');
            break;
        case '1':     //user-based
            url = 'http://'+ localhost +'/TestPTAPI/view/createAccOne.php';
            loadInputPage(url,htmlName);
            $("#responseResults").html('');
            break;  
    }
}

function getInputURL6()
{
    var casinoName = document.getElementById('casinoName').value;
    var n=casinoName.search("RTG");
    var url;
    var htmlName = '#CheckTransInputs';
    var localhost = location.host;
    var casino = '';
    
    if(n != -1){
        casino = "RTG";
    } else {
        n = casinoName.search("MG");
        if(n != -1){
            casino = "MG";
        } else {
            n = casinoName.search("PT");
            if(n != -1){
                casino = "PT";
            }
        }
    }
    
    switch(casino){
        case 'RTG':
            url = 'http://'+ localhost +'/TestPTAPI/view/transRTG.php';
            loadInputPage(url,htmlName);
            $("#responseResults").html('');
            break;
        case 'MG':
            url = 'http://'+ localhost +'/TestPTAPI/view/transMG.php';
            loadInputPage(url,htmlName);
            $("#responseResults").html('');
            break;  
        case 'PT':
            url = 'http://'+ localhost +'/TestPTAPI/view/transPT.php';
            loadInputPage(url,htmlName);
            $("#responseResults").html('');
            break;  
    }
}

function getBalanceResult() {
    var casinoNames = document.getElementById('casinoName').value;
    var username = document.getElementById('user').value;
    var localhost = location.host;
    if(getBalance_validate(casinoNames,username) == true){
        window.setInterval(function(){
            $.ajax({
                url: 'http://'+ localhost +'/TestPTAPI/controller/getFunction.php',
                type: "GET",
                data: "casino="+casinoNames+"&user="+username+"&do=getBalance",
                success: function(response){
                        $("#responseResults").html("<b>Results:</b> <br> &nbsp;&nbsp;&nbsp; "+response);
                }
            });
        });
    } else {
        alert('Inputs are incomplete.');
    }
}

function createAccountResults(){
    var usermode = document.getElementById('usermode').value;
    var casinoNames = document.getElementById('casinoName').value;
    var username = document.getElementById('user').value;
    var bdate = document.getElementById('bdate').value;
    var email = document.getElementById('email').value;
    var phone = document.getElementById('phone').value;
    var address = document.getElementById('address').value;
    var city = document.getElementById('city').value;
    var zipcode = document.getElementById('zipcode').value;
    var gender = document.getElementById('gender').value;
    var localhost = location.host;
    var fname = '', lname = '';
    
    if(usermode == '0'){
        var splitted = username.split('-');
        fname = splitted[0];
        lname = splitted[1];

        if(createAccount_validate(casinoNames,username,email,address)){
             window.setInterval(function(){
                $.ajax({
                    url: 'http://'+ localhost +'/TestPTAPI/controller/getFunction.php',
                    type: "GET",
                    data: "casino="+casinoNames+"&user="+username+"&fname="+fname+"&lname="+lname+"&bdate="+bdate+"&gender="+gender+"&email="+email+"&phone="+phone+"&address="+address+"&city="+city+"&zipcode="+zipcode+"&do=createAccount",
                    success: function(response){
                            $("#responseResults").html("<b>Results:</b> <br> &nbsp;&nbsp;&nbsp; "+response);
                    }
                });
            });
        } else {
            alert("Required Inputs are incomplete.");
        }
    } else {
        fname = document.getElementById('fname').value;
        lname = document.getElementById('lname').value;

        if(createAccount_validate(casinoNames,username,email,address)){
             window.setInterval(function(){
                $.ajax({
                    url: 'http://'+ localhost +'/TestPTAPI/controller/getFunction.php',
                    type: "GET",
                    data: "casino="+casinoNames+"&user="+username+"&fname="+fname+"&lname="+lname+"&bdate="+bdate+"&gender="+gender+"&email="+email+"&phone="+phone+"&address="+address+"&city="+city+"&zipcode="+zipcode+"&do=createAccount",
                    success: function(response){
                            $("#responseResults").html("<b>Results:</b> <br> &nbsp;&nbsp;&nbsp; "+response);
                    }
                });
            });
        } else {
            alert("Required Inputs are incomplete.");
        }
    }
    
}

function depositResults(){
    var casinoName = document.getElementById('casinoName').value;
    var username = '', password = '', amount = '', tranID = '';
    var localhost = location.host;
    switch(casinoName){
        case 'RTG':
            username = document.getElementById('user').value;
            amount = document.getElementById('amount').value;
            if(deposit_validate(casinoName,username,password,amount,tranID))
            {
                window.setInterval(function(){
                    $.ajax({
                        url: 'http://'+ localhost +'/TestPTAPI/controller/getFunction.php',
                        type: "GET",
                        data: "casino="+casinoName+"&user="+username+"&amount="+amount+"&do=deposit",
                        success: function(response){
                                $("#responseResults").html("<b>Results:</b> <br> &nbsp;&nbsp;&nbsp; "+response);
                        }
                    });
                });
            } else {
                alert('Inputs are incomplete.');
            }
            break;
        case 'MG':
            username = document.getElementById('user').value;
            password = document.getElementById('pw').value;
            amount = document.getElementById('amount').value;
            tranID = document.getElementById('tranID').value;
            if(deposit_validate(casinoName,username,password,amount,tranID))
            {
                window.setInterval(function(){
                    $.ajax({
                        url: 'http://'+ localhost +'/TestPTAPI/controller/getFunction.php',
                        type: "GET",
                        data: "casino="+casinoName+"&user="+username+"&password="+password+"&amount="+amount+"&tranID="+tranID+"&do=deposit",
                        success: function(response){
                                $("#responseResults").html("<b>Results:</b> <br> &nbsp;&nbsp;&nbsp; "+response);
                        }
                    });
                });
            } else {
                alert('Inputs are incomplete.');
            }
            break;
        case 'PT':
            username = document.getElementById('user').value;
            password = document.getElementById('pw').value;
            amount = document.getElementById('amount').value;
            tranID = document.getElementById('tranID').value;
            if(deposit_validate(casinoName,username,password,amount,tranID))
            {
                window.setInterval(function(){
                    $.ajax({
                        url: 'http://'+ localhost +'/TestPTAPI/controller/getFunction.php',
                        type: "GET",
                        data: "casino="+casinoName+"&user="+username+"&password="+password+"&amount="+amount+"&tranID="+tranID+"&do=deposit",
                        success: function(response){
                                $("#responseResults").html("<b>Results:</b> <br> &nbsp;&nbsp;&nbsp; "+response);
                        }
                    });
                });
            } else {
                alert('Inputs are incomplete.');
            }
            break;
    }
}

function withdrawalResults()
{
    var casinoName = document.getElementById('casinoName').value;
    var username = '', password = '',  tranID = '';
    var localhost = location.host;
    switch(casinoName){
        case 'RTG':
            username = document.getElementById('user').value;
            if(withdrawal_validate(casinoName,username,password,tranID))
            {
                window.setInterval(function(){
                    $.ajax({
                        url: 'http://'+ localhost +'/TestPTAPI/controller/getFunction.php',
                        type: "GET",
                        data: "casino="+casinoName+"&user="+username+"&do=withdraw",
                        success: function(response){
                                $("#responseResults").html("<b>Results:</b> <br> &nbsp;&nbsp;&nbsp; "+response);
                        }
                    });
                });
            } else {
                alert('Inputs are incomplete.');
            }
            break;
        case 'MG':
            username = document.getElementById('user').value;
            password = document.getElementById('pw').value;
            tranID = document.getElementById('tranID').value;
            if(withdrawal_validate(casinoName,username,password,tranID))
            {
                window.setInterval(function(){
                    $.ajax({
                        url: 'http://'+ localhost +'/TestPTAPI/controller/getFunction.php',
                        type: "GET",
                        data: "casino="+casinoName+"&user="+username+"&password="+password+"&tranID="+tranID+"&do=withdraw",
                        success: function(response){
                                $("#responseResults").html("<b>Results:</b> <br> &nbsp;&nbsp;&nbsp; "+response);
                        }
                    });
                });
            } else {
                alert('Inputs are incomplete.');
            }
            break;
        case 'PT':
            username = document.getElementById('user').value;
            password = document.getElementById('pw').value;
            tranID = document.getElementById('tranID').value;
            if(withdrawal_validate(casinoName,username,password,tranID))
            {
                window.setInterval(function(){
                    $.ajax({
                        url: 'http://'+ localhost +'/TestPTAPI/controller/getFunction.php',
                        type: "GET",
                        data: "casino="+casinoName+"&user="+username+"&password="+password+"&tranID="+tranID+"&do=withdraw",
                        success: function(response){
                                $("#responseResults").html("<b>Results:</b> <br> &nbsp;&nbsp;&nbsp; "+response);
                        }
                    });
                });
            } else {
                alert('Inputs are incomplete.');
            }
            break;
    }
}

function pendingGamesResults()
{
    var  password = '',  tranID = '';
    var casinoName = "RTG";
    var username = document.getElementById('user').value;
    var localhost = location.host;
    if(withdrawal_validate(casinoName,username,password,tranID))
    {
        window.setInterval(function(){
            $.ajax({
                url: 'http://'+ localhost +'/TestPTAPI/controller/getFunction.php',
                type: "GET",
                data: "casino="+casinoName+"&user="+username+"&do=PendingGames",
                success: function(response){
                        $("#responseResults").html("<b>Results:</b> <br> &nbsp;&nbsp;&nbsp; "+response);
                }
            });
        });
    } else {
        alert('Inputs are incomplete.');
    }
}

function freezeResults(){
    var casinoName = document.getElementById('casinoName').value;
    var username = document.getElementById('user').value;
    var accstatus = "";
    var localhost = location.host;
    switch(casinoName){
        case "PT":
            accstatus = document.getElementById('accstats').value;
            break;
    }
    
    if(freeze_validate(casinoName,username,accstatus)){
        window.setInterval(function(){
            $.ajax({
                url: 'http://'+ localhost +'/TestPTAPI/controller/getFunction.php',
                type: "GET",
                data: "casino="+casinoName+"&user="+username+"&status="+accstatus+"&do=UnlockAcc",
                success: function(response){
                        $("#responseResults").html("<b>Results:</b> <br> &nbsp;&nbsp;&nbsp; "+response);
                }
            });
        });
    } else {
        alert('Inputs are incomplete.');
    }
}

function getInfoResults(){
    var casinoName = document.getElementById('casinoName').value;
    var username = document.getElementById('user').value;
    var password = '';
    var localhost = location.host;
    switch(casinoName){
        case 'PT':
            password = document.getElementById('pw').value;
            break;
    }
    
    if(getInfo_validate(casinoName,username,password)){
        window.setInterval(function(){
            $.ajax({
                url: 'http://'+ localhost +'/TestPTAPI/controller/getFunction.php',
                type: "GET",
                data: "casino="+casinoName+"&user="+username+"&password="+password+"&do=ViewAcc",
                success: function(response){
                        $("#responseResults").html("<b>Results:</b> <br> &nbsp;&nbsp;&nbsp; "+response);
                }
            });
        });
    } else {
        alert('Inputs are incomplete.');
    }
    
}

function revertBrokenGames(){
    var username = document.getElementById('user').value;   
    var localhost = location.host;
    if(username != null){
        window.setInterval(function(){
            $.ajax({
                url: 'http://'+ localhost +'/TestPTAPI/controller/getFunction.php',
                type: "GET",
                data: "user="+username+"&do=RevertBrokenGames",
                success: function(response){
                        $("#responseResults").html("<b>Results:</b> <br> &nbsp;&nbsp;&nbsp; "+response);
                }
            });
        });
    } else {
        alert('Inputs are incomplete.');
    }
}

function checkTransactionResults()
{
    var casinoName = document.getElementById('casinoName').value;
    var tranID = document.getElementById('tranID').value;
    var n = casinoName.search("RTG");
    var username = '', casino = '';
    var localhost = location.host;
    
    if(n != -1){
        casino = "RTG";
    } else {
        n = casinoName.search("MG");
        if(n != -1){
            casino = "MG";
        } else {
            n = casinoName.search("PT");
            if(n != -1){
                casino = "PT";
            }
        }
    }

    switch(casino){
        case 'RTG':
            username = document.getElementById('user').value;
            if(tranID != '' && username != '')
            {
                window.setInterval(function(){
                    $.ajax({
                        url: 'http://'+ localhost +'/TestPTAPI/controller/getFunction.php',
                        type: "GET",
                        data: "casino="+casinoName+"&user="+username+"&do=CheckTrans",
                        success: function(response){
                                $("#responseResults").html("<b>Results:</b> <br> &nbsp;&nbsp;&nbsp; "+response);
                        }
                    });
                });
            } else {
                alert('Inputs are incomplete.');
            }
            break;
        case 'MG':
            if(tranID != '')
            {
                window.setInterval(function(){
                    $.ajax({
                        url: 'http://'+ localhost +'/TestPTAPI/controller/getFunction.php',
                        type: "GET",
                        data: "casino="+casinoName+"&tranID="+tranID+"&do=CheckTrans",
                        success: function(response){
                                $("#responseResults").html("<b>Results:</b> <br> &nbsp;&nbsp;&nbsp; "+response);
                        }
                    });
                });
            } else {
                alert('Inputs are incomplete.');
            }
            break;
        case 'PT':
            if(tranID != '')
            {
                window.setInterval(function(){
                    $.ajax({
                        url: 'http://'+ localhost +'/TestPTAPI/controller/getFunction.php',
                        type: "GET",
                        data: "casino="+casinoName+"&tranID="+tranID+"&do=CheckTrans",
                        success: function(response){
                                $("#responseResults").html("<b>Results:</b> <br> &nbsp;&nbsp;&nbsp; "+response);
                        }
                    });
                });
            } else {
                alert('Inputs are incomplete.');
            }
            break;
    }
}
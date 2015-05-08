<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Creation of Virtual Cashier</title>
    </head>
    <script type="text/javascript" src="jscripts/jquery-1.4.1.js"></script>
        <script type="text/javascript">
        $(document).ready(function()
        {
            
            jQuery("#btnconfirm").click(function()
            {
                document.getElementById('loading').style.display='block';
                document.getElementById('fade').style.display='block';
                var url = 'process/ProcessCreateVCashier.php';
               jQuery.ajax(
                {
                   url: url,
                   type: 'post',
                   data: {page: function(){ return "CreateVirtualCashier";}
                         },
                   dataType : 'json',     
                   success: function(data)
                   {
                      document.getElementById('loading').style.display='none';
                      document.getElementById('fade').style.display='none';
                      alert(data); 
                   },
                   error: function(XMLHttpRequest, e)
                   {
                         alert(XMLHttpRequest.responseText);
                         if(XMLHttpRequest.status == 401)
                         {
                             window.location.reload();
                         }
                   }
                });
            });
            jQuery("#btnconfirm_ewallet").click(function()
            {
                document.getElementById('loading').style.display='block';
                document.getElementById('fade').style.display='block';
                var url = 'process/ProcessCreateVCashier.php';
               jQuery.ajax(
                {
                   url: url,
                   type: 'post',
                   data: {page: function(){ return "CreateVirtualCashierEwallet";}
                         },
                   dataType : 'json',     
                   success: function(data)
                   {
                      document.getElementById('loading').style.display='none';
                      document.getElementById('fade').style.display='none';
                      alert(data); 
                   },
                   error: function(XMLHttpRequest, e)
                   {
                         alert(XMLHttpRequest.responseText);
                         if(XMLHttpRequest.status == 401)
                         {
                             window.location.reload();
                         }
                   }
                });
            });
        });
        </script>
        <style> 
div
{
width:500px;
height:300px;
background-color:#FFFFDB;
box-shadow: 10px 10px 5px #888888;
}


    .myButton {
        
        -moz-box-shadow: 0px 1px 0px 0px #fff6af;
        -webkit-box-shadow: 0px 1px 0px 0px #fff6af;
        box-shadow: 0px 1px 0px 0px #fff6af;
        
        background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #ffec64), color-stop(1, #ffab23));
        background:-moz-linear-gradient(top, #ffec64 5%, #ffab23 100%);
        background:-webkit-linear-gradient(top, #ffec64 5%, #ffab23 100%);
        background:-o-linear-gradient(top, #ffec64 5%, #ffab23 100%);
        background:-ms-linear-gradient(top, #ffec64 5%, #ffab23 100%);
        background:linear-gradient(to bottom, #ffec64 5%, #ffab23 100%);
        filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffec64', endColorstr='#ffab23',GradientType=0);
        
        background-color:#ffec64;
        
        -moz-border-radius:6px;
        -webkit-border-radius:6px;
        border-radius:6px;
        
        border:1px solid #ffaa22;
        
        display:inline-block;
        color:#333333;
        font-family:Courier New;
        font-size:15px;
        font-weight:bold;
        padding:6px 24px;
        text-decoration:none;
        
        text-shadow:0px 1px 0px #ffee66;
        
    }
    .myButton:hover {
        
        background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #ffab23), color-stop(1, #ffec64));
        background:-moz-linear-gradient(top, #ffab23 5%, #ffec64 100%);
        background:-webkit-linear-gradient(top, #ffab23 5%, #ffec64 100%);
        background:-o-linear-gradient(top, #ffab23 5%, #ffec64 100%);
        background:-ms-linear-gradient(top, #ffab23 5%, #ffec64 100%);
        background:linear-gradient(to bottom, #ffab23 5%, #ffec64 100%);
        filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffab23', endColorstr='#ffec64',GradientType=0);
        
        background-color:#ffab23;
    }
    .myButton:active {
        position:relative;
        top:1px;
    }
    
    .black_overlay
{
			display: none;
			position: fixed;
			top: 0%;
			left: 0%;
			width: 100%;
			height: 100%;
			background-color: black;
			z-index:1001;
			-moz-opacity: 0.8;
			opacity:.80;
			filter: alpha(opacity=80);
}
    
    #loading{
                position: fixed;
                z-index: 5000;
                background: url('images/Loading.gif') no-repeat;
                height: 300px;
                width: 300px;
                margin: -40px 570px;
                display: none;
}


</style>
<body><center>
        <br/>
        <div>
            <center>
                <br/>
                <h3>Creation of Virtual Cashier</h3>
                <br/><br/>
        <input type="button" class="myButton" value="Create Virtual Cashier for EGM" id="btnconfirm" title="Creates virtual cashiers for EGM"/>
        <br /><br />
        <input type="button" class="myButton" value="Create Virtual Cashier for eWallet" id="btnconfirm_ewallet" title="Creates virtual cashiers for eWallet"/>
            </center>
        </div>
        <div id="fade" class="black_overlay" oncontextmenu="return false"></div>
        <div id="loading"></div>
        </center>
    </body>
</html>

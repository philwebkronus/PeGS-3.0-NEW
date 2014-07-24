<script type="text/javascript" src="jscripts/jquery.min.js"></script>
<script type="text/javascript" src="jscripts/jquery-helpers.js"></script>

<div id="output">
    <div id="gb_header">&nbsp;&nbsp; PLAYER API</div><br />
        <div id="inputs">
            <div id="un_input">
                <label>Method: </label>
                <div id="inputBox">
                    <select name="method" id="method">
                       <option value="-1">Select One</option>
                       <option value="1">Create Account</option>
                       <option value="2">Change Password</option>
                       <option value="3">Get Player Class ID</option>
                       <option value="4">Change Player Class ID</option>
                    </select>
                </div>
            </div>
            <div id="un_input">
                <label>Username: </label>
                <div id="inputBox">
                    <input type="text" name="user" id="user" value=""/>
                </div>
            </div>
            <div id="un_input">
                <label>Password: </label>
                <div id="inputBox">
                    <input type="text" name="pw" id="pw" value=""/>
                </div>
            </div>
            <div id="un_input">
                <label>Password: </label>
                <div id="inputBox">
                    <input type="text" name="newpw" id="newpw" value=""/>
                </div>
            </div>
            <div id="un_input">
                <label>Email: </label>
                <div id="inputBox">
                    <input type="text" name="email" id="email" value=""/>
                </div>
            </div>
            <div id="un_input">
                <label>PID: </label>
                <div id="inputBox">
                    <input type="text" name="pid" id="pid" value=""/>
                </div>
            </div>
            <div id="un_input">
                <label>Player Class ID: </label>
                <div id="inputBox">
                    <input type="text" name="playerclassid" id="playerclassid" value=""/>
                </div>
            </div>
            <div id="un_input">
                <input id="btnsubmit" type="submit" value="Submit" class="button" />
            </div>
        </div>
</div>    
    
<script>
$(document).ready(function()
{
    jQuery("#btnsubmit").click(function()
    {
        var username = document.getElementById('user').value;
        var password = document.getElementById('pw').value;
        var newpassword = document.getElementById('newpw').value;
        var email = document.getElementById('email').value;
        var pid = document.getElementById('pid').value;
        var playerclassid = document.getElementById('playerclassid').value;
        var method = document.getElementById('method').value;
        
        
                jQuery.ajax({
                            url: "playercontroller",
                            type: 'post',
                            data: {method: function() {return $("#method").val();},
                                    username: function() {return $("#user").val();},
                                    password: function() {return $("#pw").val();},
                                    newpassword: function() {return $("#newpw").val();},
                                    email: function() {return $("#email").val();},
                                    pid: function() {return $("#pid").val();},
                                    playerclassid: function() {return $("#playerclassid").val();}
                                },
                            dataType : 'json',  
                            success : function(data)
                            {
                                 alert(data);
                            },
                            error : function(XMLHttpRequest, e){
                                alert(XMLHttpRequest.responseText);
                            }
                 });
        
    });

});        
</script>    
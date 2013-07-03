<script language="javascript" type="text/javascript">
    
    $(document).ready(function() {
        
        function loadprofile() {
            $("#home-latest-news").addClass('profile-box');
            $("#carousel").hide();
            //            $("#home-login-box").addClass('profile-wrapper');
        }
        
        window.onload = loadprofile;
        
        function reloadProfile() {
            parent.window.location.href='index.php';
        }
        
        $("#txtPassword").blur(function(){
            txtpass = $('#txtPassword').val();

            if(txtpass != "")
            {
                $('#txtConfirmPassword').addClass('validate[required, custom[onlyLetterNumber], equals[txtPassword]]');
            }
        });
        
        
        
        $('#dtBirthDate').change(function()
        {
            dob1 = $('#dtBirthDate').val();
            dob = new Date(dob1.substr(0, 4), parseInt(dob1.substr(5, 2)) -1, dob1.substr(8, 2));
            var today = new Date();
            var age = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));
            $('#txtAge').val(age);
        });
        
        $("#btnUpdate").click(function() {     
            $("#dialog:ui-dialog").dialog("destroy");
            $("#UpdateProfileDialog").dialog("open");
            
        });
        
        $("#UpdateProfileDialog").dialog({
            autoOpen: false,
            modal: true,
            width: '800',
            title : 'PROFILE UPDATE',
            closeOnEscape: true,
            
            buttons: {
                "Submit": function() {
                    $("#hdnUpdateProfile").val('update');
                    $('#SubForm').submit();
                },
                "Cancel" : function() {
                    $(this).dialog("close");
                }
            },
            
            open: function (event, ui) {
                $(event.target).parent().css('position', 'fixed');
                $(event.target).parent().css('top', '5%');
                $(event.target).parent().css('left', '20%');
            }
        }).parent().appendTo($("#SubForm").validationEngine());
        
        $('#SuccessDialog').dialog({
            autoOpen: <?php echo $isOpen; ?>,
            modal: true,
            width: '400',
            title : 'Update Profile',
            closeOnEscape: true,            
            buttons: {
                "Ok": function() {
                    reloadProfile();
                    $(this).dialog("close");
                }
            }
        });
        
    });                   

</script>
<div id="home-login-box">      
    <div id="home-login-wrapper">
        <!--<div id="home-page-login-form">-->
        <div class="profile">
            <p>Hi <?php echo strtoupper($nick); ?>! [<a href="logout.php">Logout</a>]</p>
            <ul style="list-style: none; margin-left: 10px;">
                <li><strong><?php echo strtoupper($memberName); ?></strong></li>
                <li>Card Number: <?php echo $cardNumber; ?></li>
                <li>Mobile Number: <?php echo $mobileNumber; ?></li>
                <li>Email Address: <?php echo $email; ?></li>
<!--            </ul>
            <ul style="list-style: none;">-->
                <li>Current Points: <?php echo $currentPoints; ?></li>
                <li>Bonus Points: <?php echo $bonusPoints; ?></li>
                <li>Redeemed Points: <?php echo $redeemedPoints; ?></li>
                <li>Lifetime Points: <?php echo $lifetimePoints; ?></li>
            </ul>
            <?php echo $btnUpdate; ?>

            </form> <!-- End form declared in the header -->
        </div>
        <!--</div>-->
    </div>
    <!--</div>-->
    <form name="SubForm" id="SubForm" method="post" action="" enctype="multipart/form-data" >

        <!-- Update Profile page holder -->
        <div id="UpdateProfileDialog" name="UpdateProfileDialog">
            <br /><?php echo $hdnUpdateProfile; ?>
            <table>
                <tr>
                    <td width="20%">First Name*</td>
                    <td width="30%"><?php echo $txtFirstName; ?></td>
                    <td width="20%">Nickname</td>
                    <td width="30%"><?php echo $txtNickName; ?></td>
                </tr>
                <tr>
                    <td>Middle Name</td>
                    <td><?php echo $txtMiddleName; ?></td>
                    <td>Mobile Number*</td>
                    <td><?php echo $txtMobileNumber; ?></td>
                </tr>
                <tr>
                    <td>Last Name*</td>
                    <td><?php echo $txtLastName; ?></td>
                    <td>Alternate Mobile Number</td>
                    <td><?php echo $txtAlternateMobileNumber; ?></td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td><?php echo $txtPassword; ?></td>
                    <td>Email Address*</td>
                    <td><?php echo $txtEmail; ?></td>
                </tr>
                <tr>
                    <td>Confirm Password</td>
                    <td><?php echo $txtConfirmPassword; ?></td>
                    <td>Alternate Email</td>
                    <td><?php echo $txtAlternateEmail; ?></td>
                </tr>
                <tr>
                    <td>Permanent Address</td>
                    <td><?php echo $txtAddress1; ?><br/>
                        <?php echo $txtAddress2; ?><br/></td>
                    <td>Gender</td>
                    <td><?php echo $rdoGroupGender->Radios[0]; ?><?php echo $rdoGroupGender->Radios[1]; ?></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td>Birthdate*</td>
                    <td><?php echo $dtBirthDate; ?></td>
                </tr>      

                <tr>
            <!--        <td colspan="2">Attached scanned ID/<br />Supporting Documents <br />
                        <input type="file" name="ScannedID" id="ScannedID" value="" /><br />
                        <em><php echo $memberfile; ?></em>
                    </td>-->
                    <td>ID Presented*</td>
                    <td><?php echo $txtIDPresented; ?><br/>
                        <?php echo $cboIDSelection; ?></td>
                    <td>Age</td>
                    <td><?php echo $txtAge; ?></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td>Nationality</td>
                    <td><?php echo $cboNationality; ?></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td>Occupation</td>
                    <td><?php echo $cboOccupation; ?></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td><?php echo $rdoGroupSmoker->Radios[0]; ?></td>
                    <td><?php echo $rdoGroupSmoker->Radios[1]; ?></td>
                </tr>

            </table>
        </div>

        <div id="SuccessDialog" name="SuccessDialog">
            <?php if ($isOpen == 'true')
            { ?>
                <?php if ($isSuccess)
                { ?>
                    <p>
                        You have successfully updated your profile.
                    </p>
                    <?php
                }
                else
                {
                    ?>
                    <p>
                        Update profile failed.
                    </p>
                <?php } ?>
            <?php } ?>
        </div>

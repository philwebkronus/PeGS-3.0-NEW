<?php Mirage::loadLibraries(array('CardScripts', 'LoyaltyScripts')); ?>

<link type="text/css" href="css/le-frog/jquery-ui-1.8.16.custom.css" rel="stylesheet" />
<script type="text/javascript" src="jscripts/jquery-ui-1.8.16.custom.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {


        $('#verifycardbutton').click(function() {
            var cardNumber = $('#loyaltycard').val();
            if (cardNumber == '')
            {
                alert('Please enter loyalty card number.');
            }
            if ($('#isValidated').val() == 1) {
                $('#fname').focus();
                $('#registration').css("display", "block");
                $('#verifycardbutton').attr('disabled', 'disabled');
		                $('#loyaltycard').attr('disabled', 'disabled');
            }

        });

        $('#loyaltycard').bind('keydown', function(event) {
            if (event.keyCode == 13 || event.charCode == 13 || event.keyCode == 9)
            {
                $('#isValidated').val('0');
                var cardNumber = $('#loyaltycard').val();
                if (cardNumber == '')
                {
                    alert('Please enter loyalty card number.');
                    return false;
                }
                var issuccess = identifyCardRegisterMember();
                if (issuccess === "false")
                {
                    $('#isValidated').val('1');
                    $('#verifycardbutton').focus();
                }

            }

            if (event.keyCode != 9)
            {
                isEwalletSessionMode = false;
                isValidated = false;

                $('#verifycardbutton').removeAttr('disabled');
                $('#registration').css("display", "none");
            }
        });


        $('#birthdate').datepicker({
            inline: true,
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm-dd',
            maxDate: '<?php echo date('Y-m-d') ?>',
            minDate: '<?php echo date("Y-m-d", strtotime('1900-01-01')); ?>'
        });


        $('#usedefault').click(function() {

            var x = $("#usedefault").is(":checked");
            if (x === true) {
                $("#barangay").val($("#barangay_address").val());
                $("#city").val($("#city_id").val());
                $("#region").val($("#region_id").val());
                $("#barangay").attr('disabled', 'disabled');
                $("#city").attr('disabled', 'disabled');
                $("#region").attr('disabled', 'disabled');
            }
            else {
                $("#barangay").removeAttr('disabled');
                $("#city").removeAttr('disabled');
                $("#region").removeAttr('disabled');
            }

        });
        $('#registerbutton').click(function() {

            var validation = CheckForBlank();
            if (validation === true) {

                $("#registerbutton").attr('disabled', 'disabled');
                var url = '<?php echo Mirage::app()->createUrl('registermember/overview') ?>';
                var data = {
                    fname: function() {
                        return $("#fname").val();
                    },
                    mname: function() {
                        return $("#mname").val();
                    },
                    lname: function() {
                        return $("#lname").val();
                    },
                    nname: function() {
                        return $("#nname").val();
                    },
                    password: function() {
                        return $("#password").val();
                    },
                    barangay: function() {
                        return $("#barangay").val();
                    },
                    city: function() {
                        return $("#city").val();
                    },
                    region: function() {
                        return $("#region").val();
                    },
                    mobilenumber: function() {
                        return $("#mobilenumber").val();
                    },
                    altermobilenumber: function() {
                        return $("#altermobilenumber").val();
                    },
                    emailaddress: function() {
                        return $("#emailaddress").val();
                    },
                    alteremailaddress: function() {
                        return $("#alteremailaddress").val();
                    },
                    gender: function() {
                        return $("#gender").val();
                    },
                    idpresented: function() {
                        return $("#idpresented").val();
                    },
                    idnumber: function() {
                        return $("#idnumber").val();
                    },
                    nationality: function() {
                        return $("#nationality").val();
                    },
                    birthdate: function() {
                        return $("#birthdate").val();
                    },
                    occupation: function() {
                        return $("#occupation").val();
                    },
                    issmoker: function() {
                        return $("#issmoker").val();
                    },
                    refferalcode: function() {
                        return $("#refferalcode").val();
                    },
                    referrer: function() {
                        return $("#referrer").val();
                    },
                    EmailSubscription: function() {
                        var emailVal = 0;
                        var x = $("#EmailSubscription").is(":checked");
                        if (x === true) {
                            emailVal = 1;
                        }
                        return emailVal;
                    },
                    SMSSubscription: function() {
                        var smsVal = 0;
                        var x = $("#SMSSubscription").is(":checked");
                        if (x === true) {
                            smsVal = 1;
                        }
                        return smsVal;
                    },
                    civilstatus: function() {
                        return $("#civilstatus").val();
                    },
                    registerfor: function() {
                        return $("#registerfor").val();
                    },
                    loyaltycard: function() {
                        return $("#loyaltycard").val();
                    },
		};

  		showLightbox(function() {
                    $.ajax({
                        url: url,
                        type: 'post',
                        data: data,
                        success: function(data) {
                            try {
                                updateLightbox(data);
                            } catch (e) {
                                alert('Oops! Something went wrong');
                                hideLightbox();
                                location.reload(true);
                            }
                            $("#registerbutton").removeAttr('disabled');
                        },
                        error: function(e) {
                            displayError(e);
                            $("#registerbutton").removeAttr('disabled');
                            hideLightbox();
                        }
                    });
                });
            }
        });
    });</script>


<script type="text/javascript">
    function CheckForBlank() {
        if ($.trim(document.getElementById('fname').value) === "" || $.trim(document.getElementById('fname').value) === null) {
            alert("Please input First Name.");
            document.getElementById('fname').focus();
            return false;
        }
        if ($.trim(document.getElementById('mname').value) === "" || $.trim(document.getElementById('mname').value) === null) {
            alert("Please input Middle Name.");
            document.getElementById('mname').focus();
            return false;
        }
        if ($.trim(document.getElementById('lname').value) === "" || $.trim(document.getElementById('lname').value) === null) {
            alert("Please input Last Name.");
            document.getElementById('lname').focus();
            return false;
        }
        if ($.trim(document.getElementById('nname').value) === "" || $.trim(document.getElementById('nname').value) === null) {
            alert("Please input Nick Name.");
            document.getElementById('nname').focus();
            return false;
        }
        if ($.trim(document.getElementById('password').value) === "" || $.trim(document.getElementById('password').value) === null) {
            alert("Please input Password.");
            document.getElementById('password').focus();
            return false;
        }
        if ($.trim(document.getElementById('password').value).length < 5) {
            alert("Password should not be less than 5 characters long.");
            document.getElementById('password').focus();
            return false;
        }
        if ($.trim(document.getElementById('cpassword').value) === "" || $.trim(document.getElementById('cpassword').value) === null) {
            alert("Please input Confirm Password.");
            document.getElementById('cpassword').focus();
            return false;
        }
        if ($.trim(document.getElementById('password').value).length < 5) {
            alert("Confirm Password should not be less than 5 characters long.");
            document.getElementById('password').focus();
            return false;
        }
        if ($.trim(document.getElementById('password').value).toLowerCase() !== $.trim(document.getElementById('cpassword').value).toLowerCase()) {
            alert("Password and Confirm Password Does not Match.");
            document.getElementById('password').focus();
            return false;
        }
        if ($.trim(document.getElementById('mobilenumber').value) === "" || $.trim(document.getElementById('mobilenumber').value) === null) {
            alert("Please input Mobile Number.");
            document.getElementById('mobilenumber').focus();
            return false;
        }
        if ($.trim(document.getElementById('emailaddress').value) === "" || $.trim(document.getElementById('emailaddress').value) === null) {
            alert("Please input Email Address.");
            document.getElementById('emailaddress').focus();
            return false;
        }
        if ($.trim(document.getElementById('birthdate').value) === "" || $.trim(document.getElementById('birthdate').value) === null) {
            alert("Please select Birthdate.");
            document.getElementById('birthdate').focus();
            return false;
        }

        if ($.trim(document.getElementById('gender').value) === "" || $.trim(document.getElementById('gender').value) === null) {
            alert("Please select Gender.");
            document.getElementById('gender').focus();
            return false;
        }
        if ($.trim(document.getElementById('civilstatus').value) === "" || $.trim(document.getElementById('civilstatus').value) === null) {
            alert("Please select Civil Status.");
            document.getElementById('civilstatus').focus();
            return false;
        }
        if ($.trim(document.getElementById('nationality').value) === "" || $.trim(document.getElementById('nationality').value) === null) {
            alert("Please select Nationality.");
            document.getElementById('nationality').focus();
            return false;
        }
        if ($.trim(document.getElementById('occupation').value) === "" || $.trim(document.getElementById('occupation').value) === null) {
            alert("Please input Occupation.");
            document.getElementById('occupation').focus();
            return false;
        }
        if ($.trim(document.getElementById('barangay').value) === "" || $.trim(document.getElementById('barangay').value) === null) {
            alert("Please input Barangay.");
            document.getElementById('barangay').focus();
            return false;
        }
        if ($.trim(document.getElementById('city').value) === "" || $.trim(document.getElementById('city').value) === null) {
            alert("Please select City.");
            document.getElementById('city').focus();
            return false;
        }
        if ($.trim(document.getElementById('region').value) === "" || $.trim(document.getElementById('region').value) === null) {
            alert("Please select Region.");
            document.getElementById('region').focus();
            return false;
        }
        if ($.trim(document.getElementById('idpresented').value) === "" || $.trim(document.getElementById('idpresented').value) === null) {
            alert("Please select Presented ID.");
            document.getElementById('idpresented').focus();
            return false;
        }
        if ($.trim(document.getElementById('idnumber').value) === "" || $.trim(document.getElementById('idnumber').value) === null) {
            alert("Please input ID Number.");
            document.getElementById('idnumber').focus();
            return false;
        }
        if ($.trim(document.getElementById('issmoker').value) === "" || $.trim(document.getElementById('issmoker').value) === null) {
            alert("Please select if You are smoker or not.");
            document.getElementById('issmoker').focus();
            return false;
        }

        if ($.trim(document.getElementById('referrer').value) === "" || $.trim(document.getElementById('referrer').value) === null) {
            alert("Please select How did you know e-Games.");
            document.getElementById('referrer').focus();
            return false;
        }

        if ($.trim(document.getElementById('registerfor').value) === "" || $.trim(document.getElementById('registerfor').value) === null) {
            alert("Please select Why you register on e-Games.");
            document.getElementById('registerfor').focus();
            return false;
        }
        return true;
    }


</script>
<style>
    input[type=text], input[type=password]{
        border-radius: 4px;
        width: 100%;
        margin: 8px 0;
        box-sizing: border-box;
    }
    #registerbutton {
        width: 100%;
        background-color: #4CAF50;
        border: none;
        color: white;
        padding: 16px 32px;
        text-decoration: none;
        margin: 4px 2px;
        cursor: pointer;
    }

</style>
<div id="card" style="margin-left: 300px;">
    <br><br>
    <p>Input Card Number</p>
    <input type="hidden" name="isValidated" id="isValidated" value="0"/>
    <input id='loyaltycard' name="loyaltycard" required style="width: 260px;">
    <input type="submit" id="verifycardbutton" value="Submit">
</div>
<div id="registration" style="display: none;">
    <br><br>
    <b style="color: red;"><p style="margin-left: 300px;width : 600px;">Please fill up the following information:</p></b>
    <br>
    <div style="margin-left: 300px;width : 600px;">
        <fieldset border = '2' >
            <legend> <b> Personal Information: </b></legend>
            <table style ="width: 600px;
                   padding: 10px;
                   ">
                <tr>
                    <td>
                        First Name*
                    </td>
                    <td>
                        <input id='fname' name="fname" required>
                    </td>
                </tr>
                <tr>
                    <td>
                        Middle Name*
                    </td>
                    <td>
                        <input id='mname' name="mname" required>
                    </td>
                </tr>
                <tr>
                    <td>
                        Last Name*
                    </td>
                    <td>
                        <input id='lname' name="lname" required>
                    </td>
                </tr>
                <tr>
                    <td>
                        NickName*
                    </td>
                    <td>
                        <input id='nname' name="nname" required>
                    </td>
                </tr>
                <tr>
                    <td>
                        Password*
                    </td>
                    <td>
                        <input type="password" id='password' name="password" required>
                    </td>
                </tr>
                <tr>
                    <td>
                        Confirm Password*
                    </td>
                    <td>
                        <input type="password"  id='cpassword' name="cpassword" required>
                    </td>
                </tr>
                <tr>
                    <td>
                        Mobile No.*
                    </td>
                    <td>
                        <input id='mobilenumber' name="mobilenumber" required >
                    </td>
                </tr>
                <tr>
                    <td>
                        Alternate Mobile No.
                    </td>
                    <td>
                        <input id='altermobilenumber' name="altermobilenumber" required>
                    </td>
                </tr>
                <tr>
                    <td>
                        Email Address*
                    </td>
                    <td>
                        <input id='emailaddress' name="emailaddress" required>
                    </td>
                </tr>
                <tr>
                    <td>
                        Alternate Email Address
                    </td>
                    <td>
                        <input id='alteremailaddress' name="emailaddress" required>
                    </td>
                </tr>
                <tr>
                    <td>
                        Birthdate*
                    </td>
                    <td>
                        <input name='birthdate' id='birthdate' readonly value="<?php echo date("Y-m-d"); ?>" />
                    </td>
                </tr>
                <tr>
                    <td>
                        Gender*
                    </td>

                    <td>
                        <select id="gender" name="gender" required>
                            <?php
                            foreach ($GenderArray as $key => $value) {
                                foreach ($value as $key) {
                                    echo '<option value="' . $key->GenderID . '">' . $key->GenderDescription . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        Civil Status*
                    </td>
                    <td>
                        <select id="civilstatus" name="civilstatus" required>
                            <?php
                            foreach ($GetCivilStatusArray as $key => $value) {
                                foreach ($value as $key) {
                                    echo '<option value="' . $key->CivilStatusID . '">' . $key->CivilStatusDescription . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        Nationality*
                    </td>
                    <td>
                        <select id="nationality" name="nationality" required>
                            <?php
                            foreach ($GetNationalityArray as $key => $value) {
                                foreach ($value as $key) {
                                    echo '<option value="' . $key->NationalityID . '">' . $key->NationalityDescription . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        Occupation*
                    </td>
                    <td>
                        <select id="occupation" name="occupation" required>
                            <?php
                            foreach ($GetOccupationArray as $key => $value) {
                                foreach ($value as $key) {
                                    echo '<option value="' . $key->OccupationID . '">' . $key->OccupationDescription . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>

            </table>
        </fieldset>

        <br><br>
        <fieldset border='2'>
            <legend><b>Address:</b></legend>
            <table style="width: 600px; padding: 10px;">
                <tr>
                    <td>
                        Region*
                    </td>
                    <td>
                        <select id="region" name="region" required>
                          <option value="">---------Select Region--------</option>
			    <?php
                            foreach ($RegionArray as $key => $value) {
                                foreach ($value as $key) {
                                    echo '<option value="' . $key->RegionID . '">' . $key->RegionDescription . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        City*
                    </td>
                    <td>
                        <select id="city" name="city" required>
                            <option value="">---------Select City--------</option>
                            <?php
                            foreach ($CityArray as $key => $value) {
                                foreach ($value as $key) {
                                    echo '<option value="' . $key->CityID . '">' . $key->CityDescription . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        Barangay*
                    </td>
                    <td>
                        <input id='barangay' name="barangay" required>
                    </td>
                </tr>
                <tr>
                    <td>
                        &nbsp;
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" name="usedefault" id="usedefault" required ><i style="font-size: 15px;">Use Default Address</i>
                    </td>
                </tr>
            </table>
        </fieldset>


        <br><br>
        <fieldset border='2'>
            <legend><b>Other Details:</b></legend>
            <table style="width: 600px; padding: 10px;">
                <tr>
                    <td>
                        ID Presented*
                    </td>
                    <td>
                        <select id='idpresented' name='idpresented' required>
                            <?php
                            foreach ($IDPresentedArray as $key => $value) {
                                foreach ($value as $key) {
                                    echo '<option value="' . $key->PresentedID . '">' . $key->PresentedIDDescription . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        ID Number*
                    </td>
                    <td>
                        <input id='idnumber' name='idnumber' required>
                    </td>
                </tr>
                <tr>
                    <td>
                        Referral Code
                    </td>
                    <td>
                        <input id='refferalcode' name="refferalcode" required>
                    </td>
                </tr>
                <tr>
                    <td>
                        Smoker*
                    </td>
                    <td>
                        <select id="issmoker" name="issmoker" required>
                            <?php
                            foreach ($GetIsSmokerArray as $key => $value) {
                                foreach ($value as $key) {
                                    echo '<option value="' . $key->IsSmokerID . '">' . $key->IsSmokerDescription . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        How did you learn about e-Games?
                    </td>
                    <td>
                        <select id="referrer" name="referrer" required>
                            <?php
                            foreach ($GetReferrerArray as $key => $value) {
                                foreach ($value as $key) {
                                    echo '<option value="' . $key->ReferrerID . '">' . $key->ReferrerDescription . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        What to play after registration?
                    </td>
                    <td>
                        <select id="registerfor" name="registerfor" required>
                            <?php
                            foreach ($GetRegisterForArray as $key => $value) {
                                foreach ($value as $key) {
                                    echo '<option value="' . $key->RegisterForID . '">' . $key->RegisterForDescription . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </table>
        </fieldset>
        <br>
        <input type="checkbox" name="EmailSubscription" value="EmailSubscription" required>Please send me offers, bonuses and announcements by email.<br>
        <input type="checkbox" name="SMSSubscription" value="SMSSubscription" required>Please send me offers, bonuses and casino announcements by SMS.<br>
        <br>
        <input type="hidden" id="site_id" name="site_id" value="<?php echo $this->site_id; ?>">
        <input type="hidden" id="region_id" name="region_id" value="<?php echo $siteDetails['RegionID']; ?>">
        <input type="hidden" id="city_id" name="city_id" value="<?php echo $siteDetails['CityID']; ?>">
        <input type="hidden" id="barangay_address" name="barangay_address" value="<?php echo $siteDetails['SiteAddress']; ?>">
        <input type="submit" id="registerbutton" value="Submit">
    </div>
    <br><br>
</div>


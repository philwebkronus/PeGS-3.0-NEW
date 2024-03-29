<?php
//var_dump(Yii::app()->user->getState("acctype"));
//var_dump(Yii::app()->user->isGuest);
//exit;
$this->pageTitle = Yii::app()->name." - Manage Partners";
?>
<script type='text/javascript'>
//Display Partner's Details in Edit DialogBox
//Added by: Mark Kenneth Esguerra | Sep-17-13
//EDIT LINK
$("#editlinkid").live("click",function(){
    //Get Details
    var partnerID = $(this).attr('PartnerID');
    var partnerName = $(this).attr('PartnerName');
    var companyAddress = $(this).attr('CompanyAddress');
    var companyEmail = $(this).attr('CompanyEmail');
    var companyPhone = $(this).attr('CompanyPhone');
    var companyFax = $(this).attr('CompanyFax');
    var companyWebsite = $(this).attr('CompanyWebsite');
    var contactPerson = $(this).attr('ContactPerson');
    var contactPersonPosition = $(this).attr('ContactPersonPosition');
    var contactPersonPhone = $(this).attr('ContactPersonPhone');
    var contactPersonMobile = $(this).attr('ContactPersonMobile');
    var contactPersonEmail = $(this).attr('ContactPersonEmail');
    var numberOfRewardOffers = $(this).attr('NumberOfRewardOffers');
    var status = $(this).attr('Status');
    
    //Put in the respective fields
    $("#PartnerID2").val(partnerID);
    $("#Partner2").val(partnerName);
    $("#companyAddress2").val(companyAddress);
    $("#PNumber2").val(companyPhone);
    $("#FNumber2").val(companyFax);
    $("#EmailAddress2").val(companyEmail);
    $("#Website2").val(companyWebsite);
    $("#ContactPerson2").val(contactPerson);
    $("#ContactPosition2").val(contactPersonPosition);
    $("#ContactEmailAddress2").val(contactPersonEmail);
    $("#ContactPhoneNumber2").val(contactPersonPhone);
    $("#ContactMobile2").val(contactPersonMobile);
    $("#NumberOfRewardOfferings2").val(numberOfRewardOffers);
    $("#Status2").val(status).attr("selected", "selected");
    $("#LastStatus2").val(status);

    $("#editPartner2ndDialog-compdtls").dialog("open");
    return false;
});
//PARTNAME LINK
$("#partnerNameLink").live("click",function(){
    //Get Details
    var partnerID = $(this).attr('PartnerID');
    var partnerName = $(this).attr('PartnerName');
    var companyAddress = $(this).attr('CompanyAddress');
    var companyEmail = $(this).attr('CompanyEmail');
    var companyPhone = $(this).attr('CompanyPhone');
    var companyFax = $(this).attr('CompanyFax');
    var companyWebsite = $(this).attr('CompanyWebsite');
    var contactPerson = $(this).attr('ContactPerson');
    var contactPersonPosition = $(this).attr('ContactPersonPosition');
    var contactPersonPhone = $(this).attr('ContactPersonPhone');
    var contactPersonMobile = $(this).attr('ContactPersonMobile');
    var contactPersonEmail = $(this).attr('ContactPersonEmail');
    var numberOfRewardOffers = $(this).attr('NumberOfRewardOffers');
    var status = $(this).attr('Status');
    
    //Put in the respective fields
    $("#PartnerID").val(partnerID);
    $("#Partner").val(partnerName);
    $("#companyAddress").val(companyAddress);
    $("#PNumber").val(companyPhone);
    $("#FNumber").val(companyFax);
    $("#EmailAddress").val(companyEmail);
    $("#Website").val(companyWebsite);
    $("#ContactPerson").val(contactPerson);
    $("#ContactPosition").val(contactPersonPosition);
    $("#ContactEmailAddress").val(contactPersonEmail);
    $("#ContactPhoneNumber").val(contactPersonPhone);
    $("#ContactMobile").val(contactPersonMobile);
    $("#NumberOfRewardOfferings").val(numberOfRewardOffers);
    $("#Status").val(status).attr("selected", "selected");
    
    $("#PartnerID2").val(partnerID);
    $("#Partner2").val(partnerName);
    $("#companyAddress2").val(companyAddress);
    $("#PNumber2").val(companyPhone);
    $("#FNumber2").val(companyFax);
    $("#EmailAddress2").val(companyEmail);
    $("#Website2").val(companyWebsite);
    $("#ContactPerson2").val(contactPerson);
    $("#ContactPosition2").val(contactPersonPosition);
    $("#ContactEmailAddress2").val(contactPersonEmail);
    $("#ContactPhoneNumber2").val(contactPersonPhone);
    $("#ContactMobile2").val(contactPersonMobile);
    $("#NumberOfRewardOfferings2").val(numberOfRewardOffers);
    $("#Status2").val(status).attr("selected", "selected");
    $("#LastStatus2").val(status);
    
    //Disable Fields
    $("#Partner").attr('disabled','disabled');
    $("#companyAddress").attr('disabled','disabled');
    $("#PNumber").attr('disabled','disabled');
    $("#FNumber").attr('disabled','disabled');
    $("#EmailAddress").attr('disabled','disabled');
    $("#Website").attr('disabled','disabled');
    $("#ContactPerson").attr('disabled','disabled');
    $("#ContactPosition").attr('disabled','disabled');
    $("#ContactEmailAddress").attr('disabled','disabled');
    $("#ContactPhoneNumber").attr('disabled','disabled');
    $("#ContactMobile").attr('disabled','disabled');
    $("#Status").attr('disabled','disabled');
    $("#LastStatus").val(status);
    $("#NumberOfRewardOfferings").attr('disabled','disabled');
    
    $("#editPartnerDialog").dialog("open");
    return false;
});

$(".addBtn").live("click", function(){
    $("#PartnerAdd").val("");
    $("#companyAddressAdd").val("");
    $("#PNumberAdd").val("");
    $("#FNumberAdd").val("");
    $("#EmailAddressAdd").val("");
    $("#WebsiteAdd").val("");
    $("#ContactPersonAdd").val("");
    $("#UsernameAdd").val("");
    $("#ContactPositionAdd").val("");
    $("#ContactEmailAddressAdd").val("");
    $("#ContactPhoneNumberAdd").val("");
    $("#ContactMobileAdd").val("");
    $("#StatusAdd").val("");
    $("#NumberOfRewardOfferingsAdd").val("");
});
</script>

<script type="text/javascript">
    /**
     * (OPTIONAL)
     * AJAX Validation for Partners Details Forms
     * @param int form From what form
     * @author Mark Kenneth Esguerra
     * @date 09-18-13
     */
    function validateInputs(form, part)
    {
        $("#msgdialog").html("");
        $("#msgdialog2").html("");
        $("#msgdialogAdd").html("");
        //Add Partner Dialog Form
        if (form == 0)
        {
            if (part == 1)
            {
                var partnerID       = $("#PartnerIDAdd").val();
                var partnername     = $("#PartnerAdd").val();
                var address         = $("#companyAddressAdd").val();
                var phonenumber     = $("#PNumberAdd").val();
                var faxnumber       = $("#FNumberAdd").val();
                var emailaddress    = $("#EmailAddressAdd").val();
                var website         = $("#WebsiteAdd").val();

                if (partnername === "" || address === "" || phonenumber === "" || faxnumber === ""
                    || emailaddress === "" || website === "")
                {
                    $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                    $("#msgdialogAdd").html("Please fill up all fields");
                    
                    return false;
                }
                else if (/^[a-zA-Z0-9- \']*$/.test(partnername) === false)
                {
                    $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                    $("#msgdialogAdd").html("Special character/s is/are not allowed in Partner Name");
                    
                    return false;
                }
                else if (/^[a-zA-Z0-9- /,-.']*$/.test(address) === false)
                {
                    $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                    $("#msgdialogAdd").html("Special character/s is/are not allowed in Company Address");
                
                    return false;
                }
                else if (/^[a-zA-Z0-9- \+\-\(\)]*$/.test(phonenumber) === false)
                {
                    $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                    $("#msgdialogAdd").html("Special character/s is/are not allowed in Company Phone Number");
                
                    return false;
                }
                else if (/^[a-zA-Z0-9-  \+\-\(\)]*$/.test(faxnumber) === false)
                {
                    $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                    $("#msgdialogAdd").html("Special character/s is/are not allowed in Fax Number");
                
                    return false;
                }
                else if(isValidEmailAddress(emailaddress) === false)
                {
                    $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                    $("#msgdialogAdd").html("Invalid Email Address");
                    
                    return false;
                }
                else if (isValidWebsiteUrl(website) === false)
                {
                    $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                    $("#msgdialogAdd").html("Invalid Website URL");
                    
                    return false;
                }
                else
                {
                    if (partnername.length < 5)
                    {
                        $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                        $("#msgdialogAdd").html("eGames Partner Name too short (minimum is 5 characters)");
                        
                        return false;
                    }
                    else if (address.length < 5)
                    {
                        $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                        $("#msgdialogAdd").html("Company Name too short (minimum is 5 characters)");
                        
                        return false;
                    }
                    else if (phonenumber.length < 7)
                    {
                        $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                        $("#msgdialogAdd").html("Phone Number too short (minimum is 7 characters)");
                        
                        return false;
                    }
                    else if (faxnumber.length < 7)
                    {
                        $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                        $("#msgdialogAdd").html("Fax Number too short (minimum is 7 characters)");
                        
                        return false;
                    }
                    else
                    {
                        return true;
                    }
                }
            }
            else if (part == 2)
            {
                var contactperson   = $("#ContactPersonAdd").val();
                var contactposition = $("#ContactPositionAdd").val();
                var contactemail    = $("#ContactEmailAddressAdd").val();
                var contactpnumber  = $("#ContactPhoneNumberAdd").val();
                var contactmobile   = $("#ContactMobileAdd").val();
                var numberOfoffers  = $("#NumberOfRewardOfferingsAdd").val();
                var username        = $("#UsernameAdd").val();
                var status          = $("#StatusAdd").val();
                
                if (contactperson === "" || contactposition === "" || contactemail === "" 
                    || contactpnumber === "" || contactmobile === ""
                    || status == -1 || username === "")
                {
                    $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                    $("#msgdialogAdd").html("Please fill up all fields");
                }
                else if (/^[a-zA-Z0-9- ]*$/.test(contactperson) === false)
                {
                    $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                    $("#msgdialogAdd").html("Special character/s is/are not allowed in Contact Person");
                    
                    return false;
                }
                else if (/^[a-zA-Z0-9-_ ]*$/.test(username) === false)
                {
                    $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                    $("#msgdialogAdd").html("Special character/s is/are not allowed in Username");
                    
                    return false;
                }
                else if (/^[a-zA-Z0-9- ]*$/.test(contactposition) === false)
                {
                    $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                    $("#msgdialogAdd").html("Special character/s is/are not allowed in Contact Person's Position");
                
                    return false;
                }
                else if (/^[a-zA-Z0-9- \+\-\(\)]*$/.test(contactpnumber) === false)
                {
                    $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                    $("#msgdialogAdd").html("Special character/s is/are not allowed in Contact Person's Phone Number");
                
                    return false;
                }
                else if (/^[a-zA-Z0-9- ]*$/.test(contactmobile) === false)
                {
                    $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                    $("#msgdialogAdd").html("Special character/s is/are not allowed in Contact Person's Mobile");
                
                    return false;
                }
                else if (/^[a-zA-Z0-9- ]*$/.test(numberOfoffers) === false)
                {
                    $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                    $("#msgdialogAdd").html("Special character/s is/are not allowed in Number of Reward Offers");
                
                    return false;
                }
                else if(isValidEmailAddress(contactemail) === false)
                {
                    $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                    $("#msgdialogAdd").html("Invalid Email Address");
                }
//                else if (numberOfoffers <=  0)
//                {
//                    $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
//                    $("#msgdialogAdd").html("Number of Reward Offerings must be greater than zero");
//                    
//                    return false;
//                }
                else
                {
                    if (contactposition.length < 5)
                    {
                        $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                        $("#msgdialogAdd").html("Position too short (minimum is 5 characters)");
                        
                        return false;
                    }
                    else if (contactpnumber.length < 7)
                    {
                        $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                        $("#msgdialogAdd").html("Contact Person's Phone Number too short (minimum is 7 characters)");
                        
                        return false;
                    }
                    else if (contactmobile.length < 11)
                    {
                        $("#msgdialogAdd").css({'text-align':"left",'color':"red"});
                        $("#msgdialogAdd").html("Mobile Number too short (minimum is 11 characters)");
                        
                        return false;
                    }
                    else
                    {
                        $("#addpartner-form").submit();
                    }
                }
            }
        }
        //Dialog Form when PartnerName Clicks
        if (form == 1)
        {
            var partnerID       = $("#PartnerID").val();
            var partnername     = $("#Partner").val();
            var address         = $("#companyAddress").val();
            var phonenumber     = $("#PNumber").val();
            var faxnumber       = $("#FNumber").val();
            var emailaddress    = $("#EmailAddress").val();
            var website         = $("#Website").val();
            var contactperson   = $("#ContactPerson").val();
            var contactposition = $("#ContactPosition").val();
            var contactemail    = $("#ContactEmailAddress").val();
            var contactpnumber  = $("#ContactPhoneNumber").val();
            var contactmobile   = $("#ContactMobile").val();
            var numberOfoffers  = $("#NumberOfRewardOfferings").val();
            var laststatus      = $("#LastStatus").val();
            var status          = $("#Status").val();
            
            if (partnername === "" || address === "" || phonenumber === "" || faxnumber === ""
                || emailaddress === "" || website === "" || contactperson === "" 
                || contactposition === "" || contactemail === "" || contactpnumber === ""
                || contactmobile === "" || numberOfoffers === "")
            {
                $("#msgdialog2").css({'text-align':"left",'color':"red"});
                $("#msgdialog2").html("Please fill up all fields");
            }
            else if (/^[a-zA-Z0-9- \']*$/.test(partnername) === false)
            {
                $("#msgdialog2").css({'text-align':"left",'color':"red"});
                $("#msgdialog2").html("Special character/s is/are not allowed in Partner Name");

                return false;
            }
            else if (/^[a-zA-Z0-9- /,-.']*$/.test(address) === false)
            {
                $("#msgdialog2").css({'text-align':"left",'color':"red"});
                $("#msgdialog2").html("Special character/s is/are not allowed in Company Address");

                return false;
            }
            else if (/^[a-zA-Z0-9- \+\-\(\)]*$/.test(phonenumber) === false)
            {
                $("#msgdialog2").css({'text-align':"left",'color':"red"});
                $("#msgdialog2").html("Special character/s is/are not allowed in Phone Number");

                return false;
            }
            else if (/^[a-zA-Z0-9-  \+\-\(\)]*$/.test(faxnumber) === false)
            {
                $("#msgdialog2").css({'text-align':"left",'color':"red"});
                $("#msgdialog2").html("Special character/s is/are not allowed in Company Fax Number");

                return false;
            }
            else if (/^[a-zA-Z0-9- ]*$/.test(contactperson) === false)
            {
                $("#msgdialog2").css({'text-align':"left",'color':"red"});
                $("#msgdialog2").html("Special character/s is/are not allowed in Contact Person");

                return false;
            }
            else if (/^[a-zA-Z0-9- ]*$/.test(contactposition) === false)
            {
                $("#msgdialog2").css({'text-align':"left",'color':"red"});
                $("#msgdialog2").html("Special character/s is/are not allowed in Contact Person's Position");

                return false;
            }
            else if (/^[a-zA-Z0-9- ]*$/.test(contactpnumber) === false)
            {
                $("#msgdialog2").css({'text-align':"left",'color':"red"});
                $("#msgdialog2").html("Special character/s is/are not allowed in Contact Person's Phone Number");

                return false;
            }
            else if (/^[a-zA-Z0-9- ]*$/.test(contactmobile) === false)
            {
                $("#msgdialog2").css({'text-align':"left",'color':"red"});
                $("#msgdialog2").html("Special character/s is/are not allowed in Contact Person's Mobile");

                return false;
            }
            else if (/^[a-zA-Z0-9- ]*$/.test(numberOfoffers) === false)
            {
                $("#msgdialog2").css({'text-align':"left",'color':"red"});
                $("#msgdialog2").html("Special character/s is/are not allowed in Number of Reward Offers");

                return false;
            }
            else if((isValidEmailAddress(emailaddress) === false) || (isValidEmailAddress(contactemail) === false))
            {
                $("#msgdialog2").css({'text-align':"left",'color':"red"});
                $("#msgdialog2").html("Invalid Email Address");
            }
            else if (isValidWebsiteUrl(website) === false)
            {
                $("#msgdialog2").css({'text-align':"left",'color':"red"});
                $("#msgdialog2").html("Invalid Website URL");
            }
            else if (numberOfoffers <=  0)
            {
                $("#msgdialog2").css({'text-align':"left",'color':"red"});
                $("#msgdialog2").html("Number of Reward Offerings must be greater than zero");

                return false;
            }
            else if (status != laststatus)
            {
                var c = confirm("Are you sure you want to change the partner's status?");
                if (c == true)
                {
                    $("#editpartner-form-pname").submit();
                }
            }
            else
            {
                $("#editpartner-form-pname").submit();
            }
        }
        //Dialog Form when EditLink Clicks
        else if (form == 2)
        {
            if (part == 1)
            {
                var partnerID       = $("#PartnerID2").val();
                var partnername     = $("#Partner2").val();
                var address         = $("#companyAddress2").val();
                var phonenumber     = $("#PNumber2").val();
                var faxnumber       = $("#FNumber2").val();
                var emailaddress    = $("#EmailAddress2").val();
                var website         = $("#Website2").val();

                if (partnername === "" || address === "" || phonenumber === "" || faxnumber === ""
                    || emailaddress === "" || website === "")
                {
                    $("#msgdialog").css({'text-align':"left",'color':"red"});
                    $("#msgdialog").html("Please fill up all fields");
                    
                    return false;
                }
                else if (/^[a-zA-Z0-9- \']*$/.test(partnername) === false)
                {
                    $("#msgdialog").css({'text-align':"left",'color':"red"});
                    $("#msgdialog").html("Special character/s is/are not allowed in Partner Name");
                    
                    return false;
                }
                else if (/^[a-zA-Z0-9- /,-.']*$/.test(address) === false)
                {
                    $("#msgdialog").css({'text-align':"left",'color':"red"});
                    $("#msgdialog").html("Special character/s is/are not allowed in Company Address");
                
                    return false;
                }
                else if (/^[a-zA-Z0-9- \+\-\(\)]*$/.test(phonenumber) === false)
                {
                    $("#msgdialog").css({'text-align':"left",'color':"red"});
                    $("#msgdialog").html("Special character/s is/are not allowed in Phone Number");
                
                    return false;
                }
                else if (/^[a-zA-Z0-9-  \+\-\(\)]*$/.test(faxnumber) === false)
                {
                    $("#msgdialog").css({'text-align':"left",'color':"red"});
                    $("#msgdialog").html("Special character/s is/are not allowed in Fax Number");
                
                    return false;
                }
                else if(isValidEmailAddress(emailaddress) === false)
                {
                    $("#msgdialog").css({'text-align':"left",'color':"red"});
                    $("#msgdialog").html("Invalid Email Address");
                    
                    return false;
                }
                else if (isValidWebsiteUrl(website) === false)
                {
                    $("#msgdialog").css({'text-align':"left",'color':"red"});
                    $("#msgdialog").html("Invalid Website URL");
                    
                    return false;
                }
                else
                {
                    if (partnername.length < 5)
                    {
                        $("#msgdialog").css({'text-align':"left",'color':"red"});
                        $("#msgdialog").html("eGames Partner Name too short (minimum is 5 characters)");
                        
                        return false;
                    }
                    else if (address.length < 5)
                    {
                        $("#msgdialog").css({'text-align':"left",'color':"red"});
                        $("#msgdialog").html("Company Name too short (minimum is 5 characters)");
                        
                        return false;
                    }
                    else if (phonenumber.length < 7)
                    {
                        $("#msgdialog").css({'text-align':"left",'color':"red"});
                        $("#msgdialog").html("Phone Number too short (minimum is 7 characters)");
                        
                        return false;
                    }
                    else if (faxnumber.length < 7)
                    {
                        $("#msgdialog").css({'text-align':"left",'color':"red"});
                        $("#msgdialog").html("Fax Number too short (minimum is 7 characters)");
                        
                        return false;
                    }
                    else
                    {
                        return true;
                    }
                }
            }
            else if (part == 2)
            {
                var contactperson   = $("#ContactPerson2").val();
                var contactposition = $("#ContactPosition2").val();
                var contactemail    = $("#ContactEmailAddress2").val();
                var contactpnumber  = $("#ContactPhoneNumber2").val();
                var contactmobile   = $("#ContactMobile2").val();
                var numberOfoffers  = $("#NumberOfRewardOfferings2").val();
                var laststatus      = $("#LastStatus2").val();
                var status          = $("#Status2").val();
                
                if (contactperson === "" || contactposition === "" || contactemail === "" 
                    || contactpnumber === "" || contactmobile === "" || numberOfoffers === "")
                {
                    $("#msgdialog").css({'text-align':"left",'color':"red"});
                    $("#msgdialog").html("Please fill up all fields");
                    
                    return false;
                    
                }
                else if (/^[a-zA-Z0-9- ]*$/.test(contactperson) === false)
                {
                    $("#msgdialog").css({'text-align':"left",'color':"red"});
                    $("#msgdialog").html("Special character/s is/are not allowed in Contact Person");
                    
                    return false;
                }
                else if (/^[a-zA-Z0-9- ]*$/.test(contactposition) === false)
                {
                    $("#msgdialog").css({'text-align':"left",'color':"red"});
                    $("#msgdialog").html("Special character/s is/are not allowed in Contact Person's Position");
                
                    return false;
                }
                else if (/^[a-zA-Z0-9- \+\-\(\)]*$/.test(contactpnumber) === false)
                {
                    $("#msgdialog").css({'text-align':"left",'color':"red"});
                    $("#msgdialog").html("Special character/s is/are not allowed in Contact Person's Phone Number");
                
                    return false;
                }
                else if (/^[a-zA-Z0-9- ]*$/.test(contactmobile) === false)
                {
                    $("#msgdialog").css({'text-align':"left",'color':"red"});
                    $("#msgdialog").html("Special character/s is/are not allowedd in Contact Person's Mobile");
                
                    return false;
                }
                else if (/^[a-zA-Z0-9- ]*$/.test(numberOfoffers) === false)
                {
                    $("#msgdialog").css({'text-align':"left",'color':"red"});
                    $("#msgdialog").html("Special character/s is/are not allowed in Number of Reward Offers");
                
                    return false;
                }
                else if(isValidEmailAddress(contactemail) === false)
                {
                    $("#msgdialog").css({'text-align':"left",'color':"red"});
                    $("#msgdialog").html("Invalid Email Address");
                    
                    return false;
                }
//                else if (numberOfoffers <=  0)
//                {
//                    $("#msgdialog").css({'text-align':"left",'color':"red"});
//                    $("#msgdialog").html("Number of Reward Offerings must be greater than zero");
//                    
//                    return false;
//                }
                else
                {
                    if (contactposition.length < 5)
                    {
                        $("#msgdialog").css({'text-align':"left",'color':"red"});
                        $("#msgdialog").html("Position too short (minimum is 5 characters)");
                        
                        return false;
                    }
                    else if (contactpnumber.length < 7)
                    {
                        $("#msgdialog").css({'text-align':"left",'color':"red"});
                        $("#msgdialog").html("Contact Person's Phone Number too short (minimum is 7 characters)");
                        
                        return false;
                    }
                    else if (contactmobile.length < 11)
                    {
                        $("#msgdialog").css({'text-align':"left",'color':"red"});
                        $("#msgdialog").html("Mobile Number too short (minimum is 11 characters)");
                        
                        return false;
                    }
                    else
                    {
                        //Check if status is change
                        if (status != laststatus)
                        {
                            var c = confirm("Are you sure you want to change the partner's status?");
                            if (c == true)
                            {
                                $("#editpartner-form-editlink").submit();
                            }
                        }
                        else
                        {
                            $("#editpartner-form-editlink").submit();
                        }
                        return true;
                    }
                }
            }
        }
    }
    function isValidEmailAddress(emailAddress) {
        var pattern = new RegExp(/^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/);
        return pattern.test(emailAddress);
    }
    function isValidWebsiteUrl(emailAddress) {
        var pattern = new RegExp(/^(http\:\/\/|https\:\/\/)?([\w-\.]+([\w-]+\.)+[\w-]{2,4})?$/);
        return pattern.test(emailAddress);
    }
    $(document).ready(function(){
       $("#viewby").change(function(){
           var status = $("#viewby").val();
            var url = "ajaxViewPartnersBy";
            jQuery('#grid1').GridUnload();
            jQuery("#grid1").jqGrid({
                    url:url,
                    mtype:'post',
                    postData: {
                                Status : function() {return status;}
                              },
                    datatype: "json",
                    colNames:['Partner Name', 'Status', 'Number of Reward Offers', 'Contact Person', "Contact Person's Email", ''],
                    colModel:[
                            {name : 'Name', sortable : false, width : '200', resizable : true, align : 'center'},
                            {name : 'StatusName', sortable : false, width : '87', resizable : true, align : 'center'},
                            {name : 'NumberOfRewardOffers', sortable : false, width : '159', resizable : true, align : 'center'},
                            {name : 'ContactPerson', sortable : false, width : '180', resizable : true, align : 'center'},
                            {name : 'ContactPersonEmail', sortable : false, width : '180', resizable : true, align : 'center'},
                            {name : 'EditLink', 'sortable' : false, width : '59', resizable : false, align : 'center'},  
                    ],
                            
                    rowNum: 10,
                    rowList: [10,20,30],
                    height: 250,
                    width: 909,
                    shrinkToFit : false,
                    pager: "#pager1",
                    refresh: true,
                    loadonce: true,
                    viewrecords: true,
                    loadtext: "Loading...",
                    sortorder: "desc",
                    sortname: "PartnerID",
                    caption:"Manage Partners"
            });
            jQuery("#grid1").jqGrid('navGrid','#pager1',
                                {
                                    edit:false,add:false,del:false, search:false, refresh: true});
       });
    });
</script>
<h1>Manage Partners</h1>
<?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'filter-form',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
        'action' => $this->createUrl('ViewGrid')
    ));
?>
VIEW PARTNERS BY
<?php
    echo CHtml::dropDownList('ViewBy', '', array('All'=>'All',
                                                 '1'=>'Active',
                                                 '0'=>'Inactive'),
                                           array('id' => 'viewby'));
    $this->endWidget();
?>
<br /><br />
    <table id="grid1"></table>
    <div id="pager1"></div>
<div id="main" style="position:relative;">
    <center>
        <br>
        <?php echo CHtml::button('ADD PARTNER', array('class'=>'addBtn','id'=>'linkButton','onclick' => '$("#addPartnerDialog").dialog("open"); return false;')); ?>
    </center>
</div>
<?php
/**
 * Dialog Box for SUCCESS/ERROR Message
 */
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'messageDialog',
    'options' => array(
        'title' => $this->dialogtitle,
        'autoOpen' => $this->showdialog,
        'closeOnEscape' => false,
        'resizable' => false,
        'draggable' => false,
        'modal' => true,
        'open' => 'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array
            (
            'OK' => 'js:function(){
                $(this).dialog("close");
            }',
        ),
    ),
));
?>
<p style="text-align:left"><?php echo $this->dialogmsg; ?></p>
<!---------------------------------------ADD PARTNER---------------------------------------->
<?php 
$this->endWidget('zii.widgets.jui.CJuiDialog');
//Render Add Partner Pane
echo $this->renderPartial('_addpartner', array('model'=>$model)); 
//Render Update Partner Pane
echo $this->renderPartial('_updatepartner', array('model' => $model));
//Render View Details Pane
echo $this->renderPartial('_viewdetails', array('model' => $model));
?>

<!------------------------------------JqGRID----------------------------------------------->
<?php
$this->widget('application.components.widgets.JqGridWidget', array('tableID' => 'grid1', 'pagerID' => 'pager1',
    'jqGridParam' => array(
        'id' => 'grid1',
        'url' => $this->createUrl('index'),
        'rowList' =>  array('10','20','30'),
        'loadonce' => true,
        'caption' => 'Manage Partners',
        'height' => '40',
        'width' => '700',
        'shrinkToFit' => false,
        'colNames' => array('Partner Name', 'Status', 'Number of Reward Offers', 'Contact Person', "Contact Person's Email", ''),
        'colModel' => array(
            array('name' => 'Name', 'sortable' => false, 'width' => '200', 'resizable' => true, 'align' => 'center'),
            array('name' => 'Status', 'sortable' => false, 'width' => '90', 'resizable' => true, 'align' => 'center'),
            array('name' => 'NumberOfRewardOffers', 'sortable' => false, 'width' => '160', 'resizable' => true, 'align' => 'center'),
            array('name' => 'ContactPerson', 'sortable' => false, 'width' => '190', 'resizable' => true, 'align' => 'center'),
            array('name' => 'ContactPersonEmail', 'sortable' => false, 'width' => '180', 'resizable' => true, 'align' => 'center'),
            array('name' => 'EditLink', 'sortable' => false, 'width' => '58', 'resizable' => false, 'align' => 'center'),
        ),
)));
?>
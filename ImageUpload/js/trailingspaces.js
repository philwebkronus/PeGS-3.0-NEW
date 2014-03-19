/**
 * This JS File is intended to avoid trailing spaces in Add, Edit Partner Fields
 * @author Mark Kenneth Esguerra
 * @date September 30, 2013
 */

//ADD Partner-------------------------------------------------------------------
$(document).ready(function(){
   $("#PartnerAdd").keyup(function(){
       var branchdtls = $("#PartnerAdd").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#PartnerAdd").val("");
       }
       else{
           return true;
       }
   });
   $("#companyAddressAdd").keyup(function(){
       var branchdtls = $("#companyAddressAdd").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#companyAddressAdd").val("");
       }
       else{
           return true;
       }
   });
   $("#PNumberAdd").keyup(function(){
       var branchdtls = $("#PNumberAdd").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#PNumberAdd").val("");
       }
       else{
           return true;
       }
   });
   $("#FNumberAdd").keyup(function(){
       var branchdtls = $("#FNumberAdd").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#FNumberAdd").val("");
       }
       else{
           return true;
       }
   });
   $("#EmailAddressAdd").keyup(function(){
       var branchdtls = $("#EmailAddressAdd").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#EmailAddressAdd").val("");
       }
       else{
           return true;
       }
   });
   $("#WebsiteAdd").keyup(function(){
       var branchdtls = $("#WebsiteAdd").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#WebsiteAdd").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactPersonAdd").keyup(function(){
       var branchdtls = $("#ContactPersonAdd").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactPersonAdd").val("");
       }
       else{
           return true;
       }
   });
   $("#UsernameAdd").keyup(function(){
       var branchdtls = $("#UsernameAdd").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#UsernameAdd").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactPositionAdd").keyup(function(){
       var branchdtls = $("#ContactPositionAdd").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactPositionAdd").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactEmailAddressAdd").keyup(function(){
       var branchdtls = $("#ContactEmailAddressAdd").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactEmailAddressAdd").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactPhoneNumberAdd").keyup(function(){
       var branchdtls = $("#ContactPhoneNumberAdd").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactPhoneNumberAdd").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactPhoneNumberAdd").keyup(function(){
       var branchdtls = $("#ContactPhoneNumberAdd").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactPhoneNumberAdd").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactMobileAdd").keyup(function(){
       var branchdtls = $("#ContactMobileAdd").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactMobileAdd").val("");
       }
       else{
           return true;
       }
   });
   $("#NumberOfRewardOfferingsAdd").keyup(function(){
       var branchdtls = $("#NumberOfRewardOfferingsAdd").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#NumberOfRewardOfferingsAdd").val("");
       }
       else{
           return true;
       }
   });
   
   ///////////////////////////EDIT PARTNER by clicking Edit Link////////////////////////
   $("#Partner2").keyup(function(){
       var branchdtls = $("#Partner2").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#Partner2").val("");
       }
       else{
           return true;
       }
   });
   $("#companyAddress2").keyup(function(){
       var branchdtls = $("#companyAddress2").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#companyAddress2").val("");
       }
       else{
           return true;
       }
   });
   $("#PNumber2").keyup(function(){
       var branchdtls = $("#PNumber2").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#PNumber2").val("");
       }
       else{
           return true;
       }
   });
   $("#FNumber2").keyup(function(){
       var branchdtls = $("#FNumber2").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#FNumber2").val("");
       }
       else{
           return true;
       }
   });
   $("#EmailAddress2").keyup(function(){
       var branchdtls = $("#EmailAddress2").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#EmailAddress2").val("");
       }
       else{
           return true;
       }
   });
   $("#Website2").keyup(function(){
       var branchdtls = $("#Website2").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#Website2").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactPerson2").keyup(function(){
       var branchdtls = $("#ContactPerson2").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactPerson2").val("");
       }
       else{
           return true;
       }
   });
   $("#Username2").keyup(function(){
       var branchdtls = $("#Username2").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#Username2").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactPosition2").keyup(function(){
       var branchdtls = $("#ContactPosition2").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactPosition2").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactEmailAddress2").keyup(function(){
       var branchdtls = $("#ContactEmailAddress2").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactEmailAddress2").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactPhoneNumber2").keyup(function(){
       var branchdtls = $("#ContactPhoneNumber2").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactPhoneNumber2").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactPhoneNumber2").keyup(function(){
       var branchdtls = $("#ContactPhoneNumber2").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactPhoneNumber2").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactMobile2").keyup(function(){
       var branchdtls = $("#ContactMobile2").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactMobile2").val("");
       }
       else{
           return true;
       }
   });
   $("#NumberOfRewardOfferings2").keyup(function(){
       var branchdtls = $("#NumberOfRewardOfferings2").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#NumberOfRewardOfferings2").val("");
       }
       else{
           return true;
       }
   });
   /////////////////////////////EDIT PARTNER by clicking PartnerName//////////////////////
   $("#Partner").keyup(function(){
       var branchdtls = $("#Partner").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#Partner").val("");
       }
       else{
           return true;
       }
   });
   $("#companyAddress").keyup(function(){
       var branchdtls = $("#companyAddress").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#companyAddress").val("");
       }
       else{
           return true;
       }
   });
   $("#PNumber").keyup(function(){
       var branchdtls = $("#PNumber").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#PNumber").val("");
       }
       else{
           return true;
       }
   });
   $("#FNumber").keyup(function(){
       var branchdtls = $("#FNumber").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#FNumber").val("");
       }
       else{
           return true;
       }
   });
   $("#EmailAddress").keyup(function(){
       var branchdtls = $("#EmailAddress").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#EmailAddress").val("");
       }
       else{
           return true;
       }
   });
   $("#Website").keyup(function(){
       var branchdtls = $("#Website").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#Website").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactPerson").keyup(function(){
       var branchdtls = $("#ContactPerson").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactPerson").val("");
       }
       else{
           return true;
       }
   });
   $("#Username").keyup(function(){
       var branchdtls = $("#Username").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#Username").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactPosition").keyup(function(){
       var branchdtls = $("#ContactPosition").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactPosition").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactEmailAddress").keyup(function(){
       var branchdtls = $("#ContactEmailAddress").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactEmailAddress").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactPhoneNumber").keyup(function(){
       var branchdtls = $("#ContactPhoneNumber").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactPhoneNumber").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactPhoneNumber").keyup(function(){
       var branchdtls = $("#ContactPhoneNumber").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactPhoneNumber").val("");
       }
       else{
           return true;
       }
   });
   $("#ContactMobile").keyup(function(){
       var branchdtls = $("#ContactMobile").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#ContactMobile").val("");
       }
       else{
           return true;
       }
   });
   $("#NumberOfRewardOfferings").keyup(function(){
       var branchdtls = $("#NumberOfRewardOfferings").val();
       if (branchdtls.substring(0, 1) === " "){
           alert("Warning: Trailing space/s is/are not allowed");
           $("#NumberOfRewardOfferings").val("");
       }
       else{
           return true;
       }
   });
});

//////////////////////////////////////////////////////////////////////////////////////////



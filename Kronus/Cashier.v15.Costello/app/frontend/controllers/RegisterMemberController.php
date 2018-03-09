<?php

Mirage::loadComponents('FrontendController');

class RegisterMemberController extends FrontendController {

    public $title = 'Register New Member';

    public function overviewAction() {

        Mirage::loadComponents('AMPAPIV1.class');
        Mirage::loadModels('SiteDetailsModel');
        Mirage::loadModels('ReportsFormModel');

        $reportsFormModel = new ReportsFormModel();
        $AMPAPIV1 = new AMPAPIV1();
        $SiteDetailsModel = new SiteDetailsModel();

        $username = Mirage::app()->param['AMPAPIuser'];
        $password = Mirage::app()->param['AMPAPIpass'];

        $auth = $AMPAPIV1->AuthenticateSession($username, $password);

        $authDecode = json_decode($auth[1]);
        $TPSession = $authDecode->AuthenticateSession->TPSessionID;

        if ($this->isAjaxRequest() || $this->isPostRequest()) {
            // ajax and post request only
            $auth = $AMPAPIV1->AuthenticateSession($username, $password);

            $authDecode = json_decode($auth[1]);
            $TPSession = $authDecode->AuthenticateSession->TPSessionID;


            $fname = trim($_POST['fname']);
            $mname = trim($_POST['mname']);
            $lname = trim($_POST['lname']);
            $nname = trim($_POST['nname']);
            $password = sha1(trim($_POST['password']));
            $barangay = trim($_POST['barangay']);
            $city = trim($_POST['city']);
            $region = trim($_POST['region']);
            $mobilenumber = trim($_POST['mobilenumber']);
            $altermobilenumber = trim($_POST['altermobilenumber']);
            $emailaddress = trim($_POST['emailaddress']);
            $alteremailaddress = trim($_POST['alteremailaddress']);
            $gender = trim($_POST['gender']);
            $idpresented = trim($_POST['idpresented']);
            $idnumber = trim($_POST['idnumber']);
            $nationality = trim($_POST['nationality']);
            $birthdate = trim($_POST['birthdate']);
            $occupation = trim($_POST['occupation']);
            $issmoker = trim($_POST['issmoker']);
            $refferalcode = trim($_POST['refferalcode']);
            $referrer = trim($_POST['referrer']);
            $EmailSubscription = trim($_POST['EmailSubscription']);
            $SMSSubscription = trim($_POST['SMSSubscription']);
            $CivilStatus = trim($_POST['civilstatus']);
            $RegisterFor = trim($_POST['registerfor']);
   	    $UBCard = trim($_POST['loyaltycard']);

	    if ($fname != null || $mname != null || $lname != null || $password != null || $barangay != null || $city != null || $region != null ||
                    $mobilenumber != null || $emailaddress != null || $gender != null || $idpresented != null ||
                    $idnumber != null || $birthdate != null || $CivilStatus != null || $RegisterFor != null || $UBCard != null) {

		$registerMember = $AMPAPIV1->RegisterMember($TPSession, $fname, $mname, $lname, $nname, $password, $barangay, $city, $region, $mobilenumber, $altermobilenumber, $emailaddress, $alteremailaddress, $gender, $idpresented, $idnumber, $nationality, $birthdate, $occupation, $issmoker, $refferalcode, $referrer, $EmailSubscription, $SMSSubscription, $CivilStatus, $RegisterFor, $UBCard, $this->acc_id, $this->site_id);

	      $result = preg_replace('/\s(?=([^"]*"[^"]*")*[^"]*$)/', '', $registerMember[1]);
  	      $message = json_decode($result, true);

                    if ($message['AutoRegisterMember']['ErrorCode'] == 0) {
                        echo "<center><label  style='font-size: 24px; color: red; font-weight: bold; width: 800px;'>" . $message['AutoRegisterMember']['ReturnMessage'] . "</label>
                                <br /><br><input type='button' style='float: right; width: 50px; height: 25px;'  onClick='window.location.reload()' value='Ok' class='btnClose' />";
                    } else {
                        echo "<center><label  style='font-size: 24px; color: red; font-weight: bold; width: 800px;'>" . $message['AutoRegisterMember']['ReturnMessage'] . "</label>
                                <br /><br><input type='button' style='float: right; width: 50px; height: 25px;'  value='Ok' class='btnClose' />";
                    }
            }
        } else {
            if ($TPSession !== null) {
                $GetGender = $AMPAPIV1->GetGender($TPSession);
                $GetCity = $AMPAPIV1->GetCity($TPSession);
                $GetRegion = $AMPAPIV1->GetRegion($TPSession);
                $GetIDPresented = $AMPAPIV1->GetIDPresented($TPSession);
                $GetNationality = $AMPAPIV1->GetNationality($TPSession);
                $GetOccupation = $AMPAPIV1->GetOccupation($TPSession);
                $GetReferrer = $AMPAPIV1->GetReferrer($TPSession);
                $GetIsSmoker = $AMPAPIV1->GetIsSmoker($TPSession);
                $GetCivilStatus = $AMPAPIV1->GetCivilStatus($TPSession);
                $GetRegisterFor = $AMPAPIV1->GetRegisterFor($TPSession);

                $GenderDecode = json_decode($GetGender[1]);
                $GenderArray = $GenderDecode->GetGender;

                $CityDecode = json_decode($GetCity[1]);
                $CityArray = $CityDecode->GetRegion;

                $RegionDecode = json_decode($GetRegion[1]);
                $RegionArray = $RegionDecode->GetRegion;

                $IDPresentedDecode = json_decode($GetIDPresented[1]);
                $IDPresentedArray = $IDPresentedDecode->GetIDPresented;

                $GetNationalityDecode = json_decode($GetNationality[1]);
                $GetNationalityArray = $GetNationalityDecode->GetNationality;

                $GetOccupationDecode = json_decode($GetOccupation[1]);
                $GetOccupationArray = $GetOccupationDecode->GetOccupation;

                $GetReferrerDecode = json_decode($GetReferrer[1]);
                $GetReferrerArray = $GetReferrerDecode->GetReferrer;

                $GetIsSmokerDecode = json_decode($GetIsSmoker[1]);
                $GetIsSmokerArray = $GetIsSmokerDecode->GetIsSmoker;

                $GetCivilStatusDecode = json_decode($GetCivilStatus[1]);
                $GetCivilStatusArray = $GetCivilStatusDecode->GetCivilStatus;

                $GetRegisterForDecode = json_decode($GetRegisterFor[1]);
                $GetRegisterForArray = $GetRegisterForDecode->GetRegisterFor;

                $siteDetails = $SiteDetailsModel->getSiteDetailsBySiteID($this->site_id);
            }

            $this->render('registermember_overview', array('GenderArray' => $GenderArray, 'CityArray' => $CityArray,
                'RegionArray' => $RegionArray, 'IDPresentedArray' => $IDPresentedArray, 'GetNationalityArray' => $GetNationalityArray,
                'GetOccupationArray' => $GetOccupationArray, 'GetReferrerArray' => $GetReferrerArray, 'GetIsSmokerArray' => $GetIsSmokerArray,
                'GetCivilStatusArray' => $GetCivilStatusArray, 'GetRegisterForArray' => $GetRegisterForArray, 'siteDetails' => $siteDetails,
                'reportsFormModel' => $reportsFormModel
            ));
        }
    }

}


<?php

App::LoadModuleClass("Loyalty", "RewardItems");
App::LoadModuleClass("Loyalty", "RewardItemDetails");
App::LoadModuleClass("Membership", "AuditFunctions");
App::LoadModuleClass("Membership", "AuditTrail");

App::LoadCore('Validation.class.php');

App::LoadControl("DatePicker");
App::LoadControl("TextBox");
App::LoadControl("Button");
App::LoadControl("Hidden");

$fproc = new FormsProcessor();
$rewarditems = new RewardItems();
$rewarditemdetails = new RewardItemDetails();
$_Log = new AuditTrail();

$txtRewardItemName = new TextBox("txtRewardItemName", "txtRewardItemName", "FirstName");
$txtRewardItemName->ReadOnly = false;
$txtRewardItemName->ShowCaption = false;
$txtRewardItemName->Length = 30;
$txtRewardItemName->Size = 15;
$txtRewardItemName->CssClass = "validate[required]";
$txtRewardItemName->Args = 'onkeypress="javascript: return alphanumeric4(event)"';
$fproc->AddControl($txtRewardItemName);

$txtRewardItemDesc = new TextBox("txtRewardItemDesc", "txtRewardItemDesc", "FirstName");
$txtRewardItemDesc->ReadOnly = false;
$txtRewardItemDesc->ShowCaption = false;
$txtRewardItemDesc->Length = 50;
$txtRewardItemDesc->Size = 15;
$txtRewardItemDesc->CssClass = "validate[required, custom[onlyLetterSp], minSize[2]]";
$txtRewardItemName->Args = 'onkeypress="javascript: return alphanumeric4(event)"';
$fproc->AddControl($txtRewardItemDesc);

$txtItemCode = new TextBox("txtItemCode", "txtItemCode", "Item Code");
$txtItemCode->ReadOnly = false;
$txtItemCode->ShowCaption = false;
$txtItemCode->Length = 15;
$txtItemCode->Size = 15;
$txtItemCode->CssClass = "validate[required, custom[onlyLetterNumber]]";
$txtItemCode->Args = 'onkeypress="javascript: return alphanumeric4(event)"';
$fproc->AddControl($txtItemCode);

$txtRewardItemPrice = new TextBox("txtRewardItemPrice", "txtRewardItemPrice", "Reward Item Price");
$txtRewardItemPrice->ReadOnly = false;
$txtRewardItemPrice->ShowCaption = false;
$txtRewardItemPrice->Length = 30;
$txtRewardItemPrice->Size = 15;
$txtRewardItemPrice->CssClass = "validate[required, custom[onlyLetterNumber]]";
$txtRewardItemPrice->Args = 'onkeypress="javascript: return numberonly(event)"';
$fproc->AddControl($txtRewardItemPrice);

$txtRewardItemCount = new TextBox("txtRewardItemCount", "txtRewardItemCount", "Reward Item Count");
$txtRewardItemCount->ReadOnly = false;
$txtRewardItemCount->ShowCaption = false;
$txtRewardItemCount->Length = 30;
$txtRewardItemCount->Size = 15;
$txtRewardItemCount->CssClass = "validate[required, custom[onlyNumber], minSize[1]]";
$txtRewardItemCount->Args = 'onkeypress="javascript: return numberonly(event)"';
$fproc->AddControl($txtRewardItemCount);

$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();

$dsmaxdate->AddYears(+21);
$dsmindate->AddYears(-100);

$datetime_from = date("Y-m-d");
$expirationdate = new DatePicker("expirationDate", "expirationDate", "From");
$expirationdate->MaxDate = $dsmaxdate->CurrentDate;
$expirationdate->MinDate = $dsmindate->CurrentDate;
$expirationdate->ShowCaption = false;
$expirationdate->SelectedDate = $datetime_from;
$expirationdate->Value = date('Y-m-d');
$expirationdate->YearsToDisplay = "-100:+10";
$expirationdate->CssClass = "validate[required]";
$expirationdate->isRenderJQueryScript = true;
$expirationdate->Size = 27;
$fproc->AddControl($expirationdate);

$btnSubmit = new Button("btnSubmit", "btnSubmit", "Submit");
$btnSubmit->ShowCaption = true;
$btnSubmit->IsSubmit = true;
$btnSubmit->Enabled = true;
$fproc->AddControl($btnSubmit);

$fproc->ProcessForms();

$isOpen = 'false';

if ($fproc->IsPostBack) {

    if ($btnSubmit->SubmittedValue == 'Submit') {
        if (isset($_POST['txtRewardItemName']) && isset($_POST['rewarditemdesc']) && isset($_POST['txtRewardItemPrice'])
                && isset($_POST['txtRewardItemCount']) && isset($_POST['expirationDate']) && isset($_POST['firstheader'])
                && isset($_POST['detailone1']) && isset($_POST['firstheader'])) {

            if ($_FILES['picUpload1']['size'] > 0) {

                if (isset($_FILES["picUpload1"]["name"])) {

                    $rewarditemname = $_POST['txtRewardItemName'];
                    $rewarditemdesc = $_POST['rewarditemdesc'];
                    $rewarditemcode = $_POST['txtItemCode'];
                    $rewarditemprice = $_POST['txtRewardItemPrice'];
                    $rewarditemcount = $_POST['txtRewardItemCount'];
                    $expirationdate = $_POST['expirationDate'];
                    $rewardtype = $_POST['expirationDate'];

                    $firstheader = $_POST['firstheader'];
                    $detail11 = $_POST['detailone1'];

                    if (isset($_POST['detailtwo1'])) {
                        $detail12 = $_POST['detailtwo1'];
                    } else {
                        $detail12 = '';
                    }

                    if (isset($_POST['detailthree1'])) {
                        $detail13 = $_POST['detailthree1'];
                    } else {
                        $detail13 = '';
                    }

                    if (isset($_POST['secondheader'])) {
                        $secondheader = $_POST['secondheader'];
                    } else {
                        $secondheader = '';
                    }

                    if (isset($_POST['detailone2'])) {
                        $detail21 = $_POST['detailone2'];
                    } else {
                        $detail21 = '';
                    }

                    if (isset($_POST['detailtwo2'])) {
                        $detail22 = $_POST['detailtwo2'];
                    } else {
                        $detail22 = '';
                    }

                    if (isset($_POST['detailthree2'])) {
                        $detail23 = $_POST['detailthree2'];
                    } else {
                        $detail23 = '';
                    }

                    if (isset($_POST['thirdheader'])) {
                        $thirdheader = $_POST['thirdheader'];
                    } else {
                        $thirdheader = '';
                    }

                    if (isset($_POST['detailone3'])) {
                        $detail31 = $_POST['detailone3'];
                    } else {
                        $detail31 = '';
                    }

                    if (isset($_POST['detailtwo3'])) {
                        $detail32 = $_POST['detailtwo3'];
                    } else {
                        $detail32 = '';
                    }

                    if (isset($_POST['detailthree3'])) {
                        $detail33 = $_POST['detailthree3'];
                    } else {
                        $detail33 = '';
                    }

                    if (isset($_POST['rtyes'])) {
                        $rtyes = $_POST['rt'];
                        $rewardtype = $rtyes;
                    } else {
                        $rtno = $_POST['rt'];
                        $rewardtype = $rtno;
                    }

                    if (isset($_POST['vihpyes'])) {
                        $vihpyes = $_POST['vihp'];
                        $viewhomepage = $vihpyes;
                    } else {
                        $vihpno = $_POST['vihp'];
                        $viewhomepage = $vihpno;
                    }


                    $filename1 = $_FILES['picUpload1']['name'];
                    $size1 = $_FILES['picUpload1']['size'];
                    $tmp_name1 = $_FILES['picUpload1']['tmp_name'];

                    $filename2 = $_FILES['picUpload2']['name'];
                    $size2 = $_FILES['picUpload2']['size'];
                    $tmp_name2 = $_FILES['picUpload2']['tmp_name'];

                    $filename3 = $_FILES['picUpload3']['name'];
                    $size3 = $_FILES['picUpload3']['size'];
                    $tmp_name3 = $_FILES['picUpload3']['tmp_name'];


                    if ($size1 < 204800 && $size2 < 204800 && $size3 < 204800) {

                        list($filename1, $exten) = preg_split("/\./", $filename1);
                        list($filename2, $exten2) = preg_split("/\./", $filename2);
                        list($filename3, $exten3) = preg_split("/\./", $filename3);

                        list($small, $name) = preg_split("/\_/", $filename1);
                        list($med, $name2) = preg_split("/\_/", $filename2);
                        list($large, $name3) = preg_split("/\_/", $filename3);

                        if ($name == $name2 && $name2 == $name3) {

                            $upload_path = App::getParam("images_directory");

                            $arrEntry['RewardItemName'] = "$rewarditemname";
                            $arrEntry['RewardItemDescription'] = "$rewarditemdesc";
                            $arrEntry['RewardItemCode'] = "$rewarditemcode";
                            $arrEntry['RewardItemPrice'] = "$rewarditemprice";
                            $arrEntry['RewardItemCount'] = "$rewarditemcount";
                            $arrEntry['AvailableItemCount'] = "$rewarditemcount";
                            $arrEntry['RewardItemImagePath'] = "$name" . "." . "$exten";
                            $arrEntry['ExpiryDate'] = $expirationdate;
                            $arrEntry['DateCreated'] = "now_usec()";
                            $arrEntry['CreatedByAID'] = $_SESSION['aID'];
                            $arrEntry['IsCoupon'] = $rewardtype;
                            $arrEntry['ShowInHomePage'] = $viewhomepage;
                            $arrEntry['Status'] = 1;

                            $rewarditems->StartTransaction();

                            $lastid = $rewarditems->Insert($arrEntry);

                            $arrEntry2['RewardItemID'] = $lastid;
                            $arrEntry2['HeaderOne'] = $firstheader;
                            $arrEntry2['HeaderTwo'] = $secondheader;
                            $arrEntry2['HeaderThree'] = $thirdheader;
                            $arrEntry2['DetailsOneA'] = $detail11;
                            $arrEntry2['DetailsOneB'] = $detail12;
                            $arrEntry2['DetailsOneC'] = $detail13;
                            $arrEntry2['DetailsTwoA'] = $detail21;
                            $arrEntry2['DetailsTwoB'] = $detail22;
                            $arrEntry2['DetailsTwoC'] = $detail23;
                            $arrEntry2['DetailsThreeA'] = $detail31;
                            $arrEntry2['DetailsThreeB'] = $detail32;
                            $arrEntry2['DetailsThreeC'] = $detail33;

                            if ($lastid > 0) {
                                $rewarditems->CommitTransaction();

                                $rewarditemdetails->StartTransaction();
                                $rewarditemdetails->Insert($arrEntry2);
                                $rewarditemdetails->CommitTransaction();

                                if (move_uploaded_file($tmp_name1, $upload_path . '/' . "$filename1" . '.' . "$exten")) {
                                    if (move_uploaded_file($tmp_name2, $upload_path . '/' . "$filename2" . '.' . "$exten")) {
                                        if (move_uploaded_file($tmp_name3, $upload_path . '/' . "$filename3" . '.' . "$exten")) {
                                            $upload = 1;
                                        }
                                    }
                                } else {
                                    $upload = 0;
                                }


                                if ($upload > 0) {
                                    $_Log->logEvent(AuditFunctions::MARKETING_ADD_REWARD_ITEM, ':Successful', array('ID' => $_SESSION['userinfo']['AID'], 'SessionID' => $_SESSION['userinfo']['SessionID']));
                                    $msgprompt = ("Reward Item Successfully Inserted");
                                    $isSuccess = true;
                                } else {
                                    $msgprompt = ("Error in Inserting New Item");
                                    $isSuccess = false;
                                }
                            } else {
                                $rewarditems->RollBackTransaction();
                                $msgprompt = ("Failed to insert in reward items table");
                                $isSuccess = false;
                            }
                        } else {
                            $_SESSION['msg'] = 'Failed to update records, Upload Image must be the same';
                            $msg = "false1";
                            header("Location: viewrewarditems.php?msg=" . $msg);
                        }
                    } else {
                        $msgprompt = ("File must not be greater than 200KB");
                        $isSuccess = false;
                    }
                }
            }
            $txtRewardItemName->Text = '';
            $txtRewardItemCount->Text = '';
            $txtRewardItemPrice->Text = '';
            $txtItemCode->Text = '';
        } else {
            $msgprompt = ("Please complete required fields");
            $isSuccess = false;
        }
        $isOpen = 'true';
    }
}
?>

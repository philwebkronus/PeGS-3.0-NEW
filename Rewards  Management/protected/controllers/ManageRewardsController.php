<?php

/**
 * @Description: Controller for Manage Rewards Tab
 * @Author: aqdepliyan
 * @DateCreated: 2013-09-20 01:21PM
 */


class ManageRewardsController extends Controller 
{
    public $showdialog;
    public $message;
    
    /**
     * @Description: For fetching initial data in manage rewards
     * @DateCreated: 2013-09-20
     */
    public function actionManageRewards() {
        $model = new ManageRewardsForm();
        $rewarditems = new RewardItemsModel();

        $rewardtype = 1;
        $filterby = 0;
        $data = $rewarditems->getRewardItemsForManageRewards($rewardtype, $filterby);
        $countdata = count($data);
        $ctr = 0;
        $arraynewlist = array();
        $arraydata = array();
        if (is_array($data) && sizeof($data) > 0) {
            if (!array_key_exists('errcode', $data)) {
                do {
//                    if($data[$ctr]['PartnerName'] == null || $data[$ctr]['PartnerName'] == '')
//                        $data[$ctr]['PartnerName'] = 'n/a';
//                    if($data[$ctr]['Category'] == null || $data[$ctr]['Category'] == '')
//                        $data[$ctr]['Category'] = 'n/a';
                    $arraynewlist['RewardItemID'] = $data[$ctr]['RewardItemID']; 
                    $arraynewlist['PartnerName'] = urldecode($data[$ctr]['PartnerName']); 
                    $arraynewlist['ItemName'] = urldecode($data[$ctr]['ItemName']); 
                    $arraynewlist['Category'] = urldecode($data[$ctr]['Category']); 
                    $arraynewlist['Points'] = urldecode($data[$ctr]['Points']); 
                    $arraynewlist['Eligibility'] = urldecode($data[$ctr]['Eligibility']); 
                    $arraynewlist['Status'] = urldecode($data[$ctr]['Status']); 
                    $arraynewlist['PromoPeriod'] = urldecode($data[$ctr]['OfferStartDate']." - ".$data[$ctr]['OfferEndDate']); 
                    if($arraynewlist['Status'] != 'Active'){
                        $arraynewlist['Action'] = "<div title='actionbuttons' style='padding-top: 3px;'><a href='javascript:void(0)' title='Edit' RewardItemID='".$arraynewlist['RewardItemID']."' id='editbutton'><img id='editimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-edit.png'></a>&nbsp;&nbsp;<a href='javascript:void(0)' title='Delete' RewardItemID='".$arraynewlist['RewardItemID']."' id='deletebutton'><img id='deleteimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-delete.png'></a>&nbsp;&nbsp;<a href='javascript:void(0)' title='Replenish' RewardItemID='".$arraynewlist['RewardItemID']."' id='refillbutton'><img id='refillimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-refill.png' ></a></div>";
                    } else {
                        $arraynewlist['Action'] = "<div title='actionbuttons' style='padding-top: 3px;'><a href='javascript:void(0)' title='Edit' style='visibility: hidden;' RewardItemID='".$arraynewlist['RewardItemID']."' id='editbutton'><img id='editimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-edit.png'></a>&nbsp;&nbsp;<a href='javascript:void(0)' title='Delete' RewardItemID='".$arraynewlist['RewardItemID']."' id='deletebutton'><img id='deleteimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-delete.png'></a>&nbsp;&nbsp;<a href='javascript:void(0)' title='Replenish' RewardItemID='".$arraynewlist['RewardItemID']."' id='refillbutton'><img id='refillimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-refill.png' ></a></div>";
                    }                 
                    $arraydata[$ctr]=$arraynewlist;
                    $ctr++;
                }while($ctr < $countdata);
            }
        }
        
        if (Yii::app()->request->isAjaxRequest) {
            echo jqGrid::generateJSON(10, $arraydata, 'RewardItemID');
            Yii::app()->end();
        }
        unset($arraynewlist,$arraydata);
        $this->render('managerewards', array('model' => $model));
    }

    /**
     * @Description: For fetching data after changing the view rewards by and/or rewards type (reward e-coupon, raffle e-coupon)
     * @DateCreated: 2013-09-24
     */
    public function actionRewardsList(){
        $rewarditems = new RewardItemsModel();

        if(isset($_POST['viewrewardsby'])){
            $filterby = $_POST['viewrewardsby'];
        }
        
        if(isset($_POST['rewardtype'])){
            $rewardtype = $_POST['rewardtype'];
        }
        $page = $_POST['page'];
        $limit = $_POST['rows'];
        $data = $rewarditems->getRewardItemsForManageRewards($rewardtype, $filterby);

        $countdata = count($data);
        
        $arraynewlist = array();
        $response = null;
        
        if($countdata > 0){
            
            $total_pages = ceil($countdata/$limit);
            if ($page > $total_pages)
                $page = $total_pages;
            
            $response['page'] = $page;
            $response['total'] = $total_pages;
            $response['records'] = $countdata;
            
            for($ctr = 0; $ctr < $countdata; $ctr++){
//                if($data[$ctr]['PartnerName'] == null || $data[$ctr]['PartnerName'] == '')
//                    $data[$ctr]['PartnerName'] = 'n/a';
//                if($data[$ctr]['Category'] == null || $data[$ctr]['Category'] == '')
//                    $data[$ctr]['Category'] = 'n/a';
                $arraynewlist['RewardItemID'] = $data[$ctr]['RewardItemID']; 
                $arraynewlist['PartnerName'] = $data[$ctr]['PartnerName']; 
                $arraynewlist['ItemName'] = $data[$ctr]['ItemName']; 
                $arraynewlist['Category'] = $data[$ctr]['Category']; 
                $arraynewlist['Points'] = $data[$ctr]['Points']; 
                $arraynewlist['Eligibility'] = $data[$ctr]['Eligibility']; 
                $arraynewlist['Status'] = $data[$ctr]['Status']; 
                
                $arraynewlist['PromoPeriod'] = $data[$ctr]['OfferStartDate']." - ".$data[$ctr]['OfferEndDate']; 
                
                if($arraynewlist['Status'] != 'Active'){
                    if($rewardtype == 2){
                        $arraynewlist['Action'] = "<div title='actionbuttons' style='padding-top: 3px;'><a href='javascript:void(0)' title='Edit' RewardItemID='".$arraynewlist['RewardItemID']."' id='editbutton'><img id='editimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-edit.png'></a>&nbsp;&nbsp;<a href='javascript:void(0)' title='Delete' RewardItemID='".$arraynewlist['RewardItemID']."' id='deletebutton'><img id='deleteimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-delete.png'></a>&nbsp;&nbsp;<a href='javascript:void(0)' style='visibility: hidden;' title='Replenish' RewardItemID='".$arraynewlist['RewardItemID']."' id='refillbutton'><img id='refillimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-refill.png' ></a></div>";
                    } else {
                        $arraynewlist['Action'] = "<div title='actionbuttons' style='padding-top: 3px;'><a href='javascript:void(0)' title='Edit' RewardItemID='".$arraynewlist['RewardItemID']."' id='editbutton'><img id='editimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-edit.png'></a>&nbsp;&nbsp;<a href='javascript:void(0)' title='Delete' RewardItemID='".$arraynewlist['RewardItemID']."' id='deletebutton'><img id='deleteimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-delete.png'></a>&nbsp;&nbsp;<a href='javascript:void(0)' title='Replenish' RewardItemID='".$arraynewlist['RewardItemID']."' id='refillbutton'><img id='refillimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-refill.png' ></a></div>";
                    }
                } else {
                    if($rewardtype == 2){
                        $arraynewlist['Action'] = "<div title='actionbuttons' style='padding-top: 3px;'><a href='javascript:void(0)' title='Edit' style='visibility: hidden;' RewardItemID='".$arraynewlist['RewardItemID']."' id='editbutton'><img id='editimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-edit.png'></a>&nbsp;&nbsp;<a href='javascript:void(0)' title='Delete' RewardItemID='".$arraynewlist['RewardItemID']."' id='deletebutton'><img id='deleteimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-delete.png'></a>&nbsp;&nbsp;<a href='javascript:void(0)' title='Replenish' style='visibility: hidden;'  RewardItemID='".$arraynewlist['RewardItemID']."' id='refillbutton'><img id='refillimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-refill.png' ></a></div>";
                    } else {
                        $arraynewlist['Action'] = "<div title='actionbuttons' style='padding-top: 3px;'><a href='javascript:void(0)' title='Edit' style='visibility: hidden;' RewardItemID='".$arraynewlist['RewardItemID']."' id='editbutton'><img id='editimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-edit.png'></a>&nbsp;&nbsp;<a href='javascript:void(0)' title='Delete' RewardItemID='".$arraynewlist['RewardItemID']."' id='deletebutton'><img id='deleteimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-delete.png'></a>&nbsp;&nbsp;<a href='javascript:void(0)' title='Replenish' RewardItemID='".$arraynewlist['RewardItemID']."' id='refillbutton'><img id='refillimage".$arraynewlist['RewardItemID']."' src='../../images/ui-icon-refill.png' ></a></div>";
                    }
                }     
                
                $response['rows'][$ctr]['id'] = $arraynewlist["RewardItemID"];
                $response['rows'][$ctr]['cell'] = array(
                                                                            $arraynewlist["PartnerName"],
                                                                            $arraynewlist["ItemName"],
                                                                            $arraynewlist["Category"],
                                                                            $arraynewlist["Points"],
                                                                            $arraynewlist["Eligibility"],
                                                                            $arraynewlist["Status"],
                                                                            $arraynewlist["PromoPeriod"],
                                                                            $arraynewlist["Action"],
                                                                        );
            }
            
        } else {
            $ctr = 0;
            $response['page'] = 0;
            $response['total'] = 0;
            $response['records'] = 0;
            $msg = "Record is Empty.";
            $response['msg'] = $msg;
        }
        unset($arraynewlist);
        echo json_encode($response);
        exit;
    }
    /**
     * @Description: For Manipulating Rewards (Delete, Edit and Add)
     * @Author: aqdepliyan
     * @DateCreated: 2013-09-24
     */
    public function actionManipulateReward(){
        $model = new ManageRewardsForm();
        $rewarditems = new RewardItemsModel();

        if(isset($_POST['ManageRewardsForm'])){
            $model->attributes = $_POST['ManageRewardsForm'];

            $functionname = $_POST['hdnFunctionName'];
            switch ($functionname){
                case 'DeleteReward':
                    $rewarditemid = $_POST['hdnRewardItemID'];
                    $status = 4;
                    $result = $rewarditems->updateRewardStatus($rewarditemid, $status);
                    
                    if($result['TransCode'] == 0){
                        $this->showdialog = true;
                        $this->message = "Reward Item/Coupon has been successfully deleted.";
                    } else {
                        $this->showdialog = true;
                        $this->message = "Failed to delete Reward Item/Coupon.";
                    }
                    $this->render('managerewards', array('model' => $model));
                    break;
                case 'EditReward':
                    $rewarditemid = $_POST['hdnRewardItemID-edit'];
                    $rewardid = $_POST['hdnRewardID-edit'];
                    $thblimitedphoto = $_POST['thblimitedphoto'];
                    $thboutofstockphoto = $_POST['thboutofstockphoto'];
                    $ecouponphoto = $_POST['ecouponphoto'];
                    $lmlimitedphoto = $_POST['lmlimitedphoto'];
                    $lmoutofstockphoto = $_POST['lmoutofstockphoto'];
                    $websliderphoto = $_POST['websliderphoto'];
                    $startdate = $_POST['from_date']." ".$_POST['from_hour'].":".$_POST['from_min'].":".$_POST['from_sec'];
                    $enddate = $_POST['to_date']." ".$_POST['to_hour'].":".$_POST['to_min'].":".$_POST['to_sec'];
                    $editpoints = preg_replace('/[^0-9]/s', '', $model->editpoints);
                    
                    if($model->editabout == ''){
                        $about = null;
                    } else { $about = $model->editabout; }
                    if($model->editterms == ''){
                        $terms = null;
                    } else { $terms = $model->editterms; }
                    if($model->editpartner == ''){
                        $partnerid = null;
                    } else { $partnerid = $model->editpartner; }
                    if($model->editcategory == ''){
                        $categoryid = null;
                    } else { $categoryid = $model->editcategory; }
                    if($model->editsubtext == ''){
                        $subtext = null;
                    } else { $subtext = $model->editsubtext; }
                    
                    if($rewardid == 2){
                        if(($thblimitedphoto != null && $thblimitedphoto != '') && ($thboutofstockphoto == null || $thboutofstockphoto == '')){
                            $thboutofstockphoto = $thblimitedphoto;
                        }
                        
                        if(($lmlimitedphoto != null && $lmlimitedphoto != '') && ($thblimitedphoto == null || $thblimitedphoto == '')){
                            $lmoutofstockphoto = $lmlimitedphoto;
                        }
                    }

                    $result = $rewarditems->UpdateRewardItem($rewarditemid, $rewardid, $model->editrewarditem, $editpoints, $model->editeligibility, $model->editstatus, $startdate, $enddate, 
                                                                                                                $partnerid, $categoryid, $subtext, $about, $terms, $thblimitedphoto, $thboutofstockphoto, 
                                                                                                                $ecouponphoto, $lmlimitedphoto, $lmoutofstockphoto, $websliderphoto);
                    if($result['TransCode'] == 0){
                        $this->showdialog = true;
                        if($rewardid == "2"){
                            $this->message = "Raffle e-Coupon has been successfully updated.";
                        } else {
                            $this->message = "Reward e-Coupon has been successfully updated.";
                        }
                    } else {
                        $this->showdialog = true;
                        if($rewardid == "2"){
                            $this->message = "Failed to update Raffle e-Coupon.";
                        } else {
                            $this->message = "Failed to update Reward e-Coupon.";
                        }
                    }
                    $this->render('managerewards', array('model' => $model));
                    
                    break;
                case 'AddReward':
                    $thblimitedphoto = $_POST['thblimitedphoto'];
                    $thboutofstockphoto = $_POST['thboutofstockphoto'];
                    $ecouponphoto = $_POST['ecouponphoto'];
                    $lmlimitedphoto = $_POST['lmlimitedphoto'];
                    $lmoutofstockphoto = $_POST['lmoutofstockphoto'];
                    $websliderphoto = $_POST['websliderphoto'];
                    $startdate = $_POST['add_from_date']." ".$_POST['from_hour'].":".$_POST['from_min'].":".$_POST['from_sec'];
                    $enddate = $_POST['add_to_date']." ".$_POST['to_hour'].":".$_POST['to_min'].":".$_POST['to_sec'];
                    $drawdate = null;
                    
                    if($model->addabout == ''){
                        $about = null;
                    } else { $about = $model->addabout; }
                    if($model->addrewardid == ''){
                        $rewardid = null;
                    } else { $rewardid = $model->addrewardid; }
                    if($model->addterms == ''){
                        $terms = null;
                    } else { $terms = $model->addterms; }
                    if($model->addpartner == ''){
                        $partnerid = null;
                    } else { $partnerid = $model->addpartner; }
                    if($model->addcategory == ''){
                        $categoryid = null;
                    } else { $categoryid = $model->addcategory; }
                    if($model->addsubtext == ''){
                        $subtext = null;
                    } else { $subtext = $model->addsubtext; }
                    if($model->addpromocode == ''){
                        $promocode = null;
                    } else { $promocode = $model->addpromocode; }
                    if($model->addpromoname == ''){
                        $promoname = null;
                    } else { $promoname = $model->addpromoname; }
                    
                    if($rewardid == 2){
                        if(($thblimitedphoto != null && $thblimitedphoto != '') && ($thboutofstockphoto == null || $thboutofstockphoto == '')){
                            $thboutofstockphoto = $thblimitedphoto;
                        }
                        
                        if(($lmlimitedphoto != null && $lmlimitedphoto != '') && ($thblimitedphoto == null || $thblimitedphoto == '')){
                            $lmoutofstockphoto = $lmlimitedphoto;
                        }
                        $drawdate = $_POST['drawdate']." ".$_POST['drawdate_hour'].":".$_POST['drawdate_min'].":".$_POST['drawdate_sec'];
                    }
                    $validated = true;
                    $validateitem = $rewarditems->ValidateItem($model->addrewarditem);
                    $count =  count($validateitem);
                    if($count > 0){
                        for($itr = 0; $itr < $count; $itr++){
                            if($validateitem[$itr]["Status"] != 4){
                                $validated =false;
                                break;
                            }
                        }
                    }
                    
                    if($validated) {
                        
                        if($partnerid != null || $partnerid != ""){
                            $getpartneritemid = $rewarditems->GetPartnerItemID($partnerid);
                            $partneritemid = (int)$getpartneritemid[0]["lastpartneritemid"]+1;
                            $itemcount = preg_replace('/[^0-9]/s', '', $model->additemcount);
                            $addpoints = preg_replace('/[^0-9]/s', '', $model->addpoints);
                            $serialcodestart = "00001";
                            $str = (string)$itemcount;
                            $serialcodeend = str_pad($str, 5, "0", STR_PAD_LEFT);
                        } else {
                            $partneritemid = null;
                            $itemcount = preg_replace('/[^0-9]/s', '', $model->additemcount);
                            $addpoints = preg_replace('/[^0-9]/s', '', $model->addpoints);
                            $serialcodestart = "0";
                            $serialcodeend = "0";
                        }

                        $addnewrewarditem = $rewarditems->InsertRewardItem((int)$partneritemid, (int)$rewardid, $model->addrewarditem, (int)$addpoints, (int)$model->addeligibility, 
                                                                                                                                                (int)$model->addstatus, $startdate,  $enddate, $partnerid, $categoryid, $subtext, $about, $terms, (int)$itemcount, 
                                                                                                                                                $thblimitedphoto, $thboutofstockphoto, $ecouponphoto, $lmlimitedphoto, 
                                                                                                                                                $lmoutofstockphoto, $websliderphoto, $promocode, $promoname, $drawdate, 
                                                                                                                                                $serialcodestart, $serialcodeend);
                        
                        if($addnewrewarditem['TransCode'] == 0){
                            $this->showdialog = true;
                            if($rewardid == "2"){
                                $this->message = "New Raffle e-Coupon has been successfully added.";
                            } else {
                                $this->message = "New Reward e-Coupon has been successfully added.";
                            }
                        } else {
                            $this->showdialog = true;
                            if($rewardid == "2"){
                                $this->message = "Failed to add New Reward e-Coupon.";
                            } else {
                                $this->message = "Failed to add New Raffle e-Coupon.";
                            }
                        }
                        $this->render('managerewards', array('model' => $model));
                    }

                    break;
                case 'ReplenishItem':
                    $rewarditemid = $_POST['hdnRewardItemID-replenishform'];

                    $itemcount = $model->inventoryupdate;
                    $currentinventory = $model->currentinventory;
                    $addeditemcount = $model->additems;
                    $newitemcount = str_replace( ',', '', $itemcount );
                    $currentitemcount = str_replace( ',', '', $currentinventory );
                    $addeditemcount = str_replace( ',', '', $addeditemcount );
                    if((int)$newitemcount != (int)$currentinventory){
                        if($newitemcount != 0 || $newitemcount != null){
                            $getserialendcode = $rewarditems->GetSerialCodeEnd($rewarditemid);
                            $total = (int)$getserialendcode[0]["SerialCodeEnd"] + (int)$addeditemcount;
                            $str = (string)$total;
                            $newserialcodeend = str_pad($str, 5, "0", STR_PAD_LEFT);
                            $result = $rewarditems->replenishItem($rewarditemid, (int)$newitemcount, (int)$currentitemcount, (int)$addeditemcount, $newserialcodeend);

                            if($result['TransCode'] == 0){
                                $this->showdialog = true;
                                $this->message = $result['TransMsg'];
                            } else {
                                $this->showdialog = true;
                                $this->message = "Failed to replenish Reward Item/Coupon.";
                            }
                        } else {
                            $this->showdialog = true;
                            $this->message = "Failed to replenish Reward Item/Coupon. New Item Count is invalid.";
                        }
                    } else {
                        $this->showdialog = true;
                        $this->message = "Records unchanged.";
                    }
                    $this->render('managerewards', array('model' => $model));
                    break;
            }
        } else {
            $this->redirect('managerewards');
        }
    }
    
    /**
     * @Description: For fetching List of Partners
     * @Author: aqdepliyan
     * @DateCreated: 2013-10-01
     */
    public function actionActivePartners(){
        $refpartners = new RefPartnerModel();
        
        $data = $refpartners->getPartners();
        if(isset($data[0])){
            $result['ListofPartners'] = $data;
            $result['CountofPartners'] = count($data);
            $result['showdialog'] = false;
            $result['message'] = '';
        } else {
            $result['showdialog'] = true;
            $result['message'] = "Reward Item Partner is deactivated.";
        }
        echo json_encode($result);
        exit;
    }
    
    /**
     * @Description: For fetching List of Category
     * @Author: aqdepliyan
     * @DateCreated: 2013-10-01
     */
    public function actionCategoryList(){
        $refcategory = new RefCategoryModel();
        
        $data = $refcategory->getCategory();
        if(isset($data[0])){
            $result['ListofCategories'] = $data;
            $result['CountofCategories'] = count($data);
            $result['showdialog'] = false;
            $result['message'] = '';
        } else {
            $result['showdialog'] = true;
            $result['message'] = "No Active Category.";
        }
        echo json_encode($result);
        exit;
    }

    /**
     * @Description: For Fetching List of Rewards
     * @Author: aqdepliyan
     * @DateCreated: 2013-09-25
     * @param int $RewardItemID
     */
    public function actionRewardDetails($RewardItemID){
        $rewarditems = new RewardItemsModel();

        $rewarditemid = isset($RewardItemID) ? $rewarditemid = $RewardItemID : $rewarditemid = 0;
        $data = $rewarditems->getRewardDetailsUsingRewardItemID($rewarditemid);
        $countdata = count($data);
        
        if($countdata == 1){
            if($data[0]['Status'] == "Active"){
                $result['showdialog'] = true;
                $result['message'] = "Reward Item/Coupon is active and not editable.";
            } else if($data[0]['Status'] == "Deactivated"){
                $result['showdialog'] = true;
                $result['message'] = "Reward Item/Coupon is already removed.";
            } else {
                $result = $data[0];
                $result['showdialog'] = false;
                $OfferStartDate = new DateTime($result['OfferStartDate']);
                $StartHour = $OfferStartDate->format("H");
                $StartMin = $OfferStartDate->format("i");
                $StartSec = $OfferStartDate->format("s");
                $StartDate = $OfferStartDate->format("Y-m-d H:i:s");
                $result['OfferStartDate'] = $OfferStartDate->format("Y-m-d");
                $result['OfferStartHour'] = $StartHour;
                $result['OfferStartMin'] = $StartMin;
                $result['OfferStartSec'] = $StartSec;
                $OfferEndDate = new DateTime($result['OfferEndDate']);
                $EndHour = $OfferEndDate->format("H");
                $EndMin = $OfferEndDate->format("i");
                $EndSec = $OfferEndDate->format("s");
                $EndDate = $OfferEndDate->format("Y-m-d H:i:s");
                $result['OfferEndDate'] = $OfferEndDate->format("Y-m-d");
                $result['OfferEndHour'] = $EndHour;
                $result['OfferEndMin'] = $EndMin;
                $result['OfferEndSec'] = $EndSec;
                $result["PromoDate"] = $StartDate.' - '.$EndDate;
                $result['message'] = '';
            }
        } else {
            $result['showdialog'] = true;
            $result['message'] = "Reward Item/Coupon is not found.";
        }
        echo json_encode($result);
        exit;
    }
    
    /**
     * @Description: For fetching current inventory of a reward item/coupon
     * @Author: aqdepliyan 
     * @DateCreated: 2013-10-01
     * @param int $RewardItemID
     */
    public function actionCurrentInventory($RewardItemID){
        $rewarditems = new RewardItemsModel();
        
        $data = $rewarditems->getCurrentInventory($RewardItemID);
        if(isset($data[0])){
            $result = $data[0];
            $result['showdialog'] = false;
            $result['message'] = '';
        } else {
            $result['showdialog'] = true;
            $result['message'] = "Reward Item/Coupon is not found.";
        }
        echo json_encode($result);
        exit;
    }

    private function sanitize($str) {
        $str = trim($str);
        
        return $str;
    }
    
    
    public function actionThumbnailLimited () 
    {
        echo "<link rel='stylesheet' type='text/css' href='".Yii::app()->request->baseUrl."/css/main.css' />";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery.min.js'></script>";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery-ui.min.js'></script>";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery-1.7.2.min.js'></script>";
        
        if(isset(Yii::app()->session['uploadedfile']) && isset(Yii::app()->session['message'])){
            if(Yii::app()->session['message'] == "Image Uploaded"){
                    $scripts = '$("#thblimited").attr("src","../..'.Yii::app()->params['image_path'].Yii::app()->session['uploadedfile'].'");
                                        $("#thblimitedmsgbox").removeAttr("style");
                                        $("#thblimitedmsgbox").attr("style", "z-index: 5; background-color: green; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#thbsubmit_limited").attr("IsFilled", "Yes");
                                        $("#thbsubmit_limited").attr("ImageName", "'.Yii::app()->session['uploadedfile'].'");
                                        $("#thblimited").attr("IsFilled", "Yes");
                                        $("#thblimited").attr("ImageName", "'.Yii::app()->session['uploadedfile'].'");
                                        $("#msg1").removeAttr("style");
                                        $("#msg1").attr("style", "position:relative; top: -108px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg1").css("color", "white");
                                        $("#msg1").html("'.Yii::app()->session['message'].'");';
            } else if(Yii::app()->session['message'] == "Invalid Image Dimension") { 
                    $scripts = '$("#thblimited").attr("src","../../images/image_preview1.jpg");
                                        $("#thblimitedmsgbox").removeAttr("style");
                                        $("#thblimitedmsgbox").attr("style", "z-index: 5; background-color: red; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#msg1").removeAttr("style");
                                        $("#msg1").attr("style", "position:relative; top: -115px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg1").css("color", "white");
                                        $("#msg1").html("'.Yii::app()->session['message'].'");';
            } else { 
                    $scripts = ' $("#thblimited").attr("src","../../images/image_preview1.jpg");
                                        $("#thblimitedmsgbox").removeAttr("style");
                                        $("#thblimitedmsgbox").attr("style", "z-index: 5; background-color: red; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#msg1").removeAttr("style");
                                        $("#msg1").attr("style", "position:relative; top: -115px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg1").css("color", "white");
                                        $("#msg1").html("'.Yii::app()->session['message'].'"); ';
            }
        } else {
            $scripts = '$("#thblimited").removeAttr("src");
                                $("#thblimited").attr("src","../../images/image_preview1.jpg");';
        }

        echo ' <script type="text/javascript">
                        $("#thbsubmitlimited").live("click", function(){
                            $("#thbsubmit_limited").click();
                        });
                        
                        $("#thbsubmit_limited").live("change", function(){
                                $("#uploadingphotoform1").submit();
                        });
                        
                        jQuery.fn.delay = function(time,func){
                            return this.each(function(){
                                setTimeout(func,time);
                            });
                        };

                        function bodyload(){
                            '.$scripts.'
                            $("#msgcontainer1").delay(5000, function(){
                                $("#msgcontainer1").css("display", "none");
                            });
                        }
                    </script>
                '; 
        
        echo CHtml::beginForm(Yii::app()->createUrl('manageRewards/uploadPhoto'), 
                                "POST", array("enctype" => "multipart/form-data", "id" => "uploadingphotoform1"));

                echo '
                    <body style="background-color: white;" onLoad="javascript: bodyload();">
                    <div id = "upload-image-container" >
                        <div id = "upload-header" style = "color: black; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; width: 150px; text-align: center; font-size: 14px;">Thumbnail Photo<br>(Limited Badge)</div>
                        <img alt = "" src="../../images/image_preview1.jpg" width = "150" height = "150" id = "thblimited">
                        <input type = "file" name = "thbsubmit_limited" id = "thbsubmit_limited" style = "visibility:hidden;position:absolute;top:0;left:0"/>
                        <div id="buttonlike" style="padding-top: 3px; height: 22px; width: 145px; appearance:button; -moz-appearance:button; -webkit-appearance:button;"><a href = "javascript: void(0);"  id = "thbsubmitlimited" title = "Upload" style = " font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; color: black; text-decoration: none; display: block; text-align: center;">Change Photo</a></div>
                        <div id="msgcontainer1"><div id = "thblimitedmsgbox" style = "z-index: 5; float: left; display: none;"></div>
                        <p id="msg1" style="display: none"></p></div>
                    </div>
                    </body>
                ';
                
        if(isset(Yii::app()->session['uploadedfile']) && isset(Yii::app()->session['message'])){
            unset(Yii::app()->session['uploadedfile'],Yii::app()->session['message']);
        }
        echo CHtml::endForm();
        Yii::app()->end();
    }
    
    public function actionThumbnailOutofstock () 
    {
        echo "<link rel='stylesheet' type='text/css' href='".Yii::app()->request->baseUrl."/css/main.css' />";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery-1.7.2.min.js'></script>";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery.min.js'></script>";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery-ui.min.js'></script>";

        if(isset(Yii::app()->session['uploadedfile']) && isset(Yii::app()->session['message'])){
            if(Yii::app()->session['message'] == "Image Uploaded"){
                    $scripts = '$("#thboutofstock").attr("src","../..'.Yii::app()->params['image_path'].Yii::app()->session['uploadedfile'].'");
                                        $("#thboutofstockmsgbox").removeAttr("style");
                                        $("#thboutofstockmsgbox").attr("style", "z-index: 5; background-color: green; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#thbsubmit_outofstock").attr("IsFilled", "Yes");
                                        $("#thbsubmit_outofstock").attr("ImageName", "'.Yii::app()->session['uploadedfile'].'");
                                        $("#msg2").removeAttr("style");
                                        $("#msg2").attr("style", "position:relative; top: -108px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg2").css("color", "white");
                                        $("#msg2").html("'.Yii::app()->session['message'].'");';
            } else if(Yii::app()->session['message'] == "Invalid Image Dimension") { 
                    $scripts = '$("#thboutofstock").attr("src","../../images/image_preview1.jpg");
                                        $("#thboutofstockmsgbox").removeAttr("style");
                                        $("#thboutofstockmsgbox").attr("style", "z-index: 5; background-color: red; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#msg2").removeAttr("style");
                                        $("#msg2").attr("style", "position:relative; top: -115px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg2").css("color", "white");
                                        $("#msg2").html("'.Yii::app()->session['message'].'");';
            } else { 
                    $scripts = ' $("#thboutofstock").attr("src","../../images/image_preview1.jpg");
                                        $("#thboutofstockmsgbox").removeAttr("style");
                                        $("#thboutofstockmsgbox").attr("style", "z-index: 5; background-color: red; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#msg2").removeAttr("style");
                                        $("#msg2").attr("style", "position:relative; top: -115px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg2").css("color", "white");
                                        $("#msg2").html("'.Yii::app()->session['message'].'"); ';
            }
        } else {
            $scripts = '$("#thboutofstock").removeAttr("src");
                                $("#thboutofstock").attr("src","../../images/image_preview1.jpg");';
        }
        
        echo ' <script type="text/javascript">
                        $("#thbsubmitoutofstock").live("click", function(){
                            $("#thbsubmit_outofstock").click();
                        });
                        
                        $("#thbsubmit_outofstock").live("change", function(){
                                $("#uploadingphotoform2").submit();
                        });
                        
                        jQuery.fn.delay = function(time,func){
                            return this.each(function(){
                                setTimeout(func,time);
                            });
                        };
                        
                        function bodyload(){
                            '.$scripts.'
                            $("#msgcontainer2").delay(5000, function(){
                                $("#msgcontainer2").css("display", "none");
                            });
                        }
                    </script>
                '; 
        
        echo CHtml::beginForm(Yii::app()->createUrl('manageRewards/uploadPhoto'), 
                                "POST", array("enctype" => "multipart/form-data", "id" => "uploadingphotoform2"));
        
                echo '
                    <body style="background-color: white;" onLoad="javascript: bodyload();">
                        <div id="upload-image-container" >
                            <div id="upload-header" style="color: black; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; width: 150px; text-align: center; font-size: 14px;">Thumbnail Photo<br>(Out-Of-Stock Badge)</div>
                            <img alt=""  src="../../images/image_preview1.jpg" width="150" height="150" id="thboutofstock">
                            <input type="file" name="thbsubmit_outofstock" id="thbsubmit_outofstock"  style="visibility:hidden;position:absolute;top:0;left:0"/>
                            <div id="buttonlike" style="padding-top: 3px; height: 22px; width: 145px;appearance:button; -moz-appearance:button; -webkit-appearance:button;"><a href = "javascript: void(0);"  id = "thbsubmitoutofstock" title = "Upload" style = " font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; color: black; text-decoration: none; display: block; text-align: center;">Change Photo</a></div>
                            <!--<input type="submit" name="thbsubmitoutofstock" id="thbsubmitoutofstock" style="font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; height: 30px; width: 150px;" value="Change Photo">-->
                            <div id="msgcontainer2"><div id = "thboutofstockmsgbox" style = "z-index: 5; float: left; display: none;"></div>
                            <p id="msg2" style="display: none"></p></div>
                        </div>
                    </body>
                ';
        
        if(isset(Yii::app()->session['uploadedfile']) && isset(Yii::app()->session['message'])){
            unset(Yii::app()->session['uploadedfile'],Yii::app()->session['message']);
        }
                
        echo CHtml::endForm();
        Yii::app()->end();
    }
    
    public function actionECoupon () 
    {
        echo "<link rel='stylesheet' type='text/css' href='".Yii::app()->request->baseUrl."/css/main.css' />";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery-1.7.2.min.js'></script>";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery.min.js'></script>";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery-ui.min.js'></script>";
        
        if(isset(Yii::app()->session['uploadedfile']) && isset(Yii::app()->session['message'])){
            if(Yii::app()->session['message'] == "Image Uploaded"){
                    $scripts = '$("#ecoupon").attr("src","../..'.Yii::app()->params['image_path'].Yii::app()->session['uploadedfile'].'");
                                        $("#ecouponmsgbox").removeAttr("style");
                                        $("#ecouponmsgbox").attr("style", "z-index: 5; background-color: green; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#ecoupon_submit").attr("IsFilled", "Yes");
                                        $("#ecoupon_submit").attr("ImageName", "'.Yii::app()->session['uploadedfile'].'");
                                        $("#msg3").removeAttr("style");
                                        $("#msg3").attr("style", "position:relative; top: -108px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg3").css("color", "white");
                                        $("#msg3").html("'.Yii::app()->session['message'].'");';
            } else if(Yii::app()->session['message'] == "Invalid Image Dimension") { 
                    $scripts = '$("#ecoupon").attr("src","../../images/image_preview1.jpg");
                                        $("#ecouponmsgbox").removeAttr("style");
                                        $("#ecouponmsgbox").attr("style", "z-index: 5; background-color: red; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#msg3").removeAttr("style");
                                        $("#msg3").attr("style", "position:relative; top: -115px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg3").css("color", "white");
                                        $("#msg3").html("'.Yii::app()->session['message'].'");';
            } else { 
                    $scripts = ' $("#ecoupon").attr("src","../../images/image_preview1.jpg");
                                        $("#ecouponmsgbox").removeAttr("style");
                                        $("#ecouponmsgbox").attr("style", "z-index: 5; background-color: red; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#msg3").removeAttr("style");
                                        $("#msg3").attr("style", "position:relative; top: -115px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg3").css("color", "white");
                                        $("#msg3").html("'.Yii::app()->session['message'].'"); ';
            }
        } else {
            $scripts = '$("#ecoupon").removeAttr("src");
                                $("#ecoupon").attr("src","../../images/image_preview1.jpg");';
        }
        
        echo ' <script type="text/javascript">
                        $("#ecouponsubmit").live("click", function(){
                            $("#ecoupon_submit").click();
                        });
                        
                        $("#ecoupon_submit").live("change", function(){
                                $("#uploadingphotoform3").submit();
                        });
                        
                        jQuery.fn.delay = function(time,func){
                            return this.each(function(){
                                setTimeout(func,time);
                            });
                        };
                        
                        function bodyload(){
                            '.$scripts.'
                            $("#msgcontainer3").delay(5000, function(){
                                $("#msgcontainer3").css("display", "none");
                            });
                        }
                    </script>
                '; 
        
        
        echo CHtml::beginForm(Yii::app()->createUrl('manageRewards/uploadPhoto'), 
                                "POST", array("enctype" => "multipart/form-data", "id" => "uploadingphotoform3"));
        
                echo '
                    <body style="background-color: white;" onLoad="javascript: bodyload();">
                        <div id="upload-image-container" >
                            <div id="upload-header" style="color: black; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; width: 150px; text-align: center; font-size: 14px;">E-Coupon Photo<br><br></div>
                            <img alt=""  src="../../images/image_preview1.jpg" width="150" height="150" id="ecoupon">
                            <input type="file" name="ecoupon_submit" id="ecoupon_submit"  style="visibility:hidden;position:absolute;top:0;left:0"/>
                            <div id="buttonlike" style="padding-top: 3px; height: 22px; width: 145px;appearance:button; -moz-appearance:button; -webkit-appearance:button;"><a href = "javascript: void(0);"  id = "ecouponsubmit" title = "Upload" style = " font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; color: black; text-decoration: none; display: block; text-align: center;">Change Photo</a></div>
                            <!--<input type="submit" name="ecouponsubmit" id="ecouponsubmit" style="font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; height: 30px; width: 150px;" value="Change Photo">-->
                            <div id="msgcontainer3"><div id = "ecouponmsgbox" style = "z-index: 5; float: left; display: none;"></div>
                            <p id="msg3" style="display: none"></p></div>
                        </div>
                    </body>
                ';
        
        if(isset(Yii::app()->session['uploadedfile']) && isset(Yii::app()->session['message'])){
            unset(Yii::app()->session['uploadedfile'],Yii::app()->session['message']);
        }
                
        echo CHtml::endForm();
        Yii::app()->end();
    }
    
    public function actionLearnMoreLimited () 
    {
        echo "<link rel='stylesheet' type='text/css' href='".Yii::app()->request->baseUrl."/css/main.css' />";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery-1.7.2.min.js'></script>";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery.min.js'></script>";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery-ui.min.js'></script>";
        
        if(isset(Yii::app()->session['uploadedfile']) && isset(Yii::app()->session['message'])){
            if(Yii::app()->session['message'] == "Image Uploaded"){
                    $scripts = '$("#lmlimited").attr("src","../..'.Yii::app()->params['image_path'].Yii::app()->session['uploadedfile'].'");
                                        $("#lmlimitedmsgbox").removeAttr("style");
                                        $("#lmlimitedmsgbox").attr("style", "z-index: 5; background-color: green; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#lmsubmit_limited").attr("IsFilled", "Yes");
                                        $("#lmsubmit_limited").attr("ImageName", "'.Yii::app()->session['uploadedfile'].'");
                                        $("#msg4").removeAttr("style");
                                        $("#msg4").attr("style", "position:relative; top: -108px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg4").css("color", "white");
                                        $("#msg4").html("'.Yii::app()->session['message'].'");';
            } else if(Yii::app()->session['message'] == "Invalid Image Dimension") { 
                    $scripts = '$("#lmlimited").attr("src","../../images/image_preview1.jpg");
                                        $("#lmlimitedmsgbox").removeAttr("style");
                                        $("#lmlimitedmsgbox").attr("style", "z-index: 5; background-color: red; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#msg4").removeAttr("style");
                                        $("#msg4").attr("style", "position:relative; top: -115px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg4").css("color", "white");
                                        $("#msg4").html("'.Yii::app()->session['message'].'");';
            } else { 
                    $scripts = ' $("#lmlimited").attr("src","../../images/image_preview1.jpg");
                                        $("#lmlimitedmsgbox").removeAttr("style");
                                        $("#lmlimitedmsgbox").attr("style", "z-index: 5; background-color: red; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#msg4").removeAttr("style");
                                        $("#msg4").attr("style", "position:relative; top: -115px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg4").css("color", "white");
                                        $("#msg4").html("'.Yii::app()->session['message'].'"); ';
            }
        } else {
            $scripts = '$("#lmlimited").removeAttr("src");
                                $("#lmlimited").attr("src","../../images/image_preview1.jpg");';
        }
        
        echo ' <script type="text/javascript">
                        $("#lmsubmitlimited").live("click", function(){
                            $("#lmsubmit_limited").click();
                        });
                        
                        $("#lmsubmit_limited").live("change", function(){
                                $("#uploadingphotoform4").submit();
                        });
                        
                        jQuery.fn.delay = function(time,func){
                            return this.each(function(){
                                setTimeout(func,time);
                            });
                        };
                        
                        function bodyload(){
                            '.$scripts.'
                            $("#msgcontainer4").delay(5000, function(){
                                $("#msgcontainer4").css("display", "none");
                            });
                        }
                    </script>
                '; 
        
        echo CHtml::beginForm(Yii::app()->createUrl('manageRewards/uploadPhoto'), 
                                "POST", array("enctype" => "multipart/form-data", "id" => "uploadingphotoform4"));
        
                echo '
                    <body style="background-color: white;" onLoad="javascript: bodyload();">
                        <div id="upload-image-container" >
                            <div id="upload-header" style="color: black; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; width: 150px; text-align: center; font-size: 14px;">Learn More Photo<br>(Limited Badge)</div>
                            <img alt=""  src="../../images/image_preview1.jpg" width="150" height="150" id="lmlimited">
                            <input type="file" name="lmsubmit_limited" id="lmsubmit_limited"  style="visibility:hidden;position:absolute;top:0;left:0"/>
                            <div id="buttonlike" style="padding-top: 3px; height: 22px; width: 145px;appearance:button; -moz-appearance:button; -webkit-appearance:button;"><a href = "javascript: void(0);"  id = "lmsubmitlimited" title = "Upload" style = " font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; color: black; text-decoration: none; display: block; text-align: center;">Change Photo</a></div>
                            <!--<input type="submit" name="lmsubmitlimited" id="lmsubmitlimited" style="font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; height: 30px; width: 150px;" value="Change Photo">-->
                            <div id="msgcontainer4"><div id = "lmlimitedmsgbox" style = "z-index: 5; float: left; display: none;"></div>
                            <p id="msg4" style="display: none"></p></div>
                        </div>
                    </body>
                ';
        
        if(isset(Yii::app()->session['uploadedfile']) && isset(Yii::app()->session['message'])){
            unset(Yii::app()->session['uploadedfile'],Yii::app()->session['message']);
        }
                
        echo CHtml::endForm();
        Yii::app()->end();
    }
    
    public function actionLearnMoreOutofstock () 
    {
        echo "<link rel='stylesheet' type='text/css' href='".Yii::app()->request->baseUrl."/css/main.css' />";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery-1.7.2.min.js'></script>";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery.min.js'></script>";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery-ui.min.js'></script>";
        
        if(isset(Yii::app()->session['uploadedfile']) && isset(Yii::app()->session['message'])){
            if(Yii::app()->session['message'] == "Image Uploaded"){
                    $scripts = '$("#lmoutofstock").attr("src","../../'.Yii::app()->params['image_path'].Yii::app()->session['uploadedfile'].'");
                                        $("#lmoutofstockmsgbox").removeAttr("style");
                                        $("#lmoutofstockmsgbox").attr("style", "z-index: 5; background-color: green; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#lmsubmit_outofstock").attr("IsFilled", "Yes");
                                        $("#lmsubmit_outofstock").attr("ImageName", "'.Yii::app()->session['uploadedfile'].'");
                                        $("#msg5").removeAttr("style");
                                        $("#msg5").attr("style", "position:relative; top: -108px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg5").css("color", "white");
                                        $("#msg5").html("'.Yii::app()->session['message'].'");';
            } else if(Yii::app()->session['message'] == "Invalid Image Dimension") { 
                    $scripts = '$("#lmoutofstock").attr("src","../../images/image_preview1.jpg");
                                        $("#lmoutofstockmsgbox").removeAttr("style");
                                        $("#lmoutofstockmsgbox").attr("style", "z-index: 5; background-color: red; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#msg5").removeAttr("style");
                                        $("#msg5").attr("style", "position:relative; top: -115px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg5").css("color", "white");
                                        $("#msg5").html("'.Yii::app()->session['message'].'");';
            } else { 
                    $scripts = ' $("#lmoutofstock").attr("src","../../images/image_preview1.jpg");
                                        $("#lmoutofstockmsgbox").removeAttr("style");
                                        $("#lmoutofstockmsgbox").attr("style", "z-index: 5; background-color: red; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#msg5").removeAttr("style");
                                        $("#msg5").attr("style", "position:relative; top: -115px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg5").css("color", "white");
                                        $("#msg5").html("'.Yii::app()->session['message'].'"); ';
            }
        } else {
            $scripts = '$("#lmoutofstock").removeAttr("src");
                                $("#lmoutofstock").attr("src","../../images/image_preview1.jpg");';
        }
        
        echo ' <script type="text/javascript">
                        $("#lmsubmitoutofstock").live("click", function(){
                            $("#lmsubmit_outofstock").click();
                        });
                        
                        $("#lmsubmit_outofstock").live("change", function(){
                                $("#uploadingphotoform5").submit();
                        });
                        
                        jQuery.fn.delay = function(time,func){
                            return this.each(function(){
                                setTimeout(func,time);
                            });
                        };
                        
                        function bodyload(){
                            '.$scripts.'
                            $("#msgcontainer5").delay(5000, function(){
                                $("#msgcontainer5").css("display", "none");
                            });
                        }
                    </script>
                '; 
        
        echo CHtml::beginForm(Yii::app()->createUrl('manageRewards/uploadPhoto'), 
                                "POST", array("enctype" => "multipart/form-data", "id" => "uploadingphotoform5"));
        
                echo '
                    <body style="background-color: white;" onLoad="javascript: bodyload();">
                        <div id="upload-image-container" >
                            <div id="upload-header" style="color: black; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; width: 150px; text-align: center; font-size: 14px;">Learn More Photo<br>(Out-Of-Stock Badge)</div>
                            <img alt=""  src="../../images/image_preview1.jpg" width="150" height="150" id="lmoutofstock">
                            <input type="file" name="lmsubmit_outofstock" id="lmsubmit_outofstock"  style="visibility:hidden;position:absolute;top:0;left:0"/>
                            <div id="buttonlike" style="padding-top: 3px; height: 22px; width: 145px;appearance:button; -moz-appearance:button; -webkit-appearance:button;"><a href = "javascript: void(0);"  id = "lmsubmitoutofstock" title = "Upload" style = " font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; color: black; text-decoration: none; display: block; text-align: center;">Change Photo</a></div>
                            <!--<input type="submit" name="lmsubmitoutofstock" id="lmsubmitoutofstock" style="font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; height: 30px; width: 150px;" value="Change Photo">-->
                            <div id="msgcontainer5"><div id = "lmoutofstockmsgbox" style = "z-index: 5; float: left; display: none;"></div>
                            <p id="msg5" style="display: none"></p></div>
                        </div>
                    </body>
                ';
                
        if(isset(Yii::app()->session['uploadedfile']) && isset(Yii::app()->session['message'])){
            unset(Yii::app()->session['uploadedfile'],Yii::app()->session['message']);
        }
        
        echo CHtml::endForm();
        Yii::app()->end();
    }
    
    public function actionWebsiteSlider () 
    {
        echo "<link rel='stylesheet' type='text/css' href='".Yii::app()->request->baseUrl."/css/main.css' />";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery-1.7.2.min.js'></script>";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery.min.js'></script>";
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery-ui.min.js'></script>";
        
        if(isset(Yii::app()->session['uploadedfile']) && isset(Yii::app()->session['message'])){
            if(Yii::app()->session['message'] == "Image Uploaded"){
                    $scripts = '$("#webslider").attr("src","../../'.Yii::app()->params['image_path'].Yii::app()->session['uploadedfile'].'");
                                        $("#webslidermsgbox").removeAttr("style");
                                        $("#webslidermsgbox").attr("style", "z-index: 5; background-color: green; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#webslider_submit").attr("IsFilled", "Yes");
                                        $("#webslider_submit").attr("ImageName", "'.Yii::app()->session['uploadedfile'].'");
                                        $("#msg6").removeAttr("style");
                                        $("#msg6").attr("style", "position:relative; top: -108px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg6").css("color", "white");
                                        $("#msg6").html("'.Yii::app()->session['message'].'");';
            } else if(Yii::app()->session['message'] == "Invalid Image Dimension") { 
                    $scripts = '$("#webslider").attr("src","../../images/image_preview1.jpg");
                                        $("#webslidermsgbox").removeAttr("style");
                                        $("#webslidermsgbox").attr("style", "z-index: 5; background-color: red; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#msg6").removeAttr("style");
                                        $("#msg6").attr("style", "position:relative; top: -115px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg6").css("color", "white");
                                        $("#msg6").html("'.Yii::app()->session['message'].'");';
            } else { 
                    $scripts = ' $("#webslider").attr("src","../../images/image_preview1.jpg");
                                        $("#webslidermsgbox").removeAttr("style");
                                        $("#webslidermsgbox").attr("style", "z-index: 5; background-color: red; display: block; height: 40px; position:relative; top: -70px;");
                                        $("#msg6").removeAttr("style");
                                        $("#msg6").attr("style", "position:relative; top: -115px; z-index: 6; font-weight: bold; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 12px; text-align: center; vertical-align: middle;");
                                        $("#msg6").css("color", "white");
                                        $("#msg6").html("'.Yii::app()->session['message'].'"); ';
            }
        } else {
            $scripts = '$("#webslider").removeAttr("src");
                                $("#webslider").attr("src","../../images/image_preview1.jpg");';
        }
        
        echo ' <script type="text/javascript">
                        $("#webslidersubmit").live("click", function(){
                            $("#webslider_submit").click();
                        });
                        
                        $("#webslider_submit").live("change", function(){
                                $("#uploadingphotoform6").submit();
                        });
                        
                        jQuery.fn.delay = function(time,func){
                            return this.each(function(){
                                setTimeout(func,time);
                            });
                        };
                        
                        function bodyload(){
                            '.$scripts.'
                            $("#msgcontainer6").delay(5000, function(){
                                $("#msgcontainer6").css("display", "none");
                            });
                        }
                    </script>
                '; 

        echo CHtml::beginForm(Yii::app()->createUrl('manageRewards/uploadPhoto'), 
                                "POST", array("enctype" => "multipart/form-data", "id" => "uploadingphotoform6"));
        
                echo '
                    <body style="background-color: white;" onLoad="javascript: bodyload();">
                        <div id="upload-image-container" >
                            <div id="upload-header" style="color: black; font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; width: 150px; text-align: center; font-size: 14px;">Website Slider Photo<br><br></div>
                            <img alt=""  src="../../images/image_preview1.jpg" width="150" height="150" id="webslider">
                            <input type="file" name="webslider_submit" id="webslider_submit"  style="visibility:hidden;position:absolute;top:0;left:0"/>
                            <div id="buttonlike" style="padding-top: 3px; height: 22px; width: 145px;appearance:button; -moz-appearance:button; -webkit-appearance:button;"><a href = "javascript: void(0);"  id = "webslidersubmit" title = "Upload" style = " font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; color: black; text-decoration: none; display: block; text-align: center;">Change Photo</a></div>
                            <!--<input type="submit" name="webslidersubmit" id="webslidersubmit" style="font-family: Lucida Grande,Lucida Sans,Arial,sans-serif; font-size: 1.1em; height: 30px; width: 150px;" value="Change Photo">-->
                            <div id="msgcontainer6"><div id = "webslidermsgbox" style = "z-index: 5; float: left; display: none;"></div>
                            <p id="msg6" style="display: none"></p></div>
                        </div>
                    </body>
                ';
        
        if(isset(Yii::app()->session['uploadedfile']) && isset(Yii::app()->session['message'])){
            unset(Yii::app()->session['uploadedfile'],Yii::app()->session['message']);
        }
                
        echo CHtml::endForm();
        Yii::app()->end();
    }
    
    public function actionUploadPhoto () {
        
        if(isset(Yii::app()->session['uploadedfile']) && isset(Yii::app()->session['message'])){
            unset(Yii::app()->session['uploadedfile']);
            unset(Yii::app()->session['message']);
        }
        
        echo "<script type='text/javascript' src='".
                Yii::app()->request->baseUrl."/js/jquery-1.7.2.min.js'></script>";
        
         $allowedExtensions = Yii::app()->params['image_allowed_ext'];
         if(isset($_FILES['thbsubmit_limited'])) {
             $file = isset($_FILES['thbsubmit_limited']["name"]) ? $_FILES['thbsubmit_limited'] : '';
             $idname = "thbsubmit_limited";
             $redirectURL = "thumbnailLimited";
         } else if(isset($_FILES['thbsubmit_outofstock'])) {
             $file = isset($_FILES['thbsubmit_outofstock']["name"]) ? $_FILES['thbsubmit_outofstock'] : '';
             $idname = "thbsubmit_outofstock";
             $redirectURL = "thumbnailOutofstock";
         } else if(isset($_FILES['ecoupon_submit'])) {
             $file = isset($_FILES['ecoupon_submit']["name"]) ? $_FILES['ecoupon_submit'] : '';
             $idname = "ecouponsubmit";
             $redirectURL = "eCoupon";
         } else if(isset($_FILES['lmsubmit_limited'])) {
             $file = isset($_FILES['lmsubmit_limited']["name"]) ? $_FILES['lmsubmit_limited'] : '';
             $idname = "lmsubmit_limited";
             $redirectURL = "learnMoreLimited";
         } else if(isset($_FILES['lmsubmit_outofstock'])) {
             $file = isset($_FILES['lmsubmit_outofstock']["name"]) ? $_FILES['lmsubmit_outofstock'] : '';
             $idname = "lmsubmit_outofstock";
             $redirectURL = "learnMoreOutofstock";
         } else if(isset($_FILES['webslider_submit'])) {
             $file = isset($_FILES['webslider_submit']["name"]) ? $_FILES['webslider_submit'] : '';
             $idname = "webslidersubmit";
             $redirectURL = "websiteSlider";
         }
        if($file != ""){
                $fileArr = explode("." , $file["name"]);
                $ext = $fileArr[count($fileArr)-1];

                $size2 = $file['size'];
                $upload_location = Yii::app()->params['image_directory'];

                if($file["error"] > 0){
                    $result['message'] = "Upload Failed";
                } else {
                    if ($size2 < 307200 ) { // 200KB = 204800; 300 KB = 307200; 1 KB = 1024
                        if (in_array($ext, $allowedExtensions)){
                            if(file_exists("$upload_location".$file["name"])){
                                $result['message'] = "File already exists";
                            } else {
                                $image_info = getimagesize($file["tmp_name"]);
                                $image_width = $image_info[0];
                                $image_height = $image_info[1];
                                if($idname == "thbsubmit_limited" || $idname == "thbsubmit_outofstock"){
                                    if($image_width == 280 && $image_height == 175){
                                        move_uploaded_file($file["tmp_name"],"$upload_location".$file["name"]);
                                        $result['uploadedfile'] = $file["name"];
                                        $result['message'] = "Image Uploaded";
                                    } else {
                                        $result['message'] = "Invalid Image Dimension";
                                    }
                                } else if($idname == "lmsubmit_limited" || $idname == "lmsubmit_outofstock"){
                                    if($image_width == 560 && $image_height == 362){
                                        move_uploaded_file($file["tmp_name"],"$upload_location".$file["name"]);
                                        $result['uploadedfile'] = $file["name"];
                                        $result['message'] = "Image Uploaded";
                                    } else {
                                        $result['message'] = "Invalid Image Dimension";
                                    }
                                } else if($idname == "ecouponsubmit"){
//                                    if($image_width == 216 && $image_height == 216){
                                        move_uploaded_file($file["tmp_name"],"$upload_location".$file["name"]);
                                        $result['uploadedfile'] = $file["name"];
                                        $result['message'] = "Image Uploaded";
//                                    } else {
//                                        $result['message'] = "Invalid Image Dimension";
//                                    }
                                } else if($idname == "webslidersubmit"){
                                    if($image_width == 606 && $image_height == 372){
                                        move_uploaded_file($file["tmp_name"],"$upload_location".$file["name"]);
                                        $result['uploadedfile'] = $file["name"];
                                        $result['message'] = "Image Uploaded";
                                    } else {
                                        $result['message'] = "Invalid Image Dimension";
                                    }
                                }

                            }
                        } else {
                            $result['message'] = "Invalid Extension";
                        }
                    } else{
                        $result['message'] = "Too Large File Size";
                    }
                }
        } else {
            $result= "";
        }
        
        if(!isset($result['uploadedfile'])){
            $result['uploadedfile'] = '';
        }
        
        Yii::app()->session['uploadedfile'] = $result['uploadedfile'];
        Yii::app()->session['message'] = $result['message'];
        $this->redirect($redirectURL);
        Yii::app()->end();
        
    }
    
    
}

?>

<?php

/*
 * Description: Fetching and encoding data into JSON array to be displayed in JQGRID for list of Reward Offers to view.
 * @Author: Junjun S. Hernandez
 * Date Created: 07-12-2013 06:35:35 PM
 */

//Attach and Initialize framework
require_once("../../init.inc.php");


//Load Modules to be used.
App::LoadModuleClass("Loyalty", "RewardOffers");
App::LoadModuleClass("Loyalty", "RewardItems");
App::LoadModuleClass("Loyalty", "CardTypes");
App::LoadModuleClass("Loyalty", "Promos");
App::LoadModuleClass("Loyalty", "Partners");

//Load Needed Core Class.
App::LoadCore('Validation.class.php');

//Initialize Modules
$_RewardOffers = new RewardOffers();
$_RewardItems = new RewardItems();
$_CardTypes = new CardTypes();
$_Promos = new Promos();
$_Partners = new Partners();

$page = $_POST['page'];
$limit = $_POST['rows'];
$sidx = $_POST['sidx'];
$sord = $_POST['sord'];

$response = null;

if (isset($_POST['pager'])) {

$RewardOffersID = $_RewardOffers->getRewardOffersID();
$RewardOffers = $_RewardOffers->getRewardOffers();
$countRewardOffers = count($RewardOffersID);

if ($countRewardOffers > 0)
{
$total_pages = ceil($countRewardOffers/$limit);
}
else
{
$total_pages = 0;
}
if ($page > $total_pages)
{
$page = $total_pages;
}

$start = $limit * $page - $limit;
if($countRewardOffers == 0){
$start = 0;
}

$response->page = $page;
$response->total = $total_pages;
$response->records = $countRewardOffers;

$ctr = 0;
;
do {
    

$rewarditemid = $RewardOffers[$ctr]['RewardItemID'];
$cardtypeid = $RewardOffers[$ctr]['CardTypeID'];
$promoid = $RewardOffers[$ctr]['PromoID'];
$partnerid = $RewardOffers[$ctr]['PartnerID'];

$RewardItems = $_RewardItems->getRewardNameByID($rewarditemid);
$CardTypes = $_CardTypes->getCardTypeNameByID($cardtypeid);
$Promos = $_Promos->getPromoNameByID($promoid);
$Partners = $_Partners->getPartnerNameByID($partnerid);

$rewardname = $RewardItems[0]['RewardItemName'];
$cardtype = $CardTypes[0]['CardTypeName'];
$promoname = $Promos[0]['Name'];
$partners = $Partners[0]['PartnerName'];
$requiredpoints = $RewardOffers[$ctr]['RequiredPoints'];
$offerstartdate = $RewardOffers[$ctr]['OfferStartDate'];
$offerendate = $RewardOffers[$ctr]['OfferEndDate'];
switch ($RewardOffers[$ctr]['Status']) {
case 1:
$status = 'Active';
break;
case 2:
$status = 'Inactive';
break;
case 3:
$status = 'Deactivated';
break;
case 4:
$status = 'Expired';
break;
}
$RewardOfferID = "<form name='rewardoffersid' id='rewardoffersid' method='POST' action='editrewardoffers.php'>".
                 "<input type='hidden' id='hdnRewardOfferID' value='".$RewardOffers[$ctr]['RewardOfferID']."' name='hdnRewardOfferID' />".
                 "<input type='hidden' id='hdnRewardItemID' value='".$RewardOffers[$ctr]['RewardItemID']."' name='hdnRewardItemID' />".
                 "<input type='hidden' id='hdnCardTypeID' value='".$RewardOffers[$ctr]['CardTypeID']."' name='hdnCardTypeID' />".
                 "<input type='hidden' id='hdnPromoID' value='".$RewardOffers[$ctr]['PromoID']."' name='hdnPromoID' />".
                 "<input type='hidden' id='hdnPartnerID' value='".$RewardOffers[$ctr]['PartnerID']."' name='hdnPartnerID' />".
                 "<input type='hidden' id='hdnRequiredPoints' value='".$RewardOffers[$ctr]['RequiredPoints']."' name='hdnRequiredPoints' />".
                 "<input type='hidden' id='hdnStartDate' value='".$RewardOffers[$ctr]['OfferStartDate']."' name='hdnStartDate' />".
                 "<input type='hidden' id='hdnEndDate' value='".$RewardOffers[$ctr]['OfferEndDate']."' name='hdnEndDate' />".
                 "<input type='hidden' id='hdnStatus' value='".$RewardOffers[$ctr]['Status']."' name='hdnStatus' />".
                 "<input type='Submit' id='btnUpdateRewardOffer' value='Update Details' class='btnUpdateRewardOffer' name='btnUpdateRewardOffer' /></form>";
$response->rows[$ctr]['id'] = $RewardOffersID[$ctr]['RewardOfferID'];
$response->rows[$ctr]['cell'] = array($rewardname,
 $cardtype,
 $promoname,
 $partners,
 $requiredpoints,
 $offerstartdate,
 $offerendate,
 $status,
 $RewardOfferID
);
$ctr++;
} while ($ctr != $countRewardOffers);
echo json_encode($response);
exit;
break;
}
?>
<?php $this->pageTitle=Yii::app()->name; ?>

<!--<h3>Welcome to <i><php echo CHtml::encode(Yii::app()->name); ?></i></h3>-->

<?php

if(!Yii::app()->user->isAdmin())
    $site = Yii::app()->user->getSiteID();
else
    $site = null;

?>

<div class="summary-group">
    <div class="summary-title">Site Info</div>
    <ul>
        <li>You are logged as: <b><?php echo Yii::app()->user->getName(); ?></b></li>
        
        <?php 
        $accountType = Yii::app()->user->getAccountTypeID();
        if($accountType == 2 || $accountType == 3 || $accountType == 4)
        {?>
            <li>Site Code : <b> <?php echo Yii::app()->user->getSiteCode(); ?></b></li>
        <?php 
        }?>
            
        <li>Total EGM Machines : <b><?php echo Statistics::egmCountBySite($site);?></b></li>
        <li>Active EGM Machines : <b><?php echo Statistics::egmCountBySite($site,1);?></b></li>
    </ul>    
</div>

<div class="summary-group">
    <div class="summary-title">Voucher Summary</div>
    <p class="information"><i>Summary for <?php echo date('F d, Y'); ?></i></p>
<ul>    
    <li>(<b><?php echo Statistics::redeemedTicketsBySite($site); ?></b>) total redeemed tickets </li>
    <li>(<b><?php echo Statistics::generatedTicketsBySite($site); ?></b>) total generated tickets </li>
    <li>(<b><?php echo Statistics::usedTicketsBySite($site); ?></b>) total used tickets </li>
    <li>(<b><?php echo Statistics::usedCouponsBySite($site); ?></b>) total used coupons </li>
    <li>(<b><?php echo Statistics::voidTicketsBySite($site); ?></b>) total void tickets </li>
</ul>
</div>
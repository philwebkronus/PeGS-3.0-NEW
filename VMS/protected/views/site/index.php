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
    </ul>    
</div>

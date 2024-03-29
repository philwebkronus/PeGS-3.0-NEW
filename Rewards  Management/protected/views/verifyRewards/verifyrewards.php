<?php  
/**
 * @Description: Verify Rewards View
 */
 ?>

<?php
$this->breadcrumbs=array(
	'Verify Rewards',
);?>
<?php 
$partners = new RefPartnerModel(); 
$rewarditems = new RewardItemsModel();
$rafflecoupons = new RaffleCouponModel(); 
$partneracct = new PartnersModel();
$this->pageTitle = Yii::app()->name . ' - Verify Rewards';

?>
<script type="text/javascript">
 jQuery(document).ready(function(){
    
    
    $("input[name='rewardsecoupons']").click(function()
    {
        document.getElementById('rewardpage').style.display='block';
        <?php
        if (!isset(Yii::app()->session['PartnerPID']))
        {
        ?>
        document.getElementById('rafflepage').style.display='none';
        <?php
        }
        ?>
        $('#raffleecoupons').attr('checked',false);
        $("#ecouponserial2").val('');
        $("#ecouponsecuritycode2").val('');
        document.getElementById("VerifyRewardsForm_egamespartner").selectedIndex = 0;
        document.getElementById("rewarditem").selectedIndex = 0;
        $("#errmsg").html("");
    });
     
     
    $("input[name='raffleecoupons']").click(function()
    {
        $('#rewardsecoupons').attr('checked',false);
        document.getElementById('rafflepage').style.display='block';
        document.getElementById('rewardpage').style.display='none';
        $("#ecouponserial").val('');
        $("#ecouponsecuritycode").val('');
        document.getElementById("rafflepromo").selectedIndex = 0;
        $("#errmsg2").html(""); 
    });
    //Rewards E-Coupon
    $("#Submit").live("click", function(){
       var partner = $("#egamespartner").val();
       var item = $("#VerifyRewardsForm_rewarditem").val();
       var serialcode = $("#ecouponserial").val();
       var securitycode = $("#ecouponsecuritycode").val();
       
       if (partner == -1 || item == -1 || serialcode == "" || securitycode == "")
       {
           $("#errmsg").html("Make sure all fields are filled out.");
           return false;       
       }
       else
       {
           $("#VerifyRewardsForm").submit();
       }
    });
    
    $("#egamespartner").change(function(){
       $("#errmsg").html(""); 
    });
    $("#VerifyRewardsForm_rewarditem").change(function(){
       $("#errmsg").html(""); 
    });
    $("#ecouponserial").blur(function(){
       $("#errmsg").html(""); 
    });
    $("#ecouponsecuritycode").blur(function(){
       $("#errmsg").html(""); 
    });
    //Raffle E-Coupons
    $("#Submit2").live("click", function(){
       var raffleitem = $("#rafflepromo").val();
       var serialcode2 = $("#ecouponserial2").val();
       var securitycode2 = $("#ecouponsecuritycode2").val();
       
       if (raffleitem == -1 || serialcode2 == "" || securitycode2 == "")
       {
           $("#errmsg2").html("Make sure all fields are filled out.");
           return false;       
       }
       else
       {
           $("#VerifyRewardsForm").submit();
       }
    });
    
    $("#rafflepromo").change(function(){
       $("#errmsg2").html(""); 
    });
    $("#ecouponserial2").blur(function(){
       $("#errmsg2").html(""); 
    });
    $("#ecouponsecuritycode2").blur(function(){
       $("#errmsg2").html(""); 
    });
    
 });
</script>
<!--Disable Right Click--Added by: mgesguerra 09-16-13-->
<script type="text/javascript">
    $(function () {
      $(document).bind("contextmenu",function(e){
        e.preventDefault();
      });
    });
</script>    

<h2>Verify Rewards</h2>
<hr color="black" />
<div class="row" style="padding: 10px 5px; background: #EFEFEF;">
<?php
        $form = $this->beginWidget('CActiveForm', array(
                                   'id' => 'VerifyRewardsForm',
                                   'enableClientValidation' => true,
                                   'clientOptions' => array(
                                       'validateOnSubmit' => true,
                                    ),
        ));
        ?>
    <table>
        <tr>
        <td>
            <input type="radio" id="rewardsecoupons" name="rewardsecoupons" value="1" checked/>
            
            <?php echo CHtml::label("Rewards e-Coupons", "rewardsecoupons");?>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <?php 
            if (!isset(Yii::app()->session['PartnerPID']))
            {
            ?>
                <input type="radio" id="raffleecoupons" name="raffleecoupons" value="2"/>
            
                <?php echo CHtml::label("Raffle e-Coupons", "raffleecoupons");?>
            <?php
            } 
            ?>
        </td>  
        </tr>
        
   
    </table>
     <div id="rewardpage">   
        <table style="width:500px; margin: 50px;">
            <tr>
                <td><span id="errmsg" style="color: red"></span> </td>
            </tr>   
            <tr>
            <td><?php echo CHtml::label("eGames Partner : ", "egamespartnerlbl");?></td>    
            <td>
                <?php
                    $model = new VerifyRewardsForm();
                    if (isset(Yii::app()->session['PartnerPID']))
                    {
                        $partnerpid = Yii::app()->session['PartnerPID'];
                        $egamespartners = $partners->getPartnerByContactPerson($partnerpid);
                        $arrPartners = CHtml::listData($egamespartners, 'PartnerID', 'PartnerName');
                        echo $form->dropDownList($model, 'egamespartner', $arrPartners); 
                    }
                    else if (isset(Yii::app()->session['AID']))
                    {
                        $egamespartners = $partners->getPartners();
                        array_unshift($egamespartners, array('PartnerID'=>'-1', 'PartnerName'=>'- Please Select -'));
                        $arrPartners = CHtml::listData($egamespartners, 'PartnerID', 'PartnerName');
                        echo $form->dropDownList($model, 'egamespartner', $arrPartners, array('ajax' => array(
                                                                                             'type' => 'POST',
                                                                                             'url' => CController::createUrl('ajaxGetRewardItems'),
                                                                                             'update' => '#VerifyRewardsForm_rewarditem',
                                                                                             'data' => array('VerifyRewardsForm_egamespartner'=>'js:this.value')
                                                                             ))); 
                    }
                ?>
            </td>
            </tr>
            <tr>
                <td><?php echo CHtml::label("Reward Item : ", "rewarditemlbl");?></td>    
            <td>
                    <?php
                        if (isset(Yii::app()->session['PartnerPID']))
                        {
                            $partnerpid = Yii::app()->session['PartnerPID'];
                            $arrItems = $rewarditems->getRewardItemsJoinPartners($partnerpid);
                            array_unshift($arrItems, array('RewardItemID'=>'-1', 'ItemName'=>'-Please Select-'));
                            $itemList = CHtml::listData($arrItems, 'RewardItemID', 'ItemName');
                            echo $form->dropDownList($model, 'rewarditem', $itemList); 
                            
                        }
                        else
                        {
                            echo $form->dropDownList($model, 'rewarditem', array('-1'=>'- Please Select -'), array('value'=>'','style' => 'width: 200px;')); 
                        }
                    ?>
            </td>
            </tr>
            <tr>
                <td><?php echo CHtml::label("e-Coupon Serial Code : ", "ecouponserial");?></td>    
            <td>
                    <?php echo $form->textField($model,'ecouponserial', array('id'=>'ecouponserial',  'style'=>'width: 250px;', 'onkeypress' => 'return numberandletter1(event);')); ?>
            </td>
            </tr>
            <tr>
                <td><?php echo CHtml::label("e-Coupon Security Code : ", "ecouponsecuritycode");?></td>    
            <td>
                    <?php echo $form->textField($model,'ecouponsecuritycode', array('id'=>'ecouponsecuritycode',  'style'=>'width: 250px;', 'onkeypress' => 'return numberandletter1(event);')); ?>
            </td>
            </tr>
        </table>
         
           <div id="submitverify" style="width: 100%; text-align: center;">
            <?php echo CHtml::submitButton("Verify", array('id'=>'Submit','name' => 'Submit')); ?>
           </div> 
    </div>
    <?php 
    if (!isset(Yii::app()->session['PartnerPID']))
    {
    ?>
        <div id="rafflepage" style="display: none">   
        <table style="width:500px; margin: 50px;">
            <tr>
                <td><span id="errmsg2" style="color: red"></span> </td>
            </tr>
            <tr>
                <td><?php echo CHtml::label("Raffle Promo : ", "rafflepromo");?></td>    
            <td>
                    <?php
                        $arrItems = $rewarditems->selectRaffleItems();
                        array_unshift($arrItems, array('RewardItemID' => '-1', 'ItemName' => '-Please Select-'));
                        $itemList = CHtml::listData($arrItems, 'RewardItemID','ItemName');
                    ?>
                    <?php echo $form->dropDownList($model, 'rafflepromo', $itemList, array('id' => 'rafflepromo',  'style'=>'width: 200px;')); ?>
            </td>
            </tr>
            <tr>
                <td><?php echo CHtml::label("e-Coupon Serial Code : ", "ecouponserial2");?></td>    
            <td>
                    <?php echo $form->textField($model,'ecouponserial2', array('id'=>'ecouponserial2',  'style'=>'width: 250px;', 'onkeypress' => 'return numberandletter1(event);')); ?>
            </td>
            </tr>
            <tr>
                <td><?php echo CHtml::label("e-Coupon Security Code : ", "ecouponsecuritycode2");?></td>    
            <td>
                    <?php echo $form->textField($model,'ecouponsecuritycode2', array('id'=>'ecouponsecuritycode2',  'style'=>'width: 250px;', 'onkeypress' => 'return numberandletter1(event);')); ?>
            </td>
            </tr>
        </table>

              <div id="submitverify2" style="width: 100%; text-align: center;">
                <?php echo CHtml::submitButton("Verify", array('id'=>'Submit2','name' => 'Submit2')); ?>
              </div> 
        </div>
    <?php
    }
    ?>
    <?php $this->endWidget(); ?>
</div>
<?php echo CHtml::beginForm(array('verifyRewards/verifyrewards'), 'POST', array(
        'id'=>'VerifyRewardsForm',
        'name'=>'VerifyRewardsForm')); ?>
        
<?php echo CHtml::endForm(); ?>    

<?php
/** Start Widget **/
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'alertdialog',
    'options'=>array(
        'autoOpen'=>$this->showDialog2,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>400,
        'height'=>200,
        'buttons' => array
        (
            'OK'=>'js:function(){

                $(this).dialog("close");
            }',
        ),
    ),
));

if ($this->dialogMsg2 != NULL || $this->dialogMsg3 != NULL)
{
    echo $this->dialogMsg;
    echo "<br/>";
    echo $this->dialogMsg2;
    echo "<br/>";
    echo $this->dialogMsg3;
    echo "<br/>";
}
else
{
    echo $this->dialogMsg;
}

    
$this->endWidget('zii.widgets.jui.CJuiDialog');
/** End Widget **/

?>

<?php
/** Start Widget **/
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'successdialog',
    'options'=>array(
        'autoOpen'=>$this->showDialogSuccess,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>350,
        'height'=>200,
        'open'=>'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array
        (
            'PROCEED'=>'js:function(){
                $("#LogVerificationForm").submit();
                $(this).dialog("close");
                $("#ecouponserial").val("");
                $("#ecouponsecuritycode").val("");
                $("#ecouponserial2").val("");
                $("#ecouponsecuritycode2").val("");
                document.getElementById("egamespartner").selectedIndex = 0;
                document.getElementById("rewarditem").selectedIndex = 0;
            }',
        ),
    ),
));

if (isset($this->dialogMsg2) || isset($this->dialogMsg3))
{
    echo $this->dialogMsg;
    echo "<br/>";
    echo $this->dialogMsg2;
    echo "<br/>";
    echo $this->dialogMsg3;
    echo "<br/>";
}
else
{
    echo $this->dialogMsg;
}
?>
<?php echo CHtml::beginForm(array('verifyRewards/logVerification'), 'POST', array(
        'id'=>'LogVerificationForm',
        'name'=>'LogVerificationForm')); ?>
        
<?php echo CHtml::endForm(); ?>        
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
/** End Widget **/

?>


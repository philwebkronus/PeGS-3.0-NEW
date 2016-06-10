<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Change PIN</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
    
        <div class="row">
            <?php echo CHtml::label('SystemUsername','SystemUsername'); ?>
            <?php echo CHtml::textField('SystemUsername'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('AccessDate','AccessDate'); ?>
            <?php echo CHtml::textField('AccessDate'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('Token','Token'); ?>
            <?php echo CHtml::textField('Token'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('Action Code','actionCodeLabel'); ?>
            <?php echo CHtml::dropDownList('ActionCode', "",
              array("" =>"Choose One",0 => 'Reset PIN', 1 => 'Change PIN'),
                    array('id'=>'ActionCode', "name" => "ActionCode", 'onchange' => 'changeMenu();')); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('Card Number','cardNumber'); ?>
            <?php echo CHtml::textField('CardNumber'); ?>
        </div>
    
        <div class="row" id="currentPinDiv" style="display: none">
            <?php echo CHtml::label('Current PIN','currentPinLabel'); ?>
            <?php echo CHtml::textField('CurrentPin'); ?>
        </div>
    
        <div class="row" id="newPinDiv" style="display: none">
            <?php echo CHtml::label('New PIN','newPinLabel'); ?>
            <?php echo CHtml::textField('NewPin'); ?>
        </div>  
        
        <div class="row" style="margin-left: 225px;">
            <?php echo CHtml::submitButton('Invoke'); ?>
        </div>
    
    <?php echo CHtml::endForm(); ?>
</div>
 
<script type="text/javascript">
function changeMenu()
{
    if(document.getElementById('ActionCode').value == 1)
    {
       document.getElementById('currentPinDiv').style.display = "block";
       document.getElementById('newPinDiv').style.display = "block"; 
    }
    
    else 
    {
        document.getElementById('currentPinDiv').style.display = "none";
        document.getElementById('newPinDiv').style.display = "none"; 
    }
}
</script>

<div class="result" style="margin-left: 20px; word-wrap: break-word;">
    <?php if (!is_null($result)) : ?>
        <p>JSON Result :</p>
        <?php echo $result; ?>
    <?php endif; ?>
</div>

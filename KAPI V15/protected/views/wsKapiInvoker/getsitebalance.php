<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Get Site Balance</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
    
        <div class="row">
            <?php echo CHtml::label('Site Code:','sitecode'); ?>
            <?php echo CHtml::textField('txtsitecode'); ?>
        </div>
    
        <div class="row" style="margin-left: 225px;">
            <?php echo CHtml::submitButton('Invoke'); ?>
        </div>
    
    <?php echo CHtml::endForm(); ?>
</div>


<div class="result" style="margin-left: 20px;">
    <?php if (!is_null($result)) : ?>
        <p>JSON Result :</p>
        <?php echo $result; ?>
    <?php endif; ?>
</div><?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
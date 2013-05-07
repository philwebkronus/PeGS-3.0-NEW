<?php

/**
 * @author owliber
 * @date Oct 22, 2012
 * @filename menu-form.php
 * 
 */
?>
<!-- Menu Form -->
<?php

switch($action)
{
    case 'create':   
    ?>
    <?php echo CHtml::beginForm('manage', 'POST', array('id'=>'CreateForm','name'=>'CreateForm')); ?>
    <div class="row">
        <?php echo CHtml::label("Name","Name"); ?><br />
        <?php echo CHtml::textField("Name", ""); ?>
    </div>
    <div class="row">
        <?php echo CHtml::label("Link", "for_Link"); ?><br />
        <?php echo CHtml::textField("Link"); ?>
    </div>
    <div class="row">
        <?php echo CHtml::label("Description", "for_Description"); ?><br />
        <?php echo CHtml::textField("Description"); ?>
    </div>
    <div class="row">
        <?php echo CHtml::label("Status", "for_Status"); ?><br />
        <?php echo CHtml::dropDownList("Status", 1, SiteMenu::getMenuStatus()); ?>   
        <?php echo CHtml::hiddenField("Submit", "Create"); ?>
    </div>
    <?php echo CHtml::endForm(); ?>
    <?php
    
    break;

    case 'update':
    ?>
    <?php echo CHtml::beginForm('manage', 'POST', array('id'=>'UpdateForm','name'=>'UpdateForm')); ?>
    <div class="row">
        <?php echo CHtml::label("Name", "Name"); ?><br />
        <?php echo CHtml::textField("Name", $menu['Name']); ?>
    </div>
    <div class="row">
        <?php echo CHtml::label("Link", "for_Link"); ?><br />
        <?php echo CHtml::textField("Link", $menu['Link']); ?>
    </div>
    <div class="row">
        <?php echo CHtml::label("Description", "for_Description"); ?><br />
        <?php echo CHtml::textField("Description", $menu['Description']); ?>
    </div>
    <div class="row">
        <?php echo CHtml::label("Status", "for_Status"); ?><br />
        <?php echo CHtml::dropDownList("Status", $menu['Status'], SiteMenu::getMenuStatus()); ?>
        <?php echo CHtml::hiddenField("MenuID", $menu["MenuID"]); ?>
        <?php echo CHtml::hiddenField("Submit", "Update"); ?>
    </div>
    <?php echo CHtml::endForm(); ?>
    
    <?php
    
    break;

    case 'delete':
   
    ?>
    <?php echo CHtml::beginForm('manage', 'POST', array('id'=>'DeleteForm','name'=>'DeleteForm')); ?>
    <?php echo CHtml::hiddenField("MenuID", $menu["MenuID"]); ?>
    <?php echo CHtml::hiddenField("Submit", "Delete"); ?>
    <div class="row">
        <p>Are you sure you want to delete <b><?php echo $menu["Name"];?></b> menu?</p>
    </div>    
    <?php echo CHtml::endForm(); ?>
    <?php

    break;

    case 'changeStatus':
        
        $status = $menu["Status"];        
        $label = $status == 0 ? "Enable" : "Disable";
        
    ?>
    <?php echo CHtml::beginForm('manage', 'POST', array('id'=>'ToggleForm','name'=>'ToggleForm')); ?>
    <?php echo CHtml::hiddenField("MenuID", $menu["MenuID"]); ?>
    <?php echo CHtml::hiddenField("Status", $menu["Status"]); ?>
    <?php echo CHtml::hiddenField("Submit", $menu["Status"] == 1 ? 'Disable' : 'Enable'); ?>
    <div class="row">
        <p>Are you sure you want to <?php echo strtolower($label); ?> <b><?php echo $menu["Name"]; ?></b> menu?</p>
    </div>    
    <?php echo CHtml::endForm(); ?>
    <?php
    
    break;

}?> <!-- Switch -->



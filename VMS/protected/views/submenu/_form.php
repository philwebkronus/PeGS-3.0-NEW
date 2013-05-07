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
    <?php echo CHtml::beginForm('', 'POST', array('id'=>'CreateForm','name'=>'CreateForm')); ?>
    <div class="row">
        <?php echo CHtml::label("Menu Name", "MenuID"); ?><br />
        <?php echo CHtml::dropDownList("MenuID", "", SiteMenu::getMenuList()); ?>
    </div>
    <div class="row">
        <?php echo CHtml::label("Sub Menu", "Submenu"); ?><br />
        <?php echo CHtml::textField("Submenu"); ?>
    </div>
    <div class="row">
        <?php echo CHtml::label("Link", "Link"); ?><br />
        <?php echo CHtml::textField("Link"); ?>
    </div>
    <div class="row">
        <?php echo CHtml::label("Description", "Description"); ?><br />
        <?php echo CHtml::textField("Description"); ?>
    </div>
    <div class="row">
        <?php echo CHtml::label("Status", "Status"); ?>
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
        <?php echo CHtml::label("Menu Name", "MenuID"); ?><br />
        <?php echo CHtml::dropDownList("MenuID", $submenu["MenuID"], SiteMenu::getMenuList()); ?>
    </div>
    <div class="row">
        <?php echo CHtml::label("Submenu", "submenu"); ?><br />
        <?php echo CHtml::textField("Name", $submenu['Name']); ?>
    </div>
    <div class="row">
        <?php echo CHtml::label("Link", "Link"); ?><br />
        <?php echo CHtml::textField("Link", $submenu['Link']); ?>
    </div>
    <div class="row">
        <?php echo CHtml::label("Description", "Description"); ?><br />
        <?php echo CHtml::textField("Description", $submenu['Description']); ?>
    </div>
    <div class="row">
        <?php echo CHtml::label("Status", "Status"); ?><br />
        <?php echo CHtml::dropDownList("Status", $submenu['Status'], SiteMenu::getMenuStatus()); ?>
        <?php echo CHtml::hiddenField("Submit", "Update"); ?>
        <?php echo CHtml::hiddenField("SubMenuID", $submenu["SubMenuID"]); ?>
    </div>
    <?php echo CHtml::endForm(); ?>
    <?php
    
    break;

    case 'delete':
   
    ?>
    <?php echo CHtml::beginForm('manage', 'POST', array('id'=>'DeleteForm','name'=>'DeleteForm')); ?>
    <div class="row">
        <p>Are you sure you want to delete <b><?php echo $submenu["Name"]; ?></b> menu?</p>
    </div>    
    <?php echo CHtml::hiddenField("Submit", "Delete"); ?>
    <?php echo CHtml::hiddenField("SubMenuID", $submenu["SubMenuID"]); ?>
    <?php echo CHtml::endForm(); ?>
    <?php
    break;

    case 'changeStatus':
        
        $status = $submenu["Status"];        
        $label = $status == 0 ? "Enable" : "Disable";        
        
    ?>
    <?php echo CHtml::beginForm('manage', 'POST', array('id'=>'ToggleForm','name'=>'ToggleForm')); ?>
    <?php echo CHtml::hiddenField("Status", $status); ?>
    <?php echo CHtml::hiddenField("Submit", $label); ?>
    <?php echo CHtml::hiddenField("SubMenuID", $submenu["SubMenuID"]); ?>
    <div class="row">
        <p>Are you sure you want to <?php echo strtolower($label); ?> <b><?php echo $submenu["Name"]; ?></b> menu?</p>
    </div>
    <?php echo CHtml::endForm(); ?>
    <?php
    break;

}?> <!-- Switch -->
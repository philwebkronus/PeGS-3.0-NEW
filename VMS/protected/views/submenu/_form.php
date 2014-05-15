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
<table>
    <tr>
           
    <?php echo CHtml::beginForm('', 'POST', array('id'=>'CreateForm','name'=>'CreateForm')); ?>
        <td>
            <?php echo CHtml::label("Menu Name", "MenuID"); ?><br />
        </td> 
        <td>
            <?php echo CHtml::dropDownList("MenuID", "", SiteMenu::getMenuList()); ?>
        </td> 
    </tr>
    <tr>
        <td>
            <?php echo CHtml::label("Sub Menu", "Submenu"); ?>
        </td>
        <td>
            <?php echo CHtml::textField("Submenu"); ?>
        </td>
    </tr>    
    <tr>
        <td>
            <?php echo CHtml::label("Link", "Link"); ?>
        </td>
        <td>
            <?php echo CHtml::textField("Link"); ?>
        </td> 
    </tr>    
    <tr>
        <td>    
            <?php echo CHtml::label("Description", "Description"); ?>
        </td>
        <td>    
            <?php echo CHtml::textField("Description"); ?>
        </td>   
    </tr>    
    <tr>
        <td>
            <?php echo CHtml::label("Status", "Status"); ?>
        </td>
        <td>
            <?php echo CHtml::dropDownList("Status", 1, SiteMenu::getMenuStatus()); ?>  
        </td> 
    </tr>    
    <div class="row">
        <?php echo CHtml::hiddenField("Submit", "Create"); ?>
    </div>
</table>

    <?php echo CHtml::endForm(); ?>
    <?php
    
    break;

    case 'update':
    
    ?>
<table>
       
    <?php echo CHtml::beginForm('manage', 'POST', array('id'=>'UpdateForm','name'=>'UpdateForm')); ?>
    <tr>
        <td>
            <?php echo CHtml::label("Menu Name", "MenuID"); ?>
        </td>   
        <td>
            <?php echo CHtml::dropDownList("MenuID", $submenu["MenuID"], SiteMenu::getMenuList()); ?>
        </td> 
    </tr>
    <tr>
        <td>
            <?php echo CHtml::label("Submenu", "submenu"); ?>
        </td>
        <td>
            <?php echo CHtml::textField("Name", $submenu['Name']); ?>
        </td>
    </tr>    
    <tr>
        <td>
            <?php echo CHtml::label("Link", "Link"); ?>
        </td>    
        <td>
            <?php echo CHtml::textField("Link", $submenu['Link']); ?>
        </td> 
    </tr>    
    <tr>
        <td>
            <?php echo CHtml::label("Description", "Description"); ?>
        </td>
        <td>
            <?php echo CHtml::textField("Description", $submenu['Description']); ?>
        </td> 
    </tr>    
    <tr>
        <td>
            <?php echo CHtml::label("Status", "Status"); ?>
        </td>    
        <td>
            <?php echo CHtml::dropDownList("Status", $submenu['Status'], SiteMenu::getMenuStatus()); ?>
        </td>    
    </tr>    
    <div class="row">
        <?php echo CHtml::hiddenField("Submit", "Update"); ?>
        <?php echo CHtml::hiddenField("SubMenuID", $submenu["SubMenuID"]); ?>
    </div>
</table>    
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
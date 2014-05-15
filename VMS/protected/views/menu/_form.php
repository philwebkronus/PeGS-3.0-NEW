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
    <?php echo CHtml::beginForm('manage', 'POST', array('id'=>'CreateForm','name'=>'CreateForm')); ?>
    <td><?php echo CHtml::label("Name","Name"); ?></td>
    <td><?php echo CHtml::textField("Name", ""); ?></td>
    </tr>
    <tr>
    <td><?php echo CHtml::label("Link", "for_Link"); ?></td>
    <td><?php echo CHtml::textField("Link"); ?></td>
    </tr>
    <tr>
    <td><?php echo CHtml::label("Description", "for_Description"); ?></td>
    <td><?php echo CHtml::textField("Description"); ?></td>
    </tr>
    <tr>
    <td><?php echo CHtml::label("Status", "for_Status"); ?></td>    
    <td><?php echo CHtml::dropDownList("Status", 1, SiteMenu::getMenuStatus()); ?></td>
    </tr>
    <div class="row">
        <br />
        <?php echo CHtml::hiddenField("Submit", "Create"); ?>
    </div>
    <?php echo CHtml::endForm(); ?>
     </table>
    <?php
   
    break;

    case 'update':
    ?>
    <table>
    <?php echo CHtml::beginForm('manage', 'POST', array('id'=>'UpdateForm','name'=>'UpdateForm')); ?>
    <tr>

    <td><?php echo CHtml::label("Name", "Name"); ?></td>
    <td><?php echo CHtml::textField("Name", $menu['Name']); ?></td>
    </tr>
    <tr>

    <td><?php echo CHtml::label("Link", "for_Link"); ?></td>
    <td><?php echo CHtml::textField("Link", $menu['Link']); ?></td>
    </tr>
    <tr>

    <td><?php echo CHtml::label("Description", "for_Description"); ?></td>
    <td><?php echo CHtml::textField("Description", $menu['Description']); ?></td>
    </tr>
    <tr>

    <td><?php echo CHtml::label("Status", "for_Status"); ?></td>
    <td><?php echo CHtml::dropDownList("Status", $menu['Status'], SiteMenu::getMenuStatus()); ?></td>
    </tr>
    <div class="row">
        <br />
        
        <?php echo CHtml::hiddenField("MenuID", $menu["MenuID"]); ?>
        <?php echo CHtml::hiddenField("Submit", "Update"); ?>
    </div>
    <?php echo CHtml::endForm(); ?>
    </table>
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



<?php

/**
 * @author owliber
 * @date Oct 16, 2012
 * @filename accessrights.php
 * 
 */
?>

<?php

$this->breadcrumbs=array(
	'Administration','Access Rights',
);

?>
<?php Yii::app()->clientScript->registerScript("form-validation","checkForm()"); ?>
<script type="text/javascript">

    function checkForm()
    {
       var n = $("input:checked").length;
        if(n > 1)
        {
            return true;
        }
        else
        {
            alert('No access rights found.');
            return false;
        }
    }

</script>
    

<?php echo CHtml::beginForm('', 'post', array('onSubmit'=>'return checkForm();')); ?> 

<div class="container">
    
    <div class="span-8 colborder">
        <div><h4>Account Groups</h4><span class="small">Select an account type</span> </div>
        <hr />
        <ul class="no-style">

        <?php foreach ($accountType as $key=>$value){ 
                                 
           echo '<li>'.CHtml::radioButtonList('AccountType',$accounttypeid,array(
                           $value['AccountTypeID']=>$value['Name']),array(
                            'onClick'=>'submit();','id'=>'AccountType')).'</li>';
           
        }?>
        </ul>
    </div><!-- view span-8 -->
    
    <div class="span-12">
       <div><h4>Access Rights</h4><span class="small">Please check the desired privileges.</span></div>
       <hr />
       <ul class="no-style box">
       <?php 
       
        //Get all allowed menus and submenus
        if(count($accessrights)> 0)
        {
            foreach($accessrights as $access)
            {
                $allowedmenus[] = $access['MenuID'];
                $allowedsubmenus[] = $access['SubMenuID'];
            }
        }
        else
        {
            $allowedmenus[] = "";
            $allowedsubmenus[] = "";
        }
        
        $defaultPage = AccessRights::getDefaultPage($accounttypeid);
                
        //Display all menus/submenus
        foreach ($menus as $value)
        {
          $menuid = $value['MenuID'];
          
          //Check if menuid is allowed
          if(AccessRights::checkMenuAccess($accounttypeid, $menuid))
          {
              $link = $defaultPage == $menuid ? ' [Default]' : ' ['.CHtml::link('Set as default', Yii::app()->createUrl("/access/update", array("menuid" => $menuid,'accounttypeid'=>$accounttypeid))).']';
          }
          else
          {
              $link = "";
          }
          //echo CHtml::$labelWrapsInput=true;          
          echo '<li>'.CHtml::checkBoxList('MenuID', $allowedmenus, array($value['MenuID']=>$value['Name'])) . $link . '</li>';
          
          echo '<hr class="space" />';
          echo '<div class="prepend-1">
                <ul class="no-style">';
                foreach(SubMenu::getAllSubMenus($menuid) as $value2)
                {
                   echo '<li>'.CHtml::checkBoxList('SubMenuID', $allowedsubmenus, array($value2['SubMenuID']=>$value2['Name'])).'</li>';
                }
          echo '<ul>
                </div>'; //prepend-1
       }
       
       ?>
       </ul>
       <div class="row buttons prepend-top">
           <?php echo CHtml::submitButton('Submit',array(
                    'name'=>'Submit',
                    'class'=>'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only',
                )); ?>
           <?php echo CHtml::resetButton('Reset',array(
                    'class'=>'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only',
                )); ?>
       </div>
    </div><!-- view span-12 -->
    
</div><!-- container -->

<?php echo CHtml::endForm(); ?>


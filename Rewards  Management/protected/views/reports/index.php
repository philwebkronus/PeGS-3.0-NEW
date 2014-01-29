<?php
/**
 * Rewards Redemption View
 * Mark Kenneth Esguerra <mgesguerra@philweb.com.ph>
 * September 03, 2013
 * Philweb Corp.
 * 
 */
$this->pageTitle = Yii::app()->name." - Reports";

//Report Models
$players        = new PlayerClassificationModel();
$rewardtypes    = new RewardTypeModel();
$rewarditems    = new RewardItemsModel();
$category       = new CategoryModel();
$ref_partners   = new RefPartnerModel();
?>
<!-----ERROR DIALOG---->
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'dialog_box',
    // additional javascript options for the dialog plugin
    'options'=>array(
        'title'=>'ERROR MESSAGE',
        'autoOpen' => $this->showdialog,
        'resizable' => false,
        'draggable' => false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>350,
        'modal'=>true,
        'buttons' => array
        (
            'OK'=>'js:function(){$(this).dialog("close");}',
        ),
    ),
));
?>
<p id="dlg_msg" style="text-align: left;"><?php echo $this->message; ?></p>
    
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
<!--------------------->
<h2>Manage Reports</h2>
<script type="text/javascript">
    $(document).ready(function(){
    <?php 
        if (isset($this->wizard))
        {
            //Enable date_to if has value
    ?>
            <?php
            switch($this->wizard)
            {
            case 1:
                ?>
                    $("#step").html("Step 1. Choose Report Type");
                    $("#report-type").show();
                    $("#report-filters").hide();
                    $("#report-coverage").hide();
                    $("#btnback").hide();
                    $("#btnnext").show();
                    $("#btngenerate").hide();    
                <?php
                break;
            case 2: 
                ?>
                    $("#report-filters").show();
                    $("#report-coverage").hide();
                    $("#report-type").hide();
                    $("#btnback").show();
                    $("#btngenerate").hide();
                    $("#step").html("Step 2. Choose Report Filters");
                <?php
                break;
            case 3:
                ?>
                    $("#report-filters").hide();
                    $("#report-coverage").show();
                    $("#report-type").hide();
                    $("#btnback").show();
                    $("#btngenerate").show();
                    $("#btnnext").hide();
                    $("#step").html("Step 3. Choose Report Coverage");
                <?php
                break;
            }
            ?>
               $("#btnnext").live("click", function(){
                   if ($("#report-type").is(":visible")){
                       if ($("#ReportForm_report_type").val() == ""){
                           $("#dialog_box").dialog('open');
                           $("#dialog_box").text('Please select a Report Type');
                       }
                       else{
                            $("#report-filters").show();
                            $("#report-coverage").hide();
                            $("#report-type").hide();
                            $("#btnback").show();
                            $("#btngenerate").hide();
                            $("#step").html("Step 2. Choose Report Filters");
                       }
                   }
                   else if ($("#report-filters").is(":visible")){
                        if ($("#ReportForm_category").val() == ""){
                            $("#dialog_box").dialog('open');
                            $("#dialog_box").text('Please select a Category');
                        }
                         else if ($("#ReportForm_filter_by").val() == ""){
                            $("#dialog_box").dialog('open');
                            $("#dialog_box").text('Please select a Filter');
                        }
                        else if ($("#ReportForm_particular").val() == ""){
                            $("#dialog_box").dialog('open');
                            $("#dialog_box").text('Please choose a Particular');
                        }
                        else if ($("#ReportForm_player_segment").val() == ""){
                            $("#dialog_box").dialog('open');
                            $("#dialog_box").text('Please select a Player Segment');
                        }
                        else{
                            $("#report-filters").hide();
                            $("#report-coverage").show();
                            $("#report-type").hide();
                            $("#btnback").show();
                            $("#btngenerate").show();
                            $("#btnnext").hide();
                            $("#step").html("Step 3. Choose Report Coverage");
                       }
                   }

               });
               $("#btnback").live("click", function(){
                   if ($("#report-filters").is(":visible")){
                        $("#report-filters").hide();
                        $("#btnnext").show();
                        $("#report-coverage").hide();
                        $("#report-type").show();
                        $("#btnback").hide();
                        $("#btngenerate").hide();
                        $("#step").html("Step 1. Choose Report Type");
                   }
                   else if ($("#report-coverage").is(":visible")){
                        $("#report-filters").show();
                        $("#btnnext").show();
                        $("#report-coverage").hide();
                        $("#btnback").show();
                        $("#btngenerate").hide();
                        $("#step").html("Step 2. Choose Report Filters");
                   }
               });
            <?php
        }
        else
        {
    ?>
       var datefrom = $("#ReportForm_date_from").val().length;
       if (datefrom > 0){
           $("#ReportForm_date_to").removeAttr("disabled");
       }
       
       $("#step").html("Step 1. Choose Report Type");
       $("#report-filters").hide();
       $("#report-coverage").hide();
       $("#btnback").hide();
       $("#btngenerate").hide();
       
       $("#btnnext").live("click", function(){
           if ($("#report-type").is(":visible")){
               if ($("#ReportForm_report_type").val() == ""){
                   $("#dialog_box").dialog('open');
                   $("#dialog_box").text('Please select a Report Type');
               }
               else{
                    $("#report-filters").show();
                    $("#report-coverage").hide();
                    $("#report-type").hide();
                    $("#btnback").show();
                    $("#btngenerate").hide();
                    $("#step").html("Step 2. Choose Report Filters");
               }
           }
           else if ($("#report-filters").is(":visible")){
               if ($("#ReportForm_category").val() == ""){
                   $("#dialog_box").dialog('open');
                   $("#dialog_box").text('Please select a Category');
               }
               else if ($("#ReportForm_filter_by").val() == ""){
                   $("#dialog_box").dialog('open');
                   $("#dialog_box").text('Please select a Filter');
               }
               else if ($("#ReportForm_particular").val() == ""){
                   $("#dialog_box").dialog('open');
                   $("#dialog_box").text('Please choose a Particular');
               }
               else if ($("#ReportForm_player_segment").val() == ""){
                   $("#dialog_box").dialog('open');
                   $("#dialog_box").text('Please select a Player Segment');
               }
               else{
                    $("#report-filters").hide();
                    $("#report-coverage").show();
                    $("#report-type").hide();
                    $("#btnback").show();
                    $("#btngenerate").show();
                    $("#btnnext").hide();
                    $("#step").html("Step 3. Choose Report Coverage");
               }
           }
            
       });
       $("#btnback").live("click", function(){
           if ($("#report-filters").is(":visible")){
                $("#report-filters").hide();
                $("#btnnext").show();
                $("#report-coverage").hide();
                $("#report-type").show();
                $("#btnback").hide();
                $("#btngenerate").hide();
                $("#step").html("Step 1. Choose Report Type");
           }
           else if ($("#report-coverage").is(":visible")){
                $("#report-filters").show();
                $("#btnnext").show();
                $("#report-coverage").hide();
                $("#btnback").show();
                $("#btngenerate").hide();
                $("#step").html("Step 2. Choose Report Filters");
           }
       });
<?php } ?>
    });
</script>
<div class="form">
    <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'ReportForm',
            'enableClientValidation' => true,
            'enableAjaxValidation'=>true,
            'clientOptions'=>array(
		'validateOnSubmit'=>true,
                'validateOnChange'=>true,
                'validateOnType'=>false,
            ),
        ));
    ?>
    <h3 id="step"></h3>
    <table id="report-type">
        <tr>
            <td class="report-field">
                Report Type
            </td>     
            <td>
        <?php
            echo $form->dropDownList($model, 'report_type', array('1' => 'Rewards Redemption', 
                                                               '2' => 'Unique Member Participation',
                                                               '3' => 'Rewards Points Usage'), 
                    array('prompt'=>'Please Select'));
        ?>
            </td>
        </tr>
    </table>   
    <table id="report-filters">
        <tr>
            <td class="report-field">
                Category
            </td>
            <td>
                <?php
                    //Populate Category DropDownList
                    $arrCategory = $rewardtypes->selectCategory();
                    $categoryList = CHtml::listData($arrCategory, 'RewardID','Description');
                    echo $form->dropDownList($model, 'category', $categoryList, array('prompt'=>'Please Select', 
                                                                                      'ajax' => array(
                                                                                          'type' => 'POST',
                                                                                          'url' => CController::createUrl('getRewardItemType'),
                                                                                          'data' => array('ReportForm_category'=>'js:this.value'),
                                                                                          'success' => 'function(data){ 
                                                                                                        $("#ReportForm_hdnitemtype").val(data);
                                                                                                        $("#ReportForm_filter_by").empty();
                                                                                                        $("#ReportForm_filter_by").append("<option value>Please Select</option>");
                                                                                                        if (data == 0)
                                                                                                        {
                                                                                                            $("#ReportForm_filter_by").append("<option value=0>All</option>");
                                                                                                            $("#ReportForm_filter_by").append("<option value=1>Item</option>");
                                                                                                            $("#ReportForm_filter_by").append("<option value=2>Partner</option>");
                                                                                                            $("#ReportForm_filter_by").append("<option value=3>Category</option>");
                                                                                                        }
                                                                                                        else
                                                                                                        {
                                                                                                            $("#ReportForm_filter_by").append("<option value=1>Item</option>");
                                                                                                        }
                                                                                                        $("#ReportForm_particular").empty();
                                                                                                        $("#ReportForm_particular").append("<option value>Please Select</option>");
                                                                                                    }'
                                                                                      )));
                ?>
            </td>
        </tr>
        <tr>  
            <td class="report-field">
                Filter By
            </td>
            <td>
                <?php
                    if (isset($this->category))
                    {
                        switch ($this->category)
                        {
                            case 0: //All
                                echo $form->dropDownList($model, 'filter_by',array('0'=>'All',
                                                                           '1'=>'Item',
                                                                           '2'=>'Partner',
                                                                           '3'=>'Category'),
                                                                     array('prompt'=>'Please Select',
                                                                           'ajax' => array(
                                                                                'type' => 'POST',
                                                                                'url' => CController::createUrl('loadParticulars'),
                                                                                'update' => '#ReportForm_particular',
                                                                                'data' => array('ReportForm_filter_by'=>'js:this.value',  
                                                                                                'itemtype'=>'js:$("#ReportForm_hdnitemtype").val()')
                                                                     ),
                                ));
                                break;
                            case 1: //Reward eCoupon
                                echo $form->dropDownList($model, 'filter_by',array('0'=>'All',
                                                                           '1'=>'Item',
                                                                           '2'=>'Partner',
                                                                           '3'=>'Category'),
                                                                     array('prompt'=>'Please Select',
                                                                           'ajax' => array(
                                                                                'type' => 'POST',
                                                                                'url' => CController::createUrl('loadParticulars'),
                                                                                'update' => '#ReportForm_particular',
                                                                                'data' => array('ReportForm_filter_by'=>'js:this.value',  
                                                                                                'itemtype'=>'js:$("#ReportForm_hdnitemtype").val()')
                                                                     ),
                                ));
                                break;
                            case 2: //Raffle eCoupon
                                echo $form->dropDownList($model, 'filter_by',array('1'=>'Item'),
                                                                     array('prompt'=>'Please Select',
                                                                           'ajax' => array(
                                                                                'type' => 'POST',
                                                                                'url' => CController::createUrl('loadParticulars'),
                                                                                'update' => '#ReportForm_particular',
                                                                                'data' => array('ReportForm_filter_by'=>'js:this.value',  
                                                                                                'itemtype'=>'js:$("#ReportForm_hdnitemtype").val()')
                                                                     ),
                                ));
                                break;
                                
                        }
                    }
                    else
                    {
                        echo $form->dropDownList($model, 'filter_by',array('0'=>'All',
                                                                           '1'=>'Item',
                                                                           '2'=>'Partner',
                                                                           '3'=>'Category'),
                                                                     array('prompt'=>'Please Select',
                                                                           'ajax' => array(
                                                                                'type' => 'POST',
                                                                                'url' => CController::createUrl('loadParticulars'),
                                                                                'update' => '#ReportForm_particular',
                                                                                'data' => array('ReportForm_filter_by'=>'js:this.value',  
                                                                                                'itemtype'=>'js:$("#ReportForm_hdnitemtype").val()')
                                                                     ),
                        ));
                    }
                ?>
            </td>
        </tr>
        <tr>
            <td class="report-field">
                Choose Particular
            </td>
            <td>
                <?php
                    //Retain the selected value once there is/are error
                    //after submission since this dropdown was depending on Filter DDL. 
                    if (isset($this->filter)) //Set once the error was committed
                    {
                        $filter = $this->filter; //Get the selected filter
                        if ($filter == 1 || $filter == 2 || $filter == 3
                                                         || $filter == 0)
                        {
                            if ($filter == 1) //ITEMS
                            {
                                //in able to return to proper selection of filters
                                if (isset($this->category))
                                    if ($this->category == 2)
                                        $selector = 1;
                                else
                                    $selector = $this->itemtype;
                                
                                $arrItems = $rewarditems->selectRewardItems($selector);
                                $append = "I";
                                for ($i = 0; count($arrItems) > $i; $i++)
                                {
                                    $items[] = array('RewardItemID' => $append.$arrItems[$i]['RewardItemID'], 
                                                     'ItemName'=>$arrItems[$i]['ItemName']);
                                }
                                array_unshift($items, array('RewardItemID' => $append."0", 'ItemName' => 'All'));
                                $arrList = CHtml::listData($items, 'RewardItemID','ItemName'); 
                            }
                            else if ($filter == 2) //PARTNERS
                            {
                                $arrPartners = $ref_partners->selectPartners();
                                $append = "P";
                                for ($i = 0; count($arrPartners) > $i; $i++)
                                {
                                    $partners[] = array('PartnerID' => $append.$arrPartners[$i]['PartnerID'], 
                                                        'PartnerName'=>$arrPartners[$i]['PartnerName']);
                                }
                                array_unshift($partners, array('PartnerID' => $append."0", 'PartnerName' => 'All'));
                                $arrList = CHtml::listData($partners, 'PartnerID','PartnerName'); 
                            }
                            else if ($filter == 3) //CATEGORY
                            {
                                $arrCategories= $category->selectCategories();
                                $append = "C";
                                for ($i = 0; count($arrCategories) > $i; $i++)
                                {
                                    $categories[] = array('CategoryID' => $append.$arrCategories[$i]['CategoryID'], 
                                                          'Description'=>$arrCategories[$i]['Description']);
                                }
                                array_unshift($categories, array('CategoryID' => $append."0", 'Description' => 'All'));
                                $arrList = CHtml::listData($categories, 'CategoryID','Description');
                            }
                            else if ($filter == 0)
                            {
                                $arrItems = $rewarditems->selectRewardItems($this->itemtype);
                                $arrPartners = $ref_partners->selectPartners();
                                $arrCategories = $category->selectCategories();
                                //Retrieve ITEMS and put in array particulars
                                for ($i = 0; count($arrItems) > $i; $i++)
                                {
                                    $particulars[] = array('ParticularID' => "I".$arrItems[$i]['RewardItemID'], 
                                                           'ParticularName' => "[I] ".$arrItems[$i]['ItemName']);
                                }
                                //Retrieve PARTNERS and put in array particulars
                                for ($i = 0; count($arrPartners) > $i; $i++)
                                {
                                    $particulars[] = array('ParticularID' => "P".$arrPartners[$i]['PartnerID'], 
                                                           'ParticularName' => "[P] ".$arrPartners[$i]['PartnerName']);
                                }
                                //Retrieve CATEGORIES and put in array particulars
                                for ($i = 0; count($arrCategories) > $i; $i++)
                                {
                                    $particulars[] = array('ParticularID' => "C".$arrCategories[$i]['CategoryID'], 
                                                           'ParticularName' => "[C] ".$arrCategories[$i]['Description']);
                                }
                                array_unshift($particulars, array('ParticularID' => "A"."0", 'ParticularName' => 'All'));
                                $arrList = CHtml::listData($particulars, 'ParticularID','ParticularName');
                            }
                        echo $form->dropDownList($model, 'particular', $arrList, array('prompt'=>'Please Select'));
                        }
                    }
                    else
                    {
                        $arrParticular = array();
                        //Populate Choose Particular
                        echo $form->dropDownList($model,'particular',$arrParticular,
                                array('prompt'=>'Please Select',));
                    }
                ?>
            </td>
        </tr>
        <tr>
            <td class="report-field">
                Player Segment
            </td>
            <td>
                <?php 
                    //Populate Player Segment
                    echo $form->dropDownList($model, 'player_segment',array('2' => 'All', 
                                                                            '0' => 'Regular', 
                                                                            '1' => 'VIP'),
                            array('prompt'=>'Please Select',
                    ));
                ?> 
            </td>
        </tr>  
    </table>
    <table id="report-coverage">
        <tr>
            <td class="report-field">
                Date Coverage
            </td>    
            <td>
                <?php 
                    echo $form->dropDownList($model, 'date_coverage', array('0' => 'Daily', 
                                                                             '1' => 'Weekly', 
                                                                             '2' => 'Monthly', 
                                                                             '3' => 'Quarterly', 
                                                                             '4' => 'Yearly'),
                                array('prompt'=>'Please Select',

                        ));
                ?>
            </td>    
        </tr>    
        <tr>
            <td class="report-field">
                From
            <td>
                <?php
                    $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                        'model' => $model,
                        'attribute' => 'date_from',
                        'htmlOptions' => array(
                            'size' => '10',         // textField size
                            'maxlength' => '10',    // textField maxlength
                            'readonly' => true,
                        ),
                        'options' => array(
                            'showOn'=>'button',
                            'buttonImageOnly' => true,
                            'changeMonth' => true,
                            'changeYear' => true,
                            'buttonText'=> 'Select Date From',
                            'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.gif',
                            'dateFormat'=>'yy-mm-dd',
                            'maxDate' =>'0',
                            'onSelect' => 'js:function(selectedDate) {
                                $("#ReportForm_date_to").removeAttr("disabled");
                            }',
                        )
                    ));
                 ?>
            </td>
        </tr>
        <tr>
            <td class="report-field">
               To
            <td>
                <?php
                    $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                        'model' => $model,
                        'attribute' => 'date_to',
                        'htmlOptions' => array(
                            'size' => '10',         // textField size
                            'maxlength' => '10',    // textField maxlength
                            'readonly' => true
                        ),
                        'options' => array(
                            'showOn'=>'button',
                            'buttonImageOnly' => true,
                            'changeMonth' => true,
                            'changeYear' => true,
                            'buttonText'=> 'Select Date To',
                            'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.gif',
                            'dateFormat'=>'yy-mm-dd',
                            'maxDate' =>'0',
                            'beforeShow' => 'js:function(){
                                var selectedDate = $("#'.CHtml::activeId($model,'date_from').'").datepicker("getDate");
                                selectedDate.setDate(selectedDate.getDate() + 1);
                                $(this).datepicker("option","minDate",selectedDate);

                            }'
                        )
                    ));
                 ?>
            </td>
        </tr>
    </table>
    <?php echo $form->hiddenField($model, 'hdnitemtype', array()); ?>
    <div id="reports-btn" style="float:left;width: 370px;height: 200px;">
    <?php
        echo CHtml::submitButton('Generate', array(
                                             'style'=>'float:right',
                                             'id' => 'btngenerate'
        ));
    ?>
        <input type="button" id="btnnext" name="btnnext" value="Next" style="float:right"/>&nbsp;&nbsp;
        <input type="button" id="btnback" name="btnback" value="Back" style="float:right"/>
    </div> 
    <?php $this->endWidget(); ?>

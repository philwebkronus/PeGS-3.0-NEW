<?php
require_once("../init.inc.php");
include('sessionmanager.php');
include("controller/addrewarditemscontroller.php");
$pagetitle = "Adding of Reward Items";
$currentpage = "Administration";
?>

<?php include("header.php"); ?>
<script>
    $(document).ready(function(){
        $("input[name='vihpyes']").click(function()
        {
            $('#vihpno').attr('checked',false);
            $('#vihp').val('1');
        });
        
        $("input[name='vihpno']").click(function()
        {
            $('#vihpyes').attr('checked',false);
            $('#vihp').val('0');
        });
        
        
        $("input[name='rtyes']").click(function()
        {
            $('#rtno').attr('checked',false);
            $('#rt').val('1');
        });
        
        $("input[name='rtno']").click(function()
        {
            $('#rtyes').attr('checked',false);
            $('#rt').val('0');
        });
        
        $('#SuccessDialog').dialog({
            autoOpen: <?php echo $isOpen; ?>,
            modal: true,
            width: '400',
            title : 'Update Profile',
            closeOnEscape: true,            
            buttons: {
                "Ok": function() {
                    $(this).dialog("close");
                    window.location="addrewarditems.php";
                }
            }
        });
    });    
</script>
<style>
    <!--
    .tab { margin-left: 40px; }
    .tab2 { margin-left: 650px; }
    -->
</style>
<div align="center">
    <div class="maincontainer">
        <?php include('menu.php'); ?>
        <br />
        <div class="title" style="margin-left: 40px;">Adding of Reward Items</div>
            <br />
            <hr color="black" />
            <br />
        <div class="tab">
            <input type="hidden" name="vihp" id="vihp" value="1" />
            <input type="hidden" name="rt" id="rt" value="1" />
            <table align="center">
                <tr>
                    <td>Reward Item Name : </td>
                    <td><?php echo "$txtRewardItemName"; ?></td>
                </tr>
                <tr>
                    <td>Reward Item Description : </td>
                    <td><textarea name="rewarditemdesc" id="rewarditemdesc" cols="35" rows="6" maxlength="150" class="validate[required]" onkeypress="return alphanumeric4(event);"></textarea></td>
                </tr>
                <tr>
                    <td>Reward Item Code : </td>
                    <td><?php echo "$txtItemCode"; ?></td>
                </tr>
                <tr>
                    <td>Reward Item Price : </td>
                    <td><?php echo "$txtRewardItemPrice"; ?></td>
                </tr>
                <tr>
                    <td>Reward Item Count : </td>
                    <td><?php echo "$txtRewardItemCount"; ?></td>
                </tr>                   
            </table>
            <br/><br/>
            <table>
                <tr>
                    <td>Reward Item Image : </td>

                </tr>
                <tr>
                    <td>Small : </td>
                    <td><input type="file" name="picUpload1" id="picUpload1" class="validate[required]" /></td>
                </tr>
                <tr>
                    <td>Medium : </td>
                    <td><input type="file" name="picUpload2" id="picUpload2" class="validate[required]" /></td>
                </tr>
                <tr>
                    <td>Large : </td>
                    <td><input type="file" name="picUpload3" id="picUpload3" class="validate[required]" /></td>
                </tr>
                <tr>
                    <td><br/></td>
                </tr>
                <tr>
                    <td>Expiration Date : </td>
                    <td><?php echo "$expirationdate"; ?></td>
                </tr>
                <tr>
                    <td><br/></td>
                </tr>
                <tr>
                    <td>Reward Type</td>
                    <td>
                        <input type="radio" id="rtyes" name="rtyes" value="1"checked/>Item&nbsp;&nbsp;&nbsp;
                        <input type="radio" id="rtno" name="rtno" value="0"  />Raffle Coupon
                    </td>
                </tr>

            </table>
            <style>
                #accordion-resizer {
                    padding: 10px;
                    width: 650px;
                    height: auto;
                }
            </style>
            <script>
                $(function() {
                    $( "#accordion" ).accordion({
                        heightStyle: "fill",
                        collapsible: true
                    });
                });
                $(function() {
                    $( "#accordion-resizer" ).resizable({
                        minHeight: 140,
                        minWidth: 200,
                        resize: function() {
                            $( "#accordion" ).accordion( "refresh" );
                        }
                    });
                });
            </script>

            <br/>
            <div id="accordion-resizer" class="ui-widget-content" align="center">
                <div id="accordion">
                    <h3>First Header</h3>
                    <div>
                        <table align="center">
                            <tr>
                            <br/>
                            </tr>    
                            <tr>
                                <td>First Header : </td>
                                <td><textarea name="firstheader" id="firstheader" cols="50" rows="10" class="validate[required]" onkeypress="return alphanumeric4(event);"></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail One : </td>
                                <td><textarea name="detailone1" id="detailone1" cols="50" rows="10" class="validate[required]" onkeypress="return alphanumeric4(event);"></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail Two : </td>
                                <td><textarea name="detailtwo1" id="detailtwo1" cols="50" rows="10" onkeypress="return alphanumeric4(event);"></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail Three : </td>
                                <td><textarea name="detailthree1" id="detailthree1" cols="50" rows="10" onkeypress="return alphanumeric4(event);"></textarea></td>
                            </tr>                   
                        </table>
                    </div>
                    <h3>Second Header</h3>
                    <div>
                        <table align="center">
                            <tr>
                            <br/>
                            </tr> 
                            <tr>
                                <td>Second Header : </td>
                                <td><textarea name="secondheader" id="secondheader" cols="50" rows="10" onkeypress="return alphanumeric4(event);"></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail One : </td>
                                <td><textarea name="detailone2" id="detailone2" cols="50" rows="10" onkeypress="return alphanumeric4(event);"></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail Two : </td>
                                <td><textarea name="detailtwo2" id="detailtwo2" cols="50" rows="10" onkeypress="return alphanumeric4(event);"></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail Three : </td>
                                <td><textarea name="detailthree2" id="detailthree2" cols="50" rows="10" onkeypress="return alphanumeric4(event);"></textarea></td>
                            </tr>                   
                        </table>
                    </div>
                    <h3>Third Header</h3>
                    <div>
                        <table align="center">
                            <tr>
                            <br/>
                            </tr> 
                            <tr>
                                <td>Third Header : </td>
                                <td><textarea name="thirdheader" id="thirdheader" cols="50" rows="10" onkeypress="return alphanumeric4(event);"></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail One : </td>
                                <td><textarea name="detailone3" id="detailone3" cols="50" rows="10" onkeypress="return alphanumeric4(event);"></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail Two : </td>
                                <td><textarea name="detailtwo3" id="detailtwo3" cols="50" rows="10" onkeypress="return alphanumeric4(event);"></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail Three : </td>
                                <td><textarea name="detailthree3" id="detailthree3" cols="50" rows="10" onkeypress="return alphanumeric4(event);"></textarea></td>
                            </tr>                   
                        </table>
                    </div>
                </div>
            </div>
            <div>
                <table>
                    <tr>
                        <td><br/></td>
                    </tr>
                    <tr>
                        <td>Viewable in Home Page : </td>
                        <td>
                            <input type="radio" id="vihpyes" name="vihpyes" value="1"checked/>Yes&nbsp;&nbsp;&nbsp;
                            <input type="radio" id="vihpno" name="vihpno" value="0"  />No
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <br/>
        <div class="tab2">

            <?php echo "$btnSubmit"; ?>
        </div>
        <br/><br/><br/>
        <br/><br/><br/>

        <div id="SuccessDialog" name="SuccessDialog">
            <?php if ($isOpen == 'true') {
                ?>
                <?php if ($isSuccess) {
                    ?>
                    <p>
                        <?php echo $msgprompt; ?>
                    </p>
                    <?php
                } else {
                    ?>
                    <p>
                    <?php echo $msgprompt; ?>
                    </p>
                        <?php }
                    ?>
                <?php }
            ?>
        </div>
    </div>

</div>
<?php include("footer.php"); ?>
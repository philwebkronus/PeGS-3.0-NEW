<?php
require_once("init.inc.php");

$pagetitle = "Membership";

App::LoadCore("URL.class.php");
App::LoadCore("Hashing.class.php");

App::LoadModuleClass("Membership", "Members");
App::LoadModuleClass("Membership", "MemberInfo");
App::LoadModuleClass("Membership", "MemberSessions");
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Loyalty", "Cards");
App::LoadModuleClass("Loyalty", "CardTypes");
App::LoadModuleClass("Loyalty","Rewards");
App::LoadModuleClass("Membership", "Identifications");

App::LoadModuleClass("Membership", "Nationality");
App::LoadModuleClass("Membership", "Occupation");
App::LoadModuleClass("Membership", "Referrer");
App::LoadModuleClass("Membership", "Helper");
App::LoadModuleClass("Loyalty", "CardTransactions");
App::LoadModuleClass("Kronus", "Sites");

App::LoadControl("DatePicker");
App::LoadControl("TextBox");
App::LoadControl("DataGrid");
App::LoadControl("ComboBox");
App::LoadControl("Button");
App::LoadControl("RadioGroup");
App::LoadControl("Radio");
App::LoadControl("CheckBox");

$_Rewards = new Rewards();

/**
 * Carousel Controls 
 */

$viewbutton = new Button("viewbutton","viewbutton", "View More");
$viewbutton->CssClass="btnDefault roundedcorners";

(isset($_SESSION['MemberInfo'])) ? $cardtypeid = $_SESSION["MemberInfo"]["CardTypeID"] : $cardtypeid = "";

$arrRewardItems = $_Rewards->getRewardItems($cardtypeid);

 foreach ($arrRewardItems as $item) {
        $items['path'] = $item['ImagePath'];
        $items['id'] = $item['RewardItemID'];
        $items['RewardName'] = $item['RewardItemName'];
        $path[] = $items;
}

?>
<?php include "header.php"; ?> 
<?php //echo $headerinfo; ?>
<link rel="stylesheet" type="text/css" href="css/style.css">
<link rel="stylesheet" href="css/tinycarousel.css" type="text/css" media="screen"/>	
<script type="text/javascript" src="js/jquery.tinycarousel.min.js"></script>
	
<script type="text/javascript">
    
    $(document).ready(function() {
        
        $('#slider').tinycarousel({
                    interval: true,
                    intervaltime: 3000,
                    animation: true,
                    duration: 1000
                });
               
               
        $('#slider').tinycarousel_start();
                
        $(".popup").click(function (e) {
            InitializeDialog($("#detailbox"), $(this).attr("href"));
            e.preventDefault();
            $("#detailbox").dialog("open");
        });
        
        <?php
        if (isset($_SESSION["MemberInfo"])) {
        ?>
            function InitializeDialog($element, page) {
                $element.dialog({
                    modal: true,
                    autoOpen: true,
                    width: 600,
                    title: "Reward Item",
                    closeOnEscape: true,
                    position: "center",
                    buttons: {
                        "REDEEM ITEM": function() {
                            $(this).dialog("close");
                        }
                    },              
                    open: function (event, ui) {
                        $element.load(page);                        
                        $(event.target).parent().css('top', '5%');
                        $(event.target).parent().css('left', '20%');
                    }
                    
                });
            }
        <?php
        } else {
        ?>
            function InitializeDialog($element, page) {
                $element.dialog({
                    modal: true,
                    autoOpen: true,
                    width: 600,
                    title: "Reward Item",
                    closeOnEscape: true,
                    position: "center",
                    buttons: {
                        "LOGIN TO REDEEM": function() {
                            $(this).dialog("close");
                        }
                    },              
                    open: function (event, ui) {
                        $element.load(page);                        
                        $(event.target).parent().css('top', '5%');
                        $(event.target).parent().css('left', '20%');
                    }

                });
            }
    <?php } ?>
    
        $('#slider .viewmore').click(function(){
            $("#slider").hide();    
            $("#carousel").show();
            $("#carousel").css("display","inline");
        });
        
         $('#carousel').tinycarousel({ display: 3});
                        
 });
</script>
</form>
<div id="main">        
    <div id="slider">
        <a class="buttons prev" href="#">left</a>
        <div class="viewport">
            <ul class="overview">
                <?php foreach ($path as $image) 
                {?>
                <li><img src ="images/rewarditems/<?php echo $image['path']; ?>" height="350" width="640" /></li>
                <?php
                }?>
            </ul>
        </div>
        <a class="buttons next" href="#">right</a>
        <div id="moreitems"><input class="viewmore" type="button" name="more" value="View More" /></div>
    </div>
    
    <div id="carousel">
        <a class="buttons prev" href="#">left</a>
        <div class="viewport">
            <ul class="overview">
                <?php foreach ($path as $image) {
                    ?>
                    <a class="popup" href="imageinfo.php?PathID=<?php echo $image['id']; ?>&CardTypeID=<?php echo $cardtypeid; ?>">
                        <li><img src ="images/rewarditems/<?php echo $image['path']; ?>"  width="200" height="100"  /></br>
                            <p><strong><u><?php echo $image['RewardName']; ?></u></strong></p></li></a>
                    <?php }
                ?>
            </ul>
        </div>
        <a class="buttons next" href="#">right</a>
    </div>

    <!-- End Carousel Wrapper -->
    <div id="rightcol">
    <?php
    if(isset($_SESSION["MemberInfo"]))
        include('profile.php');
    else
        include('login.php');
    ?>
    </div>
    <!-- End Login Wrapper -->
    
    <div id="detailbox"></div>
</div>
<?php include "footer.php"; ?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("init.inc.php");

$pagetitle = "Membership";

$customjavascripts[] = "js/jquery.tinycarousel.min.js";
$stylesheets[] = "css/tinycarousel.css";

$useCustomHeader = true;

App::LoadCore("URL.class.php");
App::LoadCore("Hashing.class.php");
App::LoadCore("Validation.class.php");
                                        
App::LoadModuleClass("Membership", "Members");
App::LoadModuleClass("Membership", "MemberInfo");
App::LoadModuleClass("Membership", "MemberSessions");
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Loyalty", "Cards");
App::LoadModuleClass("Loyalty", "CardTypes");
App::LoadModuleClass("Loyalty", "Rewards");
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
$viewbutton = new Button("viewbutton", "viewbutton", "View More");
$viewbutton->CssClass = "btnDefault roundedcorners";

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
<script type="text/javascript" language="javascript">
    $(document).ready(function() {
        
        function init() {
            $('#carousel').hide();
        }
        window.onload = init;
        
        $('#carousel').tinycarousel({ display: 3});
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
                        height: "auto",
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
                            //$(event.target).parent().css('top', '5%');
                            //$(event.target).parent().css('left', '20%');
                        }

                    });
                }
<?php } ?>
    
        $('#slider .viewmore').click(function(){
            $("#slider").hide(); 
            $("#carousel").show();
            $("#carousel").css("display","inline");
        });
     
                        
    });
</script>
</form>
<div id="main"> 
        <table>
            <tr>
                <td>
                    <div id="slider">
                        <a class="buttons prev" href="#">left</a>
                        <div class="viewport">
                            <ul class="overview">
                                <?php foreach ($path as $image) {
                                    ?>
                                    <li><img src ="images/rewarditems/<?php echo $image['path']; ?>" height="350" width="600" /></li>
                                <?php } ?>
                            </ul>
                        </div>
                        <a class="buttons next" href="#">right</a>
                        <div id="moreitems" align="right"><input class="yellow-btn viewmore" type="button" name="more" value="View More" /></div>
                    </div>

                    <!--Carousel-->
                    <div id="carousel">
                        <a class="buttons prev" href="#">left</a>
                        <div class="viewport">
                            <ul class="overview">
                                <?php foreach ($path as $image) { ?>
                                    <a class="popup" href="imageinfo.php?PathID=<?php echo $image['id']; ?>&CardTypeID=<?php echo $cardtypeid; ?>">
                                        <li><center><img src ="images/rewarditems/<?php echo $image['path']; ?>" /></center>
                                        <p><strong><u><?php echo $image['RewardName']; ?></u></strong></p></li></a>
                                <?php } ?>
                            </ul>
                        </div>
                        <a class="buttons next" href="#">right</a>
                    </div>
                    <!-- End Carousel Wrapper -->
                </td>
                <td>
                    <div id="rightcol">
                        <?php
                        if (isset($_SESSION["MemberInfo"]))
                            include('profile.php');
                        else
                            include('login.php');
                        ?>
                        <div id="home-latest-news">

                                <h3>Latest Events</h3>

                                <div id="home-latest-wrapper">                                    
                                                                        
                                                                            
                                        <div>&#187; <a href="http://staging.pegs.com/events/sfdgsdfg/">sfdgsdfg</a></div>
                                    
                                                                            
                                        <div>&#187; <a href="http://staging.pegs.com/events/4th-event-testing/">4th Event testing</a></div>
                                    
                                                                            
                                        <div>&#187; <a href="http://staging.pegs.com/events/third-event-testing/">Third Event Testing</a></div>
                                    
                                    
                               </div>
                                
                            </div>

                        </div><!-- #home-login-box -->
                        <div id="social-buttons-container" style="text-align:right;">
                            <div class="row-fluid">

                                <div class="span4 pull-right">
                                    <a href="http://www.twitter.com"><img src="http://staging.pegs.com/wp-content/themes/pegs_theme/img/twitter_icon.png" alt="Twitter" title="Twitter"></a>
                                    <a href="http://www.facebook.com"><img src="http://staging.pegs.com/wp-content/themes/pegs_theme/img/fb_icon.png" alt="Facebook" title="Facebook"></a>
                                </div>

                            </div>

                        </div><!-- #social-buttons-container --> 
                    </div>
                    <!-- End Login Wrapper -->
                </td>
            </tr>
        </table>
 
    <div id="detailbox"></div>
</div>
<?php include "footer.php"; ?>

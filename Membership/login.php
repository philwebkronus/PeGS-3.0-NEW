<?php include_once 'controller/logincontroller.php'; ?>
<?php include "header.php"; ?> 

<!--Slider Required Files-->
<link href="css/slider/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="css/slider/bootstrap-responsive.min.css" rel="stylesheet" media="screen">
<link href="css/slider/style.css" rel="stylesheet" media="screen">
<link rel="stylesheet" href="css/slider/prof_slider/ad_gallery.css">
<script type='text/javascript' src='admin/js/checkinput.js'></script>
<!-------------------------------->
<style type='text/css'>
    /*added by mkge 08-22-13
      style for login-to-redeem dialog's title bar   
    */
    .m-dialog-title{
        background: #700000;
        color: #fff;
    }
</style> 
<script type="text/javascript">
    //Added by: MKGE 08-22-13
    //Login to Redeem Dialog
    $(document).ready(function(){
       $("#gallery").click(function(event){
           
            //clear text boxes first
            $("#txtUsername").val("");
            $("#txtPassword").val("");
            $("#login-pop-up").dialog({
                autoOpen: true,
                modal: true,
                resizable: false,
                open: function(){
                    $(this).parents(".ui-dialog:first").find(".ui-dialog-titlebar").addClass("m-dialog-title");
                }
            });
            event.preventDefault();
       });
    });
</script>
<script language="javascript" type="text/javascript">

    $(window).load(function() {
        $('.ad-gallery').adGallery({effect: 'fade'
                    , loader_image: 'css/slider/loader.gif'
                    , display_next_and_prev: false
                    , display_back_and_forward: false
                    , slideshow: {
                enable: true,
                autostart: true,
                speed: 1000}
        });
        $('#scroll-to-top').click(function() {
            $("html, body").animate({scrollTop: 0}, "slow");
            return false;
        });
    });

    $(document).ready(function()
    {
        $("#loginForm").validationEngine();
        $("#viewdata").hide();
        $("#checkpointsresult").hide();
    });

//Start of javascript for checking of points.
    $("#btnCheck").live('click', function() {
        var cardnumber = $('#txtCardNumber').val();
        if (cardnumber != '') {
            //Start of ajax call for checking of points
            $.ajax(
                    {
                        url: "controller/Helper.checkpoints.php",
                        type: 'post',
                        data: {
                            CardNumber: function() {
                                return cardnumber;
                            }
                        },
                        datatype: 'json',
                        success: function(data)
                        {
                            var cardpointsresult = $.parseJSON(data);
                            $("#txtCardNumber").val("");
                            if (cardpointsresult.CurrentPoints == 'Card is Inactive') {
                                $("#tblcardnumber").html("<label>Card Number : <font color='red'>" + cardnumber + "</font></label>");
                                $("#tblcardpoints").html("<label>Message : <font color='red'>" + cardpointsresult.CurrentPoints + "</font></label>");
                            }
                            else if (cardpointsresult.CurrentPoints == 'Card is Deactivated') {
                                $("#tblcardnumber").html("<label>Card Number : <font color='red'>" + cardnumber + "</font></label>");
                                $("#tblcardpoints").html("<label>Message : <font color='red'>" + cardpointsresult.CurrentPoints + "</font></label>");
                            }
                            else if (cardpointsresult.CurrentPoints == 'Card is Banned') {
                                $("#tblcardnumber").html("<label>Card Number : <font color='red'>" + cardnumber + "</font></label>");
                                $("#tblcardpoints").html("<label>Message : <font color='red'>" + cardpointsresult.CurrentPoints + "</font></label>");
                            }
                            else if (cardpointsresult.CurrentPoints == 'Invalid Card') {
                                $("#tblcardnumber").html("<label>Card Number : <font color='red'>" + cardnumber + "</font></label>");
                                $("#tblcardpoints").html("<label>Message : <font color='red'>" + cardpointsresult.CurrentPoints + "</font></label>");
                            }
                            else if (cardpointsresult.CurrentPoints == 'Card is already Migrated') {
                                $("#tblcardnumber").html("<label>Card Number : <font color='red'>" + cardnumber + "</font></label>");
                                $("#tblcardpoints").html("<label>Message : <font color='red'>" + cardpointsresult.CurrentPoints + "</font></label>");
                            }
                            else {
                                $("#tblcardnumber").html("<label>Card Number : <font color='green'>" + cardnumber + "</font></label>");
                                $("#tblcardpoints").html("<label>Current Points : <font color='green'>" + cardpointsresult.CurrentPoints + "</font></label>");
                            }
                            $("#checkpointsresult").show();
                        },
                        error: function(error)
                        {
                            $("#txtCardNumber").val("");
                            $("#tblcardnumber").html("<label>Card Number : <font color='red'>" + cardnumber + "</font></label>");
                            $("#tblcardpoints").html("<label>Message : <font color='red'>Error on retrieving Current Points</font></label>");
                        }
                    });
            //End of ajax call for checking of points
        } else {
            $("#txtCardNumber").val("");
            $("#checkpointsresult").hide();
            alert("Please enter a valid Card Number!");
            return false;
        }
    });

    $(".checkpointslink").live('click', function() {
    $("#viewdata").show();
        if ($("#checkpoints").dialog('isOpen') !== true) {
            $("#checkpoints").dialog({
                open: function(event, ui) {
                    $(this).closest('.ui-dialog').find('.ui-dialog-titlebar-close').hide();
                },
                draggable: false,
                resizable: false,
                dialogClass: 'checkpointsdialog',
                modal: true,
                width: 350,
                height: 'auto',
                position: 'center',
                buttons:
                        {
                            "Close": function() {
                                $("#txtCardNumber").val("");
                                $("#checkpointsresult").hide();
                                $(this).dialog('close');
                            }
                        },
                title: 'Points Checking'
            }).parent().appendTo($("#viewdata"));
        }
    });
//end of javascript for checking of points
</script>
</form>
</form>
<!------LOGIN POP-UP FOR REDEEM----->
<!-----Added by: MKGE 08-22-13------>
<div id="login-pop-up" title="Login to Redeem" style="display:none; color: #fff; background: #700000">
    <form action="" method="POST" id="login-pop-up-form">
        <div class="home-login-form-wrapper">
            <div class="home-login-form-label">Username: </div>
            <div class="home-login-form-input"><?php echo $txtUsername; ?></div>
            <div class="clearfix"></div>
        </div>
        
        <div class="home-login-form-wrapper">
            <div class="home-login-form-label">Password: </div>
            <div class="home-login-form-input"><?php echo $txtPassword; ?></div>
            <div class="clearfix"></div>
        </div>
        
        <div class="home-login-form-wrapper">
            <div class="home-login-form-label">&nbsp;</div>
            <div class="home-login-form-input" style="margin-left: 94px;"><?php echo $btnLogin; ?></div>
            <div class="clearfix"></div>
        </div>
        
        <div id="home-login-form-link">
            <a class="pop-up-login-link" href="forgotpassword.php">Forgot Password?</a><br/>
            Not yet a member? Sign up <a class="pop-up-login-link" href="registration.php">here</a>
        </div>
    </form>    
</div>
<form name="loginForm" method="post" action="" id="loginForm" >
<!--<div class="login-error"><?php //echo App::GetErrorMessage();     ?></div>-->

    <!--</div>-->
    <div id="main"> 
        <div class="row-fluid">
            <div class="span8">
                <div class="round-black">
                    <!-- ### START - SLIDER GALLERY ### -->
                    <div id="gallery" class="ad-gallery">
                        <div class="ad-image-wrapper"></div>
                        <div class="ad-controls"></div>
                        <div class="ad-nav" id="ad-image">
                            <div class="ad-thumbs">
                                <ul class="ad-thumb-list">
                                    <li>
                                        <a href="images/slider/slider_photos/ConsolationPrize.jpg" >
                                            <img src="images/slider/slider_photos/ConsolationPrize.jpg" title="ConsolationPrize" longdesc="images/slider/slider_photos/ConsolationPrize.jpg" alt="Caption">
                                        </a>
                                    </li>
                                    <li>
                                        <a href="images/slider/slider_photos/devices.jpg">
                                            <img src="images/slider/slider_photos/devices.jpg" title="" longdesc="images/slider/slider_photos/devices.jpg" alt="">
                                        </a>
                                    </li>
                                    <li>
                                        <a href="images/slider/slider_photos/MacauLuxuryTour.jpg">
                                            <img src="images/slider/slider_photos/MacauLuxuryTour.jpg" title="" longdesc="images/slider/slider_photos/MacauLuxuryTour.jpg" alt="">
                                        </a>
                                    </li>
                                    <li>
                                        <a href="images/slider/slider_photos/Starcruise.jpg">
                                            <img src="images/slider/slider_photos/Starcruise.jpg" title="" longdesc="images/slider/slider_photos/Starcruise.jpg" alt="">
                                        </a>
                                    </li>
                                    <li>
                                        <a href="images/slider/slider_photos/HarleyDavidson.jpg">
                                            <img src="images/slider/slider_photos/HarleyDavidson.jpg" title="" longdesc="images/slider/slider_photos/HarleyDavidson.jpg" alt="">
                                        </a>
                                    </li>
                                    <li>
                                        <a href="images/slider/slider_photos/toyota.jpg">
                                            <img src="images/slider/slider_photos/toyota.jpg" title="" longdesc="images/slider/slider_photos/toyota.jpg" alt="">
                                        </a>
                                    </li>
                                </ul>
                            </div><!-- .ad-thumbs -->
                        </div><!-- .ad-nav -->
                    </div><!-- .ad-gallery -->    
                    <!-- ### END - SLIDER GALLERY ### -->                            
                </div>
                <br>
            </div>
            <div class="span4">
                <div id="home-login-box">    
                    <div id="home-login-wrapper">
                        <div id="home-page-login-form">
                            <div class="home-login-form-wrapper">
                                <div class="home-login-form-label">Username</div>
                                <div class="home-login-form-input"><?php echo $txtUsername; ?></div>
                                <div class="clearfix"></div>
                            </div>

                            <div class="home-login-form-wrapper">
                                <div class="home-login-form-label">Password:</div>
                                <div class="home-login-form-input"><?php echo $txtPassword; ?></div>
                                <div class="clearfix"></div>
                            </div>

                            <div class="home-login-form-wrapper">
                                <div class="home-login-form-label">&nbsp;</div>
                                <div class="home-login-form-input"><?php echo $btnLogin; ?></div>
                                <div class="clearfix"></div>
                            </div>
                            <div id="home-login-form-link">
                                <a href="forgotpassword.php">Forgot Password?</a><br/>
                                Not yet a member? Sign up <a href="registration.php">here</a><br/>
                                <a class="checkpointslink" href="#">Check Points</a>
                            </div>
                        </div>
                    </div>
                    <div id="home-latest-news">
                        <h3>Latest Events</h3>
                        <div id="home-latest-wrapper">                                    
                            <div>&#187; <a href="http://www.egamescasino.ph/events/luxury-knows-no-limits-raffle-promo/">Luxury Knows No Limits Raffle</a></div>
                            <div>&#187; <a href="http://www.egamescasino.ph/events/12th-e-games-operators-meeting/">Bar Tour Activations</a></div>
                        </div>
                    </div>
                </div>
            </div> <!-- End Login Wrapper -->
        </div>
    </div>
</form>
<!--Start of pop-up div for checking of points-->
<div id="viewdata">
    <div id="checkpoints">
        <?php echo $txtCardNumber; ?>
        <?php echo $btnCheck; ?>
        <div id="checkpointsresult">
            <table id="card-table">
                <tr><td id="tblcardnumber"></td></tr>
                <tr><td id="tblcardpoints"></td></tr>
            </table>
        </div>
    </div>
</div>
<!--End of pop-up div for checking of points-->
<?php include "footer.php"; ?>

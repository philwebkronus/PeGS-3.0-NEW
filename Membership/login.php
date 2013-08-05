<?php include_once 'controller/logincontroller.php'; ?>
<?php include "header.php"; ?> 

<!--Slider Required Files-->
<link href="css/slider/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="css/slider/bootstrap-responsive.min.css" rel="stylesheet" media="screen">
<link href="css/slider/style.css" rel="stylesheet" media="screen">
<link rel="stylesheet" href="css/slider/prof_slider/ad_gallery.css">
<!-------------------------------->
<script language="javascript" type="text/javascript">
    
    $(window).load(function() {
        $('.ad-gallery').adGallery({effect:'fade'
               ,loader_image: 'css/slider/loader.gif'
               ,display_next_and_prev: false
               ,display_back_and_forward: false
                ,slideshow: {
                           enable: true,
                           autostart: true,
                           speed: 1000}
       });
        $('#scroll-to-top').click(function() {
          $("html, body").animate({ scrollTop: 0 }, "slow");
          return false;
        });                                        
    });
    
    $(document).ready(function() 
    {
        $("#loginForm").validationEngine();
    });        
</script>
</form>
<form name="loginForm" method="post" action="" id="loginForm" >
<!--<div class="login-error"><?php //echo App::GetErrorMessage(); ?></div>-->

<!--</div>-->
<div id="main"> 
            <div class="row-fluid">
                <div class="span8">
                    <div class="round-black">
                        <!-- ### START - SLIDER GALLERY ### -->
                        <div id="gallery" class="ad-gallery">
                            <div class="ad-image-wrapper"></div>
                            <div class="ad-controls"></div>
                            <div class="ad-nav">
                                <div class="ad-thumbs">
                                    <ul class="ad-thumb-list">
                                        <li>
                                            <a href="images/slider/slider_photos/ConsolationPrize.jpg" >
                                                <img src="images/slider/slider_photos/ConsolationPrize.jpg" title="ConsolationPrize" longdesc="http://localhost/membershipsystem/images/slider/slider_photos/ConsolationPrize.jpg" alt="Caption">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="images/slider/slider_photos/devices.jpg">
                                                <img src="images/slider/slider_photos/devices.jpg" title="" longdesc="http://localhost/membershipsystem/images/slider/slider_photos/devices.jpg" alt="">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="images/slider/slider_photos/HarleyDavidson.jpg">
                                                <img src="images/slider/slider_photos/HarleyDavidson.jpg" title="" longdesc="http://localhost/membershipsystem/images/slider/slider_photos/HarleyDavidson.jpg" alt="">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="images/slider/slider_photos/Starcruise.jpg">
                                                <img src="images/slider/slider_photos/Starcruise.jpg" title="" longdesc="http://localhost/membershipsystem/images/slider/slider_photos/Starcruise.jpg" alt="">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="images/slider/slider_photos/MacauLuxuryTour.jpg">
                                                <img src="images/slider/slider_photos/MacauLuxuryTour.jpg" title="" longdesc="http://localhost/membershipsystem/images/slider/slider_photos/MacauLuxuryTour.jpg" alt="">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="images/slider/slider_photos/toyota.jpg">
                                                <img src="images/slider/slider_photos/toyota.jpg" title="" longdesc="http://localhost/membershipsystem/images/slider/slider_photos/toyota.jpg" alt="">
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
                                        Not yet a member? Sign up <a href="registration.php">here</a>
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
                    <div id="social-buttons-container" style="text-align:right;">
                        <div class="row-fluid">
                            <div class="span4 pull-right">
                                <a href="http://www.twitter.com"><img src="http://staging.pegs.com/wp-content/themes/pegs_theme/img/twitter_icon.png" alt="Twitter" title="Twitter"></a>
                                <a href="http://www.facebook.com"><img src="http://staging.pegs.com/wp-content/themes/pegs_theme/img/fb_icon.png" alt="Facebook" title="Facebook"></a>
                            </div>
                        </div>
                    </div> <!-- #social-buttons-container --> 
                </div> <!-- End Login Wrapper -->
            </div>
    </div>
</form>
<?php include "footer.php"; ?>

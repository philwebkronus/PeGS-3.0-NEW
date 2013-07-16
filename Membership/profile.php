<?php

/*
* Description: Profile View
* @author: aqdepliyan
* DateCreated: 2013-07-04 02:02:40 PM
*/

?>
<?php include_once 'controller/profilecontroller.php'; ?>
<?php include "header.php"; ?> 
<script type="text/javascript">
    
    $(document).ready(function() {
        
        
        $("#btnLearnMore").live("click",function(){
                var RewardItemID = $(this).attr('RewardItemID');
                var ProductName = $(this).attr('ProductName');
                var PartnerName = $(this).attr('PartnerName');
                var RewardOfferID = $(this).attr('RewardOfferID');
                var Points = $(this).attr('Points');
                var IsCoupon = $(this).attr('IsCoupon');
                var PlayerPoints = "<?php echo $PlayerPoints; ?>";
                $.ajax({
                    url: "controller/helpercontroller.php",
                    type: 'POST',
                    data : {
                                    rewarditemid : function(){return RewardItemID;},
                                    productname : function(){return ProductName;},
                                    partnername : function(){return PartnerName;},
                                    rewardofferid :  function(){return RewardOfferID;},
                                    points :  function(){return Points;},
                                    iscoupon :  function(){return IsCoupon;},
                                    playerpoints: function(){return PlayerPoints;}
                                },
                    success: function(response){
                        if(response){
                            window.location="redemption.php";
                        }
                    },
                    error: function(){
                        alert("Error in AJAX call.");
                    }
                });
            });
            
        function loadprofile() {
            $("#home-latest-news").addClass('profile-box');
            $("#carousel").hide();
        }
        
        window.onload = loadprofile;
        
        function reloadProfile() {
            parent.window.location.href='profile.php';
        }
        
        $("#txtPassword").blur(function(){
            txtpass = $('#txtPassword').val();

            if(txtpass != "")
            {
                $('#txtConfirmPassword').addClass('validate[required, custom[onlyLetterNumber], equals[txtPassword]]');
            }
        });
        
        
        
        $('#dtBirthDate').change(function()
        {
            dob1 = $('#dtBirthDate').val();
            dob = new Date(dob1.substr(0, 4), parseInt(dob1.substr(5, 2)) -1, dob1.substr(8, 2));
            var today = new Date();
            var age = Math.floor((today-dob) / (365.25 * 24 * 60 * 60 * 1000));
            $('#txtAge').val(age);
        });
        
        $("#btnUpdate").click(function() {     
            $("#dialog:ui-dialog").dialog("destroy");
            $("#UpdateProfileDialog").dialog("open");
            
        });
        
        $("#UpdateProfileDialog").dialog({
            autoOpen: false,
            modal: true,
            width: '800',
            title : 'PROFILE UPDATE',
            closeOnEscape: true,
            
            buttons: {
                "Submit": function() {
                    $("#hdnUpdateProfile").val('update');
                    $('#SubForm').submit();
                },
                "Cancel" : function() {
                    $(this).dialog("close");
                }
            },
            
            open: function (event, ui) {
                $(event.target).parent().css('position', 'fixed');
                $(event.target).parent().css('top', '5%');
                $(event.target).parent().css('left', '20%');
            }
        }).parent().appendTo($("#SubForm").validationEngine());
        
        $('#SuccessDialog').dialog({
            autoOpen: <?php echo $isOpen; ?>,
            modal: true,
            width: '400',
            title : 'Update Profile',
            closeOnEscape: true,            
            buttons: {
                "Ok": function() {
                    reloadProfile();
                    $(this).dialog("close");
                }
            }
        });
        
    });                   

</script>
<link href="css/slider/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="css/slider/bootstrap-responsive.min.css" rel="stylesheet" media="screen">
<link href="css/slider/prof_slider/style.css" rel="stylesheet" media="screen">
<link rel="stylesheet" href="css/slider/prof_slider/ad_gallery.css">
<script type="text/javascript">

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

</script>
    <div class="row-fluid">
        <div class="span4 round-gold" style="background-color: #FFF;">
                    <p>Hi <?php echo strtoupper($nick); ?>! [<a href="logout.php">Logout</a>]</p>
                    <ul style="list-style: none; margin-left: 10px;">
                        <li><strong><?php echo strtoupper($memberName); ?></strong></li>
                        <li>Card Number: <?php echo $cardNumber; ?></li>
                        <li>Mobile Number: <?php echo $mobileNumber; ?></li>
                        <li>Email Address: <?php echo $email; ?></li>
                        <li>Current Points: <?php echo $currentPoints; ?></li>
                        <li>Bonus Points: <?php echo $bonusPoints; ?></li>
                        <li>Redeemed Points: <?php echo $redeemedPoints; ?></li>
                        <li>Lifetime Points: <?php echo $lifetimePoints; ?></li>
                    </ul>
                    <?php echo $btnUpdate; ?>
                     <!-- End form declared in the header -->
        </div>
        </form>
        <div class="span8">
        <?php if($_SESSION["MemberInfo"]["CardTypeID"] != 3) { ?>
            <div class="round-black">
                <!-- ### START - SLIDER GALLERY ### -->
                <div id="gallery" class="ad-gallery">
                    <div class="ad-image-wrapper"></div>
                    <div class="ad-controls"></div>
                    <div class="ad-nav">
                        <div class="ad-thumbs">
                            <ul class="ad-thumb-list">
                                <li>
                                    <a href="images/slider/slider_photos/lamb.jpg">
                                        <img src="images/slider/bullet.png" title="Lamborghini" longdesc="http://www.lamborghini.com" alt="Caption">
                                    </a>
                                </li>
                                <li>
                                    <a href="images/slider/slider_photos/mac.jpg">
                                        <img src="images/slider/bullet.png" title="Apple" longdesc="" alt="">
                                    </a>
                                </li>
                                <li>
                                    <a href="images/slider/slider_photos/ubuntu.jpg">
                                        <img src="images/slider/bullet.png" title="Ubuntu" longdesc="" alt="">
                                    </a>
                                </li>
                                <li>
                                    <a href="images/slider/slider_photos/dummy_img.jpg">
                                        <img src="images/slider/bullet.png" title="Dollar" longdesc="" alt="">
                                    </a>
                                </li>
                            </ul>
                        </div><!-- .ad-thumbs -->
                      </div><!-- .ad-nav -->
                </div><!-- .ad-gallery -->    
                <!-- ### END - SLIDER GALLERY ### -->                            
            </div>
            <br>
            <!--Iteration for reward Offers Display-->
            <?php 
            $itr = 0;
            
            do{ ?>
            <div class="row-fluid product-container">
                <div class="span6 product-wrapper">
                    <div class="limited-ribbon"></div>
                    <div class="product-thumb">
                        <img src="images/slider/membership_innerpages/product_image_teaser.jpg">
                        <div class="social-overlay">
                            <div class="social-buttons">
                            <a href="#"><img src="images/slider/membership_innerpages/fb.png"></a>
                            <a href="#"><img src="images/slider/membership_innerpages/twitter.png"></a>
                            </div>
                        </div>
                    </div>
                    <div class="row-fluid product-details">
                        <div class="span6">
                            <div class="product-name"><?php echo $rewardoffers[$itr]['ProductName']; ?></div>
                            <div class="partner-name"><?php if($rewardoffers[$itr]['PartnerName'] != "" ){ 
                                                                                                        echo $rewardoffers[$itr]['PartnerName']; 
                                                                                                } else {
                                                                                                        echo "<span style='color: #FFFFFF'>None</span>";
                                                                                                }?></div>
                            <div class="product-points">Points: <?php if($rewardoffers[$itr]["Points"] != '') { echo number_format($rewardoffers[$itr]["Points"],0,'',','); } ?></div>
                        </div>
                        <div class="span6 learn-more-container">
                            <?php  
                                        $RewardItemID = $rewardoffers[$itr]["RewardItemID"];
                                        $PartnerName = $rewardoffers[$itr]['PartnerName'];
                                        $ProductName = $rewardoffers[$itr]['ProductName'];
                                        $RewardOfferID = $rewardoffers[$itr]['RewardOfferID'];
                                        $Points = $rewardoffers[$itr]['Points'];
                                        $IsCoupon = $rewardoffers[$itr]['IsCoupon'];
                            ?>
                            <input type="button" value="Learn More" class="yellow-btn-learn-more" id="btnLearnMore" RewardItemID='<?php echo $RewardItemID; ?>' 
                                PartnerName='<?php echo $PartnerName; ?>' ProductName='<?php echo $ProductName; ?>' 
                                RewardOfferID='<?php echo $RewardOfferID; ?>' Points='<?php echo $Points; ?>' IsCoupon='<?php echo $IsCoupon; ?>' />
                        </div>
                    </div>
                </div><!-- .product-wrapper -->
                <?php 
                $itr++;
                
                if(isset($rewardoffers[$itr]['ProductName'])) { ?>
                <div class="span6 product-wrapper">
                    <div class="limited-ribbon"></div>
                    <div class="product-thumb">
                        <img src="images/slider/membership_innerpages/product_image_teaser.jpg">
                        <div class="social-overlay">
                            <div class="social-buttons">
                            <a href="#"><img src="images/slider/membership_innerpages/fb.png"></a>
                            <a href="#"><img src="images/slider/membership_innerpages/twitter.png"></a>
                            </div>
                        </div>
                    </div>
                    <div class="row-fluid product-details">
                        <div class="span6">
                            <div class="product-name"><?php echo $rewardoffers[$itr]['ProductName']; ?></div>
                            <div class="partner-name"><?php if($rewardoffers[$itr]['PartnerName'] != "" ){ 
                                                                                                        echo $rewardoffers[$itr]['PartnerName']; 
                                                                                                } else {
                                                                                                        echo "<span style='color: #FFFFFF'>None</span>";
                                                                                                }?></div>
                            <div class="product-points">Points: <?php if($rewardoffers[$itr]["Points"] != '') { echo number_format($rewardoffers[$itr]["Points"],0,'',','); } ?></div>
                        </div>
                        <div class="span6 learn-more-container">
                            <?php  
                                        $RewardItemID = $rewardoffers[$itr]["RewardItemID"];
                                        $PartnerName = $rewardoffers[$itr]['PartnerName'];
                                        $ProductName = $rewardoffers[$itr]['ProductName'];
                                        $RewardOfferID = $rewardoffers[$itr]['RewardOfferID'];
                                        $Points = $rewardoffers[$itr]['Points'];
                                        $IsCoupon = $rewardoffers[$itr]['IsCoupon'];
                            ?>
                            <input type="button" value="Learn More" class="yellow-btn-learn-more" id="btnLearnMore" RewardItemID='<?php echo $RewardItemID; ?>' 
                                PartnerName='<?php echo $PartnerName; ?>' ProductName='<?php echo $ProductName; ?>' 
                                RewardOfferID='<?php echo $RewardOfferID; ?>' Points='<?php echo $Points; ?>' IsCoupon='<?php echo $IsCoupon; ?>' />
                        </div>
                    </div>
                </div><!-- .product-wrapper-->
                <?php } else { echo "</div>"; break; } ?>
            </div><!-- .product-container -->
            <?php
            $itr++;
            } while ($itr != count($rewardoffers)); ?>
            <!--End of Iteration-->
            
            <a href="#" id="scroll-to-top"></a>
         <?php } else {
            echo "<p style='font-size: 14px;'>Please migrate your Temporary Account to a Membership Card to activate Redemption.</p>";
        } ?>
        </div>
    </div>
    <form name="SubForm" id="SubForm" method="post" action="" enctype="multipart/form-data" >

        <!-- Update Profile page holder -->
        <div id="UpdateProfileDialog" name="UpdateProfileDialog">
            <br /><?php echo $hdnUpdateProfile; ?>
            <table>
                <tr>
                    <td width="20%">First Name*</td>
                    <td width="30%"><?php echo $txtFirstName; ?></td>
                    <td width="20%">Nickname</td>
                    <td width="30%"><?php echo $txtNickName; ?></td>
                </tr>
                <tr>
                    <td>Middle Name</td>
                    <td><?php echo $txtMiddleName; ?></td>
                    <td>Mobile Number*</td>
                    <td><?php echo $txtMobileNumber; ?></td>
                </tr>
                <tr>
                    <td>Last Name*</td>
                    <td><?php echo $txtLastName; ?></td>
                    <td>Alternate Mobile Number</td>
                    <td><?php echo $txtAlternateMobileNumber; ?></td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td><?php echo $txtPassword; ?></td>
                    <td>Email Address*</td>
                    <td><?php echo $txtEmail; ?></td>
                </tr>
                <tr>
                    <td>Confirm Password</td>
                    <td><?php echo $txtConfirmPassword; ?></td>
                    <td>Alternate Email</td>
                    <td><?php echo $txtAlternateEmail; ?></td>
                </tr>
                <tr>
                    <td>Permanent Address</td>
                    <td><?php echo $txtAddress1; ?><br/>
                        <?php echo $txtAddress2; ?><br/></td>
                    <td>Gender</td>
                    <td><div style="float: left"><?php echo $rdoGroupGender->Radios[0]; ?></div><div style="float: left; margin-left: 50px;"><?php echo $rdoGroupGender->Radios[1]; ?></div></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td>Birthdate*</td>
                    <td><?php echo $dtBirthDate; ?></td>
                </tr>      

                <tr>
                    <td>ID Presented*</td>
                    <td><?php echo $txtIDPresented; ?><br/>
                        <?php echo $cboIDSelection; ?></td>
                    <td>Age</td>
                    <td><?php echo $txtAge; ?></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td>Nationality</td>
                    <td><?php echo $cboNationality; ?></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td>Occupation</td>
                    <td><?php echo $cboOccupation; ?></td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td><?php echo $rdoGroupSmoker->Radios[0]; ?></td>
                    <td><?php echo $rdoGroupSmoker->Radios[1]; ?></td>
                </tr>

            </table>
        </div>

        <div id="SuccessDialog" name="SuccessDialog">
            <?php if ($isOpen == 'true')
            { 
                if(isset($resultmsg)){
                    echo "<p>".$resultmsg."</p>";
                }
            } ?>
        </div>
<?php include "footer.php"; ?>
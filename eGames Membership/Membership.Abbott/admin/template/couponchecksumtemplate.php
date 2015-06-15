<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-03-06
 * Company: Philweb
 * ***************** */
?>
<tr>
                                    <td nowrap><strong>e-Coupon Series: </strong></td>
                                    <td><strong>000500 - 001200</strong></td>
                                </tr>
                                <tr>
                                    <td>No. of Coupons :</td>
                                    <td>$quantity</td>
                                </tr>
                                <tr>
                                    <td>Issuing Cafe:</td>
                                    <td>TIM</td>
                                </tr>
                                <tr>
                                    <td>Date Redeemed:</td>
                                    <td>date("F j, Y, g:i a"); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>Promo Code:</td>
                                    <td>13001</td>
                                </tr>
                                <tr>
                                    <td>Promo Title:</td>
                                    <td>Summer Raffle</td>
                                </tr>
                                <tr>
                                    <td>Promo Period:</td>
                                    <td>March 30 to April 30, 2013</td>
                                </tr>
                                <tr>
                                    <td>Draw Date:</td>
                                    <td>May 10, 2013 4PM</td>
                                </tr>
                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>Card Number:</td>
                                    <td><?php echo $cardno; ?></td>
                                </tr>
                                <tr>
                                    <td>Name:</td>
                                    <td><?php echo $playername; ?></td>
                                </tr>
                                <!-- tr>
                                    <td>Address:</td>
                                    <td>#11 Maginhawa Street, Diliman, Quezon City NCR</td>
                                </tr -->
                                
                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td>Birthday:</td>
                                    <td><?php echo date("F j, Y", strtotime($birthdate)); ?></td>
                                </tr>
                                <tr>
                                    <td>E-mail Address:</td>
                                    <td><?php echo $email; ?></td>
                                </tr>
                                <tr>
                                    <td>Contact Number:</td>
                                    <td><?php echo $contactno; ?></td>
                                </tr>

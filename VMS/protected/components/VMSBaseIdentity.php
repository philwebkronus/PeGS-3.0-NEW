<?php

/**
 * @author owliber
 * @date Nov 12, 2012
 * @filename VMSUserRoles.php
 * 
 */

class VMSBaseIdentity extends Controller
{
     /**
     * @return array action filters
     */
    public function filters()
    {
            return array(
                    'accessControl', // perform access control for CRUD operations
            );
    }
    
    public function accessRules()
    {                
        
            return array(     
                    array('allow',  //login
                            'actions'=>array('login'),
                            'users'=>array('*'),
                    ),
                    array('allow',
                            /**
                             * Allow authenticated users to perform actions 
                             * defined in all menu/submenu links
                             */
                            'actions'=>array('index','verify','manage'),
                            'users'=>Yii::app()->user->allowedUsers(),
                    ),
                    array('allow', // allow other actions from logged users
                            'actions'=>array('login','logout','index','error', //SiteController actions
                                             'update','list','generate','monitor',
                                             'changestatus','delete','exportToCSV','exportToExcel','exporttoexcelcoupon','exporttoexcelticket',
                                             'siteConversionDataTable','dataTable',
                                             'ajaxGetTerminal','ajaxStackerSessions','ajaxVoucherUsage',
                                             'ajaxStackerDetails','ajaxLastQuery','ajaxEGMachines','ajaxVoucherMonitor',
                                             'reimbursableVoucherDataTable','voucherUsageDataTable',
                                             'validationDataTable','vmslogs','apilogs', 'loadBatch','getVoucherInfo',
                                             'regenerate', 'loadTicketStatus', 'confChangeTicketStatus', 'getChangeStatusHistory',
                                             'index','exportXls', 'viewTicketConf', 'ticketAutoGenConf', 'confirmConfigSetting', 
                                             'getConfigurationHistory', 'coupon', 'ticket', 'changeCouponStatus', 
                                             'changeTicketStatus','loadAllTicketInfo', 'getTotalTickets', 'getTicketRedemptions', 
                                             'getTicketCuOffSummary', 'checkDateRange'
                                       ),
                            'users'=>array('@'),
                    ),
                    array('deny',  // deny all users
                            'users'=>array('*'),
                    ),
            );
    }
}
?>

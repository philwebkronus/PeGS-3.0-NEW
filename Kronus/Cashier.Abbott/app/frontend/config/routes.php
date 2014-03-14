<?php

return array(
    'default_page'=>'Terminal/overview',
    'error_404'=>'Error/error404', // page not found
    'error_401'=>'Error/error401', // unauthorized
    'error_500'=>'Error/error500', // internal server error
    
    // route for authentication
    'login'=>'Authenticate/login',
    'passkey'=>'Authenticate/passKey',
    'logout'=>'Authenticate/logout',
    'forgotwizard'=>'Authenticate/forgotWizard',
    'storemachineinfo'=>'Authenticate/storeMachineInfo',
    'changepass'=>'Authenticate/changePassword',
    'forgotpass'=>'Authenticate/forgotPassword',
    'forgotuser'=>'Authenticate/forgotUsername',
    'updatepassword'=>'Authenticate/updatepassword',
    
    // routes for terminal monitoring
    'terminal/overview'=>'Terminal/overview',
    'terminal'=>'Terminal/overview',
    'terminal/startsession'=>'Terminal/startSessionClick', // click
    'terminal/sitebalance'=>'Terminal/getSiteBalance',
    'terminal/redeem' => 'Terminal/redeemClick', // click
    'terminal/reload'=>'Terminal/reloadClick', // click
    'terminal/startsessionhk'=>'Terminal/startSessionHotkey', // hot key
    'terminal/denomination'=>'Terminal/getDenominationAndCasino', // onchange of terminal
    'terminal/reloadhk'=>'Terminal/reloadHotkey', // hot key
    'terminal/redeemhk'=>'Terminal/redeemHotkey', // hot key
    'terminal/redeemgetbalance' =>'Terminal/redeemGetBalance',
    'terminal/ping'=>'Terminal/ping',
    
    // routes for start session stand alone
    'startsession'=>'StartSession/overview',
    
    // routes for reload session stand alone
    'reload'=>'ReloadSession/overview',
    
    // routes for redeem stand alone
    'redeem'=>'Redeem/overview',
    'redeem/getamount'=>'Redeem/getRedeemableAmountAndDetails',
    'redeem/getdetail'=>'Redeem/getDetail',
    
    // routes for loyalty
    'loyalty/cardinquiry'=>'Loyalty/cardInquiry',                       
    'loyalty/transferpoints'=>'Loyalty/transferPoints',
    
    // routes for reports
    'reports'=>'Reports/overview',
    'reports/transactionhistory'=>'Reports/transactionHistory',
    'reports/transactionhistorypercashier'=>'Reports/transactionHistoryPerCashier',
    
    // routes for view transaction
    'viewtrans/overview'=>'ViewTransaction/overview',
    'viewtrans'=>'ViewTransaction/viewTransaction',
    
    // routes for refresh
    'refresh'=>'Refresh/getBalancePerPage',
    
    'pdf/transactionhistory'=>'PDFGenerator/transactionHistory',
    'pdf/transactionhistorycashier'=>'PDFGenerator/transactionHistoryCashier',
    
    /************************ monitoring module routes ************************/
    'monitoring/overview'=>'monitoring/TerminalMonitoring/overview',
    
    //'spyder'=>'Spyder/run',
    'spyder/run'=>'sapi/Spyder/run',
);

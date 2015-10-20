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
    'checkreferrer'=>'Authenticate/checkReferrer',
    
    // routes for terminal monitoring
    'terminal/overview'=>'Terminal/overview',
    'terminal'=>'Terminal/overview',
    'terminal/startsession'=>'Terminal/startSessionClick', // click
    'terminal/sitebalance'=>'Terminal/getSiteBalance',
    'terminal/redeem' => 'Terminal/redeemClick', // click
    'terminal/reload'=>'Terminal/reloadClick', // click
    'terminal/startsessionhk'=>'Terminal/startSessionHotkey', // hot key
    'terminal/denomination'=>'Terminal/getDenominationAndCasino', // onchange of terminal
    'terminal/reloadamount'=>'Terminal/getDenomination', // onchange of terminal
    'terminal/reloadhk'=>'Terminal/reloadHotkey', // hot key
    'terminal/redeemhk'=>'Terminal/redeemHotkey', // hot key
    'terminal/redeemgetbalance' =>'Terminal/redeemGetBalance',
    'terminal/ping'=>'Terminal/ping',
    'terminal/unlock'=>'Terminal/unlockClick',
    'terminal/lock'=>'Terminal/lockClick',
    'terminal/redeemclose'=>'Terminal/closeHotkey',
    'terminal/unlockhk'=>'Terminal/unlockHotKey',
    'terminal/calllock'=>'Terminal/callLock',
    'terminal/callUnlock'=>'Terminal/callUnlock',
    'terminal/getCasinoServices'=>'Terminal/getAndCountCasinoServices',
    'terminal/isEwallet'=>'Terminal/isEwalletSession',
    'terminal/checkSession'=>'Terminal/checkExistingSession',
    
    // routes for (forceT) termin monitoring
    'fterminal/overview'=>'ForceTTerminal/overview',
    
    // routes for start session stand alone
    'startsession'=>'StartSession/overview',
    
    // routes for reload session stand alone
    'reload'=>'ReloadSession/overview',
    'reload/ubaccount'=>'ReloadSession/reloadUbaccount',
    
    // routes for redeem stand alone
    'redeem'=>'Redeem/overview',
    'redeem/redeemForcet'=>'Redeem/withdrawForcet',
    'redeem/getamount'=>'Redeem/getRedeemableAmountAndDetails',
    'redeem/getdetail'=>'Redeem/getDetail',
    'redeem/getbalance'=>'Redeem/getRedeemableAmount',
    
    // routes for loyalty
    'loyalty/cardinquiry'=>'Loyalty/cardInquiry',                       
    'loyalty/transferpoints'=>'Loyalty/transferPoints',
    
    // routes for reports
    'reports'=>'Reports/overview',
    'reports/transactionhistory'=>'Reports/transactionHistory',
    'reports/transactionhistorypertID'=>'Reports/transactionHistoryPerTerminalID',
    'reports/transactionhistorytotal'=>'Reports/transactionHistoryTotal',
    'reports/transactionhistorysales'=>'Reports/transactionHistorySales',
    'reports/transactionhistorypercashier'=>'Reports/transactionHistoryPerCashier',
    'reports/transactionhistorypercashierwtID'=>'Reports/transactionHistoryPerCashierWithTerminalID',
    'reports/transactionhistoryperctotal'=>'Reports/transactionHistoryPerCashierTotal',
    'reports/transactionhistorypercsales'=>'Reports/transactionHistoryPerCashierSales',
    'reports/transactionhistorypervirtualcashier'=>'Reports/transactionHistoryPerVirtualCashier',
    'reports/transactionhistorypervcashierwtID'=>'Reports/transactionHistoryPerVirtualCashierWithTerminalID',
    'reports/transactionhistorypervctotal'=>'Reports/transactionHistoryPerVirtualCashierTotal',
    'reports/transactionhistorypervcsales'=>'Reports/transactionHistoryPerVirtualCashierSales',
    'reports/eWalletPerSite'=>'Reports/eWalletTransactionHistoryPerSite',
    'reports/eWalletPerSiteTotal'=>'Reports/eWalletTransactionHistoryPerSiteTotal',
    'reports/eWalletPerCashier'=>'Reports/eWalletTransactionHistoryPerCashier',
    'reports/eWalletPerCashierTotal'=>'Reports/eWalletTransactionHistoryPerCashierTotal',
    
    // routes for view transaction
    'viewtrans/overview'=>'ViewTransaction/overview',
    'viewtrans/overview2'=>'ViewTransaction/overview2',
    'viewtrans'=>'ViewTransaction/viewTransaction',
    'viewtranspervc'=>'ViewTransaction/viewTransactionPerVirtualCashier',
    'viewtrans/history'=>'ViewTransaction/history',
    'viewtrans/ewalletPerSite'=>'ViewTransaction/viewEwalletTransactionPerSite',
    'viewtrans/ewalletPerCashier'=>'ViewTransaction/viewEwalletTransactionPerCashier',
    'viewtrans/ewalletPerSite'=>'ViewTransaction/viewEwalletTransactionPerSite',
    'viewtrans/ewalletPerVCashier'=>'ViewTransaction/viewEwalletTransactionPerVirtualCashier',
    
    // routes for refresh
    'refresh'=>'Refresh/getBalancePerPage',
    
    'pdf/transactionhistory'=>'PDFGenerator/transactionHistory',
    'pdf/transactionhistorycashier'=>'PDFGenerator/transactionHistoryCashier',
    
    // routes for Force T. - Load and Withdraw
    'forcet'=>'ForceT/overview',
    'forcet/load'=>'ForceT/load',
    'forcet/withdraw'=>'ForceT/withdraw',
    
    // routes for Force T - Load
    'load/getamount'=>'Load/getRedeemableAmountAndDetails3',
    
    // routes for Force T - withdraw
    'withdraw/getamount'=>'Withdraw/getRedeemableAmountAndDetails3',
    'withdraw/getdetail'=>'Withdraw/getDetail',
    
    
    // routes for Lock and Unlock
    'lockandunlock'=>'LockAndUnlock/overview',
    
    // routes for Lock (Standalone)
    'lock'=>'LockTerminal/overview',
    'lock/getamount'=>'LockTerminal/getRedeemableAmountAndDetails4',
    'lock/getamount2'=>'LockTerminal/getRedeemableAmountAndDetails5',
    'lock/getdetail'=>'LockTerminal/getDetail',
    
    // routes for Unlock (Standalone)
    'unlock'=>'UnlockTerminal/overview',
    
    /************************ monitoring module routes ************************/
    'monitoring/overview'=>'monitoring/TerminalMonitoring/overview',
    'monitoring/newtm'=>'monitoring/ForceTTerminal/overview',
    
    //'spyder'=>'Spyder/run',
    'spyder/run'=>'sapi/Spyder/run',
    
    
);

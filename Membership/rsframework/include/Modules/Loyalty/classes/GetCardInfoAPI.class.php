<?php

/*
 * @author : owliber
 * @date : 2013-04-22
 */

class GetCardInfoAPI extends BaseEntity
{

    public function GetCardInfoAPI()
    {
        $this->ConnString = "loyalty";
    }
    
    /**
     * 
     * @param int $cardnumber
     * @return string array of Member details and card information
     */
    public function GetCardInfo( $cardnumber, $status, $isreg )
    {
        //Load MemberInfo Module Class
        App::LoadModuleClass("Membership", "MemberInfo");
        App::LoadModuleClass("Membership", "TempMembers");
        App::LoadModuleClass("Membership", "MemberServices");        
        App::LoadModuleClass("Membership", "MigrateMember");  
        App::LoadModuleClass("Membership", "ActivateMember");
        App::LoadModuleClass("Membership", "Helper");
        App::LoadModuleClass('Loyalty', "MemberCards");
        App::LoadModuleClass("Loyalty", "Cards");   
        
        
        //Instantiate Models
        $_MemberInfo = new MemberInfo();
        $_TempMembers = new TempMembers();
        $_MemberServices = new MemberServices();
        //$_MigrateMember = new MigrateMember();
        $_MemberCards = new MemberCards();
        $_Cards = new Cards();
        
        $_ActivateMember = new ActivateMember();
        $_Helper = new Helper();
                
        switch ( $status )
        {
            case CardStatus::INACTIVE:            
                $result = array("CardInfo"=>array(
                                        "MID"              => "",
                                        "Username"         => "",
                                        "CardNumber"       => "",
                                        "MemberUsername"   => "",
                                        "CardType"         => "",
                                        "MemberName"       => "",
                                        "RegistrationDate" => "",
                                        "Birthdate"        => "",
                                        "CurrentPoints"    => "",
                                        "LifetimePoints"   => "",
                                        "RedeemedPoints"   => "",
                                        "IsCompleteInfo"   => "",
                                        "MemberID"         => "",                                                                     
                                        "CasinoArray"      => "",
                                        "CardStatus"       => intval(CardStatus::INACTIVE),
                                        "DateVerified"     => "",
                                        "MobileNumber"     => "",
                                        "Email"            => "",
                                        "IsReg"            => intval($isreg),
                                        "CoolingPeriod"    => "",
                                        "StatusCode"       => intval(CardStatus::INACTIVE),
                                        "StatusMsg"        => 'Inactive Card',
                                        )
                           );
                
                return $result;
                break;
            
            case CardStatus::ACTIVE:      
                
                $cardinfo = $_MemberCards->getMemberPoints( $cardnumber );                
                 
                $memberInfo = $_MemberInfo->getMemberInfo($cardinfo[0]['MID']);
                $row = array_merge($cardinfo[0],$memberInfo[0]);
                                
                $cardType = $_Cards->getCardInfo( $cardnumber );
                
                //Get Member's casino services
                $casinoAccounts = $_MemberServices->getCasinoAccounts( $row['MID'] );
            
                $result = array("CardInfo"=>array(
                                         "MID"              => $row['MID'],
                                         "Username"         => "",
                                         "CardNumber"       => $cardnumber,
                                         "MemberUsername"   => $row['Email'],
                                         "CardType"         => $cardType[0]['CardTypeID'],
                                         "MemberName"       => $row['FirstName'] . ' ' . $row['LastName'],
                                         "RegistrationDate" => $row['DateCreated'],
                                         "Birthdate"        => $row['Birthdate'],
                                         "CurrentPoints"    => $row['CurrentPoints'],
                                         "LifetimePoints"   => $row['LifetimePoints'],
                                         "RedeemedPoints"   => $row['RedeemedPoints'],
                                         "IsCompleteInfo"   => $row['IsCompleteInfo'],
                                         "MemberID"         => $row['MID'],                                                                     
                                         "CasinoArray"      => $casinoAccounts,
                                         "CardStatus"       => intval($row['Status']),
                                         "DateVerified"     => $row['DateVerified'],
                                         "MobileNumber"     => $row['MobileNumber'],
                                         "Email"            => $row['Email'],
                                         "IsReg"            => intval($isreg),
                                         "CoolingPeriod"    => "",
                                         "StatusCode"       => intval(CardStatus::ACTIVE),
                                         "StatusMsg"        => 'Active Card',
                                         )
                            );
                
                return $result;
                break;
            
            case CardStatus::DEACTIVATED: 
                
                $result = array("CardInfo"=>array(
                                        "MID"              => "",
                                        "Username"         => "",
                                        "CardNumber"       => "",
                                        "MemberUsername"   => "",
                                        "CardType"         => "",
                                        "MemberName"       => "",
                                        "RegistrationDate" => "",
                                        "Birthdate"        => "",
                                        "CurrentPoints"    => "",
                                        "LifetimePoints"   => "",
                                        "RedeemedPoints"   => "",
                                        "IsCompleteInfo"   => "",
                                        "MemberID"         => "",                                                                     
                                        "CasinoArray"      => "",
                                        "CardStatus"       => intval(CardStatus::DEACTIVATED),
                                        "DateVerified"     => "",
                                        "MobileNumber"     => "",
                                        "Email"            => "",                                        
                                        "IsReg"            => intval($isreg),
                                        "CoolingPeriod"    => "",
                                        "StatusCode"       => intval(CardStatus::DEACTIVATED),
                                        "StatusMsg"        => 'Deactivated Card',
                                        )
                           );
                
                return $result;
                break;
            
            case CardStatus::OLD:     
                
                $query = "SELECT Username
                        , CardNumber
                        , CardTypeID
                        , MemberName
                        , RegistrationDate
                        , Birthdate
                        , (LifetimePoints - RedeemedPoints) AS CurrentPoints
                        , LifetimePoints
                        , RedeemedPoints
                        , IsCompleteInfo
                        , MemberID AS MID
                        , CasinoArray
                        , CardStatus
                        , DateVerified
                        , MobileNumber
                        , Email
                   FROM
                     oldcards
                   WHERE
                     CardNumber = '$cardnumber'";
        
                $row = parent::RunQuery($query); 
                                
                $result = array("CardInfo"=>array(
                                         "MID"              => "",
                                         "Username"         => $row[0]['Username'],
                                         "CardNumber"       => $row[0]['CardNumber'],
                                         "MemberUsername"   => $row[0]['Username'],
                                         "CardType"         => $row[0]['CardTypeID'],
                                         "MemberName"       => $row[0]['MemberName'],
                                         "RegistrationDate" => $row[0]['RegistrationDate'],
                                         "Birthdate"        => $row[0]['Birthdate'],
                                         "CurrentPoints"    => $row[0]['CurrentPoints'],
                                         "LifetimePoints"   => $row[0]['LifetimePoints'],
                                         "RedeemedPoints"   => $row[0]['RedeemedPoints'],
                                         "IsCompleteInfo"   => $row[0]['IsCompleteInfo'],
                                         "MemberID"         => $row[0]['MID'],                                                                     
                                         "CasinoArray"      => "",
                                         "CardStatus"       => $row[0]['CardStatus'],
                                         "DateVerified"     => $row[0]['DateVerified'],
                                         "MobileNumber"     => $row[0]['MobileNumber'],
                                         "Email"            => $row[0]['Email'],                    
                                         "IsReg"            => intval($isreg),
                                         "CoolingPeriod"    => "",
                                         "StatusCode"       => intval(CardStatus::OLD),
                                         "StatusMsg"        => 'Old Loyalty Card',
                                         )
                            );
                
                return $result;
                break;
            
            case CardStatus::OLD_MIGRATED: 
                
                $result = array("CardInfo"=>array(
                                        "MID"              => "",
                                        "Username"         => "",
                                        "CardNumber"       => "",
                                        "MemberUsername"   => "",
                                        "CardType"         => "",
                                        "MemberName"       => "",
                                        "RegistrationDate" => "",
                                        "Birthdate"        => "",
                                        "CurrentPoints"    => "",
                                        "LifetimePoints"   => "",
                                        "RedeemedPoints"   => "",
                                        "IsCompleteInfo"   => "",
                                        "MemberID"         => "",                                                                     
                                        "CasinoArray"      => "",
                                        "CardStatus"       => intval(CardStatus::OLD_MIGRATED),
                                        "DateVerified"     => "",
                                        "MobileNumber"     => "",
                                        "Email"            => "",                                        
                                        "IsReg"            => intval($isreg),
                                        "CoolingPeriod"    => "",
                                        "StatusCode"       => intval(CardStatus::OLD_MIGRATED),
                                        "StatusMsg"        => 'Migrated Old Loyalty Card',
                                        )
                           );
                
                return $result;
                break;
            
            case CardStatus::ACTIVE_TEMPORARY:   
               
                //Member has no card records yet
                if(!$_Cards->isExist( $cardnumber ))
                {  
                    
                    App::LoadModuleClass("Membership", "AuditTrail");
                    App::LoadModuleClass("Membership", "AuditFunctions");
                    
                    /**
                     * MIGRATE MEMBER ACCOUNTS
                     * Transfer temporary member information to permanent database.
                     * Generate card record per temporary account code.
                     * Generate Casino Accounts to local database
                     * Insert generated Casino Account to backend
                     */
                    
                    $_Log = new AuditTrail();
                    
                    $activation = $_ActivateMember->Migrate( $cardnumber );
                    
                    if(count ( $activation ) > 0 )
                    {
                        $status = $activation['status'];
                        
                        if($status == 'OK')
                        {
                            $MID = $activation['MID'];   
                        
                            $casinoAccounts = $_MemberServices->getCasinoAccounts( $MID );                            
                            $memberInfo = $_MemberInfo->getMemberInfo( $MID );
                            $row = $memberInfo[0];

                            $cardinfo = $_MemberCards->getMemberCardInfoByCard( $cardnumber );
                            $points = $cardinfo[0];
                            
                            $result = array("CardInfo"=>array(
                                         "MID"              => $MID,
                                         "Username"         => "",
                                         "CardNumber"       => $cardnumber,//$row['CardNumber'],
                                         "MemberUsername"   => $row['UserName'],
                                         "CardType"         => "",
                                         "MemberName"       => $row['FirstName'] . ' ' . $row['LastName'],
                                         "RegistrationDate" => $row['DateCreated'],
                                         "Birthdate"        => $row['Birthdate'],
                                         "CurrentPoints"    => $points['CurrentPoints'],
                                         "LifetimePoints"   => $points['LifetimePoints'],
                                         "RedeemedPoints"   => $points['RedeemedPoints'],
                                         "IsCompleteInfo"   => $row['IsCompleteInfo'],
                                         "MemberID"         => $MID,                                                                     
                                         "CasinoArray"      => $casinoAccounts,
                                         "CardStatus"       => intval(CardStatus::ACTIVE_TEMPORARY),
                                         "DateVerified"     => $row['DateVerified'],
                                         "MobileNumber"     => $row['MobileNumber'],
                                         "Email"            => $row['Email'],                                         
                                         "IsReg"            => intval($isreg),
                                         "CoolingPeriod"    => $_Helper->getParameterValue('COOLING_PERIOD'),
                                         "StatusCode"       => intval(CardStatus::ACTIVE_TEMPORARY),
                                         "StatusMsg"        => 'Active Temporary Account',
                                         )
                            );
                            
                            $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $cardnumber.':Success', $cardnumber);
                        }
                        else
                        {
                            $result = array("CardInfo"=>array(
                                         "MID"              => "",
                                         "Username"         => "",
                                         "CardNumber"       => $cardnumber,//$row['CardNumber'],
                                         "MemberUsername"   => "",
                                         "CardType"         => "",
                                         "MemberName"       => "",
                                         "RegistrationDate" => "",
                                         "Birthdate"        => "",
                                         "CurrentPoints"    => "",
                                         "LifetimePoints"   => "",
                                         "RedeemedPoints"   => "",
                                         "IsCompleteInfo"   => "",
                                         "MemberID"         => "",                                                                     
                                         "CasinoArray"      => "",
                                         "CardStatus"       => intval(CardStatus::ACTIVE_TEMPORARY),
                                         "DateVerified"     => "",
                                         "MobileNumber"     => "",
                                         "Email"            => "",                                         
                                         "IsReg"            => intval($isreg),
                                         "CoolingPeriod"    => "",
                                         "StatusCode"       => intval(CardStatus::MIGRATION_ERROR),
                                         "StatusMsg"        => 'Migration Error',
                                         )
                            );
                            
                            $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $cardnumber.':Failed', $cardnumber);
                            
                        }
                    }
                    else
                    {
                       $result = array("CardInfo"=>array(
                                     "MID"              => "",
                                     "Username"         => "",
                                     "CardNumber"       => $cardnumber,//$row['CardNumber'],
                                     "MemberUsername"   => "",
                                     "CardType"         => "",
                                     "MemberName"       => "",
                                     "RegistrationDate" => "",
                                     "Birthdate"        => "",
                                     "CurrentPoints"    => "",
                                     "LifetimePoints"   => "",
                                     "RedeemedPoints"   => "",
                                     "IsCompleteInfo"   => "",
                                     "MemberID"         => "",                                                                     
                                     "CasinoArray"      => "",
                                     "CardStatus"       => intval(CardStatus::ACTIVE_TEMPORARY),
                                     "DateVerified"     => "",
                                     "MobileNumber"     => "",
                                     "Email"            => "",                                         
                                     "IsReg"            => intval($isreg),
                                     "CoolingPeriod"    => "",
                                     "StatusCode"       => intval(CardStatus::MIGRATION_ERROR),
                                     "StatusMsg"        => 'Migration Error',
                                     )
                        ); 
                       
                        $_Log->logAPI(AuditFunctions::MIGRATE_TEMP, $cardnumber.':Error', $cardnumber);
                    }                                                         
                    
                }
                else
                {

                    /*
                     * Member has already Card Account using the issued temporary account code
                     * Member has already Casino Accounts created from the permanent database
                     */
                    $cardinfo = $_MemberCards->getMemberCardInfoByCard( $cardnumber );
                    //$result = $_MemberCards->getMemberPoints( $cardnumber );
                    
                    if(!App::HasError() && count( $cardinfo ) > 0)
                    {
                        $points = $cardinfo[0];
                        $MID = $points['MID'];
                                                
                        $memberInfo = $_MemberInfo->getMemberInfo( $MID );                        
                        $row = $memberInfo[0];
                        
                        $casinoAccounts = $_MemberServices->getCasinoAccounts( $MID );
                        
                    }
                    
                    $result = array("CardInfo"=>array(
                                         "MID"              => $MID,
                                         "Username"         => "",
                                         "CardNumber"       => $cardnumber,//$row['CardNumber'],
                                         "MemberUsername"   => $row['UserName'],
                                         "CardType"         => "",
                                         "MemberName"       => $row['FirstName'] . ' ' . $row['LastName'],
                                         "RegistrationDate" => $row['DateCreated'],
                                         "Birthdate"        => $row['Birthdate'],
                                         "CurrentPoints"    => $points['CurrentPoints'],
                                         "LifetimePoints"   => $points['LifetimePoints'],
                                         "RedeemedPoints"   => $points['RedeemedPoints'],
                                         "IsCompleteInfo"   => $row['IsCompleteInfo'],
                                         "MemberID"         => $MID,                                                                     
                                         "CasinoArray"      => $casinoAccounts,
                                         "CardStatus"       => intval(CardStatus::ACTIVE_TEMPORARY),
                                         "DateVerified"     => $row['DateVerified'],
                                         "MobileNumber"     => $row['MobileNumber'],
                                         "Email"            => $row['Email'],                                         
                                         "IsReg"            => intval($isreg),
                                         "CoolingPeriod"    => $_Helper->getParameterValue('COOLING_PERIOD'),
                                         "StatusCode"       => intval(CardStatus::ACTIVE_TEMPORARY),
                                         "StatusMsg"        => 'Active Temporary Account',
                                         )
                            );
                    
                }
                
                return $result;
                break;
                
            case CardStatus::INACTIVE_TEMPORARY: 
                
                $row = $_TempMembers->getTempMemberInfo( $cardnumber );   
                                
                $MID = $row[0]['MID'];
                
                $result = array("CardInfo"=>array(
                                         "MID"              => $MID,
                                         "Username"         => "",
                                         "CardNumber"       => "",
                                         "MemberUsername"   => $row[0]['UserName'],
                                         "CardType"         => "",
                                         "MemberName"       => $row[0]['FirstName'] . ' ' . $row[0]['LastName'],
                                         "RegistrationDate" => $row[0]['DateCreated'],
                                         "Birthdate"        => $row[0]['Birthdate'],
                                         "CurrentPoints"    => 0,
                                         "LifetimePoints"   => 0,
                                         "RedeemedPoints"   => 0,
                                         "IsCompleteInfo"   => $row[0]['IsCompleteInfo'],
                                         "MemberID"         => $MID,                                                                     
                                         "CasinoArray"      => "",
                                         "CardStatus"       => intval(CardStatus::INACTIVE_TEMPORARY),
                                         "DateVerified"     => $row[0]['DateVerified'],
                                         "MobileNumber"     => $row[0]['MobileNumber'],
                                         "Email"            => $row[0]['Email'],
                                         "IsReg"            => intval($isreg),
                                         "CoolingPeriod"    => $_Helper->getParameterValue('COOLING_PERIOD'),
                                         "StatusCode"       => intval(CardStatus::INACTIVE_TEMPORARY),
                                         "StatusMsg"        => 'Inactive Temporary Account',
                                         )
                            );
                
                return $result;
                break;
            
            case CardStatus::NEW_MIGRATED:
                
                $query = "SELECT c.CardNumber
                            , c.CardTypeID
                            , m.CurrentPoints
                            , m.LifetimePoints
                            , m.RedeemedPoints
                            , m.MID
                            , m.Status
                       FROM
                         cards c
                       INNER JOIN membercards m
                       ON c.CardID = m.CardID
                       WHERE
                         c.CardNumber = '$cardnumber'";
        
                $cardinfo = parent::RunQuery($query);        
                $memberInfo = $_MemberInfo->getMemberInfo($cardinfo[0]['MID']);

                $row = array_merge($cardinfo[0],$memberInfo[0]);
                
                //Get Member's casino services
                $casinoAccounts = $_MemberServices->getCasinoAccounts( $row['MID'] );
                //$casino = $_MemberInfo->getCasinoServices($row['MID']);
            
                $result = array("CardInfo"=>array(
                                         "MID"              => "",
                                         "Username"         => "",
                                         "CardNumber"       => $row['CardNumber'],
                                         "MemberUsername"   => $row['Email'],
                                         "CardType"         => $row['CardTypeID'],
                                         "MemberName"       => $row['MemberName'],
                                         "RegistrationDate" => $row['RegistrationDate'],
                                         "Birthdate"        => $row['Birthdate'],
                                         "CurrentPoints"    => $row['CurrentPoints'],
                                         "LifetimePoints"   => $row['LifetimePoints'],
                                         "RedeemedPoints"   => $row['RedeemedPoints'],
                                         "IsCompleteInfo"   => $row['IsCompleteInfo'],
                                         "MemberID"         => $row['MID'],                                                                     
                                         "CasinoArray"      => $casinoAccounts,
                                         "CardStatus"       => intval($row['Status']),
                                         "DateVerified"     => $row['DateVerified'],
                                         "MobileNumber"     => $row['MobileNumber'],
                                         "Email"            => $row['Email'],                    
                                         "IsReg"            => intval($isreg),
                                         "CoolingPeriod"    => "",
                                         "StatusCode"       => intval(CardStatus::NEW_MIGRATED),
                                         "StatusMsg"        => 'Migrated New Card',
                                         )
                            );
                
                return $result;
                break;
            
            case CardStatus::TEMPORARY_MIGRATED:
                
                $query = "SELECT c.CardNumber
                            , c.CardTypeID
                            , m.CurrentPoints
                            , m.LifetimePoints
                            , m.RedeemedPoints
                            , m.MID
                            , c.Status
                       FROM
                         cards c
                       INNER JOIN membercards m
                       ON c.CardID = m.CardID
                       WHERE
                         c.CardNumber = '$cardnumber'";
        
                $cardinfo = parent::RunQuery($query); 
                
                $row = $cardinfo[0];
                
                $result = array("CardInfo"=>array(
                                         "MID"              => $row['MID'],
                                         "Username"         => "",
                                         "CardNumber"       => "",
                                         "MemberUsername"   => "",
                                         "CardType"         => "",
                                         "MemberName"       => "",
                                         "RegistrationDate" => "",
                                         "Birthdate"        => "",
                                         "CurrentPoints"    => 0,
                                         "LifetimePoints"   => 0,
                                         "RedeemedPoints"   => 0,
                                         "IsCompleteInfo"   => "",
                                         "MemberID"         => $row['MID'],                                                                     
                                         "CasinoArray"      => "",
                                         "CardStatus"       => $row['Status'],
                                         "DateVerified"     => "",
                                         "MobileNumber"     => "",
                                         "Email"            => "",                                         
                                         "IsReg"            => intval($isreg),
                                         "CoolingPeriod"    => "",
                                         "StatusCode"       => intval(CardStatus::TEMPORARY_MIGRATED),
                                         "StatusMsg"        => 'Migrated Temporary Account',
                                         )
                            );
                
                
                return $result;
                break;
            
            case CardStatus::NOT_EXIST: 
                
                 $result = array("CardInfo"=>array(
                                        "MID"              => "",
                                        "Username"         => "",
                                        "CardNumber"       => "",
                                        "MemberUsername"   => "",
                                        "CardType"         => "",
                                        "MemberName"       => "",
                                        "RegistrationDate" => "",
                                        "Birthdate"        => "",
                                        "CurrentPoints"    => "",
                                        "LifetimePoints"   => "",
                                        "RedeemedPoints"   => "",
                                        "IsCompleteInfo"   => "",
                                        "MemberID"         => "",                                                                     
                                        "CasinoArray"      => "",
                                        "CardStatus"       => intval(CardStatus::NOT_EXIST),
                                        "DateVerified"     => "",
                                        "MobileNumber"     => "",
                                        "Email"            => "",                                        
                                        "IsReg"            => intval($isreg),
                                        "CoolingPeriod"    => "",
                                        "StatusCode"       => intval(CardStatus::NOT_EXIST),
                                        "StatusMsg"        => 'Card Not Found',
                                        )
                           );
                
                return $result;
                break;
            
            case CardStatus::BANNED:
                
                $result = array("CardInfo"=>array(
                                        "MID"              => "",
                                        "Username"         => "",
                                        "CardNumber"       => "",
                                        "MemberUsername"   => "",
                                        "CardType"         => "",
                                        "MemberName"       => "",
                                        "RegistrationDate" => "",
                                        "Birthdate"        => "",
                                        "CurrentPoints"    => "",
                                        "LifetimePoints"   => "",
                                        "RedeemedPoints"   => "",
                                        "IsCompleteInfo"   => "",
                                        "MemberID"         => "",                                                                     
                                        "CasinoArray"      => "",
                                        "CardStatus"       => intval(CardStatus::BANNED),
                                        "DateVerified"     => "",
                                        "MobileNumber"     => "",
                                        "Email"            => "",                                        
                                        "IsReg"            => intval($isreg),
                                        "CoolingPeriod"    => "",
                                        "StatusCode"       => intval(CardStatus::BANNED),
                                        "StatusMsg"        => 'Card Is Banned',
                                        )
                           );
                
                return $result;
                break;
                    
        }
        
        
    }
}

?>

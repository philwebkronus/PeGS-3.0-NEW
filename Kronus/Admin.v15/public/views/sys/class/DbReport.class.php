<?php
include "../../sys/class/DbHandler.class.php";
ini_set('display_errors',true);
ini_set('log_errors',true);

class DBReport extends DBHandler
{
    public function __construct( $connectionString )
    {
        parent::__construct($connectionString);
    }

    public function  __destruct()
    {
        parent::__destruct();
    }

//    public function GetBCF($start,$limit) {
//       $sql = "SELECT
//                        sb.SiteID,
//                        sb.Balance,
//                        s.SiteName
//               FROM sitebalance AS sb INNER JOIN sites AS s
//               ON s.SiteID = sb.SiteID LIMIT ".$start.",".$limit;
//       return $sql;
//    }

    public function GetBCF($start=null,$limit=null) {
       // select with limit
       $sql1 = "SELECT
                        sb.SiteID,
                        sb.Balance,
                        s.SiteName
               FROM sitebalance AS sb INNER JOIN sites AS s
               ON s.SiteID = sb.SiteID LIMIT ".$start.",".$limit;

       // count total of row
       $sql2 = "SELECT COUNT(sb.SiteID) as totalcount FROM sitebalance sb
          INNER JOIN sites s ON s.SiteID = sb.SiteID";


       // get all data
       $sql3 = "SELECT
                        sb.SiteID,
                        sb.Balance,
                        s.SiteName
               FROM sitebalance AS sb INNER JOIN sites AS s
               ON s.SiteID = sb.SiteID ORDER BY sb.SiteID";

       // get sum of balance
       $sql4 = "SELECT SUM(sb.balance) AS totalbalance
               FROM sitebalance AS sb INNER JOIN sites AS s
               ON s.SiteID = sb.SiteID";

       $sqls = array($sql1,$sql2,$sql3,$sql4);
       return $sqls;
    }

        /*Function that retrieves all BCF*/
    public function GetBCFPerSite()
    {
        $sql = "SELECT
                        sb.SiteID,
                        sb.Balance,
                        sb.MinBalance,
                        sb.MaxBalance,
                        sb.LastTransactionDate,
                        sb.TopUpType,
                        sb.PickUpTag,
                        s.SiteName
                FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID
                WHERE sb.SiteID = ?";
        return $sql;
    }

    public function GetMinMaxBalTotal()
    {
        $sql = "SELECT
                        sum(sb.MinBalance) as minBal,
                        sum(sb.Balance) as bal,
                        sum(sb.MaxBalance) as maxBal
                FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID
                WHERE sb.SiteID = ?";
        return $sql;
    }

    public function GetSiteTrans()
    {
        $query_string = "SELECT
                            td.TransactionDetailsID,
                            td.TransactionReferenceID,
                            td.TransactionSummaryID,
                            td.SiteID,
                            td.TerminalID,
                            td.TransactionType,
                            td.Amount,
                            td.ServiceID,
                            td.Status,
                            tm.TerminalName as termname
                         FROM transactiondetails td
                         INNER JOIN terminals tm
                         ON td.TerminalID = tm.TerminalID";
        return $query_string;
    }

    public function GetSiteTransPerDay()
    {
        $query_string = "SELECT
                            td.TransactionDetailsID,
                            td.TransactionReferenceID,
                            td.TransactionSummaryID,
                            td.SiteID,td.TerminalID,
                            td.TransactionType,
                            td.Amount,
                            td.ServiceID,
                            td.Status,
                            tm.TerminalName as termname,
                            sv.ServiceName as servname
                         FROM transactiondetails td
                         INNER JOIN terminals tm
                         ON td.TerminalID = tm.TerminalID
                         INNER JOIN ref_services sv
                         ON td.ServiceID = sv.ServiceID
                         WHERE DATE_FORMAT(td.DateCreated,'%Y-%m-%d')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')";
        return $query_string;
    }
    public function GetSiteRemit()
    {
        $query_string = "SELECT sr.RemittanceTypeID,
                                sr.BankID,
                                sr.Branch,
                                sr.Amount,
                                sr.BankTransactionID,
                                sr.BankTransactionDate,
                                sr.ChequeNumber,
                                sr.Particulars,
                                sr.Status,
                                sr.SiteID,
                                at.UserName as username,
                                st.SiteName as siteName,
                                DATE_FORMAT(sr.DateCreated,'%Y-%m-%d %h:%i:%s %p') DateCreated,
				bk.BankName as bankname,
				rt.RemittanceName as remittancename
                        FROM siteremittance  sr
                        LEFT JOIN sites st ON sr.SiteID = st.SiteID
                        LEFT JOIN accounts at ON sr.AID = at.AID
                        LEFT JOIN ref_remittancetype rt ON sr.RemittanceTypeID = rt.RemittanceTypeID
                        LEFT JOIN ref_banks bk ON sr.BankID = bk.BankID
			WHERE sr.SiteID = ?
                        ORDER BY DateCreated DESC";
        return $query_string;
    }

    public function GetSiteList()
    {
        $query_string = "SELECT SiteID,SiteName FROM sites ORDER BY SiteName ASC";
        return $query_string;
    }

    public function GetTerminalList()
    {
        $query_string = "SELECT TerminalID,TerminalName FROM terminals";
        return $query_string;
    }

    public function GetGrossHold()
    {
//        $query_string = "SELECT
//                            td.TransactionDetailsID,
//                            td.TransactionReferenceID,
//                            td.TransactionType,
//                            td.Amount,
//                            td.ServiceID,
//                            td.CreatedByAID,
//                            td.Status,
//                            st.SiteName as sitename,
//                            ts.TerminalName as terminalname,
//                            at.UserName as username,
//                            sv.ServiceName as servname
//                        FROM transactiondetails td
//                        JOIN sites st ON td.SiteID = st.SiteID
//                        JOIN terminals ts ON td.TerminalID = ts.TerminalID
//                        JOIN accounts at ON td.CreatedByAID = at.AID
//                        JOIN ref_services sv
//                        ON td.ServiceID = sv.ServiceID
//                        WHERE td.SiteID = ? AND td.TerminalID = ? AND DATE_FORMAT(td.DateCreated,'%Y-%m-%d')
//                        BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')";
        $query_string = "SELECT sum(td.Amount)
                         FROM transactiondetails td
                         JOIN terminals tr ON td.TerminalID = tr.TerminalID
                         WHERE td.TransactionType = ? AND td.TerminalID = ? AND td.SiteID = ?
                         AND DATE_FORMAT(td.DateCreated,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')";
        return $query_string;
    }

    public function GetAllGrossHold()
    {
        $query_string = "SELECT
                            td.TransactionDetailsID,
                            td.TransactionReferenceID,
                            td.TransactionType,
                            td.Amount,
                            td.ServiceID,
                            td.CreatedByAID,
                            td.Status,
                            st.SiteName as sitename,
                            ts.TerminalName as terminalname,
                            at.UserName as username,
                            sv.ServiceName as servname
                        FROM transactiondetails td
                        JOIN sites st ON td.SiteID = st.SiteID
                        JOIN terminals ts ON td.TerminalID = ts.TerminalID
                        INNER JOIN ref_services sv
                        ON td.ServiceID = sv.ServiceID
                        JOIN accounts at ON td.CreatedByAID = at.AID";
        return $query_string;
    }

    public function GetSiteDetails()
    {
        $query_string = "Select
                                st.SiteID,
                                st.SiteName,
                                st.SiteCode,
                                st.Status,
                                sd.SiteDescription,
                                sd.SiteAlias,
                                ri.IslandName as IslandName,
                                rr.RegionName as RegionName,
                                rp.ProvinceName as ProvinceName,
                                rc.CityName as CityName,
                                rb.BarangayName as BarangayName,
                                sd.SiteAddress
                         FROM sites st
                         INNER JOIN sitedetails sd
                         ON st.SiteID = sd.SiteID
                         INNER JOIN ref_islands ri
                         ON sd.IslandID = ri.IslandID
                         INNER JOIN ref_regions rr
                         ON sd.RegionID = rr.RegionID
                         INNER JOIN ref_provinces rp
                         ON sd.ProvinceID = rp.ProvinceID
                         INNER JOIN ref_cities rc
                         ON sd.CityID = rc.CityID
                         INNER JOIN ref_barangay rb
                         ON sd.BarangayID = rb.BarangayID
                         WHERE st.SiteID = ?";
        return $query_string;
    }

    public function GetSiteTerminals()
    {
        $query_string = "SELECT tr.TerminalName,tr.TerminalCode,tr.Status
                       FROM terminals tr
                       INNER JOIN sites st
                       ON tr.SiteID = st.SiteID
                       WHERE st.SiteID = ?";
        return $query_string;
    }

    public function GetSiteAccounts()
    {
        $query_string = "SELECT st.SiteID,ad.Address,ad.Email,ad.Landline,ad.MobileNumber,at.AccountTypeID,rat.Name as name,ad.Name as Username
                         FROM sites st
                         INNER JOIN accounts at
                         ON st.OwnerAID = at.AID
                         INNER JOIN accountdetails ad
                         ON at.AID = ad.AID
                         INNER JOIN ref_accounttypes rat
                         ON at.AccountTypeID = rat.AccountTypeID
                         WHERE st.SiteID = ?";
        return $query_string;
    }

    public function GetAllSiteDetails()
    {
        $query_string = "Select
                                st.SiteID,
                                st.SiteName,
                                st.SiteCode,
                                st.Status,
                                sd.SiteDescription,
                                sd.SiteAlias,
                                ri.IslandName as IslandName,
                                rr.RegionName as RegionName,
                                rp.ProvinceName as ProvinceName,
                                rc.CityName as CityName,
                                rb.BarangayName as BarangayName,
                                sd.SiteAddress
                         FROM sites st
                         INNER JOIN sitedetails sd
                         ON st.SiteID = sd.SiteID
                         INNER JOIN ref_islands ri
                         ON sd.IslandID = ri.IslandID
                         INNER JOIN ref_regions rr
                         ON sd.RegionID = rr.RegionID
                         INNER JOIN ref_provinces rp
                         ON sd.ProvinceID = rp.ProvinceID
                         INNER JOIN ref_cities rc
                         ON sd.CityID = rc.CityID
                         INNER JOIN ref_barangay rb
                         ON sd.BarangayID = rb.BarangayID";
        return $query_string;
    }

    public function GetAllSiteTerminals()
    {
        $query_string = "SELECT tr.TerminalName,tr.TerminalCode,tr.Status
                       FROM terminals tr
                       INNER JOIN sites st
                       ON tr.SiteID = st.SiteID";
        return $query_string;
    }

    public function GetAllSiteAccounts()
    {
        $query_string = "SELECT ad.Address,ad.Email,ad.Landline,ad.MobileNumber,at.AccountTypeID,rat.Name as name,ad.Name as Username
                         FROM sites st
                         INNER JOIN accounts at
                         ON st.OwnerAID = at.AID
                         INNER JOIN accountdetails ad
                         ON at.AID = ad.AID
                         INNER JOIN ref_accounttypes rat
                         ON at.AccountTypeID = rat.AccountTypeID";
        return $query_string;
    }

    public function GetTransSummary()
    {
        $query_string = "SELECT
                            ts.TransactionsSummaryID,
                            ts.SiteID,
                            ts.TerminalID,
                            ts.Deposit,
                            ts.Reload,
                            ts.Withdrawal,
                            DATE_FORMAT(ts.DateStarted,'%Y-%m-%d %h:%i:%s %p') DateStarted,
                            ts.DateEnded,
                            tr.TerminalName
                         FROM transactionsummary ts
                         JOIN terminals tr
                         ON ts.TerminalID = tr.TerminalID
                         JOIN sites st
                         ON ts.SiteID = st.SiteID
                         WHERE DATE_FORMAT(ts.DateStarted,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
			 ORDER BY tr.TerminalName,ts.DateStarted";
        return $query_string;
    }
    /*FUNCTIONS FOR RETRIEVING TOTALS*/
    public function GetStTotal()
    {
        $query_string = "SELECT sum(td.Amount) as total FROM transactiondetails td
                         INNER JOIN terminals tm
                         ON td.TerminalID = tm.TerminalID";
        return $query_string;
    }
    public function SumSiteTransPerDay()
    {
        $query_string = "SELECT sum(td.Amount) as total1
                         FROM transactiondetails td
                         LEFT JOIN terminals tm
                         ON td.TerminalID = tm.TerminalID
                         LEFT JOIN ref_services sv
                         ON td.ServiceID = sv.ServiceID
                         WHERE DATE(td.DateCreated)
                         BETWEEN DATE(?) AND DATE(?) AND td.TransactionType IN ('D','R') AND td.SiteID = ?";
        
        return $query_string;
    }
    
    public function SumSiteTransPerDay2()
    {
        $query_string = "SELECT sum(td.Amount) as total2
                         FROM transactiondetails td
                         LEFT JOIN terminals tm
                         ON td.TerminalID = tm.TerminalID
                         LEFT JOIN ref_services sv
                         ON td.ServiceID = sv.ServiceID
                         WHERE DATE(td.DateCreated)
                         BETWEEN DATE(?) AND DATE(?) AND td.TransactionType = 'W' AND td.SiteID = ?";
        
        return $query_string;
    }

    public function SumSiteRemit()
    {
        $query_string = "SELECt sum(sr.Amount) as total
			FROM siteremittance sr
			LEFT JOIN sites st ON sr.SiteID = st.SiteID
                        LEFT JOIN accounts at ON sr.AID = at.AID
                        LEFT JOIN ref_remittancetype rt ON sr.RemittanceTypeID = rt.RemittanceTypeID
                        LEFT JOIN ref_banks bk ON sr.BankID = bk.BankID
			WHERE sr.SiteID = ?";
        return $query_string;
    }

    public function SumAllGrossHold()
    {
        $query_string = "SELECt sum(Amount) as total FROM transactiondetails";
        return $query_string;
    }

    public function SumGrossHold()
    {
        $query_string = "SELECt sum(Amount) as total FROM transactiondetails td
                        JOIN sites st ON td.SiteID = st.SiteID
                        JOIN terminals ts ON td.TerminalID = ts.TerminalID
                        JOIN accounts at ON td.CreatedByAID = at.AID
                        WHERE td.SiteID = ? AND td.TerminalID = ? AND DATE_FORMAT(td.DateCreated,'%Y-%m-%d')
                        BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')";
        return $query_string;
    }

    /*Function for retrieving the sum of deposit,reload and wthdrawal of Transaction Summary*/
    public function SumDepositReloadWithdraw()
    {
        $query_string = "SELECT
                            sum(Deposit) as totalDeposit,
                            sum(Reload) as totalReload,
                            sum(Withdrawal) as totalWithdrawal
                         FROM transactionsummary
                         WHERE DATE_FORMAT(DateStarted,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')";
        return $query_string;
    }

                                                                    /*PAGING FUNCTIONS*/
    /*Function for Retrieving BCF Records for paging*/
//    public function BCFPaging($start,$limit,$field,$order)
//    {
//        $sql = "SELECT
//                        sb.SiteID,
//                        sb.Balance,
//                        sb.MinBalance,
//                        sb.MaxBalance,
//                        sb.LastTransactionDate,
//                        sb.TopUpType,
//                        sb.PickUpTag,
//                        s.SiteName
//                FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID
//                WHERE sb.SiteID = ?
//                ORDER BY ".$field." ".$order . "
//                LIMIT ".$start.",".$limit;
//        return $sql;
//    }

    public function BCFPaging()
    {
        $sql = "SELECT
                        sb.SiteID,
                        sb.Balance,
                        sb.MinBalance,
                        sb.MaxBalance,
                        sb.LastTransactionDate,
                        sb.TopUpType,
                        sb.PickUpTag,
                        s.SiteName
                FROM sitebalance sb INNER JOIN sites s ON sb.SiteID = s.SiteID
                WHERE sb.SiteID = ?";
        return $sql;
    }

    /*Function for Retrieving Site Transaction Records for paging*/
    public function SiteTransPaging($start,$limit,$field,$order)
    {
        $query_string = "SELECT
                            td.TransactionDetailsID,
                            td.TransactionReferenceID,
                            td.TransactionSummaryID,
                            td.SiteID,
                            td.TerminalID,
                            td.TransactionType,
                            td.Amount,
                            td.ServiceID,
                            td.Status,
                            tm.TerminalName as termname
                         FROM transactiondetails td
                         INNER JOIN terminals tm
                         ON td.TerminalID = tm.TerminalID
                         ORDER BY ".$field." ".$order ."
                         LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function for Retrieving Site Transaction Per Day Records for paging*/
    public function SiteTransPerDayPaging($start,$limit,$field,$order)
    {
        $query_string = "SELECT
                            td.TransactionDetailsID,
                            td.TransactionReferenceID,
                            td.TransactionSummaryID,
                            td.SiteID,td.TerminalID,
                            td.TransactionType,
                            td.Amount,
                            td.ServiceID,
                            td.Status,
                            tm.TerminalName as termname,
                            sv.ServiceName as servname
                         FROM transactiondetails td
                         INNER JOIN terminals tm
                         ON td.TerminalID = tm.TerminalID
                         INNER JOIN ref_services sv
                         ON td.ServiceID = sv.ServiceID
                         WHERE DATE_FORMAT(td.DateCreated,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
                         AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
                         ORDER BY ".$field." ".$order . "
                         LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function for retrieving Site Transaction records per day with no limit*/
    public function SiteTransPerDayPagingNoLimit()
    {
        $query_string = "SELECT
                            td.TransactionDetailsID,
                            td.TransactionReferenceID,
                            td.TransactionSummaryID,
                            td.SiteID,td.TerminalID,
                            td.TransactionType,
                            td.Amount,
                            td.ServiceID,
                            td.Status,
                            tm.TerminalName as termname,
                            sv.ServiceName as servname
                         FROM transactiondetails td
                         LEFT JOIN terminals tm
                         ON td.TerminalID = tm.TerminalID
                         LEFT JOIN ref_services sv
                         ON td.ServiceID = sv.ServiceID
                         WHERE DATE(td.DateCreated) BETWEEN DATE(?) AND DATE(?)
			 AND td.SiteID = ?";
        return $query_string;
    }

    /*Function for Retrieving Site Remmitance Records for paging*/
    public function SiteRemitPaging($start,$limit)
    {
        $query_string = "SELECT sr.SiteRemittanceID,
                                sr.RemittanceTypeID,
                                sr.BankID,
                                sr.Branch,
                                sr.Amount,
                                sr.BankTransactionID,
                                sr.BankTransactionDate,
                                sr.ChequeNumber,
                                sr.Particulars,
                                sr.Status,
                                sr.SiteID,
                                at.UserName as username,
                                st.SiteName as siteName,
                                DATE_FORMAT(sr.DateCreated,'%Y-%m-%d %h:%i:%s %p') DateCreated,
				bk.BankName as bankname,
				rt.RemittanceName as remittancename
                        FROM sites st
                        LEFT JOIN siteremittance sr ON sr.SiteID = st.SiteID
                        LEFT JOIN accounts at ON sr.AID = at.AID
                        LEFT JOIN ref_remittancetype rt ON sr.RemittanceTypeID = rt.RemittanceTypeID
                        LEFT JOIN ref_banks bk ON sr.BankID = bk.BankID
                        ORDER BY sr.DateCreated DESC
                        LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function to retrieve all Gross Hold for pagination*/
    public function AllGrossHoldPaging($start,$limit)
    {
        $query_string = "SELECT
                            td.TransactionDetailsID,
                            td.TransactionReferenceID,
                            td.TransactionType,
                            td.Amount,
                            td.ServiceID,
                            td.CreatedByAID,
                            td.Status,
                            st.SiteName as sitename,
                            ts.TerminalName as terminalname,
                            at.UserName as username,
                            sv.ServiceName as servicename
                        FROM transactiondetails td
                        JOIN sites st ON td.SiteID = st.SiteID
                        JOIN terminals ts ON td.TerminalID = ts.TerminalID
                        JOIN accounts at ON td.CreatedByAID = at.AID
                        JOIN ref_services sv ON td.ServiceID = sv.ServiceID
                        LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function to retrieve Gross Hold for pagination*/
    public function GrossHoldPaging($start,$limit)
    {
        $query_string = "SELECT
                            td.TransactionDetailsID,
                            td.TransactionReferenceID,
                            td.TransactionType,
                            td.Amount,
                            td.ServiceID,
                            td.CreatedByAID,
                            td.Status,
                            st.SiteName as sitename,
                            ts.TerminalName as terminalname,
                            at.UserName as username,
                            sv.ServiceName as servicename
                        FROM transactiondetails td
                        JOIN sites st ON td.SiteID = st.SiteID
                        JOIN terminals ts ON td.TerminalID = ts.TerminalID
                        JOIN accounts at ON td.CreatedByAID = at.AID
                        JOIN ref_services sv ON td.ServiceID = sv.ServiceID
                        WHERE td.SiteID = ? AND td.TerminalID = ? AND DATE_FORMAT(td.DateCreated,'%Y-%m-%d %H:%i:%s')
                        BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
                        LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function to retrieve Site Details for pagination*/
    public function GetSiteDetailsPaging($start,$limit)
    {
        $query_string = "Select
                                st.SiteID,
                                st.SiteName,
                                st.SiteCode,
                                st.Status,
                                sd.SiteDescription,
                                sd.SiteAlias,
                                ri.IslandName as IslandName,
                                rr.RegionName as RegionName,
                                rp.ProvinceName as ProvinceName,
                                rc.CityName as CityName,
                                rb.BarangayName as BarangayName,
                                sd.SiteAddress
                         FROM sites st
                         INNER JOIN sitedetails sd
                         ON st.SiteID = sd.SiteID
                         INNER JOIN ref_islands ri
                         ON sd.IslandID = ri.IslandID
                         INNER JOIN ref_regions rr
                         ON sd.RegionID = rr.RegionID
                         INNER JOIN ref_provinces rp
                         ON sd.ProvinceID = rp.ProvinceID
                         INNER JOIN ref_cities rc
                         ON sd.CityID = rc.CityID
                         INNER JOIN ref_barangay rb
                         ON sd.BarangayID = rb.BarangayID
                         WHERE st.SiteID = ?
                         LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function to retrieve Terminals under a site for pagination*/
    public function GetSiteTerminalsPaging($start,$limit)
    {
        $query_string = "SELECT tr.TerminalID,tr.TerminalName,tr.TerminalCode,tr.Status
                       FROM terminals tr
                       INNER JOIN sites st
                       ON tr.SiteID = st.SiteID
                       WHERE st.SiteID = ?
                       LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function to retrieve Accounts under a site for pagination*/
    public function GetSiteAccountsPaging($start,$limit)
    {
        $query_string = "SELECT st.SiteID,ad.Address,ad.Email,ad.Landline,ad.MobileNumber,at.AccountTypeID,rat.Name as name,ad.Name as Username
                         FROM sites st
                         INNER JOIN accounts at
                         ON st.OwnerAID = at.AID
                         INNER JOIN accountdetails ad
                         ON at.AID = ad.AID
                         INNER JOIN ref_accounttypes rat
                         ON at.AccountTypeID = rat.AccountTypeID
                         WHERE st.SiteID = ?
                         LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function to retrieve All Site Details for pagination*/
    public function GetAllSiteDetailsPaging($start,$limit)
    {
        $query_string = "Select
                                st.SiteID,
                                st.SiteName,
                                st.SiteCode,
                                st.Status,
                                sd.SiteDescription,
                                sd.SiteAlias,
                                ri.IslandName as IslandName,
                                rr.RegionName as RegionName,
                                rp.ProvinceName as ProvinceName,
                                rc.CityName as CityName,
                                rb.BarangayName as BarangayName,
                                sd.SiteAddress
                         FROM sites st
                         INNER JOIN sitedetails sd
                         ON st.SiteID = sd.SiteID
                         INNER JOIN ref_islands ri
                         ON sd.IslandID = ri.IslandID
                         INNER JOIN ref_regions rr
                         ON sd.RegionID = rr.RegionID
                         INNER JOIN ref_provinces rp
                         ON sd.ProvinceID = rp.ProvinceID
                         INNER JOIN ref_cities rc
                         ON sd.CityID = rc.CityID
                         INNER JOIN ref_barangay rb
                         ON sd.BarangayID = rb.BarangayID
                         LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function to retrieve All Terminals under a site for pagination*/
    public function GetAllSiteTerminalsPaging($start,$limit)
    {
        $query_string = "SELECT tr.TerminalID,tr.TerminalName,tr.TerminalCode,tr.Status
                       FROM terminals tr
                       INNER JOIN sites st
                       ON tr.SiteID = st.SiteID
                       LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function to retrieve all Accounts under a site for pagination*/
    public function GetAllSiteAccountsPaging($start,$limit)
    {
        $query_string = "SELECT st.SiteID,ad.Address,ad.Email,ad.Landline,ad.MobileNumber,at.AccountTypeID,rat.Name as name,ad.Name as Username
                         FROM sites st
                         INNER JOIN accounts at
                         ON st.OwnerAID = at.AID
                         INNER JOIN accountdetails ad
                         ON at.AID = ad.AID
                         INNER JOIN ref_accounttypes rat
                         ON at.AccountTypeID = rat.AccountTypeID
                         LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function for retreving Transaction Summary with Limit*/
    public function GetTransSummaryPaging($start,$limit)
    {
        $query_string = "SELECT
                            ts.TransactionsSummaryID,
                            ts.SiteID,
                            ts.TerminalID,
                            ts.Deposit,
                            ts.Reload,
                            ts.Withdrawal,
                            ts.DateStarted,
                            ts.DateEnded,
                            tr.TerminalName
                         FROM transactionsummary ts
                         JOIN terminals tr
                         ON ts.TerminalID = tr.TerminalID
                         JOIN sites st
                         ON ts.SiteID = st.SiteID
                         WHERE DATE_FORMAT(ts.DateStarted,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
                         ORDER BY tr.TerminalName,ts.DateStarted ASC
                         LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /******************************************************************TOP UP******************************************************************************/
    /*Function for retrieving records from transaction details*/
    public function GetTransDetails($start,$limit,$field,$order)
    {
        $query_string = "SELECT
                            td.TransactionDetailsID,
                            td.TransactionReferenceID,
                            td.SiteID,
                            td.TerminalID,
                            td.TransactionType,
                            td.Amount,
                            td.DateCreated,
                            td.ServiceID,
                            td.Status,
                            st.SiteName,
                            tm.TerminalName,
                            sv.ServiceName as servname
                         FROM transactiondetails td
                         JOIN sites st ON td.SiteID = st.SiteID
                         JOIN terminals tm ON td.TerminalID = tm.TerminalID
                         JOIN ref_services sv ON td.ServiceID = sv.ServiceID
                         WHERE DATE_FORMAT(td.DateCreated,'%Y-%m-%d %H:%i:%s') BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
                         ORDER BY ".$field." ".$order ."
                         LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function for retrieving total row count from transaction details*/
    public function GetCountTransDetails()
    {
        $query_string = "SELECT count(*) as total_rows
                         FROM transactiondetails
                         WHERE DATE_FORMAT(DateCreated,'%Y-%m-%d %H:%i:%s') BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')";
        return $query_string;
    }

    /*Function for retrieving records from site running balance*/
    public function GetSiteRunningBal($start,$limit)
    {
        $query_string = "SELECT
                            srb.TransactionDate,
                            srb.SiteID,
                            srb.TerminalID,
                            sum(srb.Deposit) as Deposit,
                            sum(srb.Reload) as Reload,
                            sum(srb.Withdrawal) as Withdrawal,
                            st.SiteName,
                            tm.TerminalName,
                            ifnull(sum(mr.ActualAmount),0) as ActualAmount
                         FROM siterunningbalance srb
                         LEFT JOIN sites st ON srb.SiteID = st.SiteID
                         LEFT JOIN terminals tm ON srb.TerminalID = tm.TerminalID
                         LEFT JOIN manualredemptions mr ON srb.TransactionDate = mr.TransactionDate
                         WHERE DATE_FORMAT(srb.TransactionDate,'%Y-%m-%d %H:%i:%s')
			 BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') 
                         AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') GROUP BY srb.SiteID
                         LIMIT ".$start.",".$limit."";
        return $query_string;
    }

    /*Function for retrieving total row count from site running balance*/
    public function GetCountSiteRunningBal()
    {
        $query_string = "SELECT count(distinct SiteID) as total_rows FROM siterunningbalance
                         WHERE DATE_FORMAT(TransactionDate,'%Y-%m-%d %H:%i:%s') BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')";
        return $query_string;
    }

    /*Function for getting total amount for Site Running Balance*/
    public function GetTotalSiteRunningBal()
    {
        $query_string = "SELECT
                            sum(srb.Deposit) as deposit_total,
                            sum(srb.Reload) as reload_total,
                            sum(srb.Withdrawal) as withdrawal_total,
                            ifnull(sum(mr.ActualAmount),0) as ActualAmount
                         FROM siterunningbalance srb
                         LEFT JOIN manualredemptions mr ON srb.TransactionDate = mr.TransactionDate
                         WHERE DATE_FORMAT(srb.TransactionDate,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')";
        return $query_string;
    }

    /*Function to get manual redemptions from manualredemptions*/
    public function GetManRedemp()
    {
        $query_string = "SELECT sum(ActualAmount) as ActualAmount
                         FROM manualredemptions
                         WHERE DATE_FORMAT(TransactionDate,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
                         AND SiteID = ?";
        return $query_string;
    }
    /*Function for getting total Deposit,Reload and Withdrawal from Transaction Details*/
    public function GetTotalTransDetails()
    {
        $query_string = "SELECT sum(Amount) as total FROM transactiondetails
                         WHERE DATE_FORMAT(DateCreated,'%Y-%m-%d %H:%i:%s') BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')";
        return $query_string;
    }

    /*Function for retrieving records from site remittance*/
    public function GetPostedDepSiteRemit($start,$limit)
    {
        $query_string = "SELECT sr.SiteRemittanceID,
                                sr.RemittanceTypeID,
                                sr.BankID,
                                sr.Branch,
                                sr.Amount,
                                sr.BankTransactionID,
                                sr.BankTransactionDate,
                                sr.ChequeNumber,
                                sr.Particulars,
                                sr.Status,
                                sr.SiteID,
                                at.UserName as username,
                                st.SiteName as siteName,
                                DATE_FORMAT(sr.DateCreated,'%Y-%m-%d %h:%i:%s %p') DateCreated,
				bk.BankName as bankname,
				rt.RemittanceName as remittancename,
				ats.Username as VerifiedBy,
				DATE_FORMAT(sr.StatusUpdateDate,'%Y-%m-%d %h:%i:%s %p') StatusUpdateDate
                        FROM siteremittance sr
                        LEFT JOIN sites st ON sr.SiteID = st.SiteID
                        LEFT JOIN accounts at ON sr.AID = at.AID
                        LEFT JOIN ref_remittancetype rt ON sr.RemittanceTypeID = rt.RemittanceTypeID
                        LEFT JOIN ref_banks bk ON sr.BankID = bk.BankID
			LEFT JOIN accounts ats ON sr.VerifiedBy = ats.AID
                        WHERE DATE_FORMAT(sr.DateCreated,'%Y-%m-%d %H:%i:%s') BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND sr.Status = 0
                        LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function for retrieving total row count from site remittance*/
    public function GetCountSiteRemit()
    {
        $query_string = "SELECT count(*) as total_rows FROM siteremittance
                         WHERE DATE_FORMAT(DateCreated,'%Y-%m-%d %H:%i:%s') BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND Status = 0";
        return $query_string;
    }

    /*Function for retrieving all records from site remittance */
    public function GetAllSiteRemit()
    {
        $query_string = "SELECT sr.SiteRemittanceID,
                                sr.RemittanceTypeID,
                                sr.BankID,
                                sr.Branch,
                                sr.Amount,
                                sr.BankTransactionID,
                                sr.BankTransactionDate,
                                sr.ChequeNumber,
                                sr.Particulars,
                                sr.Status,
                                sr.SiteID,
                                at.UserName as username,
                                st.SiteName as siteName,
                                DATE_FORMAT(sr.DateCreated,'%Y-%m-%d %h:%i:%s %p') DateCreated,
				bk.BankName as bankname,
				rt.RemittanceName as remittancename,
				ats.Username as VerifiedBy,
				DATE_FORMAT(sr.StatusUpdateDate,'%Y-%m-%d %h:%i:%s %p') StatusUpdateDate
                        FROM siteremittance sr
                        LEFT JOIN sites st ON sr.SiteID = st.SiteID
                        LEFT JOIN accounts at ON sr.AID = at.AID
			LEFT JOIN accounts ats ON sr.VerifiedBy = ats.AID
                        LEFT JOIN ref_remittancetype rt ON sr.RemittanceTypeID = rt.RemittanceTypeID
                        LEFT JOIN ref_banks bk ON sr.BankID = bk.BankID
                        WHERE DATE_FORMAT(sr.DateCreated,'%Y-%m-%d %H:%i:%s') BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
                        AND sr.Status = 0";
        return $query_string;
    }

    /*Function for retrieving total amount from site remittance*/
    public function GetTotalSiteRemit()
    {
        $query_string = "SELECT sum(Amount) as total FROM siteremittance
                         WHERE DATE_FORMAT(DateCreated,'%Y-%m-%d %H:%i:%s') BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND Status = 0";
        return $query_string;
    }

    /*Function for retrieving limited records from site remittance for reversal of deposits*/
    public function GetReversalDepSiteRemit($start,$limit)
    {
        $query_string = "SELECT sr.SiteRemittanceID,
                                sr.RemittanceTypeID,
                                sr.BankID,
                                sr.Branch,
                                sr.Amount,
                                sr.BankTransactionID,
                                sr.BankTransactionDate,
                                sr.ChequeNumber,
                                sr.Particulars,
                                sr.Status,
                                sr.SiteID,
                                at.UserName as username,
                                st.SiteName as siteName,
                                DATE_FORMAT(sr.DateCreated,'%Y-%m-%d %h:%i:%s %p') DateCreated,
				bk.BankName as bankname,
				rt.RemittanceName as remittancename
                        FROM siteremittance sr
                        INNER JOIN sites st ON sr.SiteID = st.SiteID
                        INNER JOIN accounts at ON sr.AID = at.AID
                        INNER JOIN ref_remittancetype rt ON sr.RemittanceTypeID = rt.RemittanceTypeID
                        INNER JOIN ref_banks bk ON sr.BankID = bk.BankID
                        WHERE DATE_FORMAT(sr.DateCreated,'%Y-%m-%d %H:%i:%s') BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND sr.Status = 1
                        LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function for retrieving row count from site remittance for reversal of deposits*/
    public function GetCountReversalDepSiteRemit()
    {
        $query_string = "SELECT count(*) as total_rows FROM siteremittance
                         WHERE DATE_FORMAT(DateCreated,'%Y-%m-%d %H:%i:%s') BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND Status = 1";
        return $query_string;
    }

    /*Function for retrieving all records from site remittance for reversal of deposits*/
    public function GetAllReversalDepSiteRemit()
    {
        $query_string = "SELECT sr.SiteRemittanceID,
                                sr.RemittanceTypeID,
                                sr.BankID,
                                sr.Branch,
                                sr.Amount,
                                sr.BankTransactionID,
                                sr.BankTransactionDate,
                                sr.ChequeNumber,
                                sr.Particulars,
                                sr.Status,
                                sr.SiteID,
                                at.UserName as username,
                                st.SiteName as siteName,
                                DATE_FORMAT(sr.DateCreated,'%Y-%m-%d %h:%i:%s %p') DateCreated,
				bk.BankName as bankname,
				rt.RemittanceName as remittancename
                        FROM sites st
                        INNER JOIN siteremittance sr ON sr.SiteID = st.SiteID
                        INNER JOIN accounts at ON sr.AID = at.AID
                        INNER JOIN ref_remittancetype rt ON sr.RemittanceTypeID = rt.RemittanceTypeID
                        INNER JOIN ref_banks bk ON sr.BankID = bk.BankID
                        WHERE DATE_FORMAT(sr.DateCreated,'%Y-%m-%d %H:%i:%s') BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND sr.Status = 1";
        return $query_string;
    }

    /*Function for retrieving total amount from site remittance for reversal of deposits*/
    public function GetTotalReversalDepSiteRemit()
    {
        $query_string = "SELECT sum(Amount) as total FROM siteremittance
                         WHERE DATE_FORMAT(DateCreated,'%Y-%m-%d %H:%i:%s') BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
                         AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND Status = 1";
        return $query_string;
    }

    /*Function for retrieving all records from topuptransactionhistory for manual,reversal of manual top up*/
    public function GetManualAutoTopUp($start,$limit)
    {
        $query_string = "SELECT
                            tuth.TopupHistoryID,
                            tuth.SiteID,
                            tuth.StartBalance,
                            tuth.EndBalance,
                            tuth.MinBalance,
                            tuth.MaxBalance,
                            tuth.TopupAmount,
                            tuth.TotalTopupAmount,
                            tuth.TopupType,
                            tuth.TopupTransactionType,
                            tuth.DateCreated,
                            tuth.Remarks,
                            tuth.TopupCount,
		            tuth.CreatedByAID,
		            act.UserName,
                            st.SiteName
                         FROM topuptransactionhistory tuth
                         JOIN sites st ON tuth.SiteID = st.SiteID
		         INNER JOIN accounts act ON tuth.CreatedByAID = act.AID
                         WHERE DATE(tuth.DateCreated) BETWEEN DATE(?)
                         AND DATE(?)
                         AND tuth.TopupTransactionType IN (?,?)
                         LIMIT ".$start.",".$limit;
        return $query_string;
    }

    //function for retrieving autotopup
    public function GetAutoTopUp($start,$limit)
    {
        $query_string = "SELECT
                            tuth.TopupHistoryID,
                            tuth.SiteID,
                            tuth.StartBalance,
                            tuth.EndBalance,
                            tuth.MinBalance,
                            tuth.MaxBalance,
                            tuth.TopupAmount,
                            tuth.TotalTopupAmount,
                            tuth.TopupType,
                            tuth.TopupTransactionType,
                            tuth.DateCreated,
                            tuth.Remarks,
                            tuth.TopupCount,
		            tuth.CreatedByAID,
                            st.SiteName
                         FROM topuptransactionhistory tuth
                         JOIN sites st ON tuth.SiteID = st.SiteID
                         WHERE DATE(tuth.DateCreated) BETWEEN DATE(?)
                         AND DATE(?)
                         AND tuth.TopupTransactionType IN (?,?)
                         LIMIT ".$start.",".$limit;
        return $query_string;
    }
    
    /*Function for retrieving total amounts from topuptransactionhistory for manual & auto top up,reversal of manual top up*/
    public function GetTotalManualAutoTopUp()
    {
        $query_string = "SELECT
                            sum(StartBalance) as StartBalance,
                            sum(EndBalance) as EndBalance,
                            sum(MinBalance) as MinBalance,
                            sum(MaxBalance) as MaxBalance,
                            sum(TopupAmount) as TopupAmount,
                            sum(TotalTopupAmount) as TotalTopupAmount
                         FROM topuptransactionhistory
                         WHERE DATE(DateCreated) BETWEEN DATE(?)
                         AND DATE(?)
                         AND TopupTransactionType IN (?,?)";
        return $query_string;
    }

    /*Function for retrieving total row count from topuptransactionhistory for manual & auto top up,reversal of manual top up*/
    public function GetCountManualAutoTopUp()
    {
        $query_string = "select count(*) as total_rows from topuptransactionhistory
                         WHERE DATE(DateCreated) BETWEEN DATE(?)
                         AND DATE(?)
                         AND TopupTransactionType IN (?,?)";
        return $query_string;
    }

    /*Function for retrieving total amounts from topuptransactionhistory for manual,reversal of manual top up*/
    public function GetAllManualAutoTopUp()
    {
        $query_string = "SELECT
                            tuth.TopupHistoryID,
                            tuth.SiteID,
                            tuth.StartBalance,
                            tuth.EndBalance,
                            tuth.MinBalance,
                            tuth.MaxBalance,
                            tuth.TopupAmount,
                            tuth.TotalTopupAmount,
                            tuth.TopupType,
                            tuth.TopupTransactionType,
                            tuth.DateCreated,
                            tuth.Remarks,
                            tuth.TopupCount,
		            tuth.CreatedByAID,
		            act.UserName,
                            st.SiteName
                         FROM topuptransactionhistory tuth
                         JOIN sites st ON tuth.SiteID = st.SiteID
		         INNER JOIN accounts act ON tuth.CreatedByAID = act.AID
                         WHERE DATE(tuth.DateCreated) BETWEEN DATE(?)
                         AND DATE(?)
                         AND tuth.TopupTransactionType IN (?,?)";
        return $query_string;
    }
    
    /*function for retrieving autotopup accounts */
    public function GetAllAutoTopUp()
    {
        $query_string = "SELECT
                            tuth.TopupHistoryID,
                            tuth.SiteID,
                            tuth.StartBalance,
                            tuth.EndBalance,
                            tuth.MinBalance,
                            tuth.MaxBalance,
                            tuth.TopupAmount,
                            tuth.TotalTopupAmount,
                            tuth.TopupType,
                            tuth.TopupTransactionType,
                            tuth.DateCreated,
                            tuth.Remarks,
                            tuth.TopupCount,
		            tuth.CreatedByAID,
                            st.SiteName
                         FROM topuptransactionhistory tuth
                         JOIN sites st ON tuth.SiteID = st.SiteID
                         WHERE DATE(tuth.DateCreated) BETWEEN DATE(?)
                         AND DATE(?)
                         AND tuth.TopupTransactionType IN (?,?)";
        return $query_string;
    }

    /*Function for all retrieving records from audittrail*/
    public function GetAllAuditTrail()
    {
	/*$query_string = "SELECT * FROM audittrail at WHERE DATE_FORMAT(at.TransDateTime,'%Y-%m-%d') = DATE_FORMAT(?,'%Y-%m-%d') LIMIT ";*/
        $query_string = "SELECT
                                at.ID,
                                at.TransDetails,
                                DATE_FORMAT(at.TransDateTime,'%Y-%m-%d %h:%i:%s %p') TransDateTime,
                                at.RemoteIP,
                                at.AID,
                                ac.AccountTypeID as accounttype,
                                ac.UserName as username
                         FROM audittrail at
                         JOIN accounts ac ON at.AID = ac.AID
                         WHERE DATE_FORMAT(at.TransDateTime,'%Y-%m-%d') = DATE_FORMAT(?,'%Y-%m-%d') AND ac.AccountTypeID = ?
			 ORDER BY at.TransDateTime DESC";
        return $query_string;
    }

    /*Function for retrieving records from audittrail for paging*/
    public function GetAuditTrail($start,$limit)
    {
        $query_string = "SELECT
                                at.ID,
                                at.TransDetails,
                                DATE_FORMAT(at.TransDateTime,'%Y-%m-%d %h:%i:%s %p') TransDateTime,
                                at.RemoteIP,
                                at.AID,
                                ac.AccountTypeID as accounttype,
                                ac.UserName as username
                         FROM audittrail at
                         JOIN accounts ac ON at.AID = ac.AID
                         WHERE DATE_FORMAT(at.TransDateTime,'%Y-%m-%d') = DATE_FORMAT(?,'%Y-%m-%d') AND ac.AccountTypeID = ?
			 ORDER BY at.TransDateTime DESC
                         LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function for retrieving row count from audittrail*/
    public function GetCountAuditTrail()
    {
        $query_string = "SELECT count(*) as total_row
                         FROM audittrail at
                         JOIN accounts ac ON at.AID = ac.AID
                         WHERE DATE_FORMAT(at.TransDateTime,'%Y-%m-%d') = DATE_FORMAT(?,'%Y-%m-%d') AND ac.AccountTypeID = ?";
        return $query_string;
    }

    /*Function for retrieving all records from audittrail for admin user*/
    public function GetAllAdminAuditTrail()
    {
        $query_string = "SELECT
                                at.ID,
                                at.TransDetails,
                                DATE_FORMAT(at.TransDateTime,'%Y-%m-%d %h:%i:%s %p') TransDateTime,
                                at.RemoteIP,
                                at.AID,
                                ac.AccountTypeID as accounttype,
                                ac.UserName as username
                         FROM audittrail at
                         JOIN accounts ac ON at.AID = ac.AID
                         WHERE DATE_FORMAT(at.TransDateTime,'%Y-%m-%d') = DATE_FORMAT(?,'%Y-%m-%d')
			 ORDER BY at.TransDateTime DESC";
        return $query_string;
    }

    /*Function for retrieving all records from audittrail for admin user-pagination*/
    public function GetAdminAuditTrail($start,$limit)
    {
        $query_string = "SELECT
                                at.ID,
                                at.TransDetails,
				DATE_FORMAT(at.TransDateTime,'%Y-%m-%d %h:%i:%s %p') TransDateTime,
                                at.RemoteIP,
                                at.AID,
                                ac.AccountTypeID as accounttype,
                                ac.UserName as username
                         FROM audittrail at
                         JOIN accounts ac ON at.AID = ac.AID
                         WHERE DATE_FORMAT(at.TransDateTime,'%Y-%m-%d') = DATE_FORMAT(?,'%Y-%m-%d')
			 ORDER BY at.TransDateTime DESC
                         LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function for retrieving row count from audittrail*/
    public function GetCountAllAuditTrail()
    {
        $query_string = "SELECT count(*) as total_row
                         FROM audittrail at
                         JOIN accounts ac ON at.AID = ac.AID
                         WHERE DATE_FORMAT(at.TransDateTime,'%Y-%m-%d') = DATE_FORMAT(?,'%Y-%m-%d')";
        return $query_string;
    }

    /*Function for retrieving records for PEGS Operators,PEGS Ops*/
    public function GetAllSiteIDForAuditTrail()
    {
        $query_string = "SELECT distinct sc.SiteID,ac.AccountTypeID
                         FROM siteaccounts sc
                         JOIN accounts ac ON sc.AID = ac.AID
                         WHERE sc.AID = ?";
        return $query_string;
    }

    /*Function to get all AID under SiteID*/
    public function GetAllAIDForAuditTrail()
    {
        $query_string = "select AID,SiteID from siteaccounts where SiteID = ?";
        return $query_string;
    }

    /*Function to get all audit trail for all AID queried*/
    public function GetAllSelectedAuditTrail()
    {
        $query_string = "SELECT count(*) as total_rows
                         FROM audittrail at
                         JOIN accounts ac ON at.AID = ac.AID
                         WHERE DATE_FORMAT(at.TransDateTime,'%Y-%m-%d') = DATE_FORMAT(?,'%Y-%m-%d') AND at.AID = ?";
        return $query_string;
    }

    /*FUnction to get limited audit trail records*/
    public function GetSelectedAuditTrail()
    {
        $query_string = "SELECT
                                at.ID,
                                at.TransDetails,
				DATE_FORMAT(at.TransDateTime,'%Y-%m-%d %h:%i:%s %p') TransDateTime,
                                at.RemoteIP,
                                at.AID,
                                ac.AccountTypeID as accounttype,
                                ac.UserName as username
                         FROM audittrail at
                         JOIN accounts ac ON at.AID = ac.AID
                         WHERE DATE_FORMAT(at.TransDateTime,'%Y-%m-%d') = DATE_FORMAT(?,'%Y-%m-%d') AND at.AID = ?
			 ORDER BY at.TransDateTime DESC";
        return $query_string;
    }

    /*Function to get limited audit trail for cashier*/
    public function GetCashierAuditTrail($start,$limit)
    {
        $query_string = "SELECT
                                at.ID,
                                at.TransDetails,
                                at.TransDateTime,
                                at.RemoteIP,
                                at.AID,
                                ac.AccountTypeID as accounttype,
                                ac.UserName as username
                         FROM audittrail at
                         JOIN accounts ac ON at.AID = ac.AID
                         WHERE DATE_FORMAT(at.TransDateTime,'%Y-%m-%d') = DATE_FORMAT(?,'%Y-%m-%d') AND at.AID = ?
                         LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function to get all audit trail for cashier*/
    public function GetAllCashierAuditTrail()
    {
        $query_string = "SELECT
                                at.ID,
                                at.TransDetails,
                                DATE_FORMAT(at.TransDateTime,'%Y-%m-%d %h:%i:%s %p') TransDateTime,
                                at.RemoteIP,
                                at.AID,
                                ac.AccountTypeID as accounttype,
                                ac.UserName as username
                         FROM audittrail at
                         JOIN accounts ac ON at.AID = ac.AID
                         WHERE DATE_FORMAT(at.TransDateTime,'%Y-%m-%d') = DATE_FORMAT(?,'%Y-%m-%d') AND at.AID = ?
			 ORDER BY at.TransDateTime DESC";
        return $query_string;
    }

    /*Function to get the row count of audit trail for cashier*/
    public function GetCountCashierAuditTrail()
    {
        $query_string = "SELECT count(*) as total_row
                         FROM audittrail at
                         JOIN accounts ac ON at.AID = ac.AID
                         WHERE DATE_FORMAT(at.TransDateTime,'%Y-%m-%d') = DATE_FORMAT(?,'%Y-%m-%d') AND at.AID = ?";
        return $query_string;
    }

    /*Function to get all unique site ids from transactiondetails*/
    public function GetAllUniqueSiteID()
    {
        $query_string = "SELECT distinct SiteID
                         FROM transactiondetails
                         WHERE DATE_FORMAT(DateCreated,'%Y-%m-%d %H:%i:%s') BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')";
        return $query_string;
    }

    /*Function to get all unique site ids from transactiondetails with limits*/
    public function GetUniqueSiteID($start,$limit,$field,$order)
    {
        $query_string = "SELECT distinct SiteID FROM transactiondetails
                         WHERE DATE_FORMAT(DateCreated,'%Y-%m-%d %H:%i:%s') BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
                         ORDER BY ".$field." ".$order ."
                         LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function to get the sum of Deposit,Reload and Withdrawal from transactiondetails*/
    public function GetDepositWithdrawalReload()
    {
        $query_string = "SELECT sum(ts.Amount) as transaction
                         FROM transactiondetails ts
                         JOIN sites st ON ts.SiteID = st.SiteID
                         WHERE DATE_FORMAT(ts.DateCreated,'%Y-%m-%d %H:%i:%s') BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
                         AND ts.TransactionType = ? AND ts.SiteID = ?";
        return $query_string;
    }

    /*Function to get the manual redemptions from manualredemptions*/
    public function GetManualRedemptions()
    {
        $query_string = "SELECT
                            ManualRedemptionsID,
                            SiteID,TerminalID,
                            sum(ReportedAmount) as ReportedAmount,
                            sum(ActualAmount) as ActualAmount,
                            TransactionDate,
                            RequestedByAID,
                            ProcessedByAID,
                            Remarks,
                            DateEffective,
                            Status,
                            TransactionID
                         FROM manualredemptions
                         WHERE DATE_FORMAT(TransactionDate,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
                         AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
                         AND SiteID = ?";
        return $query_string;
    }

    /*Function to get the sum of Deposit,Reload and Withdrawal from transactiondetails*/
    public function GetSiteName()
    {
        $query_string = "SELECT SiteName FROM sites WHERE SiteID = ?";
        return $query_string;
    }

    /*Function to retrieve all manual redemptions from manualredemptions*/
    public function GetAllManualRedmpt()
    {
        $query_string = "SELECT
                            mr.ManualRedemptionsID,
                            mr.ReportedAmount,
                            mr.ActualAmount,
                            mr.Remarks,
                            mr.Status,
                            DATE(mr.DateEffective) DateEffective,
                            st.SiteName,
                            tm.TerminalCode,
                            at.UserName
                         FROM  manualredemptions mr
                         JOIN sites st ON mr.SiteID = st.SiteID
                         JOIN terminals tm ON mr.TerminalID = tm.TerminalID
                         JOIN accounts at ON mr.ProcessedByAID = at.AID
                         WHERE DATE(mr.TransactionDate) BETWEEN DATE(?) AND DATE(?)";
        return $query_string;
    }

    /*Function to retrieve manual redemptions with limits from manualredemptions*/
    public function GetManualRedmpt($start,$limit)
    {
        $query_string = "SELECT
                            mr.ManualRedemptionsID,
                            mr.ReportedAmount,
                            mr.ActualAmount,
                            mr.Remarks,
                            mr.Status,
                            DATE(mr.DateEffective) DateEffective,
                            st.SiteName,
                            tm.TerminalCode,
                            at.UserName
                         FROM manualredemptions mr
                         JOIN sites st ON mr.SiteID = st.SiteID
                         JOIN terminals tm ON mr.TerminalID = tm.TerminalID
                         JOIN accounts at ON mr.ProcessedByAID = at.AID
                         WHERE DATE(mr.TransactionDate) BETWEEN DATE(?) AND DATE(?)
                         LIMIT " . $start . "," . $limit;
        return $query_string;
    }

    /*Function to retrieve row count from manualredemptions*/
    public function GetCountManualRedmpt()
    {
        $query_string = "SELECT count(*) as total_row
                         FROM manualredemptions
                         WHERE DATE(TransactionDate)
                         BETWEEN DATE(?) AND DATE(?)";
        return $query_string;
    }

    /*Function to retrieve total reported and actual amount from manualredemption*/
    public function GetTotalManualRedmpt()
    {
        $query_string = "SELECT
                            sum(ReportedAmount) as total_rptdamount,
                            sum(ActualAmount) as total_actlamount
                         FROM manualredemptions
                         WHERE DATE(TransactionDate)
                         BETWEEN DATE(?) AND DATE(?)";
        return $query_string;
    }

    /******************************************************************TOP UP PDF & EXCEL*****************************************************************/
    /*Function for retrieving all records from transaction details for PDF and Excel*/
    public function GetAllTransDetails()
    {
        $query_string = "SELECT
                            td.TransactionDetailsID,
                            td.TransactionReferenceID,
                            td.SiteID,
                            td.TerminalID,
                            td.TransactionType,
                            td.Amount,
                            td.DateCreated,
                            td.ServiceID,
                            td.Status,
                            st.SiteName,
                            tm.TerminalName,
                            sv.ServiceName as servname
                         FROM transactiondetails td
                         JOIN sites st ON td.SiteID = st.SiteID
                         JOIN terminals tm ON td.TerminalID = tm.TerminalID
                         JOIN ref_services sv ON td.ServiceID = sv.ServiceID
                         WHERE DATE_FORMAT(td.DateCreated,'%Y-%m-%d %H:%i:%s') BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')";
        return $query_string;
    }

    /*Function for retrieving all records from site transaction details for PDF and Excel*/
    public function GetAllSiteRunningBal()
    {
        $query_string = "SELECT
                            srb.TransactionDate,
                            srb.SiteID,
                            srb.TerminalID,
                            sum(srb.Deposit) as Deposit,
                            sum(srb.Reload) as Reload,
                            sum(srb.Withdrawal) as Withdrawal,
                            st.SiteName,
                            tm.TerminalName,
                            ifnull(sum(mr.ActualAmount),0) as ActualAmount
                         FROM siterunningbalance srb
                         LEFT JOIN sites st ON srb.SiteID = st.SiteID
                         LEFT JOIN terminals tm ON srb.TerminalID = tm.TerminalID
                         LEFT JOIN manualredemptions mr ON srb.TransactionDate = mr.TransactionDate
                         WHERE DATE_FORMAT(srb.TransactionDate,'%Y-%m-%d %H:%i:%s')
			 BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') 
                         AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') GROUP BY srb.SiteID";
        return $query_string;
    }

    /*Function to get all unique site id from transactiondetails for PEGS supervisor GrossHold*/
    public function GetAllSupUniqueTerminalID()
    {
        $query_string = "SELECT distinct TerminalID FROM transactiondetails WHERE SiteID = ?
                         AND DATE_FORMAT(DateCreated,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')";
        return $query_string;
    }

    /*Function to get unique site id with limits from transactiondetails for PEGS supervisor GrossHold*/
    public function GetSupUniqueTerminalID($start,$limit)
    {
        $query_string = "SELECT distinct TerminalID FROM transactiondetails
                         WHERE SiteID = ?
                         AND DATE_FORMAT(DateCreated,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
                         LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function to get total page summary Deposit,Withdrawal and Reload from transactiondetails for PEGS supervisor GrossHold*/
    public function GetSupDepositWithdrawalReload()
    {
        $query_string = "SELECT sum(Amount) as total FROM transactiondetails WHERE TerminalID = ? AND TransactionType = ? AND SiteID = ?
                         AND DATE_FORMAT(DateCreated,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')";
        return $query_string;
    }
    
   /*Function to get grand total summary Deposit,Withdrawal and Reload from transactiondetails for PEGS supervisor GrossHold*/
    public function GetTotDepositWithdrawReload()
    {
        $query_string = "SELECT sum(Amount) as total FROM transactiondetails WHERE TransactionType = ? AND SiteID = ?
                         AND DATE_FORMAT(DateCreated,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')";
        return $query_string;
    }

    /*Function to get site id for supervisor*/
    public function GetSiteID()
    {
        $query_string = "SELECT SiteID FROM siteaccounts WHERE AID = ?";
        return $query_string;
    }

    /*Function to get terminal name*/
    public function GetTerminalName()
    {
        $query_string = "SELECT TerminalName FROM terminals WHERE TerminalID = ?";
        return $query_string;
    }

    /*Function to get all sites of operators*/
    public function GetSiteIds($start,$limit)
    {
        $query_string = "SELECT distinct SiteID FROM siteaccounts
                         WHERE AID = ?
                         LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function to get all sites of operators*/
    public function GetAllSiteIds()
    {
        $query_string = "SELECT distinct SiteID FROM siteaccounts
                         WHERE AID = ?";
        return $query_string;
    }

    /*Function to get records from siteplayingbalance for playing balance report*/
    public function GetPlayingBalance($start,$limit)
    {
        $query_string = "SELECT
                            srb.SiteID,
                            srb.TerminalID,
                            st.SiteName,
                            tm.TerminalName,
                            srb.PrevBalance,
                            ifnull(srb.Reload,0) as Reload,
                            ifnull(srb.Deposit,0) as Deposit,
                            DATE_FORMAT(srb.TransactionDate,'%Y-%m-%d %h:%i:%s %p') TransactionDate
                         FROM siterunningbalance srb
                         JOIN sites st ON srb.SiteID = st.SiteID
                         JOIN terminals tm ON srb.TerminalID = tm.TerminalID
                         WHERE DATE_FORMAT(srb.TransactionDate,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
                         ORDER BY srb.TransactionDate ASC
                         LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function to get all records from siteplayingbalance for playing balance report*/
    public function GetAllPlayingBalance()
    {
        $query_string = "SELECT DISTINCT SiteID
                         FROM siterunningbalance
                         WHERE DATE_FORMAT(TransactionDate,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
			 ORDER BY SiteID";
//        $query_string = "SELECT
//                            srb.SiteID,
//                            srb.TerminalID,
//                            st.SiteName,
//                            tm.TerminalName,
//                            srb.PrevBalance,
//			    sum(srb.Reload) as reload,
//			    sum(srb.Deposit) as deposit,
//			    sum(srb.Withdrawal) as withdrawal,
//                            DATE_FORMAT(srb.TransactionDate,'%Y-%m-%d %h:%i:%s %p') TransactionDate
//                         FROM siterunningbalance srb
//                         left JOIN sites st ON srb.SiteID = st.SiteID
//                         left JOIN terminals tm ON srb.TerminalID = tm.TerminalID
//                         WHERE DATE_FORMAT(srb.TransactionDate,'%Y-%m-%d %H:%i:%s')
//                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
//                         AND srb.SiteID = ?";
//        $query_string = "SELECT
//                            srb.SiteID,
//                            srb.TerminalID,
//                            st.SiteName,
//                            tm.TerminalName,
//                            srb.PrevBalance,
//                            ifnull(srb.Reload,0) as Reload,
//                            ifnull(srb.Deposit,0) as Deposit,
//                            DATE_FORMAT(srb.TransactionDate,'%Y-%m-%d %h:%i:%s %p') TransactionDate
//                         FROM siterunningbalance srb
//                         JOIN sites st ON srb.SiteID = st.SiteID
//                         JOIN terminals tm ON srb.TerminalID = tm.TerminalID
//                         WHERE DATE_FORMAT(srb.TransactionDate,'%Y-%m-%d %H:%i:%s')
//                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
//                         ORDER BY srb.TransactionDate ASC
//                         LIMIT 0,10000";
        return $query_string;
    }

    /*Function to row count from siteplayingbalance for playing balance report*/
    public function GetPlayingBalanceLimit($start,$limit)
    {
        $query_string = "SELECT DISTINCT SiteID
                         FROM siterunningbalance
                         WHERE DATE_FORMAT(TransactionDate,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
			 ORDER BY SiteID
                         LIMIT " . $start . "," . $limit;
//        $query_string = "SELECT
//                            count(*) as total_row
//                         FROM siterunningbalance srb
//                         JOIN sites st ON srb.SiteID = st.SiteID
//                         JOIN terminals tm ON srb.TerminalID = tm.TerminalID
//                         WHERE DATE_FORMAT(srb.TransactionDate,'%Y-%m-%d %H:%i:%s')
//                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
//                         ORDER BY srb.TransactionDate ASC";
        return $query_string;
    }

    /*Function to get all site ids from siterunningbalance*/
    public function GetAllUniqueSite()
    {
        $query_string = "SELECT distinct srb.SiteID,st.SiteName
                         FROM siterunningbalance srb
                         JOIN sites st ON srb.SiteID = st.SiteID
                         WHERE DATE_FORMAT(TransactionDate,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')";
        return $query_string;
    }

    /*++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    /*Function to get records from siteplayingbalance for playing balance report */
    public function GetPlayingBalancePerSite()
    {
        $query_string = "SELECT
                            srb.SiteID,
                            srb.TerminalID,
                            st.SiteName,
                            tm.TerminalName,
                            srb.PrevBalance,
			    sum(srb.Reload) as reload,
			    sum(srb.Deposit) as deposit,
			    sum(srb.Withdrawal) as withdrawal,
                            DATE_FORMAT(srb.TransactionDate,'%Y-%m-%d %h:%i:%s %p') TransactionDate
                         FROM siterunningbalance srb
                         left JOIN sites st ON srb.SiteID = st.SiteID
                         left JOIN terminals tm ON srb.TerminalID = tm.TerminalID
                         WHERE DATE_FORMAT(srb.TransactionDate,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
                         AND srb.SiteID = ?";
//        $query_string = "SELECT
//                            srb.SiteID,
//                            srb.TerminalID,
//                            st.SiteName,
//                            tm.TerminalName,
//                            srb.PrevBalance,
//                            ifnull(srb.Reload,0) as Reload,
//                            ifnull(srb.Deposit,0) as Deposit,
//                            DATE_FORMAT(srb.TransactionDate,'%Y-%m-%d %h:%i:%s %p') TransactionDate
//                         FROM siterunningbalance srb
//                         JOIN sites st ON srb.SiteID = st.SiteID
//                         JOIN terminals tm ON srb.TerminalID = tm.TerminalID
//                         WHERE DATE_FORMAT(srb.TransactionDate,'%Y-%m-%d %H:%i:%s')
//                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
//                         AND srb.SiteID = ?
//                         ORDER BY srb.TransactionDate ASC
//                         LIMIT ".$start.",".$limit;
        return $query_string;
    }

    /*Function to get all records from siteplayingbalance for playing balance report with specific siteid*/
    public function GetLastTransPerSite()
    {
        $query_string = "SELECT
                            PrevBalance,
                            Deposit,
                            Reload
                         FROM siterunningbalance
                         WHERE DATE_FORMAT(TransactionDate,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
                         AND SiteID = ?
                         AND Withdrawal IS NULL ORDER BY TransactionDate DESC limit 1";
        return $query_string;
    }

    /*Function to row count from siteplayingbalance for playing balance report with specific siteid*/
    public function GetCountPlayingBalancePerSite()
    {
        $query_string = "SELECT
                            count(*) as total_row
                         FROM siterunningbalance srb
                         JOIN sites st ON srb.SiteID = st.SiteID
                         JOIN terminals tm ON srb.TerminalID = tm.TerminalID
                         WHERE DATE_FORMAT(srb.TransactionDate,'%Y-%m-%d %H:%i:%s')
                         BETWEEN DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s') AND DATE_FORMAT(?,'%Y-%m-%d %H:%i:%s')
                         AND srb.SiteID = ?
                         ORDER BY srb.TransactionDate ASC";
        return $query_string;
    }

 }
?>

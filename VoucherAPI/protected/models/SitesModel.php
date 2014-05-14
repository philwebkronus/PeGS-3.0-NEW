<?php

/**
 * Description of SitesModel
 *
 * @author jshernandez
 */
class SitesModel extends CFormModel {

    public $_connection;

    CONST ACCOUNTTYPE_ID_SITE_OPERATOR = 2;
    CONST ACCOUNTTYPE_ID_SITE_SUPERVISOR = 3;
    CONST ACCOUNTTYPE_ID_SITE_CASHIER = 4;

    public function __construct() {
        $this->_connection = Yii::app()->db2;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/21/13
     * @param int $siteid
     * @return object
     */
    public function getSiteName($siteid) {
        $sql = "SELECT SiteID, SiteName FROM sites
                WHERE SiteID = :siteid";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":siteid", $siteid);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * @author Edson Perez
     * @date 10/25/13
     * @purpose Get ALL active sites
     * @return array sites
     */
    public function fetchAllActiveSites() {
        if (($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_SUPERVISOR) ||
                ($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_CASHIER)) {
            $sql = "SELECT s.SiteID, s.SiteName FROM sites s
                INNER JOIN siteaccounts sa ON sa.SiteID = s.SiteID
                INNER JOIN accounts a ON a.AID = sa.AID
                WHERE s.Status = 1 AND s.SiteID <> 1 AND a.AccountTypeID = :account_type_id AND a.AID = :aid
                ORDER BY s.SiteName ASC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":account_type_id", $_SESSION['AccountType']);
            $command->bindValue(":aid", $_SESSION['AID']);
            $result = $command->queryAll();

            $site = array();
            foreach ($result as $row) {
                $site[$row['SiteID']] = $row['SiteName'];
            }
        } else if ($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_OPERATOR) {
            $sql = "SELECT s.SiteID, s.SiteName FROM sites s
                INNER JOIN siteaccounts sa ON sa.SiteID = s.SiteID
                INNER JOIN accounts a ON a.AID = sa.AID
                WHERE s.Status = 1 AND s.SiteID <> 1 AND a.AccountTypeID = :account_type_id AND a.AID = :aid
                ORDER BY s.SiteName ASC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":account_type_id", $_SESSION['AccountType']);
            $command->bindValue(":aid", $_SESSION['AID']);
            $result = $command->queryAll();

            $site = array('All' => 'All');
            foreach ($result as $row) {
                $site[$row['SiteID']] = $row['SiteName'];
            }
        } else {
            $sql = "SELECT SiteID, SiteName FROM sites WHERE Status = 1 AND SiteID <> 1
                ORDER BY SiteName ASC";
            $command = $this->_connection->createCommand($sql);

            $result = $command->queryAll();

            $site = array('All' => 'All');
            foreach ($result as $row) {
                $site[$row['SiteID']] = $row['SiteName'];
            }
        }

        return $site;
    }

    /**
     * @description Get Site ID by logged-in cashier.
     * @author Noel Antonio
     * @dateCreated 11-14-2013
     */
    public function getSiteId($aid) {
        $query = "SELECT SiteID FROM siteaccounts WHERE Status = 1
                    AND AID = :aid";
        $command = $this->_connection->createCommand($query);
        $command->bindValue(":aid", $aid);
        $result = $command->queryRow();

        return $result;
    }
    /**
     * Get Site Codes
     * @return array PeGS Site Codes
     * @author Mark Kenneth Esguerra
     * @date March 26, 2014
     */
    public function getSiteCodes($siteIDs = null)
    {
        if (is_null($siteIDs))
        {
            $query = "SELECT SiteID, SiteCode FROM sites WHERE SiteID <> 1 ORDER BY SiteCode ASC";
            $command = $this->_connection->createCommand($query);
            $result = $command->queryAll();
        }
        else
        {
            $query = "SELECT SiteID, SiteCode FROM sites WHERE SiteID IN ("."'".implode("','",$siteIDs)."'".") AND 
                      SiteID <> 1 ORDER BY SiteCode ASC";
            $command = $this->_connection->createCommand($query);
            $result = $command->queryAll();
        }
        
        
        return $result;
    }
    /**
     * Select SiteIDs of different accounts
     * @param int $aid AID of the user
     * @return array All SiteID
     * @author Mark Kenneth Esguerra [04-01-14]
     */
    public function getSiteIDs($aid)
    {
        $query = "SELECT SiteID FROM siteaccounts WHERE Status = 1
                    AND AID = :aid";
        $command = $this->_connection->createCommand($query);
        $command->bindValue(":aid", $aid);
        $result = $command->queryAll();
        
        return $result;
    }
}

?>

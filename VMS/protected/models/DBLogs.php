<?php

/*
 * @Date Dec 11, 2012
 * @Author owliber
 */

class DBLogs extends CFormModel
{
    public function getAuditTrailByDate($dateFrom,$dateto)
    {
        //Add 1 day to dateto to return all records for the current date
        //$newdate = strtotime ( '+1 day' , strtotime ( $dateto ) ) ;
        //$dateto = date ( 'Y-m-d' , $newdate );
        
        $query = "SELECT a.ID,a.TransDateTime,a.AID,c.SiteCode,a.TransDetails,a.RemoteIP
                    FROM
                      audittrail a
                      INNER JOIN siteaccounts b ON a.AID = b.AID
                      INNER JOIN sites c ON b.SiteID = c.SiteID
                    WHERE
                      a.TransDateTime >=:datefrom AND a.TransDateTime <=:dateto
                    ORDER BY 1 DESC";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValues(array(':datefrom'=>$dateFrom,':dateto'=>$dateto));
        $result = $sql->queryAll();
        return $result;
    }
    
    public function getAPILogsByDate($dateFrom,$dateTo)
    {
        $query = "SELECT  a.LogID
                        , a.APIMethod
                        , CASE a.Source
                            WHEN 1 THEN 'KAPI'
                            WHEN 2 THEN 'EGM'
                            WHEN 3 THEN 'CASHIER'
                          END `Source`
                        , c.TerminalCode
                        , a.TransDateTime
                        , a.TransDetails
                        , a.ReferenceID
                        , a.TrackingID
                        , a.RemoteIP
                        , CASE a.Status
                            WHEN 1 THEN 'Successful'
                            WHEN 2 THEN 'Failed'
                        END `Status`
                   FROM
                     apilogs a
                     LEFT JOIN egmrequestlogs b
                        ON a.TrackingID = b.TrackingID 
                     INNER JOIN terminals c 
                        ON (b.TerminalID = c.TerminalID)
                   WHERE
                     a.TransDateTime >=:datefrom AND TransDateTime <:dateto
                        ORDER BY 1 DESC";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValues(array(':datefrom'=>$dateFrom,':dateto'=>$dateTo));
        $result = $sql->queryAll();
        return $result;
    }
}
?>

<?php
/**
 * Site Gross Hold Cut Off
 * @date May 27, 2014
 * @author Mark Kenneth Esguerra
 */
class SiteGrossHoldCutOff extends CFormModel
{
    public $connection;
    
    public function __construct()
    {
        $this->connection = Yii::app()->db2;
    }
    /**
     * Get Active Tickets by TransDate
     * @param type $transdate date
     * @return array Result
     */
    public function getActiveTicketsByDate($transdate, $dateTo, $sitecode)
    {
        //if all site
        if ($sitecode == 'All')
        {
            $sql = "SELECT ReportDate, RunningActiveTickets, RunningActiveTicketCount 
                    FROM sitegrossholdcutoff 
                    WHERE ReportDate = :transdate";
            $command = $this->connection->createCommand($sql);
            $command->bindValue(":transdate", $transdate);
            $result = $command->queryAll();
        }
        else if (is_array($sitecode))
        {
            $sitecode = implode(",", $sitecode);
            
            $sql = "SELECT ReportDate, RunningActiveTickets, RunningActiveTicketCount 
                FROM sitegrossholdcutoff 
                WHERE ReportDate = :transdate AND SiteID IN ($sitecode)";
            $command = $this->connection->createCommand($sql);
            $command->bindValue(":transdate", $transdate);
            $result = $command->queryAll();
        }
        else
        {
            $sql = "SELECT ReportDate, RunningActiveTickets, RunningActiveTicketCount 
                FROM sitegrossholdcutoff 
                WHERE ReportDate = :transdate AND SiteID = :siteID";
            $command = $this->connection->createCommand($sql);
            $command->bindValue(":transdate", $transdate);
            $command->bindValue(":siteID", $sitecode);
            $result = $command->queryAll();
        }
        
        return $result;
    }
}
?>

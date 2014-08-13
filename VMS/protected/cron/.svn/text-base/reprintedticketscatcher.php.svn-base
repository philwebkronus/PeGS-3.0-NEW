<?php

/**
 * @author JunJun S. Hernandez
 * @dateCreated 03-14-2014
 * This function is used to get the reprinted tickets
 */
include 'config.php';
include 'cron.class.php';

try {
    $connVMS = new cron($connString[0]);
    $connVMS->open();
    $lastLogDate = $connVMS->getLastRunDate();
    $result = '';
    
    if (($lastLogDate != 0) || ($lastLogDate != '')) {     
        $connSpyder = new cron($connString[1]);
        $connSpyder->open();
        $reprinted_tickets_resultset = $connSpyder->getPrintedTicketsFromSpyder($lastLogDate);
        $connSpyder->close();
        if (($reprinted_tickets_resultset != 0) || ($reprinted_tickets_resultset != '')) {
            foreach ($reprinted_tickets_resultset as $value) {
                $dateReprinted = $value['LogDate'];
                $tickets = $value['Remarks'];
                $fieldSeparator = '\|';
                list($reprintedBy, $ticketCode) = split($fieldSeparator, $tickets);
                $result = $connVMS->updateTicketReprint($ticketCode, $dateReprinted, $reprintedBy);
            }
        }
    }
    // close the connection
    $connVMS->close();
    print $result . "<br/>";
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
}
?>
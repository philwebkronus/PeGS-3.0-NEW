<?php

/**
 * This file is used to create additional functions and logical methods 
 * necessary for each modules.
 * @author Noel Antonio
 * @dateCreated 03-20-2014
 * @modified JunJun S. Hernandez
 * @dateModified 03-21-2014
 */

class Helpers
{
    /**
     * This function is used to remove the 18-digit padding on tickets 
     * and get the original ticket code which is existing in the database.
     * 
     * Minimum of upto 7-digit tickets.
     * 
     * This logic can be used to any ticket-in (TI) procedures inside the
     * module controller.
     * 
     * @param string $padded_ticket the 18-digit ticket
     * @return string ticket code original ticket code in the database.
     * @return array ticket recordset.
     */
    public function remove_ticket_pad($padded_ticket)
    {
        $model = new TicketsModel();
        $ticket_details = array();
        
        $ticket_code = substr($padded_ticket, 0, 9);
        $code_length = strlen($ticket_code);
        do 
        {
            $ticket_code = substr($ticket_code, 0, $code_length);
            $ticket_details = $model->checkTicketCode($ticket_code);
            if (!empty($ticket_details)) {
                $code_length = 0;
            } else {
                $code_length--;
            }
        } while ($code_length >= 7);
        
        return array('ticket_code'=>$ticket_code, 'data'=>$ticket_details);
    }
    
    /**
     * 
     */
    public function insert_ticket_pad($voucherCode, $terminalName = '')
    {
        if(empty($terminalName) || $terminalName == "") {
            $ticketModel = new TicketModel();
            $terminal = $ticketModel->getTerminalCodeByTicketCode($voucherCode);
            $terminalName = str_replace(Yii::app()->params['sitePrefix'], "", $terminal);
        } else {
            $terminalName = $terminalName;
        }
        $terminalDelimiter = Helpers::create_terminal_delimiter($terminalName);
        $constDelimiter = Yii::app()->params['constant_delimiter'];
        $finalConstDelimiter = Yii::app()->params['ticket_increment'] . $constDelimiter . $terminalDelimiter;
        $dbTicketLength = strlen($voucherCode);
        $var = "XXXXXXX" . $finalConstDelimiter;
        $delimiter = substr($var, $dbTicketLength, strlen($var));
        $ticketCode = $voucherCode . $delimiter;

        return $ticketCode;
    }
    
    public function create_terminal_delimiter($terminalName) {
        $terminalNameLength = strlen($terminalName);
        $terminalNameDelimiter = "";
        if ($terminalNameLength > 3) {
            $terminalNameDelimiter = substr($terminalName, 0, 3);
        } else {
            $terminalNameDelimiter = $terminalName;
        }
        
        return $terminalNameDelimiter;
    }
    
    public function generate_ticket() {
        $model = new TicketsModel();
        $ticket = $model->generateTicketCode();
        $ctr = 0;
        do {
            $ticketCode = $ticket;
            $ctr++;
        } while((!isset($ticketCode) || $ticketCode == "") && ($ctr==2));
        
        return $ticketCode;
    }
}

?>

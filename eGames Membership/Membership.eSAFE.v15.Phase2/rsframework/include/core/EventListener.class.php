<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2012-01-30
 * Company: Philweb
 * ***************** */

class EventListener extends BaseObject
{

    var $FormsProcessor;
    var $EventValue;
    var $EventAction;
    var $EventSender;
    var $EventSenderControl;
    var $EventValueControl;
    var $EventActionControl;
    var $HasEvent = false;

    function EventListener($fproc)
    {
        App::LoadControl("Hidden");

        $this->FormsProcessor = $fproc;

        $hdnEventListenerValue = new Hidden("_EventValue", "_EventValue", "_EventValue");
        //$hdnEventListenerValue->Text = "";
        $fproc->AddControl($hdnEventListenerValue);

        $hdnEventListenerAction = new Hidden("_EventAction", "_EventAction", "_EventAction");
        //$hdnEventListenerAction->Text = "";
        $fproc->AddControl($hdnEventListenerAction);

        $hdnEventListenerSender = new Hidden("_EventSender", "_EventSender", "_EventSender");
        //$hdnEventListenerSender->Text = "";
        $fproc->AddControl($hdnEventListenerSender);

        $this->EventValueControl = $hdnEventListenerValue;
        $this->EventActionControl = $hdnEventListenerAction;
        $this->EventSenderControl = $hdnEventListenerSender;
    }

    function ScanEvents()
    {
        $fproc = $this->FormsProcessor;
        if ($fproc->IsPostBack)
        {
            $hdnEventListenerID = $this->EventValueControl;
            $hdnEventListenerAction = $this->EventActionControl;
            $hdnEventListenerSender = $this->EventSenderControl;
            $this->EventValue = $hdnEventListenerID->SubmittedValue;
            $this->EventAction = $hdnEventListenerAction->SubmittedValue;
            $this->EventSender = $hdnEventListenerSender->SubmittedValue;

            if ($this->EventValue != '' && $this->EventAction != '')
            {
                $this->HasEvent = true;
            }
        }
    }

    function RenderScript()
    {
        $scriptstr = "";

        $scriptstr .= "<script language='javascript' type='text/javascript'>
    function _EventListener(_value, _sender, _action)
    {
        ev = document.getElementById('_EventValue');
        ev.value = _value;
        ac = document.getElementById('_EventAction');
        ac.value = _action;
        sn = document.getElementById('_EventSender');
        sn.value = _sender;

        $('#_EventSender').closest(\"form\").submit();

    }
    
function _ClearEvent()
{
    $('#_EventValue').val('');
    $('#_EventAction').val('');
    $('#_EventSender').val('');
}
</script>";
        $scriptstr .= $this->EventValueControl;
        $scriptstr .= $this->EventActionControl;
        $scriptstr .= $this->EventSenderControl;
        echo $scriptstr;
    }

    function RenderEventHandler($eventvalue, $eventsender, $eventaction, $formname = '')
    {
        $eventstring = "javascript:_EventListener($eventvalue, $eventsender, '$eventaction', '$formname');";
        echo $eventstring;
    }

    function ReturnEventHandler($eventvalue, $eventsender, $eventaction, $formname = '')
    {
        $eventstring = "javascript:_EventListener('$eventvalue', '$eventsender', '$eventaction');";
        return $eventstring;
    }

    function ClearEvent()
    {
        $hdnEventListenerID = $this->EventValueControl;
        $hdnEventListenerAction = $this->EventActionControl;
        $hdnEventListenerSender = $this->EventSenderControl;
        $hdnEventListenerID->Text = '';
        $hdnEventListenerAction->Text = '';
        $hdnEventListenerSender->Text = '';
        $this->EventValue = '';
        $this->EventAction = '';
        $this->EventSender = '';
    }

}

?>

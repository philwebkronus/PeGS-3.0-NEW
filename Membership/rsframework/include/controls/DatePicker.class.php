<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2012-08-14
 * Company: Philweb
 * ***************** */
App::LoadControl("TextBox");

class DatePicker extends TextBox
{

    public $Period = DatePickerPeriod::Day;
    public $Range = false;
    public $MinDate = "";
    public $MaxDate = "";
    public $CalendarImage = "images/calendar.gif";
    public $SelectedDate;
    public $Text;
    public $YearsToDisplay = "";
    public $AdditionalOptions = "";
    private $CurrentDate;
    public $isRenderJQueryScript = false;

    /**
     * The constructor
     */
    function DatePicker($Name = null, $ID = null, $Caption = null)
    {
        $this->Name = $Name;
        $this->ID = $ID;
        $this->Caption = $Caption;
        $this->Size = 10;
        $this->ReadOnly = true;
        $this->Init("DatePicker");
    }

    /**
     *
     * @return string Returns the control in string format eg. <input type="text" id="controlid"/>
     */
    function Render()
    {
        $this->Text = $this->SelectedDate;
        $text = $this->CurrentDate;
        $retval = parent::Render();
        if ($this->isRenderJQueryScript) 
        {
            $retval = $this->renderJQueryScript() . $retval;
        }
        return $retval;
    }

    function getJQueryScript()
    {
        $mindatestr = "";
        $maxdatestr = "";
        if ($this->MinDate != "")
        {
            $ds = new DateSelector($this->MinDate);
            $year = $ds->Year;
            $month = $ds->Month - 1;
            $day = $ds->Day;
            $mindatestr = "minDate: new Date($year, $month, $day),";
        }
        if ($this->MinDate != "")
        {
            $ds = new DateSelector($this->MaxDate);
            $year = $ds->Year;
            $month = $ds->Month - 1;
            $day = $ds->Day;
            $maxdatestr = "maxDate: new Date($year, $month, $day),";
        }

        if ($this->Period == DatePickerPeriod::Day)
        {
            $jqueryscript = "
            <script language=\"javascript\" type=\"text/javascript\">
            $(function() {
        $( \"#" . $this->ID . "\" ).datepicker({
            showOn: \"button\",
            buttonImage: \"" . $this->CalendarImage . "\",
            buttonImageOnly: true,
            dateFormat: \"yy-mm-dd\",
            changeMonth: true,
            changeYear: true,
            closeText: \"Close\",
            showButtonPanel: true,
            $mindatestr
            $maxdatestr
            $this->AdditionalOptions
            buttonText: \"\"
            
        });
    });
 </script>           
";
            //            onSelect: function( selectedDate ) {
            //                $( \"#txtToDate\" ).datepicker( \"option\", \"minDate\", selectedDate );
            //                dt = new Date(selectedDate);
            //                dt.setDate(dt.getDate() +6);
            //                strdate = $.datepicker.formatDate('yy-mm-dd', dt);
            //                $( \"#txtToDate\" ).datepicker( \"option\", \"maxDate\", strdate );
            //            }
        }

        if ($this->Period == DatePickerPeriod::Month)
        {
            $jqueryscript = "
                <script language=\"javascript\" type=\"text/javascript\">
                $(function() {
         $( \"#" . $this->ID . "\" ).datepicker({
            showOn: \"button\",
            buttonImage: \"" . $this->CalendarImage . "\",
            buttonImageOnly: true,
            dateFormat: \"yy-mm\",
            changeMonth: true,
            changeYear: true,
            closeText: \"Close\",
            showButtonPanel: true,
            showMonthAfterYear: false,
            $mindatestr
            $maxdatestr
            $this->AdditionalOptions
            buttonText: \"\",
            
            onClose: function(dateText, inst) { 
                var month = $(\"#ui-datepicker-div .ui-datepicker-month :selected\").val();
                var year = $(\"#ui-datepicker-div .ui-datepicker-year :selected\").val();
                $(this).datepicker('setDate', new Date(year, month, 1));
            },
        
            beforeShow : function(input, inst) {
            
                if ((datestr = $(this).val()).length > 0) {
                    year = datestr.substring(0, 4);
                    month = parseInt(datestr.substring(5, 8)) - 1;
                    $(this).datepicker('option', 'defaultDate', parsedate($( \"#" . $this->ID . "\" ).val()));
                    //$(this).datepicker('setDate', new Date(year, month, 1));
                }}
            
        });
        
    });
                 </script>
                 <style>
    .ui-datepicker-calendar {
        display: none;
    }
</style>
                 ";
        }
        return $jqueryscript;
    }

    function renderJQueryScript()
    {
        $mindatestr = "";
        $maxdatestr = "";
        $yearstodisplaystr = "";
        if ($this->MinDate != "")
        {
            $ds = new DateSelector($this->MinDate);
            $year = $ds->Year;
            $month = $ds->Month - 1;
            $day = $ds->Day;
            $mindatestr = "minDate: new Date($year, $month, $day),";
        }
        if ($this->MinDate != "")
        {
            $ds = new DateSelector($this->MaxDate);
            $year = $ds->Year;
            $month = $ds->Month - 1;
            $day = $ds->Day;
            $maxdatestr = "maxDate: new Date($year, $month, $day),";
        }
        if ($this->YearsToDisplay != "")
        {
            $yearstodisplaystr = "yearRange: 'c" . $this->YearsToDisplay . ":c',";
        }

        if ($this->Period == DatePickerPeriod::Day)
        {
            $jqueryscript = "
            <script language=\"javascript\" type=\"text/javascript\">
            $(function() {
        $( \"#" . $this->ID . "\" ).datepicker({
            showOn: \"button\",
            buttonImage: \"" . $this->CalendarImage . "\",
            buttonImageOnly: true,
            dateFormat: \"yy-mm-dd\",
            changeMonth: true,
            changeYear: true,
            closeText: \"Close\",
            showButtonPanel: true,
            $mindatestr
            $maxdatestr
            $yearstodisplaystr
            $this->AdditionalOptions
            buttonText: \"\"
            
        });
    });
 </script>           
";
            //            onSelect: function( selectedDate ) {
            //                $( \"#txtToDate\" ).datepicker( \"option\", \"minDate\", selectedDate );
            //                dt = new Date(selectedDate);
            //                dt.setDate(dt.getDate() +6);
            //                strdate = $.datepicker.formatDate('yy-mm-dd', dt);
            //                $( \"#txtToDate\" ).datepicker( \"option\", \"maxDate\", strdate );
            //            }
        }

        if ($this->Period == DatePickerPeriod::Month)
        {
            $jqueryscript = "
                <script language=\"javascript\" type=\"text/javascript\">
                $(function() {
         $( \"#" . $this->ID . "\" ).datepicker({
            showOn: \"button\",
            buttonImage: \"" . $this->CalendarImage . "\",
            buttonImageOnly: true,
            dateFormat: \"yy-mm\",
            changeMonth: true,
            changeYear: true,
            closeText: \"Close\",
            showButtonPanel: true,
            showMonthAfterYear: false,
            $mindatestr
            $maxdatestr
            $this->AdditionalOptions
            buttonText: \"\",
            
            onClose: function(dateText, inst) { 
                var month = $(\"#ui-datepicker-div .ui-datepicker-month :selected\").val();
                var year = $(\"#ui-datepicker-div .ui-datepicker-year :selected\").val();
                $(this).datepicker('setDate', new Date(year, month, 1));
            },
        
            beforeShow : function(input, inst) {
            
                if ((datestr = $(this).val()).length > 0) {
                    year = datestr.substring(0, 4);
                    month = parseInt(datestr.substring(5, 8)) - 1;
                    $(this).datepicker('option', 'defaultDate', parsedate($( \"#" . $this->ID . "\" ).val()));
                    //$(this).datepicker('setDate', new Date(year, month, 1));
                }}
            
        });
        
    });
                 </script>
                 <style>
    .ui-datepicker-calendar {
        display: none;
    }
</style>
                 ";
        }
        echo $jqueryscript;
    }

}

class DatePickerPeriod
{
    const Day = "day";
    const Week = "week";
    const Month = "month";
    const Year = "year";
}

?>

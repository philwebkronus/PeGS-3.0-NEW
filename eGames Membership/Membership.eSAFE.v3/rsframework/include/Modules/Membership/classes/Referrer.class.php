<?php

/*
 * @author = owliber
 * @date = 2013-04-16
 */
?>
<?php

class Referrer extends BaseEntity
{
    function Referrer()
    {
        $this->TableName = "ref_referrer";
        $this->ConnString = "membership";
        $this->Identity = "HearAboutID";
    }
}
?>

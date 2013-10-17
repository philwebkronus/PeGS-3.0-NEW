<?php
/**
 * Report Model
 * Mark Kenneth Esguerra
 * Sep-3-13
 */
class ReportForm extends CFormModel
{
    public $date_from;
    public $date_to;
    public $category;
    public $filter_by;
    public $particular;
    public $player_segment;
    
    public static function model($classname = __CLASS__)
    {
        return parent::model($classname);
    }
    
    public function rules()
    {
        return array(
            array('category, filter_by, particular, player_segment, date_from, date_to','required'),
            array('particular','safe')
        );
    }
    public function attributeLabels()
    {
        return array(
            'category' => 'Category',
            'filter_by' => 'Filter By',
            'particular' => 'Choose Particular',
            'player_segment' => 'Player Segment',
            'date_from' => 'From',
            'date_to' => 'To'
        );
    }
}
?>

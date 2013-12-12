<?php

/**
 * @Description: Form Model for Manage Mystery Rewards Module.
 * @Author: aqdepliyan
 * @DateCreated: 2013-11-06
 */
Class ManageMysteryRewardsForm extends CFormModel 
{
    
    public $viewrewardsby;
    
    //Variable for Mystery Reward Item Editing Form
    public $editrewarditem;
    public $editmysteryrewarditem;
    public $editcategory;
    public $editpoints;
    public $editeligibility;
    public $editstatus;
    public $editabout;
    public $editmysteryabout;
    public $editterms;
    public $editmysteryterms;
    public $editsubtext;
    public $editmysterysubtext;
    
    //Variable for Mystery Reward Item Adding Form
    public $addrewarditem;
    public $addmysteryrewarditem;
    public $addcategory;
    public $addpoints;
    public $addeligibility;
    public $addstatus;
    public $addmysteryabout;
    public $addabout;
    public $addmysteryterms;
    public $addterms;
    public $addmysterysubtext;
    public $addsubtext;
    public $additemcount;

    public function rules() {
        return array(
            //all fields are required
            array(' editpartner, editrewarditem, editcategory, editpoints, editsubtext, 
                        editeligibility, editstatus, editavailableitemcount, editabout, 
                        editterms, additems, currentinventory, inventoryupdate, addpartner, addrewarditem,
                        addmysteryrewarditem, editmysteryrewarditem, addmysterysubtext, editmysterysubtext,
                        editmysteryterms, editmysteryabout, addmysteryterms, addmysteryabout
                        addcategory, addpoints, addeligibility, addstatus, additemcount, addavailableitemcount,
                        addrewardid, addpromoname, addpromocode, addsubtext', 'required'),
            
            array('additems', 'length', 'max' => 5,'min' => 1),
            array('editrewarditem', 'length', 'max' => 50),
            array('editmysteryrewarditem', 'length', 'max' => 50),
            array('addrewarditem', 'length', 'max' => 50),
            array('addmysteryrewarditem', 'length', 'max' => 50),
            array('editsubtext', 'length', 'max' => 200),
            array('editmysterysubtext', 'length', 'max' => 200),
            array('addsubtext', 'length', 'max' => 200),
            array('addmysterysubtext', 'length', 'max' => 200),
            array('editpoints', 'length', 'max' => 6, 'min' => 2),
            array('addpoints', 'length', 'max' => 6, 'min' => 2),
            array('additemcount', 'length', 'max' => 5, 'min' => 1),
            array('editabout', 'length', 'max' => 500, 'min' => 10),
            array('editmysteryabout', 'length', 'max' => 500, 'min' => 10),
            array('editterms', 'length', 'max' => 500, 'min' => 10),
            array('editmysteryterms', 'length', 'max' => 500, 'min' => 10),
            array('addabout', 'length', 'max' => 500, 'min' => 10),
            array('addmysteryabout', 'length', 'max' => 500, 'min' => 10),
            array('addterms', 'length', 'max' => 500, 'min' => 10),
            array('addmysteryterms', 'length', 'max' => 500, 'min' => 10),
        );
    }
    
}

?>

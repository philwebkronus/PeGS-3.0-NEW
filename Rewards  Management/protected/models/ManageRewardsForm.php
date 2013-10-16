<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

Class ManageRewardsForm extends CFormModel 
{
    
    public $viewrewardsby;
    public $rewardtype;
    
    //Variable for Reward Item Editing Form
    public $editpartner;
    public $editrewarditem;
    public $editcategory;
    public $editpoints;
    public $editeligibility;
    public $editstatus;
    public $editavailableitemcount;
    public $editabout;
    public $editterms;
    public $editsubtext;
    
    //Variable for Reward Item Adding Form
    public $addpartner;
    public $addrewarditem;
    public $addcategory;
    public $addpoints;
    public $addeligibility;
    public $addstatus;
    public $addavailableitemcount;
    public $addabout;
    public $addterms;
    public $addsubtext;
    public $addrewardid;
    public $additemcount;
    public $addpromoname;
    public $addpromocode;


    //Variable for Replenishing Form
    public $additems;
    public $currentinventory;
    public $inventoryupdate;

    public function rules() {
        return array(
            //all fields are required
            array(' editpartner, editrewarditem, editcategory, editpoints, editsubtext, 
                        editeligibility, editstatus, editavailableitemcount, editabout, 
                        editterms, additems, currentinventory, inventoryupdate, addpartner, addrewarditem,
                        addcategory, addpoints, addeligibility, addstatus, additemcount, addavailableitemcount,
                        addrewardid, addpromoname, addpromocode, addsubtext', 'required'),
            
            array('additems', 'length', 'max' => 5),
            array('editsubtext', 'length', 'max' => 200),
            array('editpoints', 'length', 'max' => 6, 'min' => 2),
            array('addpoints', 'length', 'max' => 6, 'min' => 2),
            array('editabout', 'length', 'max' => 500, 'min' => 10),
            array('editterms', 'length', 'max' => 500, 'min' => 10),
            array('addabout', 'length', 'max' => 500, 'min' => 10),
            array('addterms', 'length', 'max' => 500, 'min' => 10),
        );
    }
    
}

?>

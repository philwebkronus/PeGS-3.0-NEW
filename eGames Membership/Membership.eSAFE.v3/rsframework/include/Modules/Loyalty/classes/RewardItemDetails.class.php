<?php

/* * ***************** 
 * Author: Gerardo Jagolino
 * Date Created: 2013-07-10
 * Company: Philweb
 * ***************** */

class RewardItemDetails extends BaseEntity
{

    public function RewardItemDetails()
    {
        $this->ConnString = "loyalty";
        $this->TableName = "rewarditemdetails";
        $this->DatabaseType = DatabaseTypes::PDO;
        $this->Identity = "RewardItemID";
    }
    
     /**
    * @author Gerardo V. Jagolino Jr.
    * @param $headerone, $detailsoneA, $headertwo = '', $headerthree = '',
            $detailsoneB = '', $detailsoneC = '',$detailstwoA = '',
            $detailstwoB = '',$detailstwoC = '',$detailsthreeA = '' ,$detailsthreeB = '' 
            ,$detailsthreeC = '', $rewarditemid = ''
    * @return int
    * update headers and details for reward item details table
    */ 
    public function updateHeaders($headerone, $detailsoneA, $headertwo = '', $headerthree = '',
            $detailsoneB = '', $detailsoneC = '',$detailstwoA = '',
            $detailstwoB = '',$detailstwoC = '',$detailsthreeA = '' ,$detailsthreeB = '' 
            ,$detailsthreeC = '', $rewarditemid = ''){
        
        $query = "UPDATE rewarditemdetails SET HeaderOne = '$headerone', HeaderTwo = '$headertwo',
            HeaderThree = '$headerthree', DetailsOneA = '$detailsoneA', DetailsOneB = '$detailsoneB', 
                DetailsOneC = '$detailsoneC', DetailsTwoA = '$detailstwoA', DetailsTwoB = '$detailstwoB', 
                    DetailsTwoC = '$detailstwoC', DetailsThreeA = '$detailsthreeA', DetailsThreeB = '$detailsthreeB',
                        DetailsThreeC = '$detailsthreeC' WHERE RewardItemID = $rewarditemid";

        return parent::ExecuteQuery($query);
    }

    

}

?>

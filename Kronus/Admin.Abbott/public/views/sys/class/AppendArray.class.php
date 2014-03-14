<?php

/**
 * This class combines array by requiring comparative keys from two binding arrays.
 * It also extends its functionality by allowing the developer to access the internal procedure
 * of the class thru its API that exposes two global variables refArray and bindArray.
 * 
 * @author Marx - Lenin C. Topico
 * @date-created March 02, 2012
 * @last-modified March 05, 2012
 */

/**
 * Instantiation of this class requires no parameter. To utilize it array appending
 * method you need to provide 7 parameters.
 * 
 * Suggested usage of this class as follows:
 * 
 * <code>
 * 
 * #Sample of using the Class with Default Functionality
 * 
 * $byVal = $joinArrays->joinArrayByKeys($operator, $sites, "AID", "OwnerAID", $mergedName, $columnsToMerge, NULL);
 * 
 * 
 * #Sample of using the Class with Extended Functionality
 * 
 * $byVal = $joinArrays->joinArrayByKeys($primaryArray, $arrayToAppend, $primaryArrayKey, $arrayToAppendKey, NULL, NULL, function(){
 * 
 *          $GLOBALS["refArray"]["nameOfDesiredKey"][] = $someValue;
 * 
 * });
 * 
 * </code>
 * 
 * @package AppendArrays
 * @subpackage Controller
 */
class AppendArrays {
    
    /**
     *This is the class property to which the newly created arrays are stored.
     * 
     * @var Array 
     */
    private $boundedArray;
    
    /**
     * This method is used to bind arrays together. By default, it combines two arrays
     * by using comparative keys. To combine multiple arrays use the extended functionality
     * of this method.
     * 
     * The method flows as follows:
     * 
     * 1. The method starts by walking thru the parent array $arrayToBindWith and converts it to global variable $GLOBALS["refArray"]. This is the external loop.
     * 2. An inner loop is then initiated by walking thru the binding array $arrayToGetFrom that is converted to global variable $GLOBALS["bindArray"].
     * 3. Within the inner loop, comparative keys: $arrayToBindWithField & $arrayToGetFromField will be used to compare values.
     * 4. If values of the parent and binding array equaled, the method will check what functionality to use.
     * 5. If $mergedColumnNames & $columnNamesToBind were set to NULL and $extendedUserAPI is defined. The method will use the extended API.
     * 6. Else, it will use the default functionality.
     * 7. Default functionality works as follows:
     *      7.1. $mergerCounter is set to 0.
     *      7.2. An inner loop is initiated by walking thru the $mergedColumnNames array.
     *      7.3. The $GLOBALS["refArray"] defined in the external loop is appended with new element with keys $merger. The value of the new element is $GLOBALS["bindArray"][$columnNamesToBind[$mergerCounter]].
     *      7.4. The $mergerCounter is then incremented.
     *      7.5. After the loop, $mergerCounter is set to 0 again.
     * 8. After the creation of new array, it is then stored in class property $boundedArray.
     * 9. And then, returns the new array.
     * 
     * @param Array $arrayToBindWith    #This is the reference or parent array.
     * @param Array $arrayToGetFrom     #This is the array to get values from or the child array.
     * @param String $arrayToBindWithField  #This the parent comparative key that also exists in the child array, not exactly of same name as the child array, but of same value.
     * @param String $arrayToGetFromField   #This is the child comparative key that also exists in the parent array, not exactly of same name as the parent array, but of same value.
     * @param Array $mergedColumnNames  #Array of keys to use as indices of values to bind to the parent array. It must have same count as $columnNamesToBind.
     * @param Array $columnNamesToBind  #Array of exact keys that exists in the child array to be used in retrieving values to bind to the parent array.
     * @param Function $extendedUserAPI #This API extends the functionality of the class exposing two API arrays $GLOBALS["refArray"] & $GLOBALS["bindArray"]
     * @return Array 
     */
    public function joinArrayByKeys($arrayToBindWith, $arrayToGetFrom, $arrayToBindWithField, $arrayToGetFromField, $mergedColumnNames, $columnNamesToBind, $extendedUserAPI) {
    
       if(sizeof($arrayToBindWith) >= 1 && sizeof($arrayToBindWithField) >= 1 ) {
           foreach($arrayToBindWith as $GLOBALS["refArray"]){

              foreach($arrayToGetFrom as $GLOBALS["bindArray"]){        

                  if($GLOBALS["refArray"][$arrayToBindWithField] == $GLOBALS["bindArray"][$arrayToGetFromField]){

                      if($extendedUserAPI == NULL){

                          $mergerCounter = 0;

                          foreach($mergedColumnNames as $merger){

                                $GLOBALS["refArray"][$merger] = $GLOBALS["bindArray"][$columnNamesToBind[$mergerCounter]];

                                $mergerCounter++;

                          }

                          $mergerCounter = 0;

                      }
                      else{

                          $extendedUserAPI();

                      }

                      $this->boundedArray[] = $GLOBALS["refArray"];

                   }

              }

           }

          return $this->boundedArray;
       }
       
    }
    
}

?>
<?php

/**
 * Json generator for jqgrid. 
 * <code>
 * Array format for data.
 * array(array(array('row1col1'),array('row1col2')),array(array('row2col1'),array('row2col2')))
 * <code>
 * @version 1.0
 * @package application.components
 * @author Bryan Salazar
 */
class jqGrid
{
    /**
     *
     * @param type $total total number of records
     * @param type $data data 
     * @param string $idField field name as id for jqgrid if empty it will get the first field
     * @param bool $showID if true id will be included
     */
    public static function generateJSON($total,$data,$idField='',$showID=false) {
        $page = $_GET['page'];
        $limit = $_GET['rows'];
        
        if($total > 0 ) {
            $total_pages = ceil($total / $limit);
        } else {
            $total_pages = 0;
        }
        
        if ($page > $total_pages) {
            $page = $total_pages;
            $start = $limit * $page - $limit;
        }
        
        if ($page == 0) {
            $start = 0;
        } else {
            $start = $limit * $page - $limit;
        }

        $limit = (int) $limit;
        
        $rows = array();
        
        $cntr = 0;
        
        foreach($data as $cell) {

            $cells = array();
            if($idField == '') {
                foreach($cell as $fielName => $value) {
                    if($idField == '')
                        $idField = $fielName;
                }
            }
            
            $newcell = array();
            foreach($cell as $fielName => $value) {
                if(!$showID) {
                    if($fielName != $idField)
                        $newcell[]=array($value);
                } else {
                    $newcell[]=array($value);
                }
                
            }
            $cells=$newcell;
            
            $rows[$cntr]['id']=$cell[$idField];
            $rows[$cntr]['cell']=$cells;
            $cntr++;
        }
        
        $var = array(
            'page'=>$page,
            'total'=>$total_pages,
            'records'=>$total,
            'rows'=>$rows
        );
        
        return json_encode($var);
    }
    
    /**
     * @param type $records total number of records without limit
     * @param array $data data
     * @return string json for jqgrid 
     */
    public static function getJSON($records,$data)
    {
        $page = $_GET['page'];
        $limit = $_GET['rows'];
        
        $count = count($data);
        
        if($count > 0 ) {
            $total_pages = ceil($count / $limit);
        } else {
            $total_pages = 0;
        }
        
        if ($page > $total_pages) {
            $page = $total_pages;
            $start = $limit * $page - $limit;
        }
        
        if ($page == 0) {
            $start = 0;
        } else {
            $start = $limit * $page - $limit;
        }

        $limit = (int) $limit;
        
        $data = array_slice($data, $start, $limit);        
        
        $rows = array();
        
        $cntr = 0;
        foreach($data as $cell) {
            $rows[$cntr]['id']=$cell[0][0];
            $rows[$cntr]['cell']=$cell;
            $cntr++;
        }
        
        $var = array(
            'page'=>$page,
            'total'=>$total_pages,
            'records'=>$records,
            'rows'=>$rows
        );
        
        return CJSON::encode($var);
    }
    
    /**
     * Generate sample data for jqgrid
     * @param int $numberOfColumn number of columns
     * @param int $numberOfRows number of rows
     * @return array 
     */
    public static function generateSampleData($numberOfColumn,$numberOfRows)
    {
        $data = array();
        for($i=0;$i<$numberOfRows;$i++) {
            $cells=array(array('row'.($i+1).'cell1'));
            for($j=1;$j<$numberOfColumn;$j++) {
                array_push($cells, array('row'.($i+1).'cell'.($j+1)));
            }
            $data[]=$cells;
        }
        return $data;
    }
}

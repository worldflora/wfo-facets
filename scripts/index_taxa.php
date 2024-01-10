<?php

/*

    This will run through all the names in the database
    and pop them in the index

    got to wfo-0000264907

*/

require_once("../config.php");
require_once('../include/SolrIndex.php');
require_once('../include/WfoFacets.php');
require_once('../include/WikiItem.php');

// we are careful not index things twice in the same session
// which will occur for each synonym of a taxon if we don't do this.
$start = time();

// get a list of WFO IDs in some sensible order...

$offset = 0;

while(true){

    $response = $mysqli->query("SELECT distinct(wfo_id)FROM wfo_facets.wfo_scores order by wfo_id limit 100 offset $offset");
    if ($response->num_rows == 0) break;

    while($row = $response->fetch_assoc()){
        echo "{$row['wfo_id']}\t";
        if(
            WfoFacets::indexTaxon($row['wfo_id'], $start)
        ){
            echo "done.\n";
        }else{
            echo "failed.\n";
        }
    }

    $offset = $offset + 100;

}
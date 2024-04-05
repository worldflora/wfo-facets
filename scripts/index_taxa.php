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

    $response = $mysqli->query("SELECT distinct(wfo_id)FROM wfo_facets.wfo_scores where wfo_id > 'wfo-0001343609' order by wfo_id limit 100 offset $offset");
    if ($response->num_rows == 0) break;

    echo "--- Page $offset ----\n";

    $first = true; // we need to act on the first of some pages
    while($row = $response->fetch_assoc()){
        echo "{$row['wfo_id']}\t";

        // we commit at the first one of each page
        // and all on the last page
        // easiest way to work out when
        if($response->num_rows < 100 || $first){
            $commit = true;
        }else{
            $commit = false;
        }

        if(
            WfoFacets::indexTaxon($row['wfo_id'], $start, $commit)
        ){
            $c = $commit ? "commit": "no-commit";
            echo "$c\tdone.\n";
        }else{
            echo "$c\tfailed.\n";
        }
        $first = false; // no longer the first one
    }

    $offset = $offset + 100;
    

}
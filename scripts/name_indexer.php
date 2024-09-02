<?php

require_once('../config.php');
require_once('../include/SolrIndex.php');
require_once('../include/WfoFacets.php');

// the name indexer looks at recently added scores 
// and indexes the associated names ids

$index = new SolrIndex();
$solr_docs = array();

$response = $mysqli->query("SELECT distinct(wfo_id) FROM wfo_facets.wfo_scores where created > CURRENT_DATE() - INTERVAL 1 DAY");

while($row = $response->fetch_assoc()){

    //echo "{$row['wfo_id']}\n";
    $solr_docs[] = WfoFacets::getTaxonIndexDoc($row['wfo_id']);

    if(count($solr_docs) > 100){
        echo "Saving ...\n";
        $solr_response = $index->saveDocs($solr_docs, true);
        $solr_docs = array();
    }

}
echo "Saving last one ...\n";
$solr_response = $index->saveDocs($solr_docs, true);
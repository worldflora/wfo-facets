<?php

require_once('../config.php');
require_once('../include/SolrIndex.php');
require_once('../include/WfoFacets.php');

// the taxon indexer works through all the accepted taxa 
// in the index and indexes them.
// used after major changes or when a new classification
// has been deployed.

$page_size = 100;
$offset = 0;
$index = new SolrIndex();
$updated_docs = array();
$start = new DateTime();

$solr_query = array(
    'query' => 'genus_string_s:Rhododendron',
    'filter' => array("classification_id_s:" . WFO_DEFAULT_VERSION, "role_s:accepted"),
    'fields' => array("wfo_id_s", "full_name_string_plain_s"),
    'limit' => $page_size,
    'offset' => $offset,
    'sort' => 'id asc'
); 

while(true){
    
    // get the next page
    $solr_query['offset'] = $offset;
    $solr_response = $index->getSolrResponse($solr_query);

    echo "Page starts $offset\n";

    $counter = 0;
    foreach ($solr_response->response->docs as $doc) {
        $counter++;
        echo "\t$counter\t{$doc->wfo_id_s}\t{$doc->full_name_string_plain_s}\t";
        $updated_docs[] = WfoFacets::getTaxonIndexDoc($doc->wfo_id_s);
        echo "done\n";
    }

    echo "Page ends\n\n";

    // save this page
    $index->saveDocs($updated_docs, true);
    $updated_docs = array();
    $offset += $page_size;

    // report progress
    $now = new DateTime();
    $diff = $start->diff($now);
    echo "Elapse time:\t";
    echo $diff->format('%a days, %h hours, %i minutes, %s seconds');
    echo "\n";
    $diff_secs = $now->getTimestamp() - $start->getTimestamp();
    $average_taxon_time = $diff_secs/$offset;
    $total_duration = floor($solr_response->response->numFound * $average_taxon_time);
    $eta = clone $start;
    $eta->modify("+{$total_duration} seconds");
    echo "ETA:\t\t";
    echo $eta->format(DATE_ATOM);
    echo "\n\n";

    // do the next page or stop
    if($offset > $solr_response->response->numFound) break;

}

$mysqli->query("INSERT INTO indexing_log (`kind`, `count`, `duration`) VALUES ('taxon',{$solr_response->response->numFound}, $total_duration);");
echo $mysqli->error;

echo "Finished all\n";
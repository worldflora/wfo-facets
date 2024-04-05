<?php

/*

This will add the synonym names to the accepted taxa
in the index and thus enable searching for taxa by 
both their accepted names and their synonyms

php -d memory_limit=2G augment_accepted.php

*/
require_once("../config.php");
require_once('../include/SolrIndex.php');
$index = new SolrIndex();

// we need to page this
$start_index = 0;
$page_size = 10000;
while(true){

    echo "Starting page at index $start_index\n";

    // get a page worth of accepted taxa
    $filters = array();
    $filters[] = 'classification_id_s:' . WFO_DEFAULT_VERSION;

    $query = array(
        'query' => 'role_s:accepted',
        'sort' => 'id asc',
        'filter' => $filters,
        'offset' => $start_index,
        'limit' => $page_size
    );

    $reply = $index->getSolrResponse($query);

    // get out of here if we don't have any left to process
    if(!isset($reply->response->docs) || count($reply->response->docs) == 0 ){
        print_r($reply);
        break;
    }else{
        $start_index = $start_index + $page_size;
    } 

    // we are going to change them inplace and send them back
    $docs = $reply->response->docs;

    for ($i=0; $i < count($docs); $i++) { 
        
        echo "{$docs[$i]->id}\t";

        $docs[$i]->all_names_alpha_ss = array();
        $docs[$i]->all_names_alpha_ss[] = $docs[$i]->full_name_string_alpha_s; // include self
        unset($docs[$i]->_version_);

        // get the synonyms for the taxon
        $query = array(
            'query' => "role_s:synonym AND accepted_id_s:{$docs[$i]->id}",
            'sort' => 'id asc',
            'fields' => array('full_name_string_alpha_s'),
            'limit' => 1000 // shouldn't have more than this!
        );

        $syn_docs = $index->getSolrDocs($query);

        echo count($syn_docs);
        echo "\n";

        // add them to the accepted 
        foreach($syn_docs as $syn_doc){
            $docs[$i]->all_names_alpha_ss[] = $syn_doc->full_name_string_alpha_s;
        }

    }

    // put the docs to the index

    echo "---- saving page ... \t";
    $index->saveDocs($docs);
    echo "done\n";

    // do the next page.

}
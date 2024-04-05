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
    //$filters[] = 'rank_s:genus';

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

        $docs[$i]->all_names_ss = array();
        $docs[$i]->deprecated_names_ss = array();
        $docs[$i]->unplaced_names_ss = array('banana');


        $docs[$i]->all_names_ss[] = $docs[$i]->full_name_string_plain_s; // include self
        unset($docs[$i]->_version_);

        // get the synonyms for the taxon
        $query = array(
            'query' => "role_s:synonym AND accepted_id_s:{$docs[$i]->id}",
            'sort' => 'id asc',
            'fields' => array('full_name_string_plain_s'),
            'limit' => 1000 // shouldn't have more than this!
        );

        $syn_docs = $index->getSolrDocs($query);

        echo count($syn_docs);
        echo "\t";

        // add them to the accepted 
        foreach($syn_docs as $syn_doc){
            $docs[$i]->all_names_ss[] = $syn_doc->full_name_string_plain_s;
        }

        // if this is a genus we add the unplaced names with this genus name
        if($docs[$i]->rank_s == 'genus'){

            echo "genus\t";
            
            $genus_name = $docs[$i]->name_string_s;

            $query = array(
                'query' => "(role_s:unplaced OR role_s:deprecated) AND genus_string_s:{$genus_name}",
                'sort' => 'id asc',
                'fields' => array('full_name_string_plain_s', 'role_s'),
                'limit' => 10000 // shouldn't have more than this!
            );

            $extra_docs = $index->getSolrDocs($query);

            // add them to the accepted 
            foreach($extra_docs as $doc){
                if($doc->role_s == 'deprecated'){
                    $docs[$i]->deprecated_names_ss[] = $doc->full_name_string_plain_s;
                }else{
                    $docs[$i]->unplaced_names_ss[] = $doc->full_name_string_plain_s;
                }
            }
            echo count($docs[$i]->unplaced_names_ss);
            echo "\t";
            echo count($docs[$i]->deprecated_names_ss);

        }
        echo "\n";

    }

    // put the docs to the index

    echo "---- saving page ... \t";
    $index->saveDocs($docs);
    echo "done\n";

}
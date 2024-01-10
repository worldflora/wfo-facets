<?php

/*
    This script will make sure there is an up to date copy of the
    wikidata cache in the SOLR index so that the client side doesn't
    have to keep calling us or maintain anything themselves.
*/

require_once('../config.php');
require_once('../include/SolrIndex.php');
require_once('../include/WfoFacets.php');
require_once('../include/WikiItem.php');

// for now we just run through the whole lot and update/overwrite
// in future we might make this efficient by just doing ones that have 
// changed recently.

$solr = new SolrIndex();

$response = $mysqli->query("SELECT q_number FROM wiki_cache;");
$rows = $response->fetch_all(MYSQLI_ASSOC);
$response->close();

foreach($rows as $row){

    $item = WikiItem::getWikiItem('Q' . $row['q_number']);

    $doc = array();
    $doc['id'] = $item->getQNumber();
    $doc['kind_s'] = 'wiki_cache';
    
    // we have a default label in english
    $doc['label_s'] = $item->getLabel();

    $labels = $item->getLabels();
    foreach($labels as $lang => $label){
        $doc["label_{$lang}_s"] = $label;
    }

    // default description in english
    $doc['description_s'] = $item->getDescription();

    $descriptions = $item->getDescriptions();
    foreach($descriptions as $lang => $d){
        $doc["description_{$lang}_s"] = $d;
    } 

    // if this is a facet then we should list its values
    $response = $mysqli->query("SELECT value_id FROM facets WHERE facet_id = {$item->getId()}");
    if($response->num_rows > 0){
        $doc['facet_values'] = array();
        while($fv_row = $response->fetch_assoc()){
            $doc['facet_values'][] = 'Q' . $fv_row['value_id'];
        }
    }

    $solr_response = $solr->saveDoc($doc);

    echo "Q{$row['q_number']}\t{$item->getLabel()}\t{$solr_response->error}\n";
}
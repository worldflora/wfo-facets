<?php

require_once('../config.php');
//require_once('header.php');
require_once('../includes/SolrIndex.php');
require_once('../includes/WfoFacets.php');

$wfo_id = $_GET['wfo_id'];

WfoFacets::indexTaxon($wfo_id);



/*
$index = new SolrIndex();
$solr_doc = $index->getDoc($wfo_id);

// remove the existing facet properties
foreach($solr_doc as $prop => $val){
    if(preg_match('/^wfo_facet_/', $prop)) unset($solr_doc->{$prop});
}
unset($solr_doc->wfo_facets_t);

// get all the values to change

$sql = "SELECT fn.title as facet_title, fn.description as facet_description, fv.title as facet_value_title, fv.description as facet_value_description, s.negated 
        FROM wfo_scores as s
        JOIN facet_values as fv on s.facet_value_id = fv.id
        JOIN facet_names as fn on fv.name_id = fn.id 
        WHERE wfo_id = '$wfo_id'
        ORDER BY fn.title, fv.title;";

$response = $mysqli->query($sql);
$scores = $response->fetch_all(MYSQLI_ASSOC);

$current_facet = '';
$facets_text = array(); // keep all the text associated with facets so we can free text search it
foreach($scores as $score){

    // are we on a new facet?
    if($score['facet_title'] != $current_facet){
        $current_facet = $score['facet_title'];
        $facet_field_name = 'wfo_facet_' .  $current_facet . '_ss';
        $facet_text[] = $score['facet_title'];
        $facet_text[] = $score['facet_description'];
        $solr_doc->{$facet_field_name} = array();
    }

    if(!in_array($score['facet_value_title'], $solr_doc->{$facet_field_name})){
        $solr_doc->{$facet_field_name}[] = $score['facet_value_title'];
        $facet_text[] = $score['facet_value_title'];
        $facet_text[] = $score['facet_value_description'];
    }

   print_r($score);

}

// for free text searching
$solr_doc->wfo_facets_t = implode(' | ', $facet_text);

$index->saveDoc($solr_doc);

*/
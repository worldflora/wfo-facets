<?php

require_once('../config.php');
//require_once('header.php');
require_once('../includes/SolrIndex.php');

/**
 * A root class for the application
 * 
 */
class WfoFacets{


    /**
     * Does the full monty to index a taxon
     * taking care of the hierarchy and negation
     */
    public static function indexTaxon($wfo_id){

        global $mysqli;

        $index = new SolrIndex();

        // this gets the heirarchy from 
        $graph_query = "{
            taxonNameById(nameId: \"$wfo_id\") {
                id
                currentPreferredUsage {
                id
                hasName {
                    id
                    fullNameStringPlain
                }
                path{
                    hasName{
                    id
                    fullNameStringPlain
                    }
                }
                }
            }
        }";

        // we use the index as a general curl wrapper as it knows about this stuff
        $payload = json_encode((object)array('query' => $graph_query, 'variables' => null));
        $response = $index->curlPostJson(PLANT_LIST_GRAPHQL_URI, $payload);

        $body = json_decode($response->body);
        
        if($body->data->taxonNameById->currentPreferredUsage){

            $ancestors = $body->data->taxonNameById->currentPreferredUsage->path;

            $path_wfos = array();

            // add self to start if we are a synonym
            if($body->data->taxonNameById->currentPreferredUsage->hasName->id != $wfo_id){
                $path_wfos[] = $wfo_id;
            }
            foreach($ancestors as $anc){
                $path_wfos[] = $anc->hasName->id;
            }

            // start from the root of the tree
            $path_wfos = array_reverse($path_wfos);
        
        
        }else{

            // we have no relations :(
            $path_wfos = array($wfo_id);

        }
        // now we have all the names/taxa in order from top to bottom

        // get the scoring for each one and add or remove it as required.
        $my_scores = array();
        foreach($path_wfos as $wfo){

            echo "<hr/>";
            echo $wfo;
            

            $sql = "SELECT fn.`title` as 'facet_name', fn.description as 'facet_description', fv.id as facet_value_id, fv.`title` as 'value_name',  fv.`description` as value_description, s.negated 
                FROM 
                wfo_facets.wfo_scores as s 
                join wfo_facets.facet_values as fv on fv.id = s.facet_value_id
                join wfo_facets.facet_names as fn on fn.id = fv.name_id
                where s.wfo_id = '$wfo'";
            $response = $mysqli->query($sql);
            $facets = $response->fetch_all(MYSQLI_ASSOC);
              
            foreach($facets as $facet){
                if(!$facet['negated']){
                    // They have it
                    $my_scores[$facet['facet_value_id']] = $facet;
                    
                }else{
                    // they don't have this facet
                    unset($my_scores[$facet['facet_value_id']]);
                }

            }

        }


        // now we need to actually update the index
        $index = new SolrIndex();
        $solr_doc = $index->getDoc($wfo_id);

        // remove the existing facet properties
        foreach($solr_doc as $prop => $val){
            if(preg_match('/^wfo_facet_/', $prop)) unset($solr_doc->{$prop});
        }
        unset($solr_doc->wfo_facets_t);

        $facets_text = array(); // keep all the text associated with facets so we can free text search it
        foreach($my_scores as $score){


            $facet_field_name = 'wfo_facet_' . $score['facet_name'] . '_ss';

            // if we don't have this facet yet then add it.
            if(!isset($solr_doc->{$facet_field_name})){
                $solr_doc->{$facet_field_name} = array();
                $facets_text[] = $score['facet_name'];
                $facets_text[] = $score['facet_description'];
            }

            // add the facet value
            $solr_doc->{$facet_field_name}[] = $score['value_name'];
            
            // add value text field so we can find stuff with free text
            $facets_text[] = $score['value_name'];
            $facets_text[] = $score['value_description'];

        }

        // for free text searching
        $solr_doc->wfo_facets_t = implode(' | ', $facets_text);



        $index->saveDoc($solr_doc);

    }


}
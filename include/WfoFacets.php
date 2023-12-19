<?php

require_once('../config.php');
require_once('../include/WfoFacets.php');
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
            
            $sql = "SELECT f.facet_id, f.value_id, s.negated 
                from wfo_scores as s 
                join facets as f on s.value_id = f.value_id 
                where s.wfo_id = '$wfo';";
            $response = $mysqli->query($sql);
            $facets = $response->fetch_all(MYSQLI_ASSOC);
              
            foreach($facets as $facet){
                if(!$facet['negated']){
                    // They have it
                    $my_scores[$facet['facet_id'] . '-' .$facet['value_id']] = $facet;
                }else{
                    // they negate it
                    unset($my_scores[$facet['facet_id'] . '-' .$facet['value_id']]);
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

        foreach($my_scores as $score){

            $facet_field_name = 'wfo_facet_Q' . $score['facet_id'] . '_ss';

            // if we don't have this facet yet then add it.
            if(!isset($solr_doc->{$facet_field_name})){
                $solr_doc->{$facet_field_name} = array();
            }

            // add the facet value
            $solr_doc->{$facet_field_name}[] = 'Q' . $score['value_id'];
        }

        // FIXME - add facets from synonyms..

        // add in the text for the facets so that we can freetext search on it
        $facets_text = array(); 
        foreach($solr_doc as $prop => $val){
            $matches = array();
            if(preg_match('/^wfo_facet_(Q[0-9]+)_ss$/', $prop, $matches)){
                $facet_q = $matches[1];
                $facet = WikiItem::getWikiItem($facet_q);
                $facets_text[] = $facet->getIntSearchText();
                foreach($solr_doc->{$prop} as $val_q){
                    $val = WikiItem::getWikiItem($val_q);
                    $facets_text[] = $val->getIntSearchText();
                }
            };
        }
        // pop them all in the text field
        $solr_doc->wfo_facets_t = implode(' | ', $facets_text);

        $index->saveDoc($solr_doc);

    }


}
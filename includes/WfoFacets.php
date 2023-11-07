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

        // we use the index as a general curl wrapper as it knows 
        // about this stuff
        $payload = json_encode((object)array('query' => $graph_query, 'variables' => null));
        $response = $index->curlPostJson(PLANT_LIST_GRAPHQL_URI, $payload);

        $body = json_decode($response->body);
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
        
        // now we have all the names/taxa in order from top to bottom

        // get the scoring for each one and add of remove it as required.

        // nah - don't do this. We need to create a narrative with the names in.

        echo "<pre>";
        print_r($path_wfos);


    }


}
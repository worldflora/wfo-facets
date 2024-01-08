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


        // FIXME NEXT
        // this should also insert the reasoning for each facet placement.

        global $mysqli;

        $index = new SolrIndex();

        // this gets the heirarchy from graphql
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
                    hasSynonym{
                        id
                        fullNameStringPlain
                    }
                }
            }
        }";

        // we use the index as a general curl wrapper as it knows about this stuff
        $payload = json_encode((object)array('query' => $graph_query, 'variables' => null));
        $response = $index->curlPostJson(PLANT_LIST_GRAPHQL_URI, $payload);

        $body = json_decode($response->body);

        // if there is no current usage then it isn't placed
        // and we return false - no indexing
        if(!isset($body->data->taxonNameById->currentPreferredUsage) || !$body->data->taxonNameById->currentPreferredUsage ){
            return false;
        }    


        // check this is an accepted name.
        // if not then index the accepted name or nothing
        if($body->data->taxonNameById->currentPreferredUsage->hasName->id !=  $body->data->taxonNameById->id){
            return WfoFacets::indexTaxon($body->data->taxonNameById->id);
        }
        
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
            
            $sql = "SELECT f.facet_id, f.value_id, s.negated, s.source_id 
                from wfo_scores as s 
                join facets as f on s.value_id = f.value_id 
                where s.wfo_id = '$wfo';";
            $response = $mysqli->query($sql);
            $facets = $response->fetch_all(MYSQLI_ASSOC);
              
            foreach($facets as $facet){

                $facet_value_id = $facet['facet_id'] . '-' . $facet['value_id'];

                // the provenance tag is of the form  wfo-0000400164-Q47542613-1
                // i.e. This taxon is said to have it by this source.
                $provenance_tag = "{$wfo}-Q{$facet['source_id']}-" . ($facet['negated'] == 1 ? 'neg': 'add');

                // Is it already recorded?
                if(isset($my_scores[$facet_value_id])){
                    // add the source as an extra
                    $my_scores[$facet_value_id]['prov'][] = $provenance_tag;
                    // update the negation status
                    $my_scores[$facet_value_id]['negated'] = $facet['negated'];
                }else{
                    // setting it up for the first time so create the provenance array
                    $facet['prov'] = array($provenance_tag);
                    unset($facet['source_id']);
                    $my_scores[$facet_value_id] = $facet;
                }

            }

        }

        // now we do the direct synonyms
        $synonyms = $body->data->taxonNameById->currentPreferredUsage->hasSynonym;
        foreach ($synonyms as $syn) {
            
            $sql = "SELECT f.facet_id, f.value_id, s.negated 
                    from wfo_scores as s 
                    join facets as f on s.value_id = f.value_id 
                    where s.wfo_id = '{$syn->id}';";
            $response = $mysqli->query($sql);
            $facets = $response->fetch_all(MYSQLI_ASSOC);

            foreach($facets as $facet){
                // we just add the non-negated ones
                // the negated ones are ignored
                if(!$facet['negated']){

                    $facet_value_id = $facet['facet_id'] . '-' . $facet['value_id'];
                    $provenance_tag = "{$syn->id}-Q{$facet['source_id']}-add"; // only add here

                    // Is it already recorded?
                    if(isset($my_scores[$facet_value_id])){
                        // add the source as an extra
                        $my_scores[$facet_value_id]['prov'][] = $provenance_tag;
                    }else{
                        // setting it up for the first time so create the provenance array
                        $facet['prov'] = array($provenance_tag);
                        unset($facet['source_id']);
                        $my_scores[$facet_value_id] = $facet;
                    }
                    
                }
            }
        }

        // now we need to actually update the index
        $index = new SolrIndex();
        $solr_doc = $index->getDoc($wfo_id);

        // remove the existing facet properties
        foreach($solr_doc as $prop => $val){
            if(preg_match('/^Q[0-9]+_ss$/', $prop)) unset($solr_doc->{$prop}); // facet field
            if(preg_match('/^Q[0-9]+_provenance_ss$/', $prop)) unset($solr_doc->{$prop}); // facet provenance field
            if(preg_match('/^Q[0-9]+_t$/', $prop)) unset($solr_doc->{$prop}); // text version
        }

        foreach($my_scores as $score){

            $facet_field_name = "Q{$score['facet_id']}_ss";
            $facet_prov_field_name = "Q{$score['facet_id']}_provenance_ss";

            // if we don't have this facet yet then add it.
            if(!isset($solr_doc->{$facet_field_name})){
                $solr_doc->{$facet_field_name} = array();
            }

            // add the facet value if it isn't negated
            if(!$score['negated']){
                $solr_doc->{$facet_field_name}[] = 'Q' . $score['value_id'];
            }

            // add it to the provenance field whether or not it is negated
            // add the value Q id to the start. The provenance tag now goes;
            // valueQ-nameWFO-sourceQ-add or valueQ-nameWFO-sourceQ-neg
            foreach($score['prov'] as $prov){
                $solr_doc->{$facet_prov_field_name}[] = "Q{$score['value_id']}-" . $prov;
            }
            
            
        }

        // add in the text for the facets so that we can freetext search on it
        $facets_text = array(); 
        foreach($solr_doc as $prop => $val){
            $matches = array();
            if(preg_match('/^(Q[0-9]+)_ss$/', $prop, $matches)){
                $facet_q = $matches[1];
                $facet = WikiItem::getWikiItem($facet_q);
                $facets_text[] = $facet->getIntSearchText();
                foreach($solr_doc->{$prop} as $val_q){
                    $val = WikiItem::getWikiItem($val_q);
                    $facets_text[] = $val->getIntSearchText();
                }
                // pop all the text in field for this property
                $facet_text_field_name = "{$facet_q}_t";
                $solr_doc->{$facet_text_field_name} = implode(' | ', $facets_text);
            };// a facet property
        }// each property in solr doc

        $index->saveDoc($solr_doc);

        return true;

    }


}
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
     * 
     * @param since unix timestamp. Will only index 
     * documents older than this if supplied.
     * This is needed to stop a taxon being indexed for
     * everyone one of its synonyms.
     */
    public static function indexTaxon($wfo_id, $since = false, $commit = true){

        $solr_doc = WfoFacets::getTaxonIndexDoc($wfo_id, $since, $commit);
        if($solr_doc){
            $index = new SolrIndex();
            $index->saveDoc($solr_doc, $commit);
            return true;
        }else{
            return false;
        }

    }

     public static function getTaxonIndexDoc($wfo_id, $since = false, $commit = true){

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
            return WfoFacets::indexTaxon($body->data->taxonNameById->currentPreferredUsage->hasName->id);
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

        // get the solr document to see if it actually needs updating
        $solr_doc = $index->getDoc($wfo_id);

        // if we have set a start time and there is one in the solr doc and the 
        // solr doc is newer than the start time then don't index it - we've just done it!
        if($since && isset($solr_doc->facets_last_indexed_i) && $solr_doc->facets_last_indexed_i > $since){
            return true;
        }

        // get the scoring for each one and add it as required.
        $my_scores = array();
        foreach($path_wfos as $wfo){

            $sql = "SELECT 
                f.id as facet_id,
                f.`name` as facet_name,
                fv.id as facet_value_id, 
                fv.`name` as facet_value_name,
                s.id as source_id,
                s.`name` as source_name
                FROM wfo_scores as ws
                JOIN facet_values AS fv ON ws.value_id = fv.id
                JOIN facets AS f ON fv.facet_id = f.id
                JOIN sources AS s ON ws.source_id = s.id
                WHERE ws.wfo_id = '{$wfo}';";

            $response = $mysqli->query($sql);
            $facets = $response->fetch_all(MYSQLI_ASSOC);
              
            foreach($facets as $facet){

                $facet_value_id = $facet['facet_id'] . '-' . $facet['facet_value_id'];

                // the provenance tag 
                $provenance_tag = "{$wfo}-s-{$facet['source_id']}-" . ($wfo == $wfo_id ? 'direct' : 'ancestor');

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

        // now we do the direct synonyms
        $synonyms = $body->data->taxonNameById->currentPreferredUsage->hasSynonym;
        foreach ($synonyms as $syn) {

            $sql = "SELECT 
                f.id as facet_id,
                f.`name` as facet_name,
                fv.id as facet_value_id, 
                fv.`name` as facet_value_name,
                s.id as source_id,
                s.`name` as source_name
                FROM wfo_scores as ws
                JOIN facet_values AS fv ON ws.value_id = fv.id
                JOIN facets AS f ON fv.facet_id = f.id
                JOIN sources AS s ON ws.source_id = s.id
                WHERE ws.wfo_id = '{$syn->id}';";
            
            $response = $mysqli->query($sql);
            $facets = $response->fetch_all(MYSQLI_ASSOC);

            foreach($facets as $facet){

                $facet_value_id = $facet['facet_id'] . '-' . $facet['facet_value_id'];
                $provenance_tag = "{$syn->id}-s-{$facet['source_id']}-synonym"; // only add here

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

        // now we need to actually update the index
  //      echo "<pre>";
  //      print_r($my_scores);
//exit;
        // remove the existing facet properties
        foreach($solr_doc as $prop => $val){
            if(preg_match('/^wfo-f-.+_ss$/', $prop)) unset($solr_doc->{$prop}); // facet field
            if(preg_match('/^wfo-fv-.+_provenance_ss$/', $prop)) unset($solr_doc->{$prop}); // facet provenance field
            if(preg_match('/^wfo-f-.+_t$/', $prop)) unset($solr_doc->{$prop}); // text version
        }

        // actually add them in
        foreach($my_scores as $score){

            $facet_field_name = "wfo-f-{$score['facet_id']}_ss";
            $facet_prov_field_name = "wfo-fv-{$score['facet_value_id']}_provenance_ss";
            $facet_text_field_name = "wfo-f-{$score['facet_id']}_t";

            // if we don't have this facet yet then add 
            // in the required fields
            if(!isset($solr_doc->{$facet_field_name})){
                $solr_doc->{$facet_field_name} = array();
                $solr_doc->{$facet_prov_field_name} = array();
                $solr_doc->{$facet_text_field_name} = $score['facet_name'] . " : ";
            }

            // add the facet value
            $solr_doc->{$facet_field_name}[] = 'wfo-fv-' . $score['facet_value_id'];

            // add it to the provenance field
            foreach($score['prov'] as $prov){
                $solr_doc->{$facet_prov_field_name}[] = $prov;
            }
            
            // add it to the text field so we can 
            $solr_doc->{$facet_text_field_name} .= $score['facet_value_name'] . " : ";
            
            
        }

        // flag when we indexed it
        $solr_doc->facets_last_indexed_i = time();

        return $solr_doc;


    }




    /**
     * Gets all the facets and their values
     * and adds them to the index in a single
     * commit.
     * 
     */
    public static function indexFacets(){

        global $mysqli;

        $solr_docs = array();

        // we create solr docs as near as damn it 
        // in the query
        $response = $mysqli->query("SELECT 
            id as db_id,
            concat('wfo-f-', id) as id,
            'wfo-facet' as kind, 
            `name` as 'name', 
            `description` as 'description',
            `link_uri` as 'link_uri'
            FROM facets ORDER BY `name`");
        $facets = $response->fetch_All(MYSQLI_ASSOC);
        $response->close();


        foreach($facets as $facet){

            // hold on to the db id
            $facet_id = $facet['db_id'];
            unset($facet['db_id']);
            
            // add the facet values for this facet
            $response = $mysqli->query("SELECT 
                concat('wfo-fv-', id) as id,
                'wfo-facet-value' as kind, 
                `name` as 'name', 
                `description` as 'description',
                `link_uri` as 'link_uri',
                concat('wfo-f-', `facet_id`) as facet_id 
                FROM facet_values 
                WHERE facet_id = $facet_id
                ORDER BY `name`");
            $facet_values = $response->fetch_All(MYSQLI_ASSOC);
            $facet['facet_values'] = array();
            foreach ($facet_values as $fv) {
               $facet['facet_values'][$fv['id']] = $fv;
            }
            $response->close();
            
            $solr_docs[] = (object)array('id'=> $facet['id'], 'json_t' => json_encode((object)$facet));

        }

        $index = new SolrIndex();
        $response = $index->saveDocs($solr_docs, true);
        echo "<pre>";
        print_r($response);

    }

    public static function indexSources(){

        global $mysqli;

        $solr_docs = array();

        // we create solr docs as near as damn it 
        // in the query
        $response = $mysqli->query("SELECT 
            concat('wfo-fs-', id) as id,
            'wfo-facet-source' as kind, 
            `name` as 'name', 
            `description` as 'description',
            `link_uri` as link_uri
            FROM sources ORDER BY `name`");
        $sources = $response->fetch_All(MYSQLI_ASSOC);
        $response->close();

        foreach ($sources as $s) {
            $solr_docs[] = (object)array('id'=> $s['id'], 'json_t' => json_encode((object)$s));
        }

        $index = new SolrIndex();
        $response = $index->saveDocs($solr_docs, true);

    }


    public static function getFacetsFromDoc($solrDoc){

        $index = new SolrIndex();
        $out = array();

        foreach($solrDoc as $prop => $val){
            $matches = array();
            if(preg_match('/^(wfo-f-[0-9]+)_s/', $prop, $matches)){

                // set up the facet
                $prop_prefix = $matches[1];
                $out[$prop_prefix] = array();
                $out[$prop_prefix]['facet_values'] = array();

                // add the values
                foreach ($solrDoc->{$prop} as $fv) {
                    $out[$prop_prefix]['facet_values'][$fv] = array();
                    $out[$prop_prefix]['facet_values'][$fv]['provenance'] = array();

                    // and their provenance 
                    $prov_prop = $fv . '_provenance_ss';
                    foreach($solrDoc->{$prov_prop} as $prov){
                        $out[$prop_prefix]['facet_values'][$fv]['provenance'][]  = $prov;
                    }

                }
                
            }
        } // fin building the structure

        // if we've not been indexed then we are empty
        if(!$out) return $out;

        // populate it with names
        $query = array('query' => "id:(" . implode(' OR ', array_keys($out)) . ")");
        $facet_docs = $index->getSolrDocs((object)$query);
        foreach ($facet_docs as $fd){
           $meta = json_decode($fd->json_t);

           $out[$fd->id]['meta']['id'] = $meta->id;
           $out[$fd->id]['meta']['name'] = $meta->name;
           $out[$fd->id]['meta']['description'] = trim($meta->description);
           $out[$fd->id]['meta']['link_uri'] = trim($meta->link_uri);

           foreach (array_keys($out[$fd->id]['facet_values']) as $fv_key) {
                
                $out[$fd->id]['facet_values'][$fv_key]['meta'] = $meta->facet_values->{$fv_key};

                // break down the provenance
                $new_provs = array();
                foreach ($out[$fd->id]['facet_values'][$fv_key]['provenance'] as $prov) {
                        //wfo-4000019729-s-37-ancestor
                        $matches = array();
                        preg_match('/^(wfo-[0-9]{10})-s-([0-9]+)-([a-z]+)$/', $prov, $matches);

                        $wfo = $matches[1];
                        $name_doc = $index->getDoc($wfo);

                        $source_id  = $matches[2];
                        $source_doc = $index->getDoc('wfo-fs-'. $source_id);
                        $source_doc = json_decode($source_doc->json_t);

                        $new_provs[] = array(
                            'wfo_id' => $wfo,
                            'full_name_html' => $name_doc->full_name_string_html_s,
                            'full_name_plain' => $name_doc->full_name_string_plain_s,
                            'source_id' => $source_id,
                            'source_name' => $source_doc->name,
                            'kind' => $matches[3],
                        );
                }
                $out[$fd->id]['facet_values'][$fv_key]['provenance'] = $new_provs;
           
            }
           
        }


        return $out;

    }
}
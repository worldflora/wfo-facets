<?php

// called to index a single name
// redirects after call

// FIXME - MUST BE GOD TO DO THIS

require_once('../config.php');
require_once('../include/SolrIndex.php');
require_once('../include/WfoFacets.php');

require_once('header.php');


$source_id = @$_GET['source_id'];
$source_id = (int)$source_id;
$offset = @$_GET['offset'];
if(!$offset) $offset =0;



$response = $mysqli->query("SELECT wfo_id FROM `snippets` WHERE source_id = $source_id ORDER BY wfo_id LIMIT 100 OFFSET $offset");

echo "<h2>Indexing snippet source taxa</h2>";

if($response->num_rows == 0){

    // we need to index the source metadata too or it won't be found
    WfoFacets::indexSnippetSources();
    WfoFacets::indexSnippets();

    // declare we are complete
    echo "<p>Indexing complete.</p>";
    echo "<script>window.location = \"snippet_source.php?source_id=$source_id\"</script>";

}else{

    $solr_docs = array();
    while($row = $response->fetch_assoc()){
        $solr_docs[] =  WfoFacets::getTaxonIndexDoc($row['wfo_id']); 
        //echo "<p>{$row['wfo_id']}</p>";
    }

    // save those docs to the index and commit them
    $index = new SolrIndex();
    $index->saveDocs($solr_docs, true);
    
    // tell them what is going on and redirect.
    echo "<p>Indexing in progress. ". number_format($offset, 0)  ." completed.</p>";

    $offset += 100;

    $uri = "snippet_source_taxa_index.php?source_id=" . $source_id . "&offset=$offset";
    echo "<script>window.location = \"$uri\"</script>";
    
}


require_once('footer.php');

//WfoFacets::indexFacetSources();

//header('Location: snippets.php');
<?php
require_once('../config.php');
require_once('../include/SolrIndex.php');
require_once('../include/ExporterFacets.php');
require_once('../include/WfoFacets.php');

$out = (object)array();

// set things up
if(@$_SESSION['exporter']){
    $exporter = unserialize($_SESSION['exporter']);
}else{
    // we are being called for the first time
    $include_syns = @$_GET['include_synonyms'] && $_GET['include_synonyms'] == 'true'; 
    $exporter = new ExporterFacets($_GET['source_id'], $include_syns);
}

// do a page
$exporter->page();

$out->message = $exporter->getMessage();
$out->finished = $exporter->finished();

// remove the exporter if it has finished
if($out->finished){
    unset($_SESSION['exporter']);
}else{
    // save the new version to the session
    $_SESSION['exporter'] = serialize($exporter);
}

sleep(1);

header('Content-Type: application/json');
echo json_encode($out, JSON_PRETTY_PRINT);
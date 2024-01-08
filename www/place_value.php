<?php

require_once('../config.php');

$value_id = $_GET["value_id"];
$facet_q = $_GET["facet_id"];
$matches = array();
if(preg_match('/^Q([0-9]+)$/', $facet_q, $matches)){
    $facet_id = $matches[1];
}else{
    echo "<p>'$facet_q' doesn't look like a Q number to me.</p>";
    exit;
}

$mysqli->query("INSERT IGNORE INTO `facets` (`facet_id`, `value_id`) VALUES ({$facet_id},{$value_id})" );
if($mysqli->error){
    throw new ErrorException($mysqli->error);
    exit;
}else{
    header("Location: index.php");
}
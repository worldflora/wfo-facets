<?php

require_once('../config.php');
require_once('../includes/WfoFacets.php');
require_once('../includes/Facet.php');

echo "Extract countries from WCVP\n";


// get a list of all the countries from the facets
$response = $mysqli->query("SELECT * FROM wfo_facets.facet_values where name_id = 2");
$countries = $response->fetch_all(MYSQLI_ASSOC);
$response->close();

foreach($countries as $country){

    // the facet to map
    $facet = Facet::getFacetByNameValue('iso_country', $country['title']); 

    // get a list of rows containing the word
    $country_safe = $mysqli->real_escape_string($country['title']);
    $response = $mysqli->query("SELECT wfo_id
        FROM kew.wcvp 
        WHERE wfo_id is not null
        AND geographic_area 
        like '%$country_safe%'");
    $rows = $response->fetch_all(MYSQLI_ASSOC);
    $response->close();

    
    // for each word we add a score.
    foreach ($rows as $row) {
        $wfo_id = $row['wfo_id'];
        $mysqli->query("INSERT INTO wfo_scores (wfo_id, facet_value_id, source_id) VALUES ('$wfo_id', {$facet->getId()}, 2)");
        if($mysqli->error){
            echo $mysqli->error;
            exit;
        }
    }
    
}

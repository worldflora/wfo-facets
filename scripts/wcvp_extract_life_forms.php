<?php

require_once('../config.php');
require_once('../includes/WfoFacets.php');
require_once('../includes/Facet.php');

echo "Create new facets from a csv file\n";

$map = get_mapping();

foreach($map as $word => $facet_value){

    // the facet to map
    $facet = Facet::getFacetByNameValue('life-form', $facet_value);  

    if(!$facet){
        echo "No facet for '$facet_value'\n";    
        continue;
    }

    // get a list of rows containing the word
    $word_safe = $mysqli->real_escape_string($word);
    $response = $mysqli->query("SELECT wfo_id
        FROM kew.wcvp 
        WHERE wfo_id is not null
        AND lifeform_description 
        like '%$word_safe%'");
    $rows = $response->fetch_all(MYSQLI_ASSOC);
    $response->close();

    
    // for each word we add a score.
    foreach ($rows as $row) {
        $wfo_id = $row['wfo_id'];
        $mysqli->query("INSERT INTO wfo_scores (wfo_id, facet_value_id, source_id) VALUES ('$wfo_id', {$facet->getId()}, 1)");
        if($mysqli->error){
            echo $mysqli->error;
            exit;
        }
    }
    

}

function get_mapping(){

    return array(
        "annual" => "annual",
        "bamboo" => "bamboo",
        "biennial" => "biennial",
        "bulb" => "bulb",
        "bulbous" => "bulb",
        "Caudex" => "caudiciform",
        "caudex" => "caudiciform",
        "climber" => "climber",
        "climbing" => "climber",
        "epiphyte" => "epiphyte",
        "epiphytic" => "epiphyte",
        "geophyte" => "geophyte",
        "helophyte" => "helophyte",
        "hemiepiphyte" => "epiphyte",
        "hemiepiphytic" => "epiphyte",
        "hemiepiphyte" => "hemiepiphyte",
        "hemiepiphytic" => "hemiepiphyte",
        "hemillithophytic" => "hemillithophyte",
        "hemiparasite" => "parasite",
        "hemiparasitic" => "parasite",
        "hemiparasite" => "hemiparasite",
        "hemiparasitic" => "hemiparasite",
        "herbaceous" => "herbaceous",
        "holomycotroph" => "holomycotroph",
        "holomycotrophic" => "holomycotrophic",
        "holoparasite" => "parasite",
        "holoparasitic" => "parasite",
        "holoparasite" => "holoparasite",
        "holoparasitic" => "holoparasite",
        "hydroannual" => "aquatic",
        "hydrogeophyte" => "aquatic",
        "hydroperennial" => "aquatic",
        "hydrophyte" => "aquatic",
        "hydroshrub" => "aquatic",
        "hydrosubshrub" => "aquatic",
        "hydroannual" => "annual",
        "hydrogeophyte" => "geophyte",
        "hydroperennial" => "perennial",
        "hydroshrub" => "shrub",
        "hydrosubshrub" => "subshrub",
        "liana" => "liana",
        "lithophyte" => "lithophyte",
        "lithophytic" => "lithophyte",
        "monocarpic" => "monocarpic",
        "parasitic" => "parasite",
        "perennial" => "perennial",
        "pseudobulb" => "pseudobulbous",
        "pseudobulbous" => "pseudobulbous",
        "rhizomatous" => "rhizomatous",
        "rhizome" => "rhizomatous",
        "scrambling" => "scrambler",
        "semiaquatic" => "aquatic",
        "semiaquatic" => "semiaquatic",
        "semisucculent" => "succulent",
        "semisucculent" => "semisucculent",
        "shrub" => "shrub",
        "subshrub" => "subshrub",
        "succulent" => "succulent",
        "tree" => "tree",
        "tuberous" => "tuberous"
    );

}
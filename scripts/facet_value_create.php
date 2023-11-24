<?php

require_once('../config.php');
require_once('../includes/WfoFacets.php');
require_once('../includes/Facet.php');
require_once('../includes/FacetValue.php');

echo "Create a facet value\n";

$facet_id = readline("Facet ID: ");
$facet = Facet::getFacet($facet_id);
if(!$facet){
    echo "\nYou must provide the id of the facet this is a value of.";
    exit;
}else{
    echo "Adding to facet: {$facet->getTitle()}\n";
}

$title = readline("Title: ");
if(!$title){
    echo "\nYou must provide a title;";
    exit;
}
$description = readline("Description: ");
$uri = readline("URI: ");

$facet = new FacetValue($facet, $title, $description, $uri);
$id = $facet->save();
echo "\nCreated:\t$id \n";
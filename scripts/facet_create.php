<?php

require_once('../config.php');
require_once('../includes/WfoFacets.php');
require_once('../includes/Facet.php');
require_once('../includes/Source.php');

echo "Create a facet\n";

$title = readline("Title: ");
if(!$title){
    echo "\nYou must provide a title;";
    exit;
}
$description = readline("Description: ");
$uri = readline("URI: ");

$facet = new Facet($title, $description, $uri);
$id = $facet->save();
echo "\nCreated:\t$id \n";
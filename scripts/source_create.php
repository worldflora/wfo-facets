<?php

require_once('../config.php');
require_once('../includes/WfoFacets.php');
require_once('../includes/Facet.php');
require_once('../includes/Source.php');

echo "Create a new data source\n";

$title = readline("Title: ");
if(!$title){
    echo "\nYou must provide a title;";
    exit;
}
$description = readline("Description: ");
$uri = readline("URI: ");

$source = new Source($title, $description, $uri);
$id = $source->save();
echo "\nCreated:\t$id \n";
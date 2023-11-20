<?php

require_once('../config.php');
require_once('../includes/WfoFacets.php');
require_once('../includes/Facet.php');
require_once('../includes/FacetValue.php');
require_once('../includes/Source.php');

echo "Import scores\n";


if(count($argv) < 3){
    echo "You must pass id of the SOURCE and the path of the csv file to import.\n";
    exit;
}

$source_id = $argv[1];
$source = Source::getSource($source_id);
if(!$source){
    echo "\nYou must provide the id of the SOURCE of these scores.";
    exit;
}else{
    echo "Adding values to with the score: {$source->getTitle()}\n";
}

$file_path = $argv[2];



echo "Importing from: $file_path\n";

if(!file_exists($file_path)){
    echo "That path looks wrong. Can't find the file.\n";
    exit;
}
$in = fopen($file_path, 'r');
$header = fgetcsv($in);
echo "Header\n";
print_r($header);
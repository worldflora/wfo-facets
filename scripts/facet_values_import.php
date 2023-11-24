<?php

require_once('../config.php');
require_once('../includes/WfoFacets.php');
require_once('../includes/Facet.php');
require_once('../includes/FacetValue.php');

echo "Importing facet values\n";

if(count($argv) < 3){
    echo "You must pass id of the facet and the path of the csv file to import.\n";
    exit;
}

$facet_id = $argv[1];
$facet = Facet::getFacet($facet_id);
if(!$facet){
    echo "\nYou must provide the id of the facet this is a value of.";
    exit;
}else{
    echo "Adding values to facet: {$facet->getTitle()}\n";
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

while($line = fgetcsv($in)){

    $fv = new FacetValue($facet, $line[0], @$line[1], @$line[2]);
    $fv->save();

}